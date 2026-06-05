@extends('layouts.admin')
@section('title', 'Playback')
@section('page-title', 'Playback')

@section('header-actions')
    <div class="flex items-center gap-2 text-xs text-gray-400">
        <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
        Auto-refresh 30s
    </div>
@endsection

@section('content')

{{-- Filters --}}
<form id="filter-form" method="GET" action="{{ route('admin.hub-playback.index') }}"
      class="mb-5 flex flex-wrap items-end gap-3 rounded-xl bg-white border border-gray-200 px-5 py-4">

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

    <div class="min-w-40">
        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
        <select name="status" onchange="this.form.submit()"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
            <option value="">All Status</option>
            @foreach(['Play','Pause','Stop','Unknown','Offline'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>

    @if(request()->hasAny(['noc','location','status']))
        <div>
            <label class="block text-xs mb-1 invisible">.</label>
            <a href="{{ route('admin.hub-playback.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </a>
        </div>
    @endif
</form>

{{-- Playback grid --}}
@forelse($playbacks as $locationId => $group)
    @php $location = $group->first()->location; $noc = $group->first()->nocInstance; @endphp
    <div class="mb-6">
        {{-- Location header --}}
        <div class="mb-3 flex items-center gap-3">
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wide">{{ $location?->name }}</h2>
            @if($noc)
                <span class="inline-flex items-center gap-1 rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 0 0 3 3h7.5a3 3 0 0 0 3-3m-16.5 0V6.375a3.375 3.375 0 0 1 6.75 0v7.875"/></svg>
                    {{ $noc->name }}
                </span>
            @endif
            <span class="text-xs text-gray-400">{{ $group->count() }} screen(s)</span>
        </div>

        {{-- Screen cards --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8">
            @foreach($group as $pb)
                @php
                    $isOffline = $pb->playback_status === 'Offline';
                    $btnColor  = $isOffline ? 'bg-gray-200 text-gray-500'
                        : (($pb->security_manager !== 'Normal' || !$pb->soap_session) ? 'bg-red-500 text-white' : 'bg-green-500 text-white');
                @endphp
                <div class="rounded-xl border border-gray-200 bg-white p-3 flex flex-col gap-2">
                    {{-- Screen name --}}
                    <div class="rounded-lg px-2 py-1 text-center text-xs font-semibold {{ $btnColor }} truncate">
                        {{ $pb->screen?->screen_name ?? 'Screen' }}
                    </div>

                    {{-- Row 1: Playback + Projector + Lamp + Dowser --}}
                    <div class="flex justify-center gap-1 text-lg">
                        {{-- Playback --}}
                        @if($isOffline)
                            <span title="Offline" class="text-gray-300">?</span>
                        @elseif($pb->playback_status === 'Play')
                            <span title="Playing" class="text-green-500">▶</span>
                        @elseif($pb->playback_status === 'Pause')
                            <span title="Paused" class="text-amber-500">⏸</span>
                        @elseif($pb->playback_status === 'Stop')
                            <span title="Stopped" class="text-red-500">⏹</span>
                        @else
                            <span title="Unknown" class="text-amber-400">?</span>
                        @endif

                        {{-- Projector --}}
                        @if($isOffline || !$pb->projector_status)
                            <span title="Projector off" class="text-gray-300">📽</span>
                        @elseif($pb->projector_lamp_stat === 'Normal')
                            <span title="Projector OK" class="text-green-500">📽</span>
                        @else
                            <span title="Projector warning" class="text-amber-500">📽</span>
                        @endif

                        {{-- Lamp --}}
                        @if(!$isOffline && $pb->lamp_status === 'On')
                            <span title="Lamp on" class="text-green-500">💡</span>
                        @else
                            <span title="Lamp off" class="text-gray-300">💡</span>
                        @endif

                        {{-- Dowser --}}
                        @if(!$isOffline && $pb->dowser_status === 'Open')
                            <span title="Dowser open" class="text-green-500">○</span>
                        @else
                            <span title="Dowser closed" class="text-gray-300">○</span>
                        @endif
                    </div>

                    {{-- Row 2: Monitor + Storage + Schedule + Sound --}}
                    <div class="flex justify-center gap-1 text-lg">
                        {{-- Monitor --}}
                        @if(!$isOffline && $pb->ip_management_server_status !== 'Offline' && $pb->soap_session)
                            <span title="Server online" class="text-green-500">🖥</span>
                        @else
                            <span title="Server offline" class="text-gray-300">🖥</span>
                        @endif

                        {{-- Storage --}}
                        @php $sg = $pb->storage_generale_status; @endphp
                        @if($sg === 'Normal')
                            <span title="Storage OK" class="text-green-500">🗄</span>
                        @elseif($sg === 'Error')
                            <span title="Storage Error" class="text-red-500 animate-pulse">🗄</span>
                        @elseif(in_array($sg, ['Yellow','Recovering']))
                            <span title="Storage Warning" class="text-amber-500">🗄</span>
                        @else
                            <span title="Storage unknown" class="text-gray-300">🗄</span>
                        @endif

                        {{-- Schedule --}}
                        @if(!$isOffline && $pb->schedule_mode === 'Running')
                            <span title="Schedule running" class="text-green-500">📅</span>
                        @else
                            <span title="Schedule stopped" class="text-gray-300">📅</span>
                        @endif

                        {{-- Sound --}}
                        @if(!$isOffline && $pb->ip_sound_status == 1 && $pb->mute_status === 'Unmuted')
                            <span title="Sound OK" class="text-green-500">🔊</span>
                        @elseif(!$isOffline && $pb->ip_sound_status == 1)
                            <span title="Muted" class="text-red-500">🔇</span>
                        @else
                            <span title="Sound offline" class="text-gray-300">🔊</span>
                        @endif
                    </div>

                    {{-- SPL title --}}
                    @if($pb->spl_title && !$isOffline)
                        <p class="text-center text-xs text-gray-500 truncate" title="{{ $pb->spl_title }}">
                            {{ Str::limit($pb->spl_title, 20) }}
                        </p>
                    @endif

                    {{-- Progress bar --}}
                    @if($pb->progress_bar !== null && !$isOffline)
                        <div class="w-full h-1.5 rounded-full bg-gray-100">
                            <div class="h-1.5 rounded-full bg-blue-500"
                                 style="width: {{ min(100, $pb->progress_bar) }}%"></div>
                        </div>
                    @endif

                    {{-- Last sync --}}
                    <p class="text-center text-xs text-gray-400">
                        {{ $pb->synced_at ? $pb->synced_at->diffForHumans() : '—' }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="rounded-xl bg-white border border-gray-200 px-6 py-16 text-center">
        <p class="text-sm font-semibold text-gray-900">No playback data</p>
        <p class="mt-1 text-sm text-gray-500">Start the scheduler to sync data from NOCs.</p>
        <code class="mt-3 inline-block rounded-md bg-gray-100 px-3 py-1.5 text-xs font-mono text-gray-700">php artisan schedule:work</code>
    </div>
@endforelse

@endsection

@push('scripts')
<script>
    // Auto-refresh every 30 seconds
    setTimeout(() => location.reload(), 30000);
</script>
@endpush
