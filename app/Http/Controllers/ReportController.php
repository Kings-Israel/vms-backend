<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Visit;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ReportController extends Controller
{
    public function activityLog(Request $request): Response
    {
        $logs = Activity::with(['causer', 'subject'])
            ->when($request->causer_id, fn($q, $id) => $q->where('causer_id', $id))
            ->when($request->log_name, fn($q, $name) => $q->where('log_name', $name))
            ->when($request->from, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->to, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Reports/ActivityLog', [
            'logs' => $logs,
            'users' => User::select('id', 'name', 'email')->get(),
            'filters' => $request->only(['causer_id', 'log_name', 'from', 'to']),
        ]);
    }

    public function visitorActivity(Request $request): Response
    {
        $visits = Visit::with(['visitor', 'unit', 'building', 'visitorType', 'checkedInBy', 'vehicle'])
            ->when($request->building_id, fn($q, $id) => $q->where('building_id', $id))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->visitor_type_id, fn($q, $id) => $q->where('visitor_type_id', $id))
            ->when($request->from, fn($q, $d) => $q->whereDate('checked_in_at', '>=', $d))
            ->when($request->to, fn($q, $d) => $q->whereDate('checked_in_at', '<=', $d))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'total_today' => Visit::whereDate('created_at', today())->count(),
            'checked_in' => Visit::where('status', 'checked_in')->count(),
            'checked_out_today' => Visit::where('status', 'checked_out')->whereDate('checked_out_at', today())->count(),
            'expected_today' => Visit::where('status', 'expected')->whereDate('expected_arrival', today())->count(),
        ];

        return Inertia::render('Reports/VisitorActivity', [
            'visits' => $visits,
            'stats' => $stats,
            'buildings' => Building::where('is_active', true)->get(),
            'filters' => $request->only(['building_id', 'status', 'visitor_type_id', 'from', 'to']),
        ]);
    }

    public function tenantActivity(Request $request): Response
    {
        $tenants = User::role('tenant')
            ->withCount(['hostedVisits as total_visits'])
            ->withCount(['hostedVisits as active_visits' => fn($q) => $q->where('status', 'checked_in')])
            ->when($request->building_id, fn($q, $id) => $q->where('building_id', $id))
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Reports/TenantActivity', [
            'tenants' => $tenants,
            'buildings' => Building::where('is_active', true)->get(),
            'filters' => $request->only(['building_id', 'search']),
        ]);
    }
}
