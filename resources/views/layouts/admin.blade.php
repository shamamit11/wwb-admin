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
                    --color-page: #f7f8fc;
                    --color-page-strong: #eef2fb;
                    --color-panel: #ffffff;
                    --color-panel-soft: #f3f6fd;
                    --color-line: #dde4f2;
                    --color-line-strong: #c8d1e5;
                    --color-ink: #141b2b;
                    --color-muted: #5b6474;
                    --color-accent: #f97316;
                    --color-accent-strong: #c85a0d;
                    --color-accent-soft: #ffe7d6;
                    --color-accent-contrast: #ffffff;
                    --color-info: #0f6cbd;
                    --color-info-soft: #dff0ff;
                    --color-success: #1f9d55;
                    --color-success-strong: #14723d;
                    --color-warning: #d97706;
                    --color-warning-strong: #9a5808;
                    --color-danger: #d14343;
                    --color-danger-strong: #a53030;
                    --color-ring: rgba(249, 115, 22, 0.22);
                    --radius-button: 5px;
                    --radius-card: 1.5rem;
                    --shadow-card: 0 18px 50px rgba(20, 27, 43, 0.08);
                }

                * { box-sizing: border-box; }
                body {
                    margin: 0;
                    font-family: "Inter", ui-sans-serif, system-ui, sans-serif;
                    background:
                        radial-gradient(circle at top left, rgba(249, 115, 22, 0.12), transparent 30%),
                        radial-gradient(circle at bottom right, rgba(15, 108, 189, 0.08), transparent 24%),
                        linear-gradient(180deg, #fbfcff 0%, var(--color-page) 100%);
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

        <div class="min-h-screen lg:grid lg:h-screen lg:grid-cols-[260px_minmax(0,1fr)]">
            <aside class="hidden border-b border-[var(--color-line)] bg-[color-mix(in_srgb,var(--color-panel)_82%,var(--color-page))] lg:block lg:h-screen lg:overflow-y-auto lg:border-r lg:border-b-0">
                <div class="flex h-full flex-col">
                    <div class="border-b border-[var(--color-line)] px-6 py-7">
                        <p class="text-lg font-semibold tracking-[-0.03em] text-[var(--color-ink)]">Wide Web Blog</p>
                        <p class="mt-1 text-sm text-[var(--color-muted)]">CMS Administrator</p>
                    </div>

                    <x-admin.sidebar-nav class="flex-1 px-4 py-6" />

                    <div class="mt-auto border-t border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                        <div class="flex items-center gap-3 rounded-[1.15rem] border border-[var(--color-line)] bg-[var(--color-panel)] px-3.5 py-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-[0.9rem] bg-[var(--color-accent-soft)] text-sm font-semibold text-[var(--color-accent-strong)]">
                                {{ strtoupper(substr($currentAdmin['name'] ?? 'A', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-[var(--color-ink)]">{{ $currentAdmin['name'] ?? 'Admin Panel' }}</p>
                                <p class="text-xs text-[var(--color-muted)]">v 4.2.0</p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="min-w-0">
                <div class="border-b border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-4 lg:hidden">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-lg font-semibold tracking-[-0.03em] text-[var(--color-ink)]">Wide Web Blog</p>
                            <p class="mt-1 text-sm text-[var(--color-muted)]">CMS Administrator</p>
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
                <header class="border-b border-[var(--color-line)] bg-[color-mix(in_srgb,var(--color-panel)_88%,white)]/95 backdrop-blur lg:sticky lg:top-0 lg:z-20">
                    <div class="flex flex-col gap-4 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Operations Console</p>
                            <p class="mt-1 text-sm text-[var(--color-muted)]">Search, alerts, and publishing shortcuts</p>
                        </div>

                        <div class="min-w-0 flex-1 lg:max-w-xl">
                            @isset($search)
                                {{ $search }}
                            @else
                                <x-admin.topbar-search />
                            @endisset
                        </div>

                        <div class="flex items-center gap-3 self-start lg:self-auto">
                            <button
                                type="button"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full text-[var(--color-muted)] transition-colors hover:bg-[var(--color-panel-soft)] hover:text-[var(--color-ink)]"
                                aria-label="Notifications"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M10 3.25a3.25 3.25 0 0 0-3.25 3.25v1.12c0 .61-.18 1.21-.52 1.71l-.92 1.36a1 1 0 0 0 .83 1.56h7.7a1 1 0 0 0 .83-1.56l-.92-1.36a3 3 0 0 1-.52-1.71V6.5A3.25 3.25 0 0 0 10 3.25Z" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M8.25 14.25a1.75 1.75 0 0 0 3.5 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </button>

                            @isset($userMenu)
                                {{ $userMenu }}
                            @else
                                <x-admin.user-chip :admin="$currentAdmin" />
                            @endisset
                        </div>
                    </div>
                </header>

                <main class="px-4 py-6 sm:px-6 lg:flex-1 lg:px-8">
                    <div class="mx-auto max-w-[1200px] space-y-6">
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
