<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Contracts\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        $auditLogs = AuditLog::with('user')
            ->latest()
            ->paginate(15);

        return view('audit-logs.index', compact('auditLogs'));
    }

    public function show(AuditLog $auditLog): View
    {
        $auditLog->load('user');

        return view('audit-logs.show', compact('auditLog'));
    }
}
