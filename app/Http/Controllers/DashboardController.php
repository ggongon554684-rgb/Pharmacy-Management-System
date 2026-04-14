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

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $roleNames = $user->roles->pluck('name');

        $dashboardData = [
            'patientCount' => Patient::count(),
            'productCount' => Product::count(),
            'frontShopStockUnits' => (int) InventoryBatch::query()
                ->forLocationCode('front')
                ->sum('quantity'),
            'backInventoryStockUnits' => (int) InventoryBatch::query()
                ->forLocationCode('back')
                ->sum('quantity'),
            'lowStockCount' => Product::query()
                ->whereRaw('COALESCE((SELECT SUM(quantity) FROM inventory_batches WHERE inventory_batches.product_id = products.id), 0) <= reorder_level')
                ->count(),
            'auditCount' => AuditLog::count(),
            'recentAudits' => AuditLog::with('user')->latest()->limit(5)->get(),
        ];

        if ($roleNames->contains('admin')) {
            $trendStart = Carbon::today()->subDays(6);
            $trendEnd = Carbon::today();

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

            $trendLabels = [];
            $salesSeries = [];
            $purchaseSeries = [];

            for ($date = $trendStart->copy(); $date->lte($trendEnd); $date->addDay()) {
                $key = $date->toDateString();
                $trendLabels[] = $date->format('M d');
                $salesSeries[] = round((float) ($salesTrend[$key] ?? 0), 2);
                $purchaseSeries[] = round((float) ($purchaseTrend[$key] ?? 0), 2);
            }

            $lowStockProducts = Product::query()
                ->whereRaw('COALESCE((SELECT SUM(quantity) FROM inventory_batches WHERE inventory_batches.product_id = products.id), 0) <= reorder_level')
                ->count();
            $normalStockProducts = Product::query()
                ->whereRaw('COALESCE((SELECT SUM(quantity) FROM inventory_batches WHERE inventory_batches.product_id = products.id), 0) > reorder_level')
                ->whereRaw('COALESCE((SELECT SUM(quantity) FROM inventory_batches WHERE inventory_batches.product_id = products.id), 0) <= (reorder_level * 2)')
                ->count();
            $overstockProducts = Product::query()
                ->whereRaw('COALESCE((SELECT SUM(quantity) FROM inventory_batches WHERE inventory_batches.product_id = products.id), 0) > (reorder_level * 2)')
                ->count();

            $topMovingProducts = SaleLineItem::query()
                ->join('inventory_batches', 'inventory_batches.id', '=', 'sale_line_items.inventory_batch_id')
                ->join('products', 'products.id', '=', 'inventory_batches.product_id')
                ->selectRaw('products.name as product_name, SUM(sale_line_items.quantity) as total_sold')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_sold')
                ->limit(8)
                ->get();

            $dashboardData = array_merge($dashboardData, [
                'totalRevenue' => (float) Sale::sum('total_amount'),
                'totalPurchaseCost' => (float) PurchaseOrder::sum('total_cost'),
                'recentSales' => Sale::with('patient')->latest()->limit(5)->get(),
                'recentPurchaseOrders' => PurchaseOrder::latest()->limit(5)->get(),
                'incomingDeliveries' => PurchaseOrder::query()
                    ->whereIn('status', ['approved', 'pending'])
                    ->whereNotNull('expected_date')
                    ->orderBy('expected_date')
                    ->limit(6)
                    ->get(),
                'trendLabels' => $trendLabels,
                'salesTrendSeries' => $salesSeries,
                'purchaseTrendSeries' => $purchaseSeries,
                'stockHealthLabels' => ['Low Stock', 'Normal Stock', 'Overstock'],
                'stockHealthSeries' => [$lowStockProducts, $normalStockProducts, $overstockProducts],
                'topMovingProductLabels' => $topMovingProducts->pluck('product_name')->values()->all(),
                'topMovingProductSeries' => $topMovingProducts->pluck('total_sold')->map(fn ($value) => (int) $value)->values()->all(),
            ]);
            return view('admin.dashboard', $dashboardData);
        } elseif ($roleNames->contains('staff')) {
            $lowStockItems = Product::query()
                ->withSum('inventoryBatches', 'quantity')
                ->whereRaw('COALESCE((SELECT SUM(quantity) FROM inventory_batches WHERE inventory_batches.product_id = products.id), 0) <= reorder_level')
                ->orderByRaw('COALESCE(inventory_batches_sum_quantity, 0) ASC')
                ->limit(5)
                ->get();

            $pendingTransfers = StockRequest::query()
                ->with('product')
                ->where('status', 'pending')
                ->latest()
                ->limit(5)
                ->get();

            $pendingIncomingDeliveries = PurchaseOrder::query()
                ->whereIn('status', ['pending', 'approved'])
                ->orderBy('expected_date')
                ->limit(3)
                ->get();

            $trendStart = Carbon::today()->subDays(6)->startOfDay();
            $trendEnd = Carbon::today()->endOfDay();
            $trendDays = [];
            for ($date = $trendStart->copy(); $date->lte($trendEnd); $date->addDay()) {
                $trendDays[] = $date->toDateString();
            }

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

            $rawTrendRows = SaleLineItem::query()
                ->join('inventory_batches', 'inventory_batches.id', '=', 'sale_line_items.inventory_batch_id')
                ->join('sales', 'sales.id', '=', 'sale_line_items.sale_id')
                ->whereBetween('sales.created_at', [$trendStart, $trendEnd])
                ->selectRaw('inventory_batches.product_id as product_id, DATE(sales.created_at) as sale_day, SUM(sale_line_items.quantity) as qty')
                ->groupBy('inventory_batches.product_id', 'sale_day')
                ->get();

            $qtyByProductAndDay = [];
            foreach ($rawTrendRows as $row) {
                $qtyByProductAndDay[(int) $row->product_id][$row->sale_day] = (int) $row->qty;
            }

            $trendColors = ['#378ADD', '#1D9E75', '#EF9F27', '#7F77DD'];
            $consumptionTrendDatasets = [];
            foreach ($topConsumedProducts as $index => $topProduct) {
                $data = [];
                foreach ($trendDays as $day) {
                    $data[] = $qtyByProductAndDay[(int) $topProduct->id][$day] ?? 0;
                }
                $consumptionTrendDatasets[] = [
                    'label' => $topProduct->name,
                    'data' => $data,
                    'borderColor' => $trendColors[$index] ?? '#378ADD',
                ];
            }

            $staffData = array_merge($dashboardData, [
                'pendingStockRequestCount' => StockRequest::query()
                    ->where('status', 'pending')
                    ->count(),
                'pendingIncomingDeliveriesCount' => PurchaseOrder::query()
                    ->whereIn('status', ['pending', 'approved'])
                    ->count(),
                'lowStockItems' => $lowStockItems,
                'pendingTransfers' => $pendingTransfers,
                'pendingIncomingDeliveries' => $pendingIncomingDeliveries,
                'trendLabels' => collect($trendDays)->map(fn (string $day) => Carbon::parse($day)->format('D'))->values()->all(),
                'consumptionTrendDatasets' => $consumptionTrendDatasets,
            ]);
            return view('staff.dashboard', $staffData);
        } elseif ($roleNames->contains('pharmacist')) {
            $todayStart = Carbon::today()->startOfDay();
            $todayEnd = Carbon::today()->endOfDay();

            $pharmacistData = array_merge($dashboardData, [
                'mySalesTodayCount' => Sale::query()
                    ->where('user_id', $user->id)
                    ->whereBetween('created_at', [$todayStart, $todayEnd])
                    ->count(),
                'mySalesTodayTotal' => (float) Sale::query()
                    ->where('user_id', $user->id)
                    ->whereBetween('created_at', [$todayStart, $todayEnd])
                    ->sum('total_amount'),
                'myPendingStockRequests' => StockRequest::query()
                    ->where('requested_by', $user->id)
                    ->where('status', 'pending')
                    ->count(),
                'myRecentSales' => Sale::with('patient')
                    ->where('user_id', $user->id)
                    ->latest()
                    ->limit(6)
                    ->get(),
            ]);

            return view('pharmacist.dashboard', $pharmacistData);
        } else {
            return view('dashboard', $dashboardData);
        }
    }
}