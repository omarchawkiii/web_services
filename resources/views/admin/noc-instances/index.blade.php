@extends('layouts.admin')

@section('title', 'NOC Instances')
@section('page-title', 'NOC Instances')

@section('header-actions')
    <a href="{{ route('admin.noc-instances.create') }}"
       class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Add NOC
    </a>
@endsection

@section('content')

<div class="rounded-xl bg-white border border-gray-200 overflow-hidden">

    @if($nocs->isEmpty())
        <div class="px-6 py-16 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 0 0 3 3h7.5a3 3 0 0 0 3-3m-16.5 0V6.375a3.375 3.375 0 0 1 6.75 0v7.875m9.75 0V11.25a3 3 0 0 0-3-3h-4.5a3 3 0 0 0-3 3v3" />
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-900">No NOC instances</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by adding your first NOC instance.</p>
            <a href="{{ route('admin.noc-instances.create') }}"
               class="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Add NOC Instance
            </a>
        </div>
    @else
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">NOC</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">URL</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Last Sync</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Active</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach($nocs as $noc)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">
                                    {{ strtoupper(substr($noc->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $noc->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $noc->admin_username }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ $noc->url }}" target="_blank"
                               class="text-sm text-blue-600 hover:underline">
                                {{ $noc->url }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $noc->getStatusColorClass() }}">
                                <span class="mr-1.5 h-1.5 w-1.5 rounded-full
                                    {{ $noc->sync_status === 'online' ? 'bg-green-500' :
                                       ($noc->sync_status === 'offline' ? 'bg-red-500' :
                                       ($noc->sync_status === 'syncing' ? 'bg-blue-500' : 'bg-gray-400')) }}">
                                </span>
                                {{ ucfirst($noc->sync_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $noc->last_sync_at ? $noc->last_sync_at->diffForHumans() : '—' }}
                        </td>
                        <td class="px-6 py-4">
                            <button type="button"
                                    data-toggle-url="{{ route('admin.noc-instances.toggle', $noc) }}"
                                    class="toggle-active relative inline-flex h-5 w-9 items-center rounded-full transition-colors
                                           {{ $noc->is_active ? 'bg-blue-600' : 'bg-gray-200' }}">
                                <span class="inline-block h-3.5 w-3.5 rounded-full bg-white shadow transition-transform
                                             {{ $noc->is_active ? 'translate-x-4.5' : 'translate-x-0.5' }}">
                                </span>
                            </button>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button"
                                        data-test-url="{{ route('admin.noc-instances.test', $noc) }}"
                                        class="test-connection inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
                                    </svg>
                                    Test
                                </button>
                                <a href="{{ route('admin.noc-instances.edit', $noc) }}"
                                   class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.noc-instances.destroy', $noc) }}"
                                      onsubmit="return confirm('Delete {{ $noc->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-md border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- Toast notification --}}
<div id="toast" class="fixed bottom-6 right-6 hidden z-50">
    <div id="toast-inner" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg">
        <span id="toast-message"></span>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const inner = document.getElementById('toast-inner');
        const msg   = document.getElementById('toast-message');
        msg.textContent = message;
        inner.className = `flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 4000);
    }

    // Test connection
    document.querySelectorAll('.test-connection').forEach(btn => {
        btn.addEventListener('click', async () => {
            const url = btn.dataset.testUrl;
            btn.disabled = true;
            btn.textContent = 'Testing…';

            try {
                const res  = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
                const data = await res.json();

                if (data.status === 'success') {
                    showToast('✓ ' + data.message, 'success');
                    // Update status badge in the row
                    const row   = btn.closest('tr');
                    const badge = row.querySelector('span.inline-flex');
                    if (badge) {
                        badge.className = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800';
                        badge.innerHTML = '<span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-green-500"></span>Online';
                    }
                } else {
                    showToast('✗ ' + data.message, 'error');
                }
            } catch (e) {
                showToast('✗ Request failed', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg> Test`;
            }
        });
    });

    // Toggle active
    document.querySelectorAll('.toggle-active').forEach(btn => {
        btn.addEventListener('click', async () => {
            const url = btn.dataset.toggleUrl;
            const res  = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
            const data = await res.json();

            if (data.is_active) {
                btn.classList.replace('bg-gray-200', 'bg-blue-600');
                btn.querySelector('span').classList.replace('translate-x-0.5', 'translate-x-4.5');
            } else {
                btn.classList.replace('bg-blue-600', 'bg-gray-200');
                btn.querySelector('span').classList.replace('translate-x-4.5', 'translate-x-0.5');
            }
        });
    });
</script>
@endpush
