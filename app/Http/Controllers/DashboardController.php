<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Visit;
use App\Models\Visitor;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $buildingId = $user->building_id;

        $todayVisits = Visit::with(['visitor', 'unit', 'visitorType'])
            ->where('building_id', $buildingId)
            ->whereDate('expected_arrival', today())
            ->orWhere(fn($q) => $q->where('building_id', $buildingId)->where('status', 'checked_in'))
            ->latest()
            ->take(10)
            ->get();

        $stats = [
            'total_visitors_today' => Visit::where('building_id', $buildingId)
                ->whereDate('created_at', today())->count(),
            'currently_inside' => Visit::where('building_id', $buildingId)
                ->where('status', 'checked_in')->count(),
            'expected_today' => Visit::where('building_id', $buildingId)
                ->where('status', 'expected')
                ->whereDate('expected_arrival', today())->count(),
            'total_tenants' => User::role('tenant')->where('building_id', $buildingId)->count(),
            'total_officers' => User::role('security_officer')->where('building_id', $buildingId)->count(),
            'active_shifts' => Shift::where('building_id', $buildingId)->where('status', 'active')->count(),
        ];

        $recentActivity = Visit::with(['visitor', 'checkedInBy'])
            ->where('building_id', $buildingId)
            ->whereIn('status', ['checked_in', 'checked_out'])
            ->latest('updated_at')
            ->take(8)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'todayVisits' => $todayVisits,
            'recentActivity' => $recentActivity,
        ]);
    }
}
