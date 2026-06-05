<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('nocInstance')->orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $locations = Location::with('nocInstance')->orderBy('name')->get();
        return view('admin.users.create', compact('locations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'username'   => 'nullable|string|max:255|unique:users,username',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8|confirmed',
            'role'       => 'required|in:1,2,3',
            'is_active'  => 'nullable|boolean',
            'locations'  => 'nullable|array',
            'locations.*'=> 'exists:locations,id',
        ]);

        $data['is_active']       = $request->has('is_active');
        $data['noc_instance_id'] = null;

        $locationIds = $data['locations'] ?? [];
        unset($data['locations']);

        $user = User::create($data);
        $user->locations()->sync($locationIds);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $locations = Location::with('nocInstance')->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'locations'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $rules = [
            'name'       => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'username'   => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'role'       => 'required|in:1,2,3',
            'is_active'  => 'nullable|boolean',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $data = $request->validate($rules);
        $data['is_active'] = $request->has('is_active');

        if (!$request->filled('password')) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    // ── Location management ───────────────────────────────────────────────────

    /**
     * GET /admin/users/{user}/locations
     * Returns the user + their locations + all available locations (AJAX).
     */
    public function showLocations(User $user): JsonResponse
    {
        return response()->json([
            'user'       => $user->only('id', 'name', 'last_name'),
            'locations'  => $user->locations()->with('nocInstance')->get()->map(fn($l) => [
                'id'         => $l->id,
                'name'       => $l->name,
                'city'       => $l->city,
                'noc_name'   => $l->nocInstance?->name,
            ]),
        ]);
    }

    /**
     * GET /admin/users/{user}/locations/edit
     * Returns user's current locations + all available locations for the edit modal.
     */
    public function editLocations(User $user): JsonResponse
    {
        $allLocations = Location::with('nocInstance')
            ->orderBy('name')
            ->get()
            ->map(fn($l) => [
                'id'       => $l->id,
                'name'     => $l->name,
                'city'     => $l->city,
                'noc_name' => $l->nocInstance?->name,
            ]);

        $userLocationIds = $user->locations()->pluck('locations.id')->toArray();

        return response()->json([
            'all_locations'      => $allLocations,
            'user_location_ids'  => $userLocationIds,
        ]);
    }

    /**
     * PUT /admin/users/{user}/locations
     * Syncs the user's location assignments.
     */
    public function updateLocations(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'location_ids'   => 'nullable|array',
            'location_ids.*' => 'exists:locations,id',
        ]);

        $user->locations()->sync($data['location_ids'] ?? []);

        return response()->json([
            'message'   => 'Locations updated successfully.',
            'locations' => $user->locations()->with('nocInstance')->get()->map(fn($l) => [
                'id'       => $l->id,
                'name'     => $l->name,
                'noc_name' => $l->nocInstance?->name,
            ]),
        ]);
    }
}
