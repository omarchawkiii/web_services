@extends('layouts.admin')
@section('title', 'Errors')
@section('page-title', 'Errors')

@section('content')

{{-- Summary cards --}}
<div class="grid grid-cols-3 gap-4 lg:grid-cols-6 mb-6">
    @php
    $cards = [
        ['label' => 'Total',     'value' => $totals['total'],     'color' => 'gray'],
        ['label' => 'KDM',       'value' => $totals['kdm'],       'color' => 'blue'],
        ['label' => 'Server',    'value' => $totals['server'],     'color' => 'red'],
        ['label' => 'Projector', 'value' => $totals['projector'],  'color' => 'amber'],
        ['label' => 'Sound',     'value' => $totals['sound'],      'color' => 'green'],
        ['label' => 'Storage',   'value' => $totals['storage'],    'color' => 'purple'],
    ];
    @endphp
    @foreach($cards as $card)
        <a href="{{ request()->fullUrlWithQuery(['tab' => strtolower($card['label'])]) }}"
           class="rounded-xl bg-white border border-gray-200 p-4 hover:border-gray-300 transition-colors">
            <p class="text-xs font-medium text-gray-500">{{ $card['label'] }}</p>
            <p class="mt-1 text-2xl font-bold {{ $card['value'] > 0 ? 'text-red-600' : 'text-gray-400' }}">
                {{ $card['value'] }}
            </p>
        </a>
    @endforeach
</div>

{{-- Filters --}}
<form id="filter-form" method="GET" action="{{ route('admin.hub-errors.index') }}"
      class="mb-4 flex flex-wrap items-end gap-3 rounded-xl bg-white border border-gray-200 px-5 py-4">
    <div class="min-w-44">
        <label class="block text-xs font-medium text-gray-500 mb-1">NOC</label>
        <select name="noc" onchange="this.form.submit()"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500">
            <option value="">All NOCs</option>
            @foreach($nocInstances as $noc)
                <option value="{{ $noc->id }}" {{ request('noc') == $noc->id ? 'selected' : '' }}>{{ $noc->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="min-w-44">
        <label class="block text-xs font-medium text-gray-500 mb-1">Location</label>
        <select name="location" onchange="this.form.submit()"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500">
            <option value="">All Locations</option>
            @foreach($locations as $loc)
                <option value="{{ $loc->id }}" {{ request('location') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
            @endforeach
        </select>
    </div>
    <input type="hidden" name="tab" value="{{ $tab }}">
</form>

{{-- Tabs --}}
@php
$tabs = ['summary','kdm','server','projector','sound','storage','raid','alarms'];
@endphp
<div class="mb-4 flex flex-wrap gap-2">
    @foreach($tabs as $t)
        <a href="{{ request()->fullUrlWithQuery(['tab' => $t]) }}"
           class="rounded-lg px-3 py-2 text-sm font-medium transition-colors
                  {{ $tab === $t ? 'bg-slate-800 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            {{ ucfirst($t) }}
        </a>
    @endforeach
</div>

{{-- Tab content --}}
<div class="rounded-xl bg-white border border-gray-200 overflow-hidden">
    @if($tab === 'summary')
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">NOC</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Location</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">KDM</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Server</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Projector</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Sound</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Storage</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Synced</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($summaries as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3"><span class="inline-flex rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">{{ $s->nocInstance?->name }}</span></td>
                        <td class="px-5 py-3 text-sm text-gray-700">{{ $s->location?->name }}</td>
                        @foreach(['kdm_errors','nbr_server_alert','nbr_projector_alert','nbr_sound_alert','nbr_storage_errors'] as $field)
                            <td class="px-5 py-3 text-center text-sm font-semibold {{ $s->$field > 0 ? 'text-red-600' : 'text-gray-300' }}">
                                {{ $s->$field }}
                            </td>
                        @endforeach
                        <td class="px-5 py-3 text-xs text-gray-400 text-center">{{ $s->synced_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-10 text-center text-sm text-gray-400">No data</td></tr>
                @endforelse
            </tbody>
        </table>
    @elseif($data->isEmpty())
        <div class="px-6 py-12 text-center text-sm text-gray-400">No {{ $tab }} errors found.</div>
    @else
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">NOC</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Location</th>
                    @if($tab === 'kdm')
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">CPL ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Annotation</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Server</th>
                    @elseif($tab === 'server')
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Event ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Criticity</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Server</th>
                    @elseif($tab === 'projector')
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Title</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Severity</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Server</th>
                    @elseif($tab === 'sound')
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Title</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Severity</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Hardware</th>
                    @elseif($tab === 'storage')
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Server</th>
                    @elseif($tab === 'raid')
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Alerts</th>
                    @elseif($tab === 'alarms')
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Screen</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">State</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Title</th>
                    @endif
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Synced</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($data as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><span class="inline-flex rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">{{ $row->nocInstance?->name }}</span></td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row->location?->name }}</td>
                        @if($tab === 'kdm')
                            <td class="px-4 py-3 text-xs font-mono text-gray-600 max-w-xs truncate">{{ $row->cpl_id ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $row->annotation_text ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row->server_name ?? '—' }}</td>
                        @elseif($tab === 'server')
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row->event_id ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row->type ?? '—' }}</td>
                            <td class="px-4 py-3"><span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-700">{{ $row->criticity ?? '—' }}</span></td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row->server_name ?? '—' }}</td>
                        @elseif($tab === 'projector')
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $row->title ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row->severity ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row->server_name ?? '—' }}</td>
                        @elseif($tab === 'sound')
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $row->title ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row->severity ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row->hardware ?? '—' }}</td>
                        @elseif($tab === 'storage')
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row->server_name ?? '—' }}</td>
                        @elseif($tab === 'raid')
                            <td class="px-4 py-3 text-sm font-semibold {{ $row->count_alerts > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $row->count_alerts }}</td>
                        @elseif($tab === 'alarms')
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row->screen?->screen_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row->alarm_working_state ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $row->title ?? '—' }}</td>
                        @endif
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $row->synced_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
