@extends('layouts.admin')

@section('title', 'Locations')
@section('page-title', 'Locations')

@section('header-actions')
    <span class="text-sm text-gray-500">{{ $locations->count() }} result(s)</span>
@endsection

@section('content')

{{-- Filters --}}
<form id="filter-form" method="GET" action="{{ route('admin.locations.index') }}"
      class="mb-5 flex flex-wrap items-end gap-3 rounded-xl bg-white border border-gray-200 px-5 py-4">

    {{-- Search --}}
    <div class="flex-1 min-w-48">
        <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
            <input id="search-input" type="text" name="search" value="{{ request('search') }}"
                   placeholder="Name, city, country, company…"
                   class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
        </div>
    </div>

    {{-- NOC filter --}}
    <div class="min-w-44">
        <label class="block text-xs font-medium text-gray-500 mb-1">NOC Instance</label>
        <select name="noc" onchange="document.getElementById('filter-form').submit()"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
            <option value="">All NOCs</option>
            @foreach($nocInstances as $noc)
                <option value="{{ $noc->id }}" {{ request('noc') == $noc->id ? 'selected' : '' }}>
                    {{ $noc->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Country filter --}}
    @if($countries->isNotEmpty())
    <div class="min-w-40">
        <label class="block text-xs font-medium text-gray-500 mb-1">Country</label>
        <select name="country" onchange="document.getElementById('filter-form').submit()"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
            <option value="">All Countries</option>
            @foreach($countries as $country)
                <option value="{{ $country }}" {{ request('country') === $country ? 'selected' : '' }}>
                    {{ $country }}
                </option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Reset --}}
    @if(request()->hasAny(['search', 'noc', 'country']))
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1 invisible">.</label>
            <a href="{{ route('admin.locations.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </a>
        </div>
    @endif
</form>

@push('scripts')
<script>
    let searchTimer;
    document.getElementById('search-input').addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => document.getElementById('filter-form').submit(), 400);
    });

    // ── Location detail modal ──────────────────────────────────────────────────
    const locModal    = document.getElementById('loc-modal');
    const locBackdrop = document.getElementById('loc-backdrop');
    const locClose    = document.getElementById('loc-close');

    function openLocModal(btn) {
        const name    = btn.dataset.name    || '';
        const country = btn.dataset.country || '—';
        const state   = btn.dataset.state   || '—';
        const city    = btn.dataset.city    || '—';
        const address = btn.dataset.address || '';
        const company = btn.dataset.company || '—';
        const tms     = btn.dataset.tms     || '—';
        const noc     = btn.dataset.noc     || '';

        document.getElementById('modal-initials').textContent = name.substring(0, 2).toUpperCase();
        document.getElementById('modal-name').textContent     = name;
        document.getElementById('modal-noc').textContent      = noc ? ('NOC: ' + noc) : '';

        const addrEl = document.getElementById('modal-address');
        addrEl.textContent   = address;
        addrEl.style.display = address ? '' : 'none';

        const cityStateEl = document.getElementById('modal-city-state');
        const parts = [city !== '—' ? city : '', state !== '—' ? state : ''].filter(Boolean);
        cityStateEl.textContent   = parts.join(', ');
        cityStateEl.style.display = parts.length ? '' : 'none';

        document.getElementById('modal-country').textContent  = country !== '—' ? country : '';
        document.getElementById('modal-country2').textContent = country;
        document.getElementById('modal-state').textContent    = state;
        document.getElementById('modal-city').textContent     = city;
        document.getElementById('modal-tms').textContent      = tms;
        document.getElementById('modal-company').textContent  = company;

        locModal.classList.remove('hidden');
        locModal.classList.add('flex');
    }

    function closeLocModal() {
        locModal.classList.add('hidden');
        locModal.classList.remove('flex');
    }

    document.querySelectorAll('.loc-detail-btn').forEach(btn => {
        btn.addEventListener('click', () => openLocModal(btn));
    });
    locClose.addEventListener('click', closeLocModal);
    locBackdrop.addEventListener('click', closeLocModal);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLocModal(); });
</script>
@endpush

{{-- Active filters badges --}}
@if(request()->hasAny(['search', 'noc', 'country']))
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <span class="text-xs text-gray-500">Active filters:</span>

        @if(request('search'))
            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-medium text-blue-700">
                Search: "{{ request('search') }}"
                <a href="{{ request()->fullUrlWithoutQuery(['search']) }}" class="ml-1 text-blue-400 hover:text-blue-600">×</a>
            </span>
        @endif

        @if(request('noc'))
            <span class="inline-flex items-center gap-1 rounded-full bg-purple-50 border border-purple-200 px-3 py-1 text-xs font-medium text-purple-700">
                NOC: {{ $nocInstances->find(request('noc'))?->name }}
                <a href="{{ request()->fullUrlWithoutQuery(['noc']) }}" class="ml-1 text-purple-400 hover:text-purple-600">×</a>
            </span>
        @endif

        @if(request('country'))
            <span class="inline-flex items-center gap-1 rounded-full bg-green-50 border border-green-200 px-3 py-1 text-xs font-medium text-green-700">
                Country: {{ request('country') }}
                <a href="{{ request()->fullUrlWithoutQuery(['country']) }}" class="ml-1 text-green-400 hover:text-green-600">×</a>
            </span>
        @endif
    </div>
