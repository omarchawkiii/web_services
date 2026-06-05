<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NocInstance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class NocInstanceController extends Controller
{
    public function index(): View
    {
        $nocs = NocInstance::orderBy('name')->get();
        return view('admin.noc-instances.index', compact('nocs'));
    }

    public function create(): View
    {
        return view('admin.noc-instances.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'url'            => 'required|url|max:500',
            'admin_username' => 'required|string|max:255',
            'admin_password' => 'required|string|max:500',
            'notes'          => 'nullable|string',
            'is_active'      => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active');

        NocInstance::create($data);

        return redirect()->route('admin.noc-instances.index')
            ->with('success', 'NOC instance created successfully.');
    }

    public function edit(NocInstance $nocInstance): View
    {
        return view('admin.noc-instances.edit', compact('nocInstance'));
    }

    public function update(Request $request, NocInstance $nocInstance): RedirectResponse
    {
        $rules = [
            'name'           => 'required|string|max:255',
            'url'            => 'required|url|max:500',
            'admin_username' => 'required|string|max:255',
            'notes'          => 'nullable|string',
            'is_active'      => 'nullable|boolean',
        ];

        if ($request->filled('admin_password')) {
            $rules['admin_password'] = 'string|max:500';
        }

        $data = $request->validate($rules);
        $data['is_active'] = $request->has('is_active');

        if (!$request->filled('admin_password')) {
            unset($data['admin_password']);
        }

        $nocInstance->update($data);

        return redirect()->route('admin.noc-instances.index')
            ->with('success', 'NOC instance updated successfully.');
    }

    public function destroy(NocInstance $nocInstance): RedirectResponse
    {
        $nocInstance->delete();

        return redirect()->route('admin.noc-instances.index')
            ->with('success', 'NOC instance deleted successfully.');
    }

    public function testConnection(NocInstance $nocInstance): JsonResponse
    {
        try {
            $response = Http::timeout(8)->post(
                rtrim($nocInstance->url, '/') . '/api/mobile/login',
                [
                    'username' => $nocInstance->admin_username,
                    'password' => $nocInstance->admin_password,
                ]
            );

            if ($response->successful() && $response->json('token')) {
                $nocInstance->update([
                    'sync_status'   => 'online',
                    'last_sync_at'  => now(),
                    'sanctum_token' => $response->json('token'),
                ]);

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Connection successful. Token received.',
                ]);
            }

            $nocInstance->update(['sync_status' => 'offline']);

            return response()->json([
                'status'  => 'error',
                'message' => 'Authentication failed: ' . ($response->json('message') ?? 'Invalid credentials'),
            ], 422);

        } catch (\Exception $e) {
            $nocInstance->update(['sync_status' => 'offline']);

            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot reach NOC: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function toggleActive(NocInstance $nocInstance): JsonResponse
    {
        $nocInstance->update(['is_active' => !$nocInstance->is_active]);

        return response()->json([
            'is_active' => $nocInstance->is_active,
        ]);
    }
}
