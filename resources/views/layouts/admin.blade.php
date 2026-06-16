<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ ($title ?? null) ? $title.' · '.config('app.name') : config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
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
                    --color-success-strong: #1e6348;
                    --color-warning: #c98a1f;
                    --color-warning-strong: #8a5e18;
                    --color-danger: #b44f45;
                    --color-danger-strong: #933e36;
                    --color-ring: #7d9f99;
                    --radius-button: 0.9rem;
                    --radius-card: 1.35rem;
                    --shadow-card: 0 18px 40px rgba(47, 39, 28, 0.08);
                }

                * { box-sizing: border-box; }
                body {
                    margin: 0;
                    font-family: "Inter", ui-sans-serif, system-ui, sans-serif;
                    background:
                        radial-gradient(circle at top left, rgba(31, 90, 82, 0.08), transparent 34%),
                        linear-gradient(180deg, #f8f4ed 0%, var(--color-page) 100%);
                    color: var(--color-ink);
                }
            </style>
        @endif
        @livewireStyles
    </head>
    <body class="overflow-hidden bg-[var(--color-page)] text-[var(--color-ink)] antialiased">
        @php($currentAdmin = app(\App\Support\Auth\AdminSessionManager::class)->user() ?? [])
        @php($pageTitle = $pageTitle ?? null)
        @php($pageDescription = $pageDescription ?? null)

        <div class="min-h-screen lg:grid lg:h-screen lg:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="hidden border-b border-[var(--color-line)] bg-[var(--color-panel)] lg:block lg:h-screen lg:overflow-y-auto lg:border-r lg:border-b-0">
                <div class="flex h-full flex-col">
                    <div class="border-b border-[var(--color-line)] px-6 py-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Wide Web Blog</p>
                        <h1 class="mt-2 text-lg font-semibold tracking-[-0.02em]">Admin Panel</h1>
                        <p class="mt-2 max-w-[18rem] text-sm text-[var(--color-muted)]">
                            Publishing-focused editorial operations built with Laravel, Livewire, and Blade.
                        </p>
                    </div>

                    <x-admin.sidebar-nav class="flex-1 px-4 py-6" />

                    <div class="border-t border-[var(--color-line)] px-4 py-4">
                        <div class="rounded-[var(--radius-card)] border border-dashed border-[var(--color-line-strong)] bg-[var(--color-panel-soft)] p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Bootstrap Status</p>
                            <p class="mt-2 text-sm text-[var(--color-muted)]">
                                Laravel 13, Livewire, auth, layout, shared CRUD components, and the route skeleton are now in place.
                            </p>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="min-w-0">
                <div class="border-b border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-4 lg:hidden">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Wide Web Blog</p>
                            <h1 class="mt-2 text-lg font-semibold tracking-[-0.02em]">Admin Panel</h1>
                        </div>

                        @if ($currentAdmin)
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-ui.button type="submit" variant="secondary" size="sm">Sign out</x-ui.button>
                            </form>
                        @endif
                    </div>

                    <details class="mt-4 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-3">
                        <summary class="cursor-pointer list-none text-sm font-semibold text-[var(--color-ink)]">
                            Browse Admin
                        </summary>
                        <div class="mt-3">
                            <x-admin.sidebar-nav />
                        </div>
                    </details>
                </div>

                <div class="lg:flex lg:h-screen lg:flex-col lg:overflow-y-auto">
                <header class="border-b border-[var(--color-line)] bg-[color-mix(in_srgb,var(--color-panel)_84%,white)]/90 backdrop-blur lg:sticky lg:top-0 lg:z-20">
                    <div class="flex flex-col gap-4 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Editorial Workspace</p>
                            <p class="mt-1 text-sm text-[var(--color-muted)]">Blade-native shadcn-inspired foundation</p>
                        </div>

                        <div class="min-w-0 flex-1 lg:max-w-xl">
                            @isset($search)
                                {{ $search }}
                            @else
                                <x-admin.topbar-search />
                            @endisset
                        </div>

                        <div class="flex items-center gap-3">
                            @isset($userMenu)
                                {{ $userMenu }}
                            @else
                                <x-admin.user-chip :admin="$currentAdmin" />
                            @endisset
                        </div>
                    </div>
                </header>

                <main class="px-4 py-6 sm:px-6 lg:flex-1 lg:px-8">
                    <div class="mx-auto max-w-7xl space-y-6">
                        @isset($errorBanner)
                            {{ $errorBanner }}
                        @endisset

                        <x-admin.flash-stack />

                        @isset($header)
                            {{ $header }}
                        @elseif ($pageTitle || $pageDescription)
                            <x-admin.page-header
                                :title="$pageTitle"
                                :description="$pageDescription"
                            />
                        @endif

                        {{ $slot }}
                    </div>
                </main>
                </div>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