@endif

{{-- Table --}}
<div class="rounded-xl bg-white border border-gray-200 overflow-hidden">

    @if($locations->isEmpty())
        <div class="px-6 py-16 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                </svg>
            </div>
            @if(request()->hasAny(['search', 'noc', 'country']))
                <h3 class="text-sm font-semibold text-gray-900">No results</h3>
                <p class="mt-1 text-sm text-gray-500">Try adjusting your filters.</p>
                <a href="{{ route('admin.locations.index') }}"
                   class="mt-3 inline-block text-sm text-blue-600 hover:underline">Clear all filters</a>
            @else
                <h3 class="text-sm font-semibold text-gray-900">No locations yet</h3>
                <p class="mt-1 text-sm text-gray-500">Run the NOC push command to sync locations.</p>
                <code class="mt-3 inline-block rounded-md bg-gray-100 px-3 py-1.5 text-xs font-mono text-gray-700">
                    php artisan hub:push-users
                </code>
            @endif
        </div>
    @else
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">City</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Country</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Company</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">TMS</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">NOC Source</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach($locations as $i => $location)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-slate-100 text-xs font-bold text-slate-600 shrink-0">
                                    {{ strtoupper(substr($location->name, 0, 2)) }}
                                </div>
                                @if(request('search'))
                                    <span class="text-sm font-medium text-gray-900">
                                        {!! preg_replace('/(' . preg_quote(request('search'), '/') . ')/i', '<mark class="bg-yellow-100 rounded px-0.5">$1</mark>', e($location->name)) !!}
                                    </span>
                                @else
                                    <span class="text-sm font-medium text-gray-900">{{ $location->name }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-700">
                            @if(request('search') && $location->city)
                                {!! preg_replace('/(' . preg_quote(request('search'), '/') . ')/i', '<mark class="bg-yellow-100 rounded px-0.5">$1</mark>', e($location->city)) !!}
                            @else
                                {{ $location->city ?? '—' }}
                            @endif
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-700">{{ $location->country ?? '—' }}</td>
                        <td class="px-5 py-3 text-sm text-gray-700">{{ $location->company ?? '—' }}</td>
                        <td class="px-5 py-3 text-sm text-gray-700">{{ $location->tms_system ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @if($location->nocInstance)
                                <span class="inline-flex items-center gap-1 rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 0 0 3 3h7.5a3 3 0 0 0 3-3m-16.5 0V6.375a3.375 3.375 0 0 1 6.75 0v7.875"/>
                                    </svg>
                                    {{ $location->nocInstance->name }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <button type="button"
                                class="loc-detail-btn inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition-colors"
                                data-name="{{ $location->name }}"
                                data-country="{{ $location->country }}"
                                data-state="{{ $location->state }}"
                                data-city="{{ $location->city }}"
                                data-address="{{ $location->address }}"
                                data-company="{{ $location->company }}"
                                data-tms="{{ $location->tms_system }}"
                                data-noc="{{ $location->nocInstance?->name }}">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                </svg>
                                Details
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- Location Detail Modal --}}
<div id="loc-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div id="loc-backdrop" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="relative w-full max-w-md rounded-2xl bg-white shadow-xl">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <div class="flex items-center gap-3">
                <div id="modal-initials" class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-slate-600"></div>
                <div>
                    <h3 id="modal-name" class="text-base font-semibold text-gray-900"></h3>
                    <p id="modal-noc" class="text-xs text-purple-600 font-medium mt-0.5"></p>
                </div>
            </div>
            <button id="loc-close" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 space-y-4">

            {{-- Address summary block --}}
            <div class="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3">
                <div class="flex items-start gap-3">
                    <svg class="h-4 w-4 text-gray-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                    </svg>
                    <div class="flex-1 space-y-0.5">
                        <p id="modal-address" class="text-sm text-gray-700"></p>
                        <p id="modal-city-state" class="text-sm text-gray-700"></p>
                        <p id="modal-country" class="text-sm font-semibold text-gray-900"></p>
                    </div>
                </div>
            </div>

            {{-- Detail grid --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-lg bg-gray-50 border border-gray-100 px-3 py-2.5">
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-medium mb-0.5">Country</p>
                    <p id="modal-country2" class="text-sm font-medium text-gray-800"></p>
                </div>
                <div class="rounded-lg bg-gray-50 border border-gray-100 px-3 py-2.5">
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-medium mb-0.5">State</p>
                    <p id="modal-state" class="text-sm font-medium text-gray-800"></p>
                </div>
                <div class="rounded-lg bg-gray-50 border border-gray-100 px-3 py-2.5">
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-medium mb-0.5">City</p>
                    <p id="modal-city" class="text-sm font-medium text-gray-800"></p>
                </div>
                <div class="rounded-lg bg-gray-50 border border-gray-100 px-3 py-2.5">
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-medium mb-0.5">TMS</p>
                    <p id="modal-tms" class="text-sm font-medium text-gray-800"></p>
                </div>
                <div class="col-span-2 rounded-lg bg-gray-50 border border-gray-100 px-3 py-2.5">
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-medium mb-0.5">Company</p>
                    <p id="modal-company" class="text-sm font-medium text-gray-800"></p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
