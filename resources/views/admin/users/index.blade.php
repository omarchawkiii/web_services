@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'Users')

@section('header-actions')
    <a href="{{ route('admin.users.create') }}"
       class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Add User
    </a>
@endsection

@section('content')

<div class="rounded-xl bg-white border border-gray-200 overflow-hidden">

    @if($users->isEmpty())
        <div class="px-6 py-16 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-900">No users</h3>
            <p class="mt-1 text-sm text-gray-500">Create your first user or sync from a NOC.</p>
            <a href="{{ route('admin.users.create') }}"
               class="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Add User
            </a>
        </div>
    @else
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">First Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Last Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Username</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Source</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Active</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach($users as $i => $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-600 shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->last_name ?? '', 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $user->last_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $user->username ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $user->role_badge_class }}">
                                {{ $user->role_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($user->nocInstance)
                                <span class="inline-flex items-center gap-1 rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 0 0 3 3h7.5a3 3 0 0 0 3-3m-16.5 0V6.375a3.375 3.375 0 0 1 6.75 0v7.875"/>
                                    </svg>
                                    {{ $user->nocInstance->name }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">Hub local</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($user->is_active)
                                <span class="inline-flex h-2 w-2 rounded-full bg-green-500"></span>
                            @else
                                <span class="inline-flex h-2 w-2 rounded-full bg-gray-300"></span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- Show Locations --}}
                                <button type="button"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->name }} {{ $user->last_name }}"
                                        class="show-locations-btn inline-flex items-center rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors"
                                        title="Show Locations">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                                    </svg>
                                </button>

                                {{-- Edit Locations --}}
                                <button type="button"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->name }} {{ $user->last_name }}"
                                        class="edit-locations-btn inline-flex items-center rounded-md border border-blue-200 bg-white px-2.5 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 transition-colors"
                                        title="Edit Locations">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z"/>
                                    </svg>
                                </button>

                                {{-- Edit --}}
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="inline-flex items-center rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                    Edit
                                </a>

                                {{-- Delete --}}
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          onsubmit="return confirm('Delete {{ addslashes($user->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center rounded-md border border-red-200 bg-white px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- ── Show Locations Modal ─────────────────────────────────────────────── --}}
<div id="show_location_modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h3 class="text-base font-semibold text-gray-900">
                Locations — <span id="show_location_user_name"></span>
            </h3>
            <button type="button" class="close-modal text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="show_location_content" class="px-6 py-5 min-h-[80px]">
            <div class="flex justify-center py-4">
                <div class="h-5 w-5 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"></div>
            </div>
        </div>
    </div>
</div>

{{-- ── Edit Locations Modal ─────────────────────────────────────────────── --}}
<div id="edit_locations_modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="w-full max-w-lg rounded-xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h3 class="text-base font-semibold text-gray-900">
                Edit Locations — <span id="edit_location_user_name"></span>
            </h3>
            <button type="button" class="close-modal text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="px-6 py-5">
            <div id="edit_location_loading" class="flex justify-center py-4">
                <div class="h-5 w-5 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"></div>
            </div>
            <div id="edit_location_form_wrapper" class="hidden space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">Select locations for this user</p>
                    <button type="button" id="select_all_locations"
                            class="text-xs font-medium text-blue-600 hover:text-blue-700">Select all</button>
                </div>
                <div id="locations_list" class="max-h-72 overflow-y-auto space-y-1 rounded-lg border border-gray-200 p-2">
                    {{-- Populated by JS --}}
                </div>
                <div id="edit_location_result" class="hidden rounded-lg px-3 py-2 text-sm"></div>
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                    <button type="button" class="close-modal rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" id="save_locations_btn"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                        Save Locations
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let editUserId = null;

// ── Modal helpers ──────────────────────────────────────────────────────────
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.getElementById(id).classList.add('flex');
}
function closeAllModals() {
    ['show_location_modal','edit_locations_modal'].forEach(id => {
        const el = document.getElementById(id);
        el.classList.add('hidden');
        el.classList.remove('flex');
    });
}
document.querySelectorAll('.close-modal').forEach(btn => btn.addEventListener('click', closeAllModals));
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAllModals(); });
['show_location_modal','edit_locations_modal'].forEach(id => {
    document.getElementById(id).addEventListener('click', e => {
        if (e.target === e.currentTarget) closeAllModals();
    });
});

