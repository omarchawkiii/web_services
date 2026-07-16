@extends('layouts.admin')
@section('title', 'All Errors')
@section('page-title', 'All Errors')

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
    .type-badge {
        display: inline-block;
        background: #4338ca;
        color: #fff;
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 500;
        text-transform: capitalize;
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
@php
    $typeList = ['server','projector','sound','storage','tms'];
    $total = $totals->sum();
@endphp
<div class="grid grid-cols-3 gap-4 lg:grid-cols-6 mb-6">
    <a href="{{ request()->fullUrlWithQuery(['device_type' => null]) }}"
       class="rounded-xl bg-white border border-gray-200 p-4 hover:border-gray-300 transition-colors">
        <p class="text-xs font-medium text-gray-500">Total</p>
        <p class="mt-1 text-2xl font-bold {{ $total > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $total }}</p>
    </a>
    @foreach($typeList as $t)
        <a href="{{ request()->fullUrlWithQuery(['device_type' => $t]) }}"
           class="rounded-xl bg-white border border-gray-200 p-4 hover:border-gray-300 transition-colors">
            <p class="text-xs font-medium text-gray-500">{{ ucfirst($t) }}</p>
            <p class="mt-1 text-2xl font-bold {{ ($totals[$t] ?? 0) > 0 ? 'text-red-600' : 'text-gray-400' }}">
                {{ $totals[$t] ?? 0 }}
            </p>
        </a>
    @endforeach
</div>

{{-- Filters --}}
<form id="filter-form" method="GET" action="{{ route('admin.hub-unified-errors.index') }}"
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
    <input type="hidden" name="device_type" value="{{ $typeFilter }}">
</form>

{{-- Type tabs --}}
<div class="mb-4 flex flex-wrap gap-2">
    <a href="{{ request()->fullUrlWithQuery(['device_type' => null]) }}"
       class="rounded-lg px-3 py-2 text-sm font-medium transition-colors
              {{ !$typeFilter ? 'bg-slate-800 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
        All
    </a>
    @foreach($typeList as $t)
        <a href="{{ request()->fullUrlWithQuery(['device_type' => $t]) }}"
           class="rounded-lg px-3 py-2 text-sm font-medium transition-colors
                  {{ $typeFilter === $t ? 'bg-slate-800 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            {{ ucfirst($t) }}
        </a>
    @endforeach
</div>

{{-- List --}}
<div class="rounded-xl bg-white border border-gray-200 overflow-hidden">
    @if($data->isEmpty())
        <div class="px-5 py-10 text-center text-sm text-gray-400">No data</div>
    @else
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">NOC</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Location</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
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
                    <tr class="clickable-row" onclick="toggleDetail('unified-{{ $i }}')">
                        <td class="px-4 py-3"><span class="inline-flex rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">{{ $row->nocInstance?->name }}</span></td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row->location?->name ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="type-badge">{{ $row->device_type }}</span></td>
                        <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">{{ $row->display_message ?? $row->message ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="sev-badge {{ $sevClass }}">{{ $row->severity ?? '—' }}</span></td>
                        <td class="px-4 py-3"><span class="screen-badge">{{ $row->screen_name ?? '—' }}</span> <span class="err-chevron">▼</span></td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $row->synced_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                    <tr id="unified-{{ $i }}" style="display:none;">
                        <td colspan="7" class="px-0 py-0">
                            <div class="err-detail-panel">
                                <div class="err-raw-msg">{{ $row->display_message ?? $row->message ?? '—' }}</div>
                                <div class="err-detail-grid">
                                    <div>
                                        <div class="err-detail-section-label">Error Details</div>
                                        @if($row->brand || $row->model)
                                        <div class="err-detail-item">Brand / Model: <span>{{ $row->brand ?? '—' }} {{ $row->model ?? '' }}</span></div>
                                        @endif
                                        @if($row->serial_number)
                                        <div class="err-detail-item">Serial Number: <span>{{ $row->serial_number }}</span></div>
                                        @endif
                                        <div class="err-detail-item">Device IP: <span>{{ $row->device_ip ?? '—' }}</span></div>
                                        <div class="err-detail-item">Date: <span>{{ $row->date_error ?? '—' }}</span></div>
                                        <div class="err-detail-item">Screen: <span>{{ $row->screen_name ?? '—' }}</span></div>
                                        @if($row->screen_number)
                                        <div class="err-detail-item">Screen Number: <span>{{ $row->screen_number }}</span></div>
                                        @endif
                                    </div>
                                    @if($row->device_sub_type)
                                    <div>
                                        <div class="err-detail-section-label">Device Sub-Type ({{ $row->device_sub_type }})</div>
                                        <div class="err-detail-item">IP: <span>{{ $row->device_sub_type_ip ?? '—' }}</span></div>
                                        <div class="err-detail-item">Model: <span>{{ $row->device_sub_type_model ?? '—' }}</span></div>
                                        <div class="err-detail-item">Title: <span>{{ $row->device_sub_type_title ?? '—' }}</span></div>
                                    </div>
                                    @endif
                                    @if($row->movie_title || $row->spl_title || $row->session_start)
                                    <div>
                                        <div class="err-detail-section-label">Playback</div>
                                        @if($row->movie_title)
                                        <div class="err-detail-item">Movie Title: <span>{{ $row->movie_title }}</span></div>
                                        @endif
                                        @if($row->spl_title)
                                        <div class="err-detail-item">SPL Title: <span>{{ $row->spl_title }}</span></div>
                                        @endif
                                        @if($row->session_start)
                                        <div class="err-detail-item">Session Start: <span>{{ $row->session_start }}</span></div>
                                        @endif
                                    </div>
                                    @endif
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
    @endif
</div>

<script>
function toggleDetail(id) {
    const row = document.getElementById(id);
    if (!row) return;
    const isOpen = row.style.display !== 'none';
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
