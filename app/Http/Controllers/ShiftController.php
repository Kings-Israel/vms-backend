<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShiftController extends Controller
{
    public function index(Request $request): Response
    {
        $shifts = Shift::with(['officer', 'building', 'relief'])
            ->when($request->building_id, fn($q, $id) => $q->where('building_id', $id))
            ->when($request->date, fn($q, $d) => $q->whereDate('starts_at', $d))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('starts_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Shifts/Index', [
            'shifts' => $shifts,
            'buildings' => Building::where('is_active', true)->get(),
            'officers' => User::role('security_officer')->get(),
            'filters' => $request->only(['building_id', 'date', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'building_id' => ['required', 'exists:buildings,id'],
            'user_id' => ['required', 'exists:users,id'],
            'relieved_by' => ['nullable', 'exists:users,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'notes' => ['nullable', 'string'],
        ]);

        Shift::create($data);
        return redirect()->route('shifts.index')->with('success', 'Shift scheduled.');
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $data = $request->validate([
            'building_id' => ['required', 'exists:buildings,id'],
            'user_id' => ['required', 'exists:users,id'],
            'relieved_by' => ['nullable', 'exists:users,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'status' => ['in:scheduled,active,completed,missed'],
            'notes' => ['nullable', 'string'],
        ]);

        $shift->update($data);
        return redirect()->route('shifts.index')->with('success', 'Shift updated.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        $shift->delete();
        return redirect()->route('shifts.index')->with('success', 'Shift deleted.');
    }
}
