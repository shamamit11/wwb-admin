<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ ($title ?? null) ? $title.' · '.config('app.name') : config('app.name') }}</title>
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
                    font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif;
                    background:
                        radial-gradient(circle at top left, rgba(31, 90, 82, 0.08), transparent 34%),
                        linear-gradient(180deg, #f8f4ed 0%, var(--color-page) 100%);
                    color: var(--color-ink);
                }
            </style>
        @endif
        @livewireStyles
    </head>
    <body class="bg-[var(--color-page)] text-[var(--color-ink)] antialiased">
        <div class="min-h-screen px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto grid min-h-[calc(100vh-3rem)] max-w-6xl items-center gap-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(380px,0.85fr)]">
                <section class="rounded-[calc(var(--radius-card)+0.4rem)] border border-[var(--color-line)] bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-panel)_70%,white),var(--color-panel))] p-8 shadow-[0_28px_60px_rgba(49,40,28,0.08)] sm:p-10">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Wide Web Blog</p>
                            <h1 class="mt-3 text-4xl font-semibold tracking-[-0.05em] sm:text-5xl">
                                Editorial operations for a publishing-first admin.
                            </h1>
                        </div>

                        <x-ui.badge class="hidden sm:inline-flex">Admin Access</x-ui.badge>
                    </div>

                    <p class="mt-5 max-w-xl text-base leading-7 text-[var(--color-muted)]">
                        Sign in with your admin credentials to manage posts, media, templates, knowledge, and SEO workflows through the Wide Web Blog service API.
                    </p>

                    <div class="mt-10 grid gap-4 sm:grid-cols-2">
                        <x-ui.card class="bg-[var(--color-panel)]">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--color-muted)]">Publishing-first</p>
                            <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">
                                Table-first management screens and editor-first creation flows.
                            </p>
                        </x-ui.card>

                        <x-ui.card class="bg-[var(--color-panel)]">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--color-muted)]">Service-driven</p>
                            <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">
                                Auth, content state, and persistence stay owned by the service API.
                            </p>
                        </x-ui.card>
                    </div>
                </section>

                <main class="space-y-4">
                    <x-admin.flash-stack />

                    @isset($header)
                        {{ $header }}
                    @endisset

                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
