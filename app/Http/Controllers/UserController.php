<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::with(['roles', 'building'])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->when($request->role, fn($q, $role) => $q->role($role))
            ->when($request->building_id, fn($q, $id) => $q->where('building_id', $id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => Role::all(),
            'buildings' => Building::where('is_active', true)->get(),
            'filters' => $request->only(['search', 'role', 'building_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Form', [
            'roles' => Role::all(),
            'buildings' => Building::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'phone' => ['nullable', 'string'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
            'building_id' => ['nullable', 'exists:buildings,id'],
            'is_active' => ['boolean'],
            'hours' => ['nullable', 'array'],
            'hours.*.day_of_week' => ['required_with:hours', 'integer', 'min:0', 'max:6'],
            'hours.*.start_time' => ['required_with:hours', 'date_format:H:i'],
            'hours.*.end_time' => ['required_with:hours', 'date_format:H:i'],
            'hours.*.is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'building_id' => $data['building_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        $user->assignRole($data['role']);

        if (!empty($data['hours'])) {
            foreach ($data['hours'] as $hour) {
                $user->workingHours()->create($hour);
            }
        }

        activity()->causedBy(auth()->user())->performedOn($user)->log('User created');

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Users/Form', [
            'user' => $user->load(['roles', 'workingHours']),
            'roles' => Role::all(),
            'buildings' => Building::where('is_active', true)->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string'],
            'password' => ['nullable', Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
            'building_id' => ['nullable', 'exists:buildings,id'],
            'is_active' => ['boolean'],
            'hours' => ['nullable', 'array'],
            'hours.*.day_of_week' => ['required_with:hours', 'integer', 'min:0', 'max:6'],
            'hours.*.start_time' => ['required_with:hours', 'date_format:H:i'],
            'hours.*.end_time' => ['required_with:hours', 'date_format:H:i'],
            'hours.*.is_active' => ['boolean'],
        ]);

        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'building_id' => $data['building_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ];

        if (!empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $user->update($update);
        $user->syncRoles([$data['role']]);

        if (!empty($data['hours'])) {
            $user->workingHours()->delete();
            foreach ($data['hours'] as $hour) {
                $user->workingHours()->create($hour);
            }
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function workingHours(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'hours' => ['required', 'array'],
            'hours.*.day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'hours.*.start_time' => ['required', 'date_format:H:i'],
            'hours.*.end_time' => ['required', 'date_format:H:i'],
            'hours.*.is_active' => ['boolean'],
        ]);

        $user->workingHours()->delete();
        foreach ($request->hours as $hour) {
            $user->workingHours()->create($hour);
        }

        return back()->with('success', 'Working hours updated.');
    }
}
