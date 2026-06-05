<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — NOC Hub</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex h-full items-center justify-center">

<div class="w-full max-w-sm px-4">

    {{-- Logo --}}
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600">
            <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 0 0 3 3h7.5a3 3 0 0 0 3-3m-16.5 0V6.375a3.375 3.375 0 0 1 6.75 0v7.875m9.75 0V11.25a3 3 0 0 0-3-3h-4.5a3 3 0 0 0-3 3v3" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-white">NOC Hub</h1>
        <p class="mt-1 text-sm text-slate-400">Sign in to your admin account</p>
    </div>

    {{-- Card --}}
    <div class="rounded-xl bg-white p-8 shadow-lg">

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900
                              placeholder-gray-400 outline-none transition
                              focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20
                              @error('email') border-red-400 @enderror"
                       placeholder="admin@example.com">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900
                              placeholder-gray-400 outline-none transition
                              focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                       placeholder="••••••••">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="remember" name="remember"
                       class="h-4 w-4 rounded border-gray-300 text-blue-600">
                <label for="remember" class="text-sm text-gray-600">Remember me</label>
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white
                           hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                           transition-colors">
                Sign in
            </button>
        </form>
    </div>
</div>

</body>
</html>
