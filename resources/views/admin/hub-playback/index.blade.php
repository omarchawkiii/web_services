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
                <div class="screen-card rounded-xl border border-gray-200 bg-white p-3 flex flex-col gap-2"
                     data-id="{{ $pb->id }}"
                     data-name="{{ $pb->screen?->screen_name ?? 'Screen' }}"
                     style="cursor:pointer">

                    {{-- Screen name --}}
                    <div class="rounded-lg px-2 py-1 text-center text-xs font-semibold {{ $btnColor }} truncate">
                        {{ $pb->screen?->screen_name ?? 'Screen' }}
                    </div>

                    {{-- Row 1: Playback + Projector + Lamp + Dowser --}}
                    <div class="flex justify-center gap-1 text-lg">
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

                        @if($isOffline || !$pb->projector_status)
                            <span title="Projector off" class="text-gray-300">📽</span>
                        @elseif($pb->projector_lamp_stat === 'Normal')
                            <span title="Projector OK" class="text-green-500">📽</span>
                        @else
                            <span title="Projector warning" class="text-amber-500">📽</span>
                        @endif

                        @if(!$isOffline && $pb->lamp_status === 'On')
                            <span title="Lamp on" class="text-green-500">💡</span>
                        @else
                            <span title="Lamp off" class="text-gray-300">💡</span>
                        @endif

                        @if(!$isOffline && $pb->dowser_status === 'Open')
                            <span title="Dowser open" class="text-green-500">○</span>
                        @else
                            <span title="Dowser closed" class="text-gray-300">○</span>
                        @endif
                    </div>

                    {{-- Row 2: Monitor + Storage + Schedule + Sound --}}
                    <div class="flex justify-center gap-1 text-lg">
                        @if(!$isOffline && $pb->ip_management_server_status !== 'Offline' && $pb->soap_session)
                            <span title="Server online" class="text-green-500">🖥</span>
                        @else
                            <span title="Server offline" class="text-gray-300">🖥</span>
                        @endif

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

                        @if(!$isOffline && $pb->schedule_mode === 'Running')
                            <span title="Schedule running" class="text-green-500">📅</span>
                        @else
                            <span title="Schedule stopped" class="text-gray-300">📅</span>
                        @endif

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

