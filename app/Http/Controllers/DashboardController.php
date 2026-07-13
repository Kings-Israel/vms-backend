<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Visit;
use App\Models\Visitor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
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

        $carBreakdown = null;
        $visitorsTrend = null;
        $period = $request->period ?? 'week';
        $trendPeriod = $request->trend_period ?? 'week';

        if ($user->hasRole(['super_admin', 'building_manager'])) {
            $carBreakdown = $this->carBreakdown($buildingId, $period);
            $visitorsTrend = $this->visitorsTrend($buildingId, $trendPeriod);
        }

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'todayVisits' => $todayVisits,
            'recentActivity' => $recentActivity,
            'carBreakdown' => $carBreakdown,
            'visitorsTrend' => $visitorsTrend,
            'filters' => ['period' => $period, 'trend_period' => $trendPeriod],
        ]);
    }

    public function visitorsWithVehicle(Request $request): Response
    {
        return $this->vehicleVisitsList($request, true, 'Dashboard/CarVisitors');
    }

    public function visitorsWithoutVehicle(Request $request): Response
    {
        return $this->vehicleVisitsList($request, false, 'Dashboard/NoCarVisitors');
    }

    private function vehicleVisitsList(Request $request, bool $hasVehicle, string $page): Response
    {
        $buildingId = auth()->user()->building_id;
        $period = $request->period ?? 'week';
        [$start] = $this->periodRange($period);

        $query = Visit::with(['visitor', 'unit', 'host', 'vehicle', 'checkedInBy'])
            ->where('building_id', $buildingId)
            ->when($hasVehicle, fn ($q) => $q->whereNotNull('vehicle_id'))
            ->when(!$hasVehicle, fn ($q) => $q->whereNull('vehicle_id'))
            ->when($request->search, function ($q, $s) {
                $q->whereHas('visitor', fn ($vq) => $vq->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('national_id', 'like', "%{$s}%"));
            })
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->date, fn ($q, $d) => $q->whereDate('expected_arrival', $d))
            ->when(!$request->date, fn ($q) => $q->whereNotNull('checked_in_at')->where('checked_in_at', '>=', $start))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render($page, [
            'visits' => $query,
            'filters' => $request->only(['search', 'status', 'date']) + ['period' => $period],
        ]);
    }

    /**
     * @return array{with_vehicle: int, without_vehicle: int}
     */
    private function carBreakdown(int $buildingId, string $period): array
    {
        [$start] = $this->periodRange($period);

        $base = fn () => Visit::where('building_id', $buildingId)
            ->whereNotNull('checked_in_at')
            ->where('checked_in_at', '>=', $start);

        return [
            'with_vehicle' => $base()->whereNotNull('vehicle_id')->count(),
            'without_vehicle' => $base()->whereNull('vehicle_id')->count(),
        ];
    }

    /**
     * @return array{labels: array<string>, data: array<int>}
     */
    private function visitorsTrend(int $buildingId, string $period): array
    {
        [$start, $bucketUnit] = $this->periodRange($period);

        $checkIns = Visit::where('building_id', $buildingId)
            ->whereNotNull('checked_in_at')
            ->where('checked_in_at', '>=', $start)
            ->get(['checked_in_at'])
            ->pluck('checked_in_at');

        $buckets = collect();
        $cursor = $this->alignToBucket($start, $bucketUnit);
        $end = $this->alignToBucket(now(), $bucketUnit);

        while ($cursor->lte($end)) {
            $buckets->put($this->bucketKey($cursor, $bucketUnit), [
                'label' => $this->bucketLabel($cursor, $bucketUnit),
                'count' => 0,
            ]);

            match ($bucketUnit) {
                'week' => $cursor->addWeek(),
                'month' => $cursor->addMonth(),
                default => $cursor->addDay(),
            };
        }

        foreach ($checkIns as $checkedInAt) {
            $key = $this->bucketKey($checkedInAt, $bucketUnit);
            if ($buckets->has($key)) {
                $bucket = $buckets->get($key);
                $bucket['count']++;
                $buckets->put($key, $bucket);
            }
        }

        return [
            'labels' => $buckets->pluck('label')->values()->all(),
            'data' => $buckets->pluck('count')->values()->all(),
        ];
    }

    private function alignToBucket(Carbon $date, string $unit): Carbon
    {
        return match ($unit) {
            'week' => $date->copy()->startOfWeek(),
            'month' => $date->copy()->startOfMonth(),
            default => $date->copy()->startOfDay(),
        };
    }

    private function bucketKey(Carbon $date, string $unit): string
    {
        return match ($unit) {
            'week' => $date->copy()->startOfWeek()->format('Y-m-d'),
            'month' => $date->format('Y-m'),
            default => $date->format('Y-m-d'),
        };
    }

    private function bucketLabel(Carbon $date, string $unit): string
    {
        return match ($unit) {
            'week' => $date->copy()->startOfWeek()->format('M j'),
            'month' => $date->format('M Y'),
            default => $date->format('M j'),
        };
    }

    /**
     * @return array{0: Carbon, 1: string}
     */
    private function periodRange(string $period): array
    {
        return match ($period) {
            'month' => [now()->subDays(29)->startOfDay(), 'day'],
            '2months' => [now()->subDays(59)->startOfDay(), 'week'],
            '6months' => [now()->subMonths(6)->startOfDay(), 'week'],
            'year' => [now()->subYear()->startOfDay(), 'month'],
            default => [now()->subDays(6)->startOfDay(), 'day'],
        };
    }
}
