<?php

namespace App\Http\Controllers;

use App\Models\VisitorType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VisitorTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $types = VisitorType::withCount('visits')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('VisitorTypes/Index', ['types' => $types]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'unique:visitor_types'],
            'description' => ['nullable', 'string'],
            'color' => ['required', 'string'],
            'icon' => ['nullable', 'string'],
            'requires_escort' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        VisitorType::create($data);
        return redirect()->route('visitor-types.index')->with('success', 'Visitor type created.');
    }

    public function update(Request $request, VisitorType $visitorType): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'unique:visitor_types,name,' . $visitorType->id],
            'description' => ['nullable', 'string'],
            'color' => ['required', 'string'],
            'icon' => ['nullable', 'string'],
            'requires_escort' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $visitorType->update($data);
        return redirect()->route('visitor-types.index')->with('success', 'Visitor type updated.');
    }

    public function destroy(VisitorType $visitorType): RedirectResponse
    {
        $visitorType->delete();
        return redirect()->route('visitor-types.index')->with('success', 'Visitor type deleted.');
    }
}