{{-- ── Detail Modal ─────────────────────────────────────────────────────────── --}}
<div id="detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl flex flex-col max-h-[90vh]">

        {{-- Modal header --}}
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 shrink-0">
            <h3 class="text-base font-bold text-gray-900" id="modal-title">Screen Detail</h3>
            <button onclick="closeDetail()" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-1 border-b border-gray-200 px-6 shrink-0">
            @foreach(['playback' => '▶ Playback', 'server' => '🖥 Server', 'projector' => '📽 Projector', 'sound' => '🔊 Sound'] as $tab => $label)
                <button onclick="switchTab('{{ $tab }}')"
                        id="tab-{{ $tab }}"
                        class="tab-btn px-4 py-2.5 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-800 -mb-px transition-colors">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Loading --}}
        <div id="modal-loading" class="flex items-center justify-center py-16">
            <svg class="h-6 w-6 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
        </div>

        {{-- Tab content --}}
        <div id="modal-content" class="hidden overflow-y-auto px-6 py-4 flex-1">

            {{-- Playback tab --}}
            <div id="tab-content-playback" class="tab-content space-y-4">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Status</p><p class="mt-0.5 font-semibold text-sm" id="d-playback-status">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">SPL Title</p><p class="mt-0.5 font-semibold text-sm truncate" id="d-spl-title">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">CPL Title</p><p class="mt-0.5 font-semibold text-sm truncate" id="d-cpl-title">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Elapsed</p><p class="mt-0.5 font-semibold text-sm" id="d-elapsed">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Remaining</p><p class="mt-0.5 font-semibold text-sm" id="d-remaining">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Progress</p><p class="mt-0.5 font-semibold text-sm" id="d-progress">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Projector</p><p class="mt-0.5 font-semibold text-sm" id="d-projector">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Lamp</p><p class="mt-0.5 font-semibold text-sm" id="d-lamp">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Dowser</p><p class="mt-0.5 font-semibold text-sm" id="d-dowser">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Sound Model</p><p class="mt-0.5 font-semibold text-sm" id="d-sound-model">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Mute</p><p class="mt-0.5 font-semibold text-sm" id="d-mute">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Format</p><p class="mt-0.5 font-semibold text-sm" id="d-format">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Security Manager</p><p class="mt-0.5 font-semibold text-sm" id="d-security">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Schedule Mode</p><p class="mt-0.5 font-semibold text-sm" id="d-schedule">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Storage</p><p class="mt-0.5 font-semibold text-sm" id="d-storage">—</p></div>
                </div>
            </div>

            {{-- Server tab --}}
            <div id="tab-content-server" class="tab-content hidden space-y-4">
                <h4 class="text-xs font-bold uppercase text-gray-400 tracking-wider">Server Info</h4>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Serial Number</p><p class="mt-0.5 font-semibold text-sm" id="d-serial">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Software</p><p class="mt-0.5 font-semibold text-sm" id="d-software">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Firmware</p><p class="mt-0.5 font-semibold text-sm" id="d-firmware">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Bundle</p><p class="mt-0.5 font-semibold text-sm" id="d-bundle">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Certificate Date</p><p class="mt-0.5 font-semibold text-sm" id="d-cert">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Show Title</p><p class="mt-0.5 font-semibold text-sm" id="d-show-title">—</p></div>
                </div>

                <h4 class="text-xs font-bold uppercase text-gray-400 tracking-wider mt-4">Storage Devices</h4>
                <div id="d-storage-devices" class="space-y-2"></div>

                <h4 class="text-xs font-bold uppercase text-gray-400 tracking-wider mt-4">SMART Data</h4>
                <div id="d-smarts" class="space-y-2"></div>
            </div>

            {{-- Projector tab --}}
            <div id="tab-content-projector" class="tab-content hidden space-y-4">
                <h4 class="text-xs font-bold uppercase text-gray-400 tracking-wider">Projector Info</h4>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Model</p><p class="mt-0.5 font-semibold text-sm" id="d-proj-model">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Serial Number</p><p class="mt-0.5 font-semibold text-sm" id="d-proj-serial">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Manufacture Date</p><p class="mt-0.5 font-semibold text-sm" id="d-proj-date">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Software</p><p class="mt-0.5 font-semibold text-sm" id="d-proj-sw">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Operating Hours</p><p class="mt-0.5 font-semibold text-sm" id="d-proj-hours">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Power Status</p><p class="mt-0.5 font-semibold text-sm" id="d-proj-power">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Shutter</p><p class="mt-0.5 font-semibold text-sm" id="d-proj-shutter">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Light Status</p><p class="mt-0.5 font-semibold text-sm" id="d-proj-light">—</p></div>
                </div>

                <h4 class="text-xs font-bold uppercase text-gray-400 tracking-wider mt-4">Lamp</h4>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Model</p><p class="mt-0.5 font-semibold text-sm" id="d-lamp-model">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Serial</p><p class="mt-0.5 font-semibold text-sm" id="d-lamp-serial">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Hours</p><p class="mt-0.5 font-semibold text-sm" id="d-lamp-hours">—</p></div>
                </div>

                <h4 class="text-xs font-bold uppercase text-gray-400 tracking-wider mt-4">Lamp History</h4>
                <div id="d-lamp-histories" class="space-y-2"></div>
            </div>

            {{-- Sound tab --}}
            <div id="tab-content-sound" class="tab-content hidden space-y-4">
                <h4 class="text-xs font-bold uppercase text-gray-400 tracking-wider">Sound System Info</h4>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Model</p><p class="mt-0.5 font-semibold text-sm" id="d-snd-model">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Serial Number</p><p class="mt-0.5 font-semibold text-sm" id="d-snd-serial">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">CAT-1700 Serial</p><p class="mt-0.5 font-semibold text-sm" id="d-snd-cat">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Software</p><p class="mt-0.5 font-semibold text-sm" id="d-snd-sw">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Bypass</p><p class="mt-0.5 font-semibold text-sm" id="d-snd-bypass">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Power Supply</p><p class="mt-0.5 font-semibold text-sm" id="d-snd-power">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">AES Status</p><p class="mt-0.5 font-semibold text-sm" id="d-snd-aes">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Alert</p><p class="mt-0.5 font-semibold text-sm" id="d-snd-alert">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Fader</p><p class="mt-0.5 font-semibold text-sm" id="d-snd-fader">—</p></div>
                    <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-gray-500">Bit Stream</p><p class="mt-0.5 font-semibold text-sm" id="d-snd-bitstream">—</p></div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Auto-refresh every 30 seconds (only if modal is closed)
    setTimeout(() => { if (modal.classList.contains('hidden')) location.reload(); }, 30000);

    // Click handler on screen cards
    document.querySelectorAll('.screen-card').forEach(function(card) {
        card.addEventListener('click', function() {
            openDetail(this.dataset.id, this.dataset.name);
        });
    });

    const modal      = document.getElementById('detail-modal');
    const loading    = document.getElementById('modal-loading');
    const content    = document.getElementById('modal-content');
    const detailBase = '{{ url('admin/playback') }}';
    let activeTab    = 'playback';

    function v(val) { return val ?? '—'; }

    function openDetail(id, screenName) {
        document.getElementById('modal-title').textContent = screenName;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        loading.classList.remove('hidden');
        content.classList.add('hidden');
        switchTab('playback');

        fetch(`${detailBase}/${id}/detail`)
            .then(r => r.json())
            .then(data => {
                fillPlayback(data.playback);
                fillServer(data.server_detail, data.storage_devices, data.server_smarts);
                fillProjector(data.projector_detail, data.light_histories);
                fillSound(data.sound_detail, data.playback);
                loading.classList.add('hidden');
                content.classList.remove('hidden');
            })
            .catch(() => {
                loading.innerHTML = '<p class="text-sm text-red-500">Failed to load data.</p>';
            });
    }

    function closeDetail() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function switchTab(tab) {
        activeTab = tab;
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('border-blue-500', 'text-blue-600');
            b.classList.add('border-transparent', 'text-gray-500');
        });
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        document.getElementById('tab-' + tab).classList.add('border-blue-500', 'text-blue-600');
        document.getElementById('tab-' + tab).classList.remove('border-transparent', 'text-gray-500');
        document.getElementById('tab-content-' + tab).classList.remove('hidden');
    }

    function set(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = v(val);
    }

    function fillPlayback(pb) {
        set('d-playback-status', pb.playback_status);
        set('d-spl-title',       pb.spl_title);
        set('d-cpl-title',       pb.cpl_title);
        set('d-elapsed',         pb.elapsed_runtime);
        set('d-remaining',       pb.remaining_runtime);
        set('d-progress',        pb.progress_bar !== null ? pb.progress_bar + '%' : null);
        set('d-projector',       pb.projector_lamp_stat);
        set('d-lamp',            pb.lamp_status);
        set('d-dowser',          pb.dowser_status);
        set('d-sound-model',     pb.sound_model);
        set('d-mute',            pb.mute_status);
        set('d-format',          pb.format_status);
        set('d-security',        pb.security_manager);
        set('d-schedule',        pb.schedule_mode);
        set('d-storage',         pb.storage_generale_status);
    }

    function fillServer(sd, devices, smarts) {
        set('d-serial',     sd?.serial_number);
        set('d-software',   sd?.main_software_version);
        set('d-firmware',   sd?.main_firmware_version);
        set('d-bundle',     sd?.bundle_version);
        set('d-cert',       sd?.certificat_date);
        set('d-show-title', sd?.show_title);

        const busLabels   = {1:'Unknown',2:'IDE',3:'USB',4:'SATA',5:'SAS',6:'Firewire'};
        const typeLabels  = {1:'Unknown',2:'CF',3:'SSD',4:'HDD'};
        const stateLabels = {1:'Undefined',2:'Not Applicable',3:'Normal',4:'Warning',5:'Error'};
        const decodeEnum  = (map, val) => val != null ? (map[val] ?? val) + ' (' + val + ')' : '—';

        const devEl = document.getElementById('d-storage-devices');
        devEl.innerHTML = devices && devices.length ? devices.map(d =>
            `<div class="rounded-lg border border-gray-200 px-3 py-2 text-xs grid grid-cols-3 gap-2">
                <span><span class="text-gray-400">Model:</span> ${v(d.model)}</span>
                <span><span class="text-gray-400">Serial:</span> ${v(d.serial_number)}</span>
                <span><span class="text-gray-400">State:</span> ${decodeEnum(stateLabels, d.working_state)}</span>
                <span><span class="text-gray-400">Bus:</span> ${decodeEnum(busLabels, d.bus)}</span>
                <span><span class="text-gray-400">Capacity:</span> ${v(d.capacity)}</span>
                <span><span class="text-gray-400">Type:</span> ${decodeEnum(typeLabels, d.type)}</span>
                <span><span class="text-gray-400">Index:</span> ${v(d.index_storage)}</span>
                <span><span class="text-gray-400">Title:</span> ${v(d.title)}</span>
                <span><span class="text-gray-400">Version:</span> ${v(d.version)}</span>
            </div>`
        ).join('') : '<p class="text-xs text-gray-400">No storage data</p>';

        const smartEl = document.getElementById('d-smarts');
        smartEl.innerHTML = smarts && smarts.length ? smarts.map(s =>
            `<div class="rounded-lg border border-gray-200 px-3 py-2 text-xs grid grid-cols-3 gap-2">
                <span><span class="text-gray-400">Health:</span> ${v(s.overall_health)}</span>
                <span><span class="text-gray-400">Power Hours:</span> ${v(s.power_on_hours)}</span>
                <span><span class="text-gray-400">Temperature:</span> ${v(s.temperature)}</span>
                <span><span class="text-gray-400">SMART:</span> ${v(s.smart_support)}</span>
                <span><span class="text-gray-400">Scan Date:</span> ${v(s.scan_date)}</span>
                <span><span class="text-gray-400">UDMA Error:</span> ${v(s.udma_error)}</span>
            </div>`
        ).join('') : '<p class="text-xs text-gray-400">No SMART data</p>';
    }

    function fillProjector(pd, lamps) {
        set('d-proj-model',   pd?.system_model);
        set('d-proj-serial',  pd?.system_serial_number);
        set('d-proj-date',    pd?.system_manufacture_date);
        set('d-proj-sw',      pd?.system_software_version);
        set('d-proj-hours',   pd?.operating_hours);
        set('d-proj-power',   pd?.power_status);
        set('d-proj-shutter', pd?.shutter_status);
        set('d-proj-light',   pd?.light_status);
        set('d-lamp-model',   pd?.light_model);
        set('d-lamp-serial',  pd?.light_serial);
        set('d-lamp-hours',   pd?.light_hours);

        const lampEl = document.getElementById('d-lamp-histories');
        lampEl.innerHTML = lamps && lamps.length ? lamps.map(l =>
            `<div class="rounded-lg border border-gray-200 px-3 py-2 text-xs grid grid-cols-3 gap-2">
                <span><span class="text-gray-400">Index:</span> ${v(l.index_lamp)}</span>
                <span><span class="text-gray-400">Serial:</span> ${v(l.serial_number)}</span>
                <span><span class="text-gray-400">Hours:</span> ${v(l.hours)}</span>
                <span><span class="text-gray-400">Date:</span> ${v(l.date_lamp)}</span>
                <span><span class="text-gray-400">Type:</span> ${v(l.type)}</span>
                <span><span class="text-gray-400">Power Range:</span> ${v(l.power_range)}</span>
            </div>`
        ).join('') : '<p class="text-xs text-gray-400">No lamp history</p>';
    }

    function fillSound(sd, pb) {
        set('d-snd-model',      sd?.model);
        set('d-snd-serial',     sd?.serial_number);
        set('d-snd-cat',        sd?.cat1700_serial_number);
        set('d-snd-sw',         sd?.software);
        set('d-snd-bypass',     sd?.bypass);
        set('d-snd-power',      sd?.power_supply);
        set('d-snd-aes',        sd?.aes_status);
        set('d-snd-alert',      sd?.alert);
        set('d-snd-fader',      pb?.fader_status);
        set('d-snd-bitstream',  pb?.bit_stream);
    }

    // Close modal on backdrop click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeDetail();
    });
</script>
@endpush
