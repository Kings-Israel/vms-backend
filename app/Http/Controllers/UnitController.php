<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    public function index(Request $request): Response
    {
        $units = Unit::with(['building', 'tenants.roles'])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('unit_number', 'like', "%{$s}%"))
            ->when($request->building_id, fn($q, $id) => $q->where('building_id', $id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Units/Index', [
            'units' => $units,
            'buildings' => Building::where('is_active', true)->get(),
            'filters' => $request->only(['search', 'building_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Units/Form', [
            'buildings' => Building::where('is_active', true)->get(),
            'tenants' => User::role('tenant')->with('roles')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'building_id' => ['required', 'exists:buildings,id'],
            'name' => ['required', 'string'],
            'floor' => ['nullable', 'string'],
            'unit_number' => ['nullable', 'string'],
            'type' => ['required', 'in:office,residential,commercial,other'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'tenant_ids' => ['nullable', 'array'],
            'tenant_ids.*' => ['exists:users,id'],
        ]);

        $unit = Unit::create($data);

        if (!empty($data['tenant_ids'])) {
            $pivotData = collect($data['tenant_ids'])->mapWithKeys(fn($id) => [
                $id => ['assigned_at' => now()->toDateString(), 'is_primary' => true]
            ])->toArray();
            $unit->tenants()->sync($pivotData);
        }

        return redirect()->route('units.index')->with('success', 'Unit created successfully.');
    }

    public function edit(Unit $unit): Response
    {
        return Inertia::render('Units/Form', [
            'unit' => $unit->load(['building', 'tenants']),
            'buildings' => Building::where('is_active', true)->get(),
            'tenants' => User::role('tenant')->with('roles')->get(),
        ]);
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $data = $request->validate([
            'building_id' => ['required', 'exists:buildings,id'],
            'name' => ['required', 'string'],
            'floor' => ['nullable', 'string'],
            'unit_number' => ['nullable', 'string'],
            'type' => ['required', 'in:office,residential,commercial,other'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'tenant_ids' => ['nullable', 'array'],
            'tenant_ids.*' => ['exists:users,id'],
        ]);

        $unit->update($data);

        $pivotData = collect($data['tenant_ids'] ?? [])->mapWithKeys(fn($id) => [
            $id => ['assigned_at' => now()->toDateString(), 'is_primary' => true]
        ])->toArray();
        $unit->tenants()->sync($pivotData);

        return redirect()->route('units.index')->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();
        return redirect()->route('units.index')->with('success', 'Unit deleted.');
    }
}
