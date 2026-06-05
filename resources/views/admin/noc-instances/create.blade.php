@extends('layouts.admin')

@section('title', 'Add NOC Instance')
@section('page-title', 'Add NOC Instance')

@section('header-actions')
    <a href="{{ route('admin.noc-instances.index') }}"
       class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
        ← Back
    </a>
@endsection

@section('content')

<div class="max-w-2xl">
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

        <form method="POST" action="{{ route('admin.noc-instances.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Display Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           placeholder="e.g. NOC Paris"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900
                                  outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20
                                  @error('name') border-red-400 @enderror">
                </div>

                <div class="sm:col-span-2">
                    <label for="url" class="block text-sm font-medium text-gray-700 mb-1">
                        NOC URL <span class="text-red-500">*</span>
                    </label>
                    <input type="url" id="url" name="url" value="{{ old('url') }}" required
                           placeholder="https://noc.example.com"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900
                                  outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20
                                  @error('url') border-red-400 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Base URL of the NOC application (without trailing slash)</p>
                </div>

                <div>
                    <label for="admin_username" class="block text-sm font-medium text-gray-700 mb-1">
                        Admin Username <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="admin_username" name="admin_username" value="{{ old('admin_username') }}" required
                           placeholder="admin"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900
                                  outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20
                                  @error('admin_username') border-red-400 @enderror">
                </div>

                <div>
                    <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1">
                        Admin Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="admin_password" name="admin_password" required
                           placeholder="••••••••"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900
                                  outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20
                                  @error('admin_password') border-red-400 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Stored encrypted</p>
                </div>

                <div class="sm:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                              placeholder="Optional notes about this NOC instance..."
                              class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900
                                     outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20
                                     resize-none">{{ old('notes') }}</textarea>
                </div>

                <div class="sm:col-span-2 flex items-center gap-3">
                    <input type="checkbox" id="is_active" name="is_active" value="1" checked
                           class="h-4 w-4 rounded border-gray-300 text-blue-600">
                    <label for="is_active" class="text-sm font-medium text-gray-700">
                        Active — include this NOC in synchronisation
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                    Create NOC Instance
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
