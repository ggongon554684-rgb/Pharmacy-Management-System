<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $roleNames = $user->roles->pluck('name');

        $dashboardData = [
            'patientCount' => Patient::count(),
            'productCount' => Product::count(),
            'auditCount' => AuditLog::count(),
            'recentAudits' => AuditLog::with('user')->latest()->limit(5)->get(),
        ];

        if ($roleNames->contains('admin')) {
            return view('admin.dashboard', $dashboardData);
        } elseif ($roleNames->contains('staff')) {
            return view('staff.dashboard', $dashboardData);
        } else {
            return view('dashboard', $dashboardData);
        }
    }
}