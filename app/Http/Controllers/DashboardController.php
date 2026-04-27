<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SaleLineItem;
use App\Models\StockRequest;
use App\Models\InventoryBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $roleNames = $user->roles->pluck('name');

        $dashboardData = $this->getSharedDashboardData();

        if ($roleNames->contains('admin')) {
            return view('admin.dashboard', array_merge($dashboardData, $this->getAdminData()));
        } elseif ($roleNames->contains('staff')) {
            return view('staff.dashboard', array_merge($dashboardData, $this->getStaffData()));
        } elseif ($roleNames->contains('pharmacist')) {
            return view('pharmacist.dashboard', array_merge($dashboardData, $this->getPharmacistData($user)));
        } else {
            return view('dashboard', $dashboardData);
        }
    }

    // -------------------------------------------------------------------------
    // Shared data — loaded for every role.
    // Cached for 5 minutes since counts rarely change second-to-second.
    // -------------------------------------------------------------------------
    private function getSharedDashboardData(): array
    {
        return Cache::remember('dashboard.shared', 300, function () {
            // Single query: get both location stock sums at once instead of two queries
            $locationStocks = InventoryBatch::query()
                ->join('inventory_locations', 'inventory_locations.id', '=', 'inventory_batches.location_id')
                ->whereIn('inventory_locations.code', ['front', 'back'])
                ->selectRaw('inventory_locations.code, SUM(inventory_batches.quantity) as total')
                ->groupBy('inventory_locations.code')
                ->pluck('total', 'code');

            // Single query: count low stock using a pure SQL HAVING clause
            // instead of fetching all products into PHP and filtering in memory
            $lowStockCount = DB::table('products')
                ->leftJoin('inventory_batches', 'inventory_batches.product_id', '=', 'products.id')
                ->selectRaw('products.id, products.reorder_level, COALESCE(SUM(inventory_batches.quantity), 0) as stock')
                ->groupBy('products.id', 'products.reorder_level')
                ->havingRaw('stock <= products.reorder_level')
                ->count();

            return [
                'patientCount'            => Patient::count(),
                'productCount'            => Product::count(),
                'frontShopStockUnits'     => (int) ($locationStocks['front'] ?? 0),
                'backInventoryStockUnits' => (int) ($locationStocks['back'] ?? 0),
                'lowStockCount'           => $lowStockCount,
                'auditCount'              => AuditLog::count(),
                'recentAudits'            => AuditLog::with('user')->latest()->limit(5)->get(),
            ];
        });
    }

    // -------------------------------------------------------------------------
    // Admin-only data. Cached for 2 minutes — charts are near-realtime enough.
    // -------------------------------------------------------------------------
    private function getAdminData(): array
    {
        return Cache::remember('dashboard.admin', 120, function () {
            $trendStart = Carbon::today()->subDays(6);
            $trendEnd   = Carbon::today();

            // Fetch sales and purchases trends in parallel-friendly separate queries
            $salesTrend = Sale::query()
                ->selectRaw('DATE(created_at) as day, SUM(total_amount) as total')
                ->whereBetween('created_at', [$trendStart->copy()->startOfDay(), $trendEnd->copy()->endOfDay()])
                ->groupBy('day')
                ->pluck('total', 'day');

            $purchaseTrend = PurchaseOrder::query()
                ->selectRaw('DATE(created_at) as day, SUM(total_cost) as total')
                ->whereBetween('created_at', [$trendStart->copy()->startOfDay(), $trendEnd->copy()->endOfDay()])
                ->groupBy('day')
                ->pluck('total', 'day');

            $trendLabels   = [];
            $salesSeries   = [];
            $purchaseSeries = [];

            for ($date = $trendStart->copy(); $date->lte($trendEnd); $date->addDay()) {
                $key             = $date->toDateString();
                $trendLabels[]   = $date->format('M d');
                $salesSeries[]   = round((float) ($salesTrend[$key] ?? 0), 2);
                $purchaseSeries[] = round((float) ($purchaseTrend[$key] ?? 0), 2);
            }

            // Stock health: single SQL query with conditional aggregation
            // instead of loading all products into PHP memory
            $stockHealth = DB::table('products')
                ->leftJoin('inventory_batches', 'inventory_batches.product_id', '=', 'products.id')
                ->selectRaw('
                    products.reorder_level,
                    COALESCE(SUM(inventory_batches.quantity), 0) as stock
                ')
                ->groupBy('products.id', 'products.reorder_level')
                ->get();

            $low      = 0;
            $normal   = 0;
            $overstock = 0;
            foreach ($stockHealth as $row) {
                $stock   = (int) $row->stock;
                $reorder = (int) $row->reorder_level;
                if ($stock <= $reorder) {
                    $low++;
                } elseif ($stock <= $reorder * 2) {
                    $normal++;
                } else {
                    $overstock++;
                }
            }

            $topMovingProducts = SaleLineItem::query()
                ->join('inventory_batches', 'inventory_batches.id', '=', 'sale_line_items.inventory_batch_id')
                ->join('products', 'products.id', '=', 'inventory_batches.product_id')
                ->selectRaw('products.name as product_name, SUM(sale_line_items.quantity) as total_sold')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_sold')
                ->limit(8)
                ->get();

            return [
                'totalRevenue'        => (float) Sale::sum('total_amount'),
                'totalPurchaseCost'   => (float) PurchaseOrder::sum('total_cost'),
                'recentSales'         => Sale::with('patient')->latest()->limit(5)->get(),
                'recentPurchaseOrders'=> PurchaseOrder::latest()->limit(5)->get(),
                'incomingDeliveries'  => PurchaseOrder::query()
                    ->whereIn('status', ['approved', 'pending'])
                    ->whereNotNull('expected_date')
                    ->orderBy('expected_date')
                    ->limit(6)
                    ->get(),
                'topMovingProducts'   => $topMovingProducts,
                'trendLabels'         => $trendLabels,
                'salesTrendSeries'    => $salesSeries,
                'purchaseTrendSeries' => $purchaseSeries,
                'stockHealthLabels'   => ['Low Stock', 'Normal Stock', 'Overstock'],
                'stockHealthSeries'   => [$low, $normal, $overstock],
            ];
        });
    }

    // -------------------------------------------------------------------------
    // Staff-only data. Cached for 2 minutes.
    // -------------------------------------------------------------------------
    private function getStaffData(): array
    {
        return Cache::remember('dashboard.staff', 120, function () {
            $trendStart = Carbon::today()->subDays(6)->startOfDay();
            $trendEnd   = Carbon::today()->endOfDay();

            $trendDays = [];
            for ($date = $trendStart->copy(); $date->lte($trendEnd); $date->addDay()) {
                $trendDays[] = $date->toDateString();
            }

            // Low stock: use subquery to avoid ONLY_FULL_GROUP_BY issues
            $lowStockItems = DB::table('products')
                ->select('products.id', 'products.name', 'products.generic_name', 'products.sku', 'products.price', 'products.reorder_level', 'products.created_at', 'products.updated_at', 'products.deleted_at')
                ->selectSub(function ($query) {
                    $query->from('inventory_batches')
                        ->selectRaw('COALESCE(SUM(quantity), 0)')
                        ->whereColumn('product_id', 'products.id');
                }, 'inventory_batches_sum_quantity')
                ->leftJoin('inventory_batches', 'inventory_batches.product_id', '=', 'products.id')
                ->groupBy('products.id', 'products.name', 'products.generic_name', 'products.sku', 'products.price', 'products.reorder_level', 'products.created_at', 'products.updated_at', 'products.deleted_at')
                ->havingRaw('inventory_batches_sum_quantity <= products.reorder_level')
                ->orderByRaw('inventory_batches_sum_quantity ASC')
                ->limit(5)
                ->get();

            $topConsumedProducts = SaleLineItem::query()
                ->join('inventory_batches', 'inventory_batches.id', '=', 'sale_line_items.inventory_batch_id')
                ->join('sales', 'sales.id', '=', 'sale_line_items.sale_id')
                ->join('products', 'products.id', '=', 'inventory_batches.product_id')
                ->whereBetween('sales.created_at', [$trendStart, $trendEnd])
                ->selectRaw('products.id, products.name, SUM(sale_line_items.quantity) as consumed')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('consumed')
                ->limit(4)
                ->get();

            // Single trend query filtered to only the top-4 product IDs
            // instead of fetching the entire table and pivoting in PHP
            $topProductIds = $topConsumedProducts->pluck('id')->all();

            $rawTrendRows = SaleLineItem::query()
                ->join('inventory_batches', 'inventory_batches.id', '=', 'sale_line_items.inventory_batch_id')
                ->join('sales', 'sales.id', '=', 'sale_line_items.sale_id')
                ->whereIn('inventory_batches.product_id', $topProductIds)
                ->whereBetween('sales.created_at', [$trendStart, $trendEnd])
                ->selectRaw('inventory_batches.product_id as product_id, DATE(sales.created_at) as sale_day, SUM(sale_line_items.quantity) as qty')
                ->groupBy('inventory_batches.product_id', 'sale_day')
                ->get();

            $qtyByProductAndDay = [];
            foreach ($rawTrendRows as $row) {
                $qtyByProductAndDay[(int) $row->product_id][$row->sale_day] = (int) $row->qty;
            }

            $trendColors             = ['#378ADD', '#1D9E75', '#EF9F27', '#7F77DD'];
            $consumptionTrendDatasets = [];
            foreach ($topConsumedProducts as $index => $topProduct) {
                $data = [];
                foreach ($trendDays as $day) {
                    $data[] = $qtyByProductAndDay[(int) $topProduct->id][$day] ?? 0;
                }
                $consumptionTrendDatasets[] = [
                    'label'       => $topProduct->name,
                    'data'        => $data,
                    'borderColor' => $trendColors[$index] ?? '#378ADD',
                ];
            }

            return [
                'pendingStockRequestCount'      => StockRequest::where('status', 'pending')->count(),
                'pendingIncomingDeliveriesCount'=> PurchaseOrder::whereIn('status', ['pending', 'approved'])->count(),
                'lowStockItems'                 => $lowStockItems,
                'pendingTransfers'              => StockRequest::with('product')->where('status', 'pending')->latest()->limit(5)->get(),
                'pendingIncomingDeliveries'     => PurchaseOrder::whereIn('status', ['pending', 'approved'])->orderBy('expected_date')->limit(3)->get(),
                'trendLabels'                   => collect($trendDays)->map(fn(string $d) => Carbon::parse($d)->format('D'))->values()->all(),
                'consumptionTrendDatasets'      => $consumptionTrendDatasets,
            ];
        });
    }

    // -------------------------------------------------------------------------
    // Pharmacist data. NOT cached — figures are personal and must be live.
    // -------------------------------------------------------------------------
    private function getPharmacistData($user): array
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd   = Carbon::today()->endOfDay();

        // Combine count + sum into a single query using selectRaw
        $todayStats = Sale::query()
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->selectRaw('COUNT(*) as sale_count, COALESCE(SUM(total_amount), 0) as sale_total')
            ->first();

        return [
            'mySalesTodayCount'      => (int) $todayStats->sale_count,
            'mySalesTodayTotal'      => (float) $todayStats->sale_total,
            'myPendingStockRequests' => StockRequest::where('requested_by', $user->id)->where('status', 'pending')->count(),
            'myRecentSales'          => Sale::with('patient')->where('user_id', $user->id)->latest()->limit(6)->get(),
        ];
    }
}