@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Global stats --}}
<div class="grid grid-cols-2 gap-4 lg:grid-cols-6 mb-6">
    @php
    $cards = [
        ['label' => 'Total NOCs',    'value' => $globalStats['total_nocs'],      'sub' => $globalStats['active_nocs'] . ' active'],
        ['label' => 'Online NOCs',   'value' => $globalStats['online_nocs'],      'sub' => 'connected'],
        ['label' => 'Screens',       'value' => $globalStats['total_playbacks'],  'sub' => $globalStats['playing'] . ' playing'],
        ['label' => 'Offline',       'value' => $globalStats['offline_screens'],  'sub' => 'screens', 'danger' => true],
        ['label' => 'Total Errors',  'value' => ($errorTotals ? ($errorTotals->kdm + $errorTotals->sound + $errorTotals->projector + $errorTotals->server + $errorTotals->storage) : 0), 'sub' => 'all types', 'danger' => true],
        ['label' => 'KDM Errors',    'value' => $errorTotals?->kdm ?? 0,         'sub' => 'KDM issues', 'danger' => true],
    ];
    @endphp
    @foreach($cards as $c)
        <div class="rounded-xl bg-white border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500">{{ $c['label'] }}</p>
            <p class="mt-1 text-3xl font-bold {{ (!empty($c['danger']) && $c['value'] > 0) ? 'text-red-600' : 'text-gray-900' }}">{{ $c['value'] }}</p>
            <p class="mt-0.5 text-xs text-gray-400">{{ $c['sub'] }}</p>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

    {{-- NOC Status --}}
    <div class="rounded-xl bg-white border border-gray-200">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">NOC Instances</h2>
            <a href="{{ route('admin.noc-instances.index') }}" class="text-xs text-blue-600 hover:underline">Manage →</a>
        </div>
        <ul class="divide-y divide-gray-100">
            @forelse($nocStats as $noc)
                <li class="flex items-center gap-4 px-5 py-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600 shrink-0">
                        {{ strtoupper(substr($noc['name'], 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $noc['name'] }}</p>
                        <p class="text-xs text-gray-400">{{ $noc['locations_count'] }} locations · {{ $noc['users_count'] }} users · Last sync: {{ $noc['last_sync_at'] ? \Carbon\Carbon::parse($noc['last_sync_at'])->diffForHumans() : 'never' }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $noc['status_class'] }}">
                            {{ ucfirst($noc['sync_status']) }}
                        </span>
                        @if(!$noc['is_active'])
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">Inactive</span>
                        @endif
                    </div>
                </li>
            @empty
                <li class="px-5 py-8 text-center text-sm text-gray-400">No NOC instances configured.</li>
            @endforelse
        </ul>
    </div>

    {{-- Recent Sync Logs --}}
    <div class="rounded-xl bg-white border border-gray-200">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Recent Syncs</h2>
            <a href="{{ route('admin.sync-logs.index') }}" class="text-xs text-blue-600 hover:underline">All logs →</a>
        </div>
        <ul class="divide-y divide-gray-100">
            @forelse($recentLogs as $log)
                <li class="flex items-center gap-3 px-5 py-3">
                    <span class="inline-flex rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 shrink-0">{{ $log->sync_type }}</span>
                    <span class="flex-1 text-xs text-gray-600 truncate">{{ $log->nocInstance?->name }}</span>
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium shrink-0
                          {{ $log->status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $log->records_synced }} rec
                    </span>
                    <span class="text-xs text-gray-400 shrink-0">{{ $log->started_at?->diffForHumans() }}</span>
                </li>
            @empty
                <li class="px-5 py-8 text-center text-sm text-gray-400">No sync activity yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
