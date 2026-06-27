<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BuildingController extends Controller
{
    public function index(Request $request): Response
    {
        $buildings = Building::withCount(['units', 'users'])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Buildings/Index', [
            'buildings' => $buildings,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Buildings/Form');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string'],
            'country' => ['required', 'string'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $building = Building::create($data);
        activity()->causedBy(auth()->user())->performedOn($building)->log('Building created');

        return redirect()->route('buildings.index')->with('success', 'Building created successfully.');
    }

    public function edit(Building $building): Response
    {
        return Inertia::render('Buildings/Form', [
            'building' => $building->load('units'),
        ]);
    }

    public function update(Request $request, Building $building): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string'],
            'country' => ['required', 'string'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $building->update($data);
        return redirect()->route('buildings.index')->with('success', 'Building updated successfully.');
    }

    public function destroy(Building $building): RedirectResponse
    {
        $building->delete();
        return redirect()->route('buildings.index')->with('success', 'Building deleted.');
    }
}
