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
                    --color-line: #e6ddd0;
                    --color-ink: #1e1a15;
                    --color-muted: #6d6356;
                    --color-accent: #1f5a52;
                    --color-accent-strong: #184941;
                    --color-accent-contrast: #f7faf9;
                    --radius-button: 0.9rem;
                    --radius-card: 1.35rem;
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
        <div class="min-h-screen px-4 py-10 sm:px-6 lg:px-8">
            <div class="mx-auto grid min-h-[calc(100vh-5rem)] max-w-6xl items-center gap-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(380px,0.85fr)]">
                <section class="rounded-[calc(var(--radius-card)+0.4rem)] border border-[var(--color-line)] bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-panel)_70%,white),var(--color-panel))] p-8 shadow-[0_28px_60px_rgba(49,40,28,0.08)] sm:p-10">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Wide Web Blog</p>
                    <h1 class="mt-4 max-w-lg text-4xl font-semibold tracking-[-0.05em] sm:text-5xl">
                        Editorial operations for a publishing-first admin.
                    </h1>
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

                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
