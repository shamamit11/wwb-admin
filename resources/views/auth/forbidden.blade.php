<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Access Denied · {{ config('app.name') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-[var(--color-page)] text-[var(--color-ink)] antialiased">
        <div class="flex min-h-screen items-center justify-center px-4">
            <x-ui.card class="w-full max-w-xl">
                <div class="space-y-4 text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Admin Access</p>
                    <h1 class="text-3xl font-semibold tracking-[-0.04em]">Access denied</h1>
                    <p class="mx-auto max-w-lg text-sm leading-6 text-[var(--color-muted)]">
                        {{ session('auth.error', 'Your account is not authorized for the Wide Web Blog admin panel.') }}
                    </p>
                    <div class="pt-2">
                        <x-ui.button as="a" :href="route('login')" variant="secondary">Return to sign in</x-ui.button>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </body>
</html>