// ── Show Locations ─────────────────────────────────────────────────────────
document.querySelectorAll('.show-locations-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const userId   = btn.dataset.userId;
        const userName = btn.dataset.userName;
        document.getElementById('show_location_user_name').textContent = userName;
        document.getElementById('show_location_content').innerHTML =
            '<div class="flex justify-center py-4"><div class="h-5 w-5 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"></div></div>';
        openModal('show_location_modal');

        try {
            const res  = await fetch(`/admin/users/${userId}/locations`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            const locs = data.locations;

            if (!locs.length) {
                document.getElementById('show_location_content').innerHTML =
                    '<p class="text-sm text-gray-400 text-center py-2">No locations assigned.</p>';
                return;
            }

            const html = locs.map(l => `
                <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-medium text-blue-800 m-1">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                    </svg>
                    ${l.name}${l.city ? ' — ' + l.city : ''}
                    ${l.noc_name ? '<span class="text-blue-500">(' + l.noc_name + ')</span>' : ''}
                </span>
            `).join('');

            document.getElementById('show_location_content').innerHTML =
                `<div class="flex flex-wrap">${html}</div>`;
        } catch (e) {
            document.getElementById('show_location_content').innerHTML =
                '<p class="text-sm text-red-500 text-center py-2">Failed to load locations.</p>';
        }
    });
});

// ── Edit Locations ─────────────────────────────────────────────────────────
document.querySelectorAll('.edit-locations-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        editUserId = btn.dataset.userId;
        const userName = btn.dataset.userName;
        document.getElementById('edit_location_user_name').textContent = userName;
        document.getElementById('edit_location_loading').classList.remove('hidden');
        document.getElementById('edit_location_form_wrapper').classList.add('hidden');
        document.getElementById('edit_location_result').classList.add('hidden');
        openModal('edit_locations_modal');

        try {
            const res  = await fetch(`/admin/users/${editUserId}/locations/edit`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            const userIds = new Set(data.user_location_ids);
            const html = data.all_locations.map(l => `
                <label class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" value="${l.id}"
                           class="location-checkbox h-4 w-4 rounded border-gray-300 text-blue-600"
                           ${userIds.has(l.id) ? 'checked' : ''}>
                    <span class="flex-1 text-sm text-gray-800">${l.name}${l.city ? ' <span class="text-gray-400">— ' + l.city + '</span>' : ''}</span>
                    ${l.noc_name ? `<span class="text-xs text-purple-600">${l.noc_name}</span>` : ''}
                </label>
            `).join('');

            document.getElementById('locations_list').innerHTML = html || '<p class="text-sm text-gray-400 text-center py-3">No locations available.</p>';
            document.getElementById('edit_location_loading').classList.add('hidden');
            document.getElementById('edit_location_form_wrapper').classList.remove('hidden');
        } catch (e) {
            document.getElementById('edit_location_loading').classList.add('hidden');
            document.getElementById('edit_location_form_wrapper').classList.remove('hidden');
            document.getElementById('locations_list').innerHTML =
                '<p class="text-sm text-red-500 text-center py-3">Failed to load locations.</p>';
        }
    });
});

// Select all toggle
let selectAllState = false;
document.getElementById('select_all_locations').addEventListener('click', function () {
    selectAllState = !selectAllState;
    document.querySelectorAll('.location-checkbox').forEach(cb => { cb.checked = selectAllState; });
    this.textContent = selectAllState ? 'Deselect all' : 'Select all';
});

// Save
document.getElementById('save_locations_btn').addEventListener('click', async () => {
    const checked = [...document.querySelectorAll('.location-checkbox:checked')].map(cb => parseInt(cb.value));
    const btn = document.getElementById('save_locations_btn');
    btn.disabled = true;
    btn.textContent = 'Saving…';

    try {
        const res  = await fetch(`/admin/users/${editUserId}/locations`, {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ location_ids: checked }),
        });
        const data = await res.json();
        const result = document.getElementById('edit_location_result');
        result.classList.remove('hidden', 'bg-green-50', 'text-green-800', 'bg-red-50', 'text-red-800');
        result.classList.add('bg-green-50', 'text-green-800');
        result.textContent = '✓ ' + data.message;
        setTimeout(() => closeAllModals(), 1200);
    } catch (e) {
        const result = document.getElementById('edit_location_result');
        result.classList.remove('hidden');
        result.classList.add('bg-red-50', 'text-red-800');
        result.textContent = '✗ Failed to save locations.';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Save Locations';
    }
});
</script>
@endpush
