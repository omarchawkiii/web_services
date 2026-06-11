@extends('layouts.admin')
@section('title', 'Schedules')
@section('page-title', 'Schedules')

@section('content')

{{-- Filters --}}
<form id="filter-form" method="GET" action="{{ route('admin.hub-schedules.index') }}"
      class="mb-4 flex flex-wrap items-end gap-3 rounded-xl bg-white border border-gray-200 px-5 py-4">

    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">
            Date <span class="font-normal text-gray-400">(5:00 AM → 4:59 AM)</span>
        </label>
        <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
    </div>
    <div class="min-w-44">
        <label class="block text-xs font-medium text-gray-500 mb-1">NOC</label>
        <select name="noc" onchange="this.form.submit()"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
            <option value="">All NOCs</option>
            @foreach($nocInstances as $noc)
                <option value="{{ $noc->id }}" {{ request('noc') == $noc->id ? 'selected' : '' }}>{{ $noc->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="min-w-44">
        <label class="block text-xs font-medium text-gray-500 mb-1">Location</label>
        <select name="location" onchange="this.form.submit()"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
            <option value="">All Locations</option>
            @foreach($locations as $loc)
                <option value="{{ $loc->id }}" {{ request('location') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="min-w-44">
        <label class="block text-xs font-medium text-gray-500 mb-1">Screen</label>
        <select name="screen" onchange="this.form.submit()"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
            <option value="">All Screens</option>
            @foreach($screens as $screen)
                <option value="{{ $screen->id }}" {{ request('screen') == $screen->id ? 'selected' : '' }}>
                    {{ $screen->screen_name ?? ('Screen ' . $screen->screen_number) }}
                </option>
            @endforeach
        </select>
    </div>
    <input type="hidden" name="tab" value="{{ $tab }}">
</form>

{{-- Tabs --}}
@php
$tabs = [
    'all'          => ['label' => 'All',           'color' => 'gray'],
    'unlinked'     => ['label' => 'Unlinked',      'color' => 'red'],
    'missing_cpls' => ['label' => 'Missing CPLs',  'color' => 'orange'],
    'missing_kdms' => ['label' => 'Missing KDMs',  'color' => 'amber'],
    'kdm_expired'  => ['label' => 'KDM Expired',   'color' => 'red'],
    'kdm_expiring' => ['label' => 'KDM Expiring',  'color' => 'amber'],
];
@endphp
<div class="mb-4 flex flex-wrap gap-2">
    @foreach($tabs as $key => $cfg)
        @php $active = $tab === $key; $count = $counts[$key] ?? 0; @endphp
        <a href="{{ request()->fullUrlWithQuery(['tab' => $key]) }}"
           class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                  {{ $active ? 'bg-slate-800 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            {{ $cfg['label'] }}
            @if($count > 0)
                <span class="rounded-full px-1.5 py-0.5 text-xs
                      {{ $active ? 'bg-white/20 text-white' : ($count > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500') }}">
                    {{ $count }}
                </span>
            @endif
        </a>
    @endforeach
</div>

{{-- Table --}}
<div class="rounded-xl bg-white border border-gray-200 overflow-hidden">
    @if($schedules->isEmpty())
        <div class="px-6 py-12 text-center text-sm text-gray-500">No schedules for this filter.</div>
    @else
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">NOC</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Location</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Screen</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date/Time</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SPL</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Note</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($schedules as $s)
                    <tr class="{{ $s->status !== 'linked' ? 'bg-red-50' : 'hover:bg-gray-50' }} transition-colors">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">
                                {{ $s->nocInstance?->name ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $s->location?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $s->screen?->screen_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 max-w-xs truncate">{{ $s->display_title }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($s->date_start)->format('d/m H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1 text-base">
                                {{-- SPL icon --}}
                                <span title="SPL" class="{{ $s->status === 'linked' ? 'text-green-500' : 'text-red-500' }}">🎬</span>
                                {{-- CPL icon --}}
                                <span title="CPL" class="{{ $s->status === 'linked' && $s->cpls == 1 ? 'text-green-500' : ($s->status !== 'linked' ? 'text-amber-500' : 'text-red-500') }}">🎞</span>
                                {{-- KDM icon --}}
                                @if($s->status === 'linked' && $s->cpls == 1 && $s->kdm == 1)
                                    <span title="KDM OK" class="text-green-500">🔑</span>
                                @elseif($s->kdm == 2)
                                    <span title="KDM Expired" class="text-red-500">🔑</span>
                                @elseif($s->status === 'linked' && $s->cpls == 1)
                                    <span title="KDM Missing" class="text-red-500">🔑</span>
                                @else
                                    <span title="KDM unknown" class="text-amber-500">🔑</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 max-w-xs">
                            @if($s->status !== 'linked')
                                <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Unlinked</span>
                            @elseif(!empty($s->kdm_notes))
                                @foreach((array)$s->kdm_notes as $note)
                                    @php
                                        $noteClass = match($note['status'] ?? '') {
                                            'valid'         => 'bg-green-100 text-green-800',
                                            'warning'       => 'bg-amber-100 text-amber-800',
                                            'expired'       => 'bg-red-100 text-red-800',
                                            'not_valid_yet' => 'bg-gray-100 text-gray-700',
                                            default         => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <span class="inline-block rounded-full px-2 py-0.5 text-xs {{ $noteClass }} mb-0.5">
                                        {{ ucfirst($note['status'] ?? '') }}: {{ $note['date'] ?? '' }}
                                    </span>
                                @endforeach
                            @elseif($s->list_cpl_notes)
                                <span class="text-red-600">{{ Str::limit($s->list_cpl_notes, 40) }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
