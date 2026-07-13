<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Shift;
use App\Models\Unit;
use App\Models\Vehicle;
use App\Models\Visit;
use App\Models\Visitor;
use App\Models\VisitorType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VisitApiController extends Controller
{
    public function todayExpected(Request $request): JsonResponse
    {
        $user = $request->user();
        $buildingId = $user->building_id;

        $visits = Visit::with(['visitor', 'unit', 'visitorType', 'vehicle', 'host'])
            ->where('building_id', $buildingId)
            ->where(function ($q) {
                $q->where('status', 'expected')
                  ->whereDate('expected_arrival', today())
                  ->orWhere('status', 'checked_in');
            })
            ->orderBy('expected_arrival')
            ->get();

        $shift = Shift::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('relief')
            ->first();

        $nextShift = Shift::where('building_id', $buildingId)
            ->where('status', 'scheduled')
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->with('officer')
            ->first();

        return response()->json([
            'visits' => $visits,
            'current_shift' => $shift,
            'next_shift' => $nextShift,
            'stats' => [
                'expected' => $visits->where('status', 'expected')->count(),
                'inside' => $visits->where('status', 'checked_in')->count(),
            ],
        ]);
    }

    public function expectedVisits(Request $request): JsonResponse
    {
        $buildingId = $request->user()->building_id;

        $visits = Visit::with(['visitor', 'unit', 'visitorType', 'vehicle', 'host'])
            ->where('building_id', $buildingId)
            ->where(function ($q) {
                $q->where('status', 'expected')
                  ->whereDate('expected_arrival', today())
                  ->orWhere('status', 'checked_in');
            })
            ->orderBy('expected_arrival')
            ->get();

        return response()->json([
                'visits' => $visits,
        ]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $data = $request->validate([
            'visit_id' => ['nullable', 'exists:visits,id'],
            // Walk-in visitor fields
            'visitor.first_name' => ['required_without:visit_id', 'string'],
            'visitor.last_name' => ['required_without:visit_id', 'string'],
            'visitor.national_id' => ['nullable', 'string'],
            'visitor.phone' => ['nullable', 'string'],
            'visitor.email' => ['nullable', 'email'],
            'visitor.company' => ['nullable', 'string'],
            'visitor.id_photo_front' => ['nullable', 'string'], // base64
            'visitor.id_photo_back' => ['nullable', 'string'],  // base64
            'visitor.photo' => ['nullable', 'string'],          // base64
            'visitor_type_id' => ['nullable', 'exists:visitor_types,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'host_user_id' => ['nullable', 'exists:users,id'],
            'vehicle.plate_number' => ['nullable', 'string'],
            'vehicle.make' => ['nullable', 'string'],
            'vehicle.model' => ['nullable', 'string'],
            'vehicle.color' => ['nullable', 'string'],
            'vehicle.plate_photo' => ['nullable', 'string'], // base64
            'purpose' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $officer = $request->user();

        if (!empty($data['visit_id'])) {
            $visit = Visit::findOrFail($data['visit_id']);

            if ($visit->visitor->is_blacklisted) {
                return response()->json(['message' => 'Visitor is blacklisted. Entry denied.', 'blacklisted' => true], 403);
            }

            $visit->update([
                'status' => 'checked_in',
                'checked_in_at' => now(),
                'checked_in_by' => $officer->id,
            ]);
        } else {
            // Walk-in
            $visitorData = $data['visitor'];

            // Handle base64 photo uploads
            foreach (['photo', 'id_photo_front', 'id_photo_back'] as $field) {
                if (!empty($visitorData[$field])) {
                    $path = $this->storeBase64Image($visitorData[$field], 'visitors');
                    $visitorData[$field] = $path;
                }
            }

            $visitor = Visitor::firstOrCreate(
                ['national_id' => $visitorData['national_id'] ?? null],
                $visitorData
            );

            if ($visitor->is_blacklisted) {
                return response()->json(['message' => 'Visitor is blacklisted. Entry denied.', 'blacklisted' => true], 403);
            }

            $vehicleId = null;
            if (!empty($data['vehicle']['plate_number'])) {
                $vehicleData = $data['vehicle'];
                if (!empty($vehicleData['plate_photo'])) {
                    $vehicleData['plate_photo'] = $this->storeBase64Image($vehicleData['plate_photo'], 'plates');
                }
                $vehicle = $visitor->vehicles()->firstOrCreate(
                    ['plate_number' => $vehicleData['plate_number']],
                    $vehicleData
                );
                $vehicleId = $vehicle->id;
            }

            $unit = $data['unit_id'] ? Unit::find($data['unit_id']) : null;

            $visit = Visit::create([
                'visitor_id' => $visitor->id,
                'building_id' => $officer->building_id,
                'unit_id' => $data['unit_id'] ?? null,
                'host_user_id' => $data['host_user_id'] ?? null,
                'visitor_type_id' => $data['visitor_type_id'] ?? null,
                'vehicle_id' => $vehicleId,
                'purpose' => $data['purpose'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'checked_in',
                'checked_in_at' => now(),
                'checked_in_by' => $officer->id,
                'is_walk_in' => true,
            ]);
        }

        activity()->causedBy($officer)->performedOn($visit)->log('Visitor checked in');

        // Notify the host tenant
        if ($visit->host) {
            $visit->host->notify(new \App\Notifications\VisitorArrived($visit));
        }

        return response()->json([
            'message' => 'Visitor checked in successfully.',
            'visit' => $visit->fresh(['visitor', 'unit', 'visitorType', 'vehicle', 'host']),
        ]);
    }

    public function checkOut(Request $request, Visit $visit): JsonResponse
    {
        if ($visit->status !== 'checked_in') {
            return response()->json(['message' => 'Visitor is not currently checked in.'], 422);
        }

        $visit->update([
            'status' => 'checked_out',
            'checked_out_at' => now(),
            'checked_out_by' => $request->user()->id,
        ]);

        activity()->causedBy($request->user())->performedOn($visit)->log('Visitor checked out');

        return response()->json([
            'message' => 'Visitor checked out successfully.',
            'visit' => $visit->fresh(['visitor', 'vehicle']),
        ]);
    }

    public function lookupByNationalId(Request $request): JsonResponse
    {
        $request->validate(['national_id' => ['required', 'string']]);

        $visitor = Visitor::with(['visitorType', 'vehicles', 'visits' => fn($q) => $q->latest()->take(3)->with('unit')])
            ->where('national_id', $request->national_id)
            ->first();

        if (!$visitor) {
            return response()->json(['found' => false, 'visitor' => null]);
        }

        $expectedVisit = Visit::where('visitor_id', $visitor->id)
            ->where('status', 'expected')
            ->whereDate('expected_arrival', today())
            ->with(['unit', 'host', 'visitorType'])
            ->first();

        return response()->json([
            'found' => true,
            'blacklisted' => $visitor->is_blacklisted,
            'visitor' => $visitor,
            'expected_visit' => $expectedVisit,
        ]);
    }

    public function lookupByPlate(Request $request): JsonResponse
    {
        $request->validate(['plate_number' => ['required', 'string']]);

        $vehicle = Vehicle::with(['visitor.visitorType'])
            ->where('plate_number', strtoupper($request->plate_number))
            ->first();

        if (!$vehicle) {
            return response()->json(['found' => false, 'vehicle' => null]);
        }

        return response()->json([
            'found' => true,
            'vehicle' => $vehicle,
            'visitor' => $vehicle->visitor,
        ]);
    }

    public function getUnits(Request $request): JsonResponse
    {
        $units = Unit::with('tenants')
            ->where('building_id', $request->user()->building_id)
            ->where('is_active', true)
            ->get();

        return response()->json(['units' => $units]);
    }

    public function getVisitorTypes(): JsonResponse
    {
        return response()->json(['visitor_types' => VisitorType::where('is_active', true)->get()]);
    }

    public function startShift(Request $request): JsonResponse
    {
        $shift = Shift::where('user_id', $request->user()->id)
            ->where('status', 'scheduled')
            ->where('starts_at', '<=', now()->addMinutes(30))
            ->first();

        if (!$shift) {
            return response()->json(['message' => 'No scheduled shift found.'], 404);
        }

        $shift->update(['status' => 'active', 'actual_start' => now()]);
        activity()->causedBy($request->user())->performedOn($shift)->log('Shift started');

        return response()->json(['message' => 'Shift started.', 'shift' => $shift->fresh('relief')]);
    }

    public function endShift(Request $request): JsonResponse
    {
        $shift = Shift::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->first();

        if (!$shift) {
            return response()->json(['message' => 'No active shift found.'], 404);
        }

        $shift->update(['status' => 'completed', 'actual_end' => now()]);
        activity()->causedBy($request->user())->performedOn($shift)->log('Shift ended');

        return response()->json(['message' => 'Shift ended.', 'shift' => $shift]);
    }

    private function storeBase64Image(string $base64, string $directory): string
    {
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $image = base64_decode($imageData);
        $filename = $directory . '/' . uniqid() . '.jpg';
        Storage::disk('public')->put($filename, $image);
        return $filename;
    }
}
