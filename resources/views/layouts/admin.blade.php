<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — NOC Hub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">

<div class="flex h-full">

    {{-- Sidebar --}}
    <aside class="flex w-64 flex-col bg-slate-900">

        {{-- Logo --}}
        <div class="flex h-16 items-center gap-3 px-6 border-b border-slate-700">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 0 0 3 3h7.5a3 3 0 0 0 3-3m-16.5 0V6.375a3.375 3.375 0 0 1 6.75 0v7.875m9.75 0V11.25a3 3 0 0 0-3-3h-4.5a3 3 0 0 0-3 3v3" />
                </svg>
            </div>
            <span class="text-lg font-semibold text-white">NOC Hub</span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4">

            {{-- Dashboard --}}
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.dashboard') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                Dashboard
            </a>

            {{-- Separator --}}
            <p class="mt-4 mb-1 px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Monitoring</p>

            <a href="{{ route('admin.hub-playback.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.hub-playback.*') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347c-.75.412-1.667-.13-1.667-.986V5.653Z"/>
                </svg>
                Playback
            </a>

            <a href="{{ route('admin.hub-schedules.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.hub-schedules.*') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                </svg>
                Schedules
            </a>

            <a href="{{ route('admin.hub-errors.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.hub-errors.*') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                </svg>
                Errors
            </a>

            <a href="{{ route('admin.sync-logs.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.sync-logs.*') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                Sync Logs
            </a>

            {{-- Separator --}}
            <p class="mt-4 mb-1 px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Administration</p>

            <a href="{{ route('admin.noc-instances.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.noc-instances.*') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 0 0 3 3h7.5a3 3 0 0 0 3-3m-16.5 0V6.375a3.375 3.375 0 0 1 6.75 0v7.875m9.75 0V11.25a3 3 0 0 0-3-3h-4.5a3 3 0 0 0-3 3v3" />
                </svg>
                NOC Instances
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.users.*') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                Users
            </a>

            <a href="{{ route('admin.locations.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.locations.*') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                </svg>
                Locations
            </a>
            <a href="/docs/mobile-api.html" target="_blank"
               class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors text-slate-400 hover:bg-slate-800 hover:text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                API Documentation
                <svg class="h-3 w-3 ml-auto" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
            </a>
        </nav>

        {{-- User info + logout --}}
        <div class="border-t border-slate-700 p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-600 text-sm font-medium text-white">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="truncate text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                    <p class="truncate text-xs text-slate-400">{{ Auth::user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout"
                            class="text-slate-400 hover:text-white transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex flex-1 flex-col overflow-hidden">

        {{-- Top bar --}}
        <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-6">
            <h1 class="text-xl font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
            <div class="flex items-center gap-2">
                @yield('header-actions')
            </div>
        </header>

        {{-- Flash messages --}}
        <div class="px-6 pt-4">
            @if(session('success'))
                <div class="mb-4 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm-.75-4.75a.75.75 0 0 0 1.5 0V9.5a.75.75 0 0 0-1.5 0v3.75Zm.75-6a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif
        </div>

        {{-- Page content --}}
        <main class="flex-1 overflow-auto px-6 py-4">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
