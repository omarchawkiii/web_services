@extends('layouts.admin')

@section('title', 'Edit ' . $nocInstance->name)
@section('page-title', 'Edit ' . $nocInstance->name)

@section('header-actions')
    <a href="{{ route('admin.noc-instances.index') }}"
       class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
        ← Back
    </a>
@endsection

@section('content')

<div class="max-w-2xl space-y-4">

    {{-- API Key card --}}
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
        <div class="flex items-start gap-3">
            <svg class="h-5 w-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z"/>
            </svg>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-amber-800 mb-2">API Key — copy this into the NOC's <code class="bg-amber-100 px-1 rounded">.env</code></p>
                <div class="flex items-center gap-2">
                    <code id="api-key-value"
                          class="flex-1 truncate rounded-lg border border-amber-300 bg-white px-3 py-2 text-xs font-mono text-gray-800 select-all">{{ $nocInstance->api_key }}</code>
                    <button type="button" id="copy-api-key"
                            class="shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-white px-3 py-2 text-xs font-medium text-amber-700 hover:bg-amber-50 transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/>
                        </svg>
                        Copy
                    </button>
                </div>
                <p class="mt-2 text-xs text-amber-700">
                    In the NOC's <code class="bg-amber-100 px-1 rounded">.env</code>:
                    <span class="font-mono">HUB_URL={{ config('app.url') }}</span> &nbsp;
                    <span class="font-mono">HUB_API_KEY={{ $nocInstance->api_key }}</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Status card --}}
    <div class="rounded-xl bg-white border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-sm font-bold text-slate-600">
                    {{ strtoupper(substr($nocInstance->name, 0, 2)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $nocInstance->name }}</p>
                    <p class="text-xs text-gray-500">
                        Last sync: {{ $nocInstance->last_sync_at ? $nocInstance->last_sync_at->diffForHumans() : 'Never' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $nocInstance->getStatusColorClass() }}">
                    {{ ucfirst($nocInstance->sync_status) }}
                </span>
                <button type="button" id="test-btn"
                        data-test-url="{{ route('admin.noc-instances.test', $nocInstance) }}"
                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
                    </svg>
                    Test Connection
                </button>
            </div>
        </div>
        <div id="test-result" class="mt-3 hidden rounded-md px-3 py-2 text-xs font-medium"></div>
    </div>

    {{-- Edit form --}}
    <div class="rounded-xl bg-white border border-gray-200 p-6">

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                <ul class="space-y-1 text-sm text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.noc-instances.update', $nocInstance) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Display Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $nocInstance->name) }}" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900
                                  outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20
                                  @error('name') border-red-400 @enderror">
                </div>

                <div class="sm:col-span-2">
                    <label for="url" class="block text-sm font-medium text-gray-700 mb-1">
                        NOC URL <span class="text-red-500">*</span>
                    </label>
                    <input type="url" id="url" name="url" value="{{ old('url', $nocInstance->url) }}" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900
                                  outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20
                                  @error('url') border-red-400 @enderror">
                </div>

                <div>
                    <label for="admin_username" class="block text-sm font-medium text-gray-700 mb-1">
                        Admin Username <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="admin_username" name="admin_username"
                           value="{{ old('admin_username', $nocInstance->admin_username) }}" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900
                                  outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20
                                  @error('admin_username') border-red-400 @enderror">
                </div>

                <div>
                    <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1">
                        Admin Password
                    </label>
                    <input type="password" id="admin_password" name="admin_password"
                           placeholder="Leave blank to keep current"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900
                                  outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                    <p class="mt-1 text-xs text-gray-500">Leave blank to keep existing password</p>
                </div>

                <div class="sm:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900
                                     outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 resize-none">{{ old('notes', $nocInstance->notes) }}</textarea>
                </div>

                <div class="sm:col-span-2 flex items-center gap-3">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           {{ $nocInstance->is_active ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-blue-600">
                    <label for="is_active" class="text-sm font-medium text-gray-700">
                        Active — include this NOC in synchronisation
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                    Save Changes
                </button>
                <a href="{{ route('admin.noc-instances.index') }}"
                   class="rounded-lg border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Copy API key
    document.getElementById('copy-api-key')?.addEventListener('click', function () {
        const key = document.getElementById('api-key-value').textContent.trim();
        navigator.clipboard.writeText(key).then(() => {
            this.innerHTML = `<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg> Copied!`;
            this.classList.add('text-green-700', 'border-green-300');
            setTimeout(() => {
                this.innerHTML = `<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/></svg> Copy`;
                this.classList.remove('text-green-700', 'border-green-300');
            }, 2000);
        });
    });

    document.getElementById('test-btn')?.addEventListener('click', async function () {
        const btn    = this;
        const result = document.getElementById('test-result');
        btn.disabled = true;
        btn.textContent = 'Testing…';

        try {
            const res  = await fetch(btn.dataset.testUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();

            result.classList.remove('hidden', 'bg-green-50', 'text-green-800', 'bg-red-50', 'text-red-800');

            if (data.status === 'success') {
                result.classList.add('bg-green-50', 'text-green-800');
                result.textContent = '✓ ' + data.message;
            } else {
                result.classList.add('bg-red-50', 'text-red-800');
                result.textContent = '✗ ' + data.message;
            }
        } catch (e) {
            result.classList.remove('hidden');
            result.classList.add('bg-red-50', 'text-red-800');
            result.textContent = '✗ Request failed';
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg> Test Connection`;
        }
    });
</script>
@endpush
