@extends('layouts.admin')
@section('title', 'Errors')
@section('page-title', 'Errors')

@section('content')

<style>
    .sev-medium   { background:#cff4fc; color:#0c7c9a; }
    .sev-warning  { background:#fff3cd; color:#856404; }
    .sev-high     { background:#ffe5cc; color:#b45309; }
    .sev-critical { background:#fde8e8; color:#b91c1c; }
    .sev-default  { background:#fff3cd; color:#856404; }

    .err-detail-panel {
        background: #0f1225;
        color: #c9d1e8;
        padding: 16px 20px;
        border-radius: 8px;
        font-size: 0.82rem;
    }
    .err-raw-msg {
        font-size: 0.85rem;
        color: #e2e8f0;
        margin-bottom: 14px;
        padding: 10px 14px;
        background: rgba(255,255,255,0.05);
        border-left: 3px solid #4f6ef7;
        border-radius: 4px;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .err-detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }
    .err-detail-section-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #7c8db0;
        margin-bottom: 8px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        padding-bottom: 4px;
    }
    .err-detail-item {
        font-size: 0.8rem;
        color: #9ca8c4;
        margin-bottom: 4px;
    }
    .err-detail-item span {
        color: #e2e8f0;
        font-weight: 500;
    }
    .clickable-row { cursor: pointer; }
    .clickable-row:hover td { background: #f8fafc; }
    .row-expanded td { background: #f1f5f9 !important; }
    .err-chevron { font-size: 0.65rem; color: #94a3b8; margin-left: 4px; }
    .screen-badge {
        display: inline-block;
        background: #0e7490;
        color: #fff;
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 500;
    }
    .sev-badge {
        display: inline-block;
        font-size: 0.72rem;
        padding: 2px 8px;
        border-radius: 9999px;
        font-weight: 600;
    }
</style>

{{-- Summary cards --}}
<div class="grid grid-cols-3 gap-4 lg:grid-cols-6 mb-6">
    @php
    $cards = [
        ['label' => 'Total',     'value' => $totals['total'],     'tab' => 'summary'],
        ['label' => 'Server',    'value' => $totals['server'],    'tab' => 'server'],
        ['label' => 'Projector', 'value' => $totals['projector'], 'tab' => 'projector'],
        ['label' => 'Sound',     'value' => $totals['sound'],     'tab' => 'sound'],
        ['label' => 'Storage',   'value' => $totals['storage'],   'tab' => 'storage'],
        ['label' => 'TMS',       'value' => $totals['tms'],       'tab' => 'tms'],
    ];
    @endphp
    @foreach($cards as $card)
        <a href="{{ request()->fullUrlWithQuery(['tab' => $card['tab']]) }}"
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
$tabs = ['summary','server','projector','sound','storage','tms'];
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

    {{-- SUMMARY --}}
    @if($tab === 'summary')
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">NOC</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Location</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Server</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Projector</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Sound</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Storage</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">TMS</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Synced</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($summaries as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3"><span class="inline-flex rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">{{ $s->nocInstance?->name }}</span></td>
                        <td class="px-5 py-3 text-sm text-gray-700">{{ $s->location?->name }}</td>
                        @foreach(['nbr_server_alert','nbr_projector_alert','nbr_sound_alert','nbr_storage_errors','nbr_tms_alert'] as $field)
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

    {{-- EMPTY --}}
    @elseif($data->isEmpty())
        <div class="px-6 py-12 text-center text-sm text-gray-400">No {{ $tab }} errors found.</div>

    {{-- SERVER --}}
    @elseif($tab === 'server')
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">NOC</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Location</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Message</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Severity</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Screen</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Synced</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="server-tbody">
                @foreach($data as $i => $row)
                    @php
                        $sev = strtolower($row->criticity ?? '');
                        $sevClass = match($sev) { 'medium'=>'sev-medium','warning'=>'sev-warning','high'=>'sev-high','critical'=>'sev-critical', default=>'sev-default' };
                    @endphp
                    <tr class="clickable-row" onclick="toggleDetail('server-{{ $i }}')">
                        <td class="px-4 py-3"><span class="inline-flex rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">{{ $row->nocInstance?->name }}</span></td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row->location?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">{{ $row->display_message ?? $row->message ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="sev-badge {{ $sevClass }}">{{ $row->criticity ?? '—' }}</span></td>
                        <td class="px-4 py-3"><span class="screen-badge">{{ $row->server_name ?? '—' }}</span> <span class="err-chevron">▼</span></td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $row->synced_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                    <tr id="server-{{ $i }}" style="display:none;">
                        <td colspan="6" class="px-0 py-0">
                            <div class="err-detail-panel">
                                <div class="err-raw-msg">{{ $row->display_message ?? $row->message ?? '—' }}</div>
                                <div class="err-detail-grid">
                                    <div>
                                        <div class="err-detail-section-label">Error Details</div>
                                        <div class="err-detail-item">Model: <span>{{ $row->screen_model ?? '—' }}</span></div>
                                        <div class="err-detail-item">Product Name: <span>{{ $row->show_title ?? '—' }}</span></div>
                                        <div class="err-detail-item">Certificate expiry: <span>{{ $row->certificat_date ?? '—' }}</span></div>
                                        <div class="err-detail-item">Serial number: <span>{{ $row->serial_number ?? '—' }}</span></div>
                                        <div class="err-detail-item">IP: <span>{{ $row->ip_projector ?? '—' }}</span></div>
                                        <div class="err-detail-item">Date: <span>{{ $row->date ?? '—' }}</span></div>
                                        <div class="err-detail-item">Screen: <span>{{ $row->server_name ?? '—' }}</span></div>
                                    </div>
                                    <div>
                                        <div class="err-detail-section-label">Recommended Action</div>
                                        <div class="err-detail-item"><span style="white-space:pre-wrap;word-break:break-word;">{{ $row->recommended_action ?? '—' }}</span></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    {{-- PROJECTOR --}}
    @elseif($tab === 'projector')
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">NOC</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Location</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Message</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Severity</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Screen</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Synced</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($data as $i => $row)
                    @php
                        $sev = strtolower($row->severity ?? '');
                        $sevClass = match($sev) { 'medium'=>'sev-medium','warning'=>'sev-warning','high'=>'sev-high','critical'=>'sev-critical', default=>'sev-default' };
                    @endphp
                    <tr class="clickable-row" onclick="toggleDetail('proj-{{ $i }}')">
                        <td class="px-4 py-3"><span class="inline-flex rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">{{ $row->nocInstance?->name }}</span></td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row->location?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">{{ $row->display_message ?? $row->message ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="sev-badge {{ $sevClass }}">{{ $row->severity ?? '—' }}</span></td>
                        <td class="px-4 py-3"><span class="screen-badge">{{ $row->server_name ?? '—' }}</span> <span class="err-chevron">▼</span></td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $row->synced_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                    <tr id="proj-{{ $i }}" style="display:none;">
                        <td colspan="6" class="px-0 py-0">
                            <div class="err-detail-panel">
                                <div class="err-raw-msg">{{ $row->display_message ?? $row->message ?? '—' }}</div>
                                <div class="err-detail-grid">
                                    <div>
                                        <div class="err-detail-section-label">Error Details</div>
                                        <div class="err-detail-item">Device: <span>{{ ($row->projector_brand ?? '—') . ' ' . ($row->projector_model ?? '') }}</span></div>
                                        <div class="err-detail-item">IP: <span>{{ $row->ip_projector ?? '—' }}</span></div>
                                        <div class="err-detail-item">Date: <span>{{ $row->time_saved ?? '—' }}</span></div>
                                        <div class="err-detail-item">Screen: <span>{{ $row->server_name ?? '—' }}</span></div>
                                    </div>
                                    <div>
                                        <div class="err-detail-section-label">Recommended Action</div>
                                        <div class="err-detail-item"><span style="white-space:pre-wrap;word-break:break-word;">{{ $row->recommended_action ?? '—' }}</span></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    {{-- SOUND --}}
    @elseif($tab === 'sound')
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">NOC</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Location</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Message</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Severity</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Screen</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Synced</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($data as $i => $row)
                    @php
                        $sev = strtolower($row->severity ?? '');
                        $sevClass = match($sev) { 'medium'=>'sev-medium','warning'=>'sev-warning','high'=>'sev-high','critical'=>'sev-critical', default=>'sev-default' };
                    @endphp
                    <tr class="clickable-row" onclick="toggleDetail('snd-{{ $i }}')">
                        <td class="px-4 py-3"><span class="inline-flex rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">{{ $row->nocInstance?->name }}</span></td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row->location?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">{{ $row->display_message ?? $row->message ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="sev-badge {{ $sevClass }}">{{ $row->severity ?? '—' }}</span></td>
                        <td class="px-4 py-3"><span class="screen-badge">{{ $row->screen ?? '—' }}</span> <span class="err-chevron">▼</span></td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $row->synced_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                    <tr id="snd-{{ $i }}" style="display:none;">
                        <td colspan="6" class="px-0 py-0">
                            <div class="err-detail-panel">
                                <div class="err-raw-msg">{{ $row->display_message ?? $row->message ?? '—' }}</div>
                                <div class="err-detail-grid">
                                    <div>
                                        <div class="err-detail-section-label">Error Details</div>
                                        @if($row->device_sub_type === 'amplifier')
                                        <div class="err-detail-item">Device: <span>{{ $row->device_sub_type_title ?? '—' }}</span></div>
                                        <div class="err-detail-item">Amplifier: <span>{{ ($row->device_sub_type_model ?? ' ') . ' - ' . ($row->device_sub_type_title ?? ' ') }}</span></div>
                                        @else
                                        <div class="err-detail-item">Model: <span>{{ ($row->device_sub_type_model ?? ' ') . ' - ' . ($row->device_sub_type_title ?? ' ') }}</span></div>
                                        @endif
                                        <div class="err-detail-item">IP: <span>{{ $row->sound_ip ?? '—' }}</span></div>
                                        <div class="err-detail-item">Date: <span>{{ $row->date_saved ?? '—' }}</span></div>
                                        <div class="err-detail-item">Screen: <span>{{ $row->screen ?? '—' }}</span></div>
                                    </div>
                                    <div>
                                        <div class="err-detail-section-label">Recommended Action</div>
                                        <div class="err-detail-item"><span style="white-space:pre-wrap;word-break:break-word;">{{ $row->recommended_action ?? '—' }}</span></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    {{-- STORAGE --}}
    @elseif($tab === 'storage')
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">NOC</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Location</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Message</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Severity</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Screen</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Synced</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($data as $i => $row)
                    @php
                        $sev = strtolower($row->storage_generale_status ?? '');
                        $sevClass = match($sev) { 'medium'=>'sev-medium','warning'=>'sev-warning','high'=>'sev-high','critical'=>'sev-critical', default=>'sev-default' };
                    @endphp
                    <tr class="clickable-row" onclick="toggleDetail('sto-{{ $i }}')">
                        <td class="px-4 py-3"><span class="inline-flex rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">{{ $row->nocInstance?->name }}</span></td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row->location?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">{{ $row->display_message ?? $row->message ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="sev-badge {{ $sevClass }}">{{ $row->storage_generale_status ?? '—' }}</span></td>
                        <td class="px-4 py-3"><span class="screen-badge">{{ $row->server_name ?? '—' }}</span> <span class="err-chevron">▼</span></td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $row->synced_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                    <tr id="sto-{{ $i }}" style="display:none;">
                        <td colspan="6" class="px-0 py-0">
                            <div class="err-detail-panel">
                                <div class="err-raw-msg">{{ $row->display_message ?? $row->message ?? '—' }}</div>
                                <div class="err-detail-grid">
                                    <div>
                                        <div class="err-detail-section-label">Error Details</div>
                                        <div class="err-detail-item">Device: <span>{{ $row->screen_model ?? '—' }}</span></div>
                                        <div class="err-detail-item">IP: <span>{{ $row->projector_ip ?? '—' }}</span></div>
                                        <div class="err-detail-item">Screen: <span>{{ $row->server_name ?? '—' }}</span></div>
                                    </div>
                                    <div>
                                        <div class="err-detail-section-label">Recommended Action</div>
                                        <div class="err-detail-item"><span style="white-space:pre-wrap;word-break:break-word;">{{ $row->recommended_action ?? '—' }}</span></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    {{-- TMS --}}
    @elseif($tab === 'tms')
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">NOC</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Location</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Message</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Severity</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Screen</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Synced</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($data as $i => $row)
                    @php
                        $sev = strtolower($row->severity ?? '');
                        $sevClass = match($sev) { 'medium'=>'sev-medium','warning'=>'sev-warning','high'=>'sev-high','critical'=>'sev-critical', default=>'sev-default' };
                    @endphp
                    <tr class="clickable-row" onclick="toggleDetail('tms-{{ $i }}')">
                        <td class="px-4 py-3"><span class="inline-flex rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">{{ $row->nocInstance?->name }}</span></td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row->location?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">{{ $row->display_message ?? $row->message ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="sev-badge {{ $sevClass }}">{{ $row->severity ?? '—' }}</span></td>
                        <td class="px-4 py-3"><span class="screen-badge">{{ $row->server_name ?? '—' }}</span> <span class="err-chevron">▼</span></td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $row->synced_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                    <tr id="tms-{{ $i }}" style="display:none;">
                        <td colspan="6" class="px-0 py-0">
                            <div class="err-detail-panel">
                                <div class="err-raw-msg">{{ $row->display_message ?? $row->message ?? '—' }}</div>
                                <div class="err-detail-grid">
                                    <div>
                                        <div class="err-detail-section-label">Error Details</div>
                                        @if($row->device_sub_type === 'playback' || true )
                                        @if($row->movie_title || true )
                                        <div class="err-detail-item">Movie Title: <span>{{ $row->movie_title }}</span></div>
                                        @endif
                                        @if($row->spl_title || true)
                                        <div class="err-detail-item">SPL Title: <span>{{ $row->spl_title }}</span></div>
                                        @endif
                                        @if($row->session_start || true)
                                        <div class="err-detail-item">Session Start: <span>{{ $row->session_start }}</span></div>
                                        @endif
                                        @if($row->time_saved)
                                        <div class="err-detail-item">Detection Time:: <span>{{ $row->time_saved }}</span></div>
                                        @endif
                                        @if($row->screen_model)
                                        <div class="err-detail-item">Model: <span>{{ $row->screen_model }}</span></div>
                                        @endif
                                        @if($row->device_sub_type_ip)
                                        <div class="err-detail-item">IP: <span>{{ $row->device_sub_type_ip }}</span></div>
                                        @endif
                                        <div class="err-detail-item">Screen: <span>{{ $row->server_name ?? '—' }}</span></div>
                                        @else
                                        @if($row->screen_model)
                                        <div class="err-detail-item">Model: <span>{{ $row->screen_model }}</span></div>
                                        @endif
                                        <div class="err-detail-item">Device IP: <span>{{ $row->device_sub_type_ip ?? '—' }}</span></div>
                                        <div class="err-detail-item">Time: <span>{{ $row->time_saved ?? '—' }}</span></div>
                                        <div class="err-detail-item">Screen: <span>{{ $row->server_name ?? '—' }}</span></div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="err-detail-section-label">Recommended Action</div>
                                        <div class="err-detail-item"><span style="white-space:pre-wrap;word-break:break-word;">{{ $row->recommended_action ?? '—' }}</span></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    {{-- RAID --}}
    @elseif($tab === 'raid')
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">NOC</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Location</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Alerts</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Synced</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($data as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><span class="inline-flex rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">{{ $row->nocInstance?->name }}</span></td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row->location?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm font-semibold {{ $row->count_alerts > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $row->count_alerts }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $row->synced_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    {{-- ALARMS --}}
    @elseif($tab === 'alarms')
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">NOC</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Location</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Screen</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">State</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Synced</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($data as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><span class="inline-flex rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">{{ $row->nocInstance?->name }}</span></td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row->location?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $row->screen?->screen_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $row->alarm_working_state ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row->title ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $row->synced_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</div>

<script>
function toggleDetail(id) {
    const row = document.getElementById(id);
    if (!row) return;
    const isOpen = row.style.display !== 'none';
    // close all detail rows in the same table
    const tbody = row.closest('tbody');
    if (tbody) {
        tbody.querySelectorAll('[id]').forEach(r => {
            if (r !== row) r.style.display = 'none';
        });
        tbody.querySelectorAll('.clickable-row').forEach(r => r.classList.remove('row-expanded'));
    }
    row.style.display = isOpen ? 'none' : 'table-row';
    if (!isOpen) {
        const clickableRow = row.previousElementSibling;
        if (clickableRow) clickableRow.classList.add('row-expanded');
    }
}
</script>

@endsection
