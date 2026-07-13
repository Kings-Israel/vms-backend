<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Unit;
use App\Models\Visit;
use App\Models\Visitor;
use App\Models\VisitorType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class VisitController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Visit::with(['visitor', 'unit', 'host', 'building', 'visitorType', 'vehicle', 'checkedInBy'])
            ->when($request->search, function ($q, $s) {
                $q->whereHas('visitor', fn($vq) => $vq->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('national_id', 'like', "%{$s}%"));
            })
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->building_id, fn($q, $id) => $q->where('building_id', $id))
            ->when($request->date, fn($q, $d) => $q->whereDate('expected_arrival', $d))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Visits/Index', [
            'visits' => $query,
            'buildings' => Building::where('is_active', true)->get(),
            'filters' => $request->only(['search', 'status', 'building_id', 'date']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Visits/Form', [
            'buildings' => Building::where('is_active', true)->with('units')->get(),
            'visitorTypes' => VisitorType::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'building_id' => ['required', 'exists:buildings,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'host_user_id' => ['nullable', 'exists:users,id'],
            'visitor_type_id' => ['nullable', 'exists:visitor_types,id'],
            'purpose' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'expected_arrival' => ['nullable', 'date'],
            'expected_departure' => ['nullable', 'date'],
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

        $visit = Visit::create([
            'visitor_id' => $visitor->id,
            'building_id' => $data['building_id'],
            'unit_id' => $data['unit_id'] ?? null,
            'host_user_id' => $data['host_user_id'] ?? null,
            'visitor_type_id' => $data['visitor_type_id'] ?? null,
            'vehicle_id' => $vehicleId,
            'purpose' => $data['purpose'] ?? null,
            'notes' => $data['notes'] ?? null,
            'expected_arrival' => $data['expected_arrival'] ?? null,
            'expected_departure' => $data['expected_departure'] ?? null,
            'status' => 'expected',
            'qr_token' => Str::random(40),
        ]);

        // Notify active security officers
        $this->notifySecurityOfficers($visit);

        return redirect()->route('visits.confirmation', $visit)->with('success', 'Visit registered successfully.');
    }

    public function confirmation(Visit $visit): Response
    {
        $visit->load(['visitor', 'unit', 'vehicle']);
        $visit->makeVisible('qr_token');

        return Inertia::render('Visits/Confirmation', [
            'visit' => $visit,
        ]);
    }

    public function destroy(Visit $visit): RedirectResponse
    {
        $visit->update(['status' => 'cancelled', 'qr_token' => null]);
        return redirect()->route('visits.index')->with('success', 'Visit cancelled.');
    }

    private function notifySecurityOfficers(Visit $visit): void
    {
        $officers = \App\Models\User::role('security_officer')
            ->where('building_id', $visit->building_id)
            ->where('is_active', true)
            ->get();

        foreach ($officers as $officer) {
            $officer->notify(new \App\Notifications\NewVisitorExpected($visit));
        }
    }
}
