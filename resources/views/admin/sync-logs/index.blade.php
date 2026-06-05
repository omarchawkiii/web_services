@extends('layouts.admin')
@section('title', 'Sync Logs')
@section('page-title', 'Sync Logs')

@section('content')

<form id="filter-form" method="GET" action="{{ route('admin.sync-logs.index') }}"
      class="mb-5 flex flex-wrap items-end gap-3 rounded-xl bg-white border border-gray-200 px-5 py-4">
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
    <div class="min-w-36">
        <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
        <select name="type" onchange="this.form.submit()"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500">
            <option value="">All Types</option>
            @foreach(['playback','schedules','errors'] as $t)
                <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
    </div>
    <div class="min-w-36">
        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
        <select name="status" onchange="this.form.submit()"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500">
            <option value="">All</option>
            <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
            <option value="error"   {{ request('status') === 'error'   ? 'selected' : '' }}>Error</option>
            <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
        </select>
    </div>
    @if(request()->hasAny(['noc','type','status']))
        <div><label class="block text-xs mb-1 invisible">.</label>
        <a href="{{ route('admin.sync-logs.index') }}" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
        </a></div>
    @endif
</form>

<div class="rounded-xl bg-white border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">NOC</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Records</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Duration</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Started</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Error</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3"><span class="inline-flex rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">{{ $log->nocInstance?->name ?? '—' }}</span></td>
                    <td class="px-5 py-3"><span class="inline-flex rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">{{ $log->sync_type }}</span></td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium
                              {{ $log->status === 'success' ? 'bg-green-100 text-green-800' : ($log->status === 'error' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                            {{ ucfirst($log->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-700">{{ $log->records_synced }}</td>
                    <td class="px-5 py-3 text-sm text-gray-500">
                        @if($log->started_at && $log->completed_at)
                            {{ $log->started_at->diffInSeconds($log->completed_at) }}s
                        @else —
                        @endif
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $log->started_at?->format('d/m H:i:s') }}</td>
                    <td class="px-5 py-3 text-xs text-red-600 max-w-xs truncate" title="{{ $log->error_message }}">{{ $log->error_message ?? '' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-gray-400">No sync logs yet. Start the scheduler.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($logs->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
