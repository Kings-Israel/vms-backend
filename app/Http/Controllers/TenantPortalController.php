<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Visit;
use App\Models\Visitor;
use App\Models\VisitorType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TenantPortalController extends Controller
{
    public function dashboard(): Response
    {
        $user = auth()->user();
        $units = $user->units;
        $unitIds = $units->pluck('id');

        $expectedVisits = Visit::with(['visitor', 'visitorType', 'vehicle'])
            ->whereIn('unit_id', $unitIds)
            ->where('status', 'expected')
            ->whereDate('expected_arrival', today())
            ->orWhere(fn($q) => $q->whereIn('unit_id', $unitIds)->where('status', 'checked_in'))
            ->latest()
            ->get();

        $stats = [
            'total_visits' => Visit::whereIn('unit_id', $unitIds)->count(),
            'today_visits' => Visit::whereIn('unit_id', $unitIds)->whereDate('created_at', today())->count(),
            'currently_inside' => Visit::whereIn('unit_id', $unitIds)->where('status', 'checked_in')->count(),
        ];

        return Inertia::render('Tenant/Dashboard', [
            'units' => $units,
            'expectedVisits' => $expectedVisits,
            'stats' => $stats,
        ]);
    }

    public function preRegisterVisitor(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $unitIds = $user->units->pluck('id');

        $data = $request->validate([
            'unit_id' => ['required', 'in:' . $unitIds->join(',')],
            'visitor_type_id' => ['nullable', 'exists:visitor_types,id'],
            'purpose' => ['nullable', 'string'],
            'expected_arrival' => ['required', 'date'],
            'expected_departure' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'visitor.first_name' => ['required', 'string'],
            'visitor.last_name' => ['required', 'string'],
            'visitor.national_id' => ['nullable', 'string'],
            'visitor.phone' => ['nullable', 'string'],
            'visitor.email' => ['nullable', 'email'],
            'visitor.company' => ['nullable', 'string'],
            'vehicle.plate_number' => ['nullable', 'string'],
            'vehicle.make' => ['nullable', 'string'],
            'vehicle.model' => ['nullable', 'string'],
            'vehicle.color' => ['nullable', 'string'],
        ]);

        $visitor = Visitor::firstOrCreate(
            ['national_id' => $data['visitor']['national_id'] ?? null],
            $data['visitor']
        );

        $vehicleId = null;
        if (!empty($data['vehicle']['plate_number'])) {
            $vehicle = $visitor->vehicles()->firstOrCreate(
                ['plate_number' => $data['vehicle']['plate_number']],
                $data['vehicle']
            );
            $vehicleId = $vehicle->id;
        }

        $unit = Unit::find($data['unit_id']);

        $visit = Visit::create([
            'visitor_id' => $visitor->id,
            'building_id' => $unit->building_id,
            'unit_id' => $unit->id,
            'host_user_id' => $user->id,
            'visitor_type_id' => $data['visitor_type_id'] ?? null,
            'vehicle_id' => $vehicleId,
            'purpose' => $data['purpose'] ?? null,
            'notes' => $data['notes'] ?? null,
            'expected_arrival' => $data['expected_arrival'],
            'expected_departure' => $data['expected_departure'] ?? null,
            'status' => 'expected',
            'qr_token' => Str::random(40),
        ]);

        // Notify security officers
        $officers = \App\Models\User::role('security_officer')
            ->where('building_id', $unit->building_id)
            ->where('is_active', true)
            ->get();

        foreach ($officers as $officer) {
            $officer->notify(new \App\Notifications\NewVisitorExpected($visit));
        }

        return redirect()->route('tenant.visits.confirmation', $visit)->with('success', 'Visitor pre-registered successfully. Security has been notified.');
    }

    public function visitConfirmation(Visit $visit): Response
    {
        $user = auth()->user();
        $unitIds = $user->units->pluck('id');

        if (!$unitIds->contains($visit->unit_id)) {
            abort(403);
        }

        $visit->load(['visitor', 'unit', 'vehicle']);
        $visit->makeVisible('qr_token');

        return Inertia::render('Tenant/VisitConfirmation', [
            'visit' => $visit,
        ]);
    }

    public function visitHistory(Request $request): Response
    {
        $user = auth()->user();
        $unitIds = $user->units->pluck('id');

        $visits = Visit::with(['visitor', 'visitorType', 'vehicle', 'checkedInBy'])
            ->whereIn('unit_id', $unitIds)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->search, function ($q, $s) {
                $q->whereHas('visitor', fn($vq) => $vq->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%"));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Tenant/VisitHistory', [
            'visits' => $visits,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function cancelVisit(Visit $visit): RedirectResponse
    {
        $user = auth()->user();
        $unitIds = $user->units->pluck('id');

        if (!$unitIds->contains($visit->unit_id)) {
            abort(403);
        }

        $visit->update(['status' => 'cancelled', 'qr_token' => null]);
        return back()->with('success', 'Visit cancelled.');
    }
}
