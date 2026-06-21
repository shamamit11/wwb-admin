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
    <body class="bg-[var(--color-page)] text-[var(--color-ink)] antialiased">
        <div class="relative min-h-screen overflow-hidden px-4 py-6 sm:px-6 lg:px-8">
            <div class="pointer-events-none absolute bottom-0 right-0 hidden h-[42vh] w-[34vw] overflow-hidden opacity-50 lg:block">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(249,115,22,0.22),transparent_28%),linear-gradient(135deg,rgba(15,108,189,0.08),transparent_52%)]"></div>
                <div class="absolute inset-[14%] rounded-[2.5rem] border border-white/70 bg-white/35 backdrop-blur-2xl"></div>
            </div>

            <div class="mx-auto grid min-h-[calc(100vh-3rem)] max-w-6xl items-center gap-10 lg:grid-cols-[minmax(0,1.15fr)_minmax(400px,0.75fr)]">
                <section class="hidden lg:block">
                    <div class="max-w-2xl">
                        <div class="inline-flex h-16 w-16 items-center justify-center rounded-[1.25rem] bg-[var(--color-accent)] text-white shadow-[0_18px_35px_rgba(249,115,22,0.28)]">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M7 17.25V6.75A1.75 1.75 0 0 1 8.75 5h8.5A1.75 1.75 0 0 1 19 6.75v10.5A1.75 1.75 0 0 1 17.25 19h-8.5A1.75 1.75 0 0 1 7 17.25Z" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M10 9.5h6M10 12.5h6M10 15.5h3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </div>

                        <p class="mt-8 text-sm font-semibold uppercase tracking-[0.28em] text-[var(--color-muted)]">Wide Web Blog</p>
                        <h1 class="mt-4 text-5xl font-semibold tracking-[-0.05em] text-[var(--color-ink)]">
                            Editorial control for a modern publishing desk.
                        </h1>
                        <p class="mt-5 max-w-xl text-lg leading-8 text-[var(--color-muted)]">
                            Sign in with your admin credentials to manage posts, media, knowledge, prompts, and SEO workflows through the Wide Web Blog service API.
                        </p>

                        <div class="mt-10 grid gap-4 sm:grid-cols-2">
                            <x-ui.card class="bg-white/88 backdrop-blur">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--color-muted)]">Publishing-first</p>
                                <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">
                                    Table-first management screens and editor-first creation flows.
                                </p>
                            </x-ui.card>

                            <x-ui.card class="bg-white/88 backdrop-blur">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--color-muted)]">Service-driven</p>
                                <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">
                                    Auth, content state, and persistence stay owned by the service API.
                                </p>
                            </x-ui.card>
                        </div>
                    </div>
                </section>

                <main class="space-y-4 lg:justify-self-end lg:w-full lg:max-w-[440px]">
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
