<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ ($title ?? null) ? $title.' · '.config('app.name') : config('app.name') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                :root {
                    --color-page: #f4f1ea;
                    --color-panel: #fffdf8;
                    --color-panel-soft: #f7f1e6;
                    --color-line: #e6ddd0;
                    --color-line-strong: #d6cab9;
                    --color-ink: #1e1a15;
                    --color-muted: #6d6356;
                    --color-accent: #1f5a52;
                    --color-accent-strong: #184941;
                    --color-accent-soft: #e2ece9;
                    --color-accent-contrast: #f7faf9;
                    --color-success: #2d7f5e;
                    --radius-button: 0.9rem;
                    --radius-card: 1.35rem;
                    --shadow-card: 0 18px 40px rgba(47, 39, 28, 0.08);
                }

                * { box-sizing: border-box; }
                body {
                    margin: 0;
                    font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif;
                    background: linear-gradient(180deg, #f8f4ed 0%, var(--color-page) 100%);
                    color: var(--color-ink);
                }
            </style>
        @endif
        @livewireStyles
    </head>
    <body class="bg-[var(--color-page)] text-[var(--color-ink)] antialiased">
        @php($currentAdmin = app(\App\Support\Auth\AdminSessionManager::class)->user() ?? [])
        <div class="min-h-screen lg:grid lg:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="border-b border-[var(--color-line)] bg-[var(--color-panel)] lg:border-r lg:border-b-0">
                <div class="flex h-full flex-col">
                    <div class="border-b border-[var(--color-line)] px-6 py-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Wide Web Blog</p>
                        <h1 class="mt-2 text-lg font-semibold tracking-[-0.02em]">Admin Panel</h1>
                        <p class="mt-2 max-w-[18rem] text-sm text-[var(--color-muted)]">
                            Publishing-focused editorial operations built with Laravel, Livewire, and Blade.
                        </p>
                    </div>

                    <nav class="flex-1 space-y-1 px-4 py-6">
                        <x-admin.nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-admin.nav-link>
                        <x-admin.nav-link href="#" disabled>Posts</x-admin.nav-link>
                        <x-admin.nav-link href="#" disabled>Categories</x-admin.nav-link>
                        <x-admin.nav-link href="#" disabled>Media Library</x-admin.nav-link>
                        <x-admin.nav-link href="#" disabled>Templates</x-admin.nav-link>
                        <x-admin.nav-link href="#" disabled>Knowledge Base</x-admin.nav-link>
                        <x-admin.nav-link href="#" disabled>SEO</x-admin.nav-link>
                        <x-admin.nav-link href="#" disabled>Settings</x-admin.nav-link>
                    </nav>

                    <div class="border-t border-[var(--color-line)] px-4 py-4">
                        <div class="rounded-[var(--radius-card)] border border-dashed border-[var(--color-line-strong)] bg-[var(--color-panel-soft)] p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Bootstrap Status</p>
                            <p class="mt-2 text-sm text-[var(--color-muted)]">
                                Laravel 13 and Livewire are installed. Auth, API client wiring, and content modules are next.
                            </p>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="min-w-0">
                <header class="border-b border-[var(--color-line)] bg-[color-mix(in_srgb,var(--color-panel)_84%,white)]/90 backdrop-blur">
                    <div class="flex items-center justify-between gap-4 px-6 py-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Editorial Workspace</p>
                            <p class="mt-1 text-sm text-[var(--color-muted)]">Blade-native shadcn-inspired foundation</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="hidden rounded-full border border-[var(--color-line)] bg-[var(--color-panel)] px-3 py-1.5 text-sm text-[var(--color-muted)] md:block">
                                API-driven admin
                            </div>
                            @if ($currentAdmin)
                                <div class="hidden rounded-full border border-[var(--color-line)] bg-[var(--color-panel)] px-3 py-1.5 text-sm text-[var(--color-muted)] lg:block">
                                    {{ $currentAdmin['name'] ?? 'Admin' }}
                                </div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="secondary" size="sm">Sign out</x-ui.button>
                                </form>
                            @endif
                        </div>
                    </div>
                </header>

                <main class="px-6 py-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
