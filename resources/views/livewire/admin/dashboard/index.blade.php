@php
    $totalVisiblePosts = count($recentDrafts) + count($recentPublishedPosts);
@endphp

<div class="space-y-8">
    @if ($dashboardError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-warning)_24%,white)] bg-[color-mix(in_srgb,var(--color-warning)_10%,white)] px-4 py-3 text-sm text-[var(--color-warning-strong)]">
            {{ $dashboardError }}
        </div>
    @endif

    <section class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h2 class="text-3xl font-semibold tracking-[-0.04em] text-[var(--color-ink)] sm:text-4xl">Admin Overview</h2>
            <p class="mt-2 text-sm leading-6 text-[var(--color-muted)] sm:text-base">
                Welcome back. Here's what's happening with Wide Web Blog today.
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <x-ui.button as="a" :href="route('posts.index')" variant="outline">Export Report</x-ui.button>
            <x-ui.button as="a" :href="route('settings.index')" variant="secondary">Dashboard Settings</x-ui.button>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
            <div class="flex items-start justify-between">
                <span class="flex h-11 w-11 items-center justify-center rounded-[0.95rem] bg-[var(--color-accent-soft)] text-[var(--color-accent-strong)]">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M5 4.5h10A1.5 1.5 0 0 1 16.5 6v8A1.5 1.5 0 0 1 15 15.5H5A1.5 1.5 0 0 1 3.5 14V6A1.5 1.5 0 0 1 5 4.5Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M7 8h6M7 11h6M7 14h3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="rounded-md bg-[color-mix(in_srgb,var(--color-success)_12%,white)] px-2 py-1 text-xs font-semibold text-[var(--color-success-strong)]">
                    Live
                </span>
            </div>
            <div class="mt-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Total Posts</p>
                <h3 class="mt-1 text-3xl font-semibold tracking-[-0.04em] text-[var(--color-ink)]">{{ $totalVisiblePosts }}</h3>
            </div>
        </div>

        <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
            <div class="flex items-start justify-between">
                <span class="flex h-11 w-11 items-center justify-center rounded-[0.95rem] bg-[var(--color-info-soft)] text-[var(--color-info)]">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M4.5 10.25 8 13.75l7.5-7.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="text-xs font-semibold text-[var(--color-muted)]">Steady</span>
            </div>
            <div class="mt-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Published</p>
                <h3 class="mt-1 text-3xl font-semibold tracking-[-0.04em] text-[var(--color-ink)]">{{ count($recentPublishedPosts) }}</h3>
            </div>
        </div>

        <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
            <div class="flex items-start justify-between">
                <span class="flex h-11 w-11 items-center justify-center rounded-[0.95rem] bg-[var(--color-panel-soft)] text-[var(--color-muted)]">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M5.25 6h9.5M5.25 10h9.5M5.25 14h5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="text-xs font-semibold text-[var(--color-accent-strong)]">Needs Action</span>
            </div>
            <div class="mt-4 flex gap-8">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Drafts</p>
                    <h3 class="mt-1 text-2xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">{{ count($recentDrafts) }}</h3>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Scheduled</p>
                    <h3 class="mt-1 text-2xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">TBC</h3>
                </div>
            </div>
        </div>

        <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
            <div class="flex items-start justify-between">
                <span class="flex h-11 w-11 items-center justify-center rounded-[0.95rem] bg-[var(--color-accent-soft)] text-[var(--color-accent-strong)]">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M4.5 6.25h11A1.25 1.25 0 0 1 16.75 7.5v5A1.25 1.25 0 0 1 15.5 13.75h-11A1.25 1.25 0 0 1 3.25 12.5v-5A1.25 1.25 0 0 1 4.5 6.25Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="m4.5 7 5.03 3.96a.75.75 0 0 0 .94 0L15.5 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="rounded-md bg-[color-mix(in_srgb,var(--color-warning)_12%,white)] px-2 py-1 text-xs font-semibold text-[var(--color-warning-strong)]">
                    Placeholder
                </span>
            </div>
            <div class="mt-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Subscribers</p>
                <h3 class="mt-1 text-3xl font-semibold tracking-[-0.04em] text-[var(--color-ink)]">TBC</h3>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <div class="overflow-hidden rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] shadow-[var(--shadow-card)] lg:col-span-8">
            <div class="flex items-center justify-between border-b border-[var(--color-line)] px-6 py-5">
                <h3 class="text-xl font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Drafts waiting for review</h3>
                <a href="{{ route('posts.index') }}" class="text-sm font-semibold text-[var(--color-accent-strong)] hover:underline">View all drafts</a>
            </div>

            @if ($recentDrafts === [])
                <div class="p-6">
                    <x-ui.empty-state
                        title="No recent drafts returned"
                        message="Either there are no draft posts yet, or the service has not returned draft records for this account."
                    >
                        <x-ui.button as="a" :href="route('posts.index')" variant="outline">Open Posts</x-ui.button>
                    </x-ui.empty-state>
                </div>
            @else
                <div class="divide-y divide-[var(--color-line)]">
                    @foreach ($recentDrafts as $post)
                        <div class="flex flex-col justify-between gap-4 px-6 py-5 transition-colors hover:bg-[var(--color-panel-soft)] sm:flex-row sm:items-center">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[0.95rem] bg-[var(--color-accent-soft)] text-[var(--color-accent-strong)]">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M5 4.5h10A1.5 1.5 0 0 1 16.5 6v8A1.5 1.5 0 0 1 15 15.5H5A1.5 1.5 0 0 1 3.5 14V6A1.5 1.5 0 0 1 5 4.5Z" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M7 8h6M7 11h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="truncate text-sm font-semibold text-[var(--color-ink)] sm:text-base">{{ $post['title'] }}</h4>
                                    <p class="mt-1 text-xs text-[var(--color-muted)]">
                                        By {{ $post['author'] ?: 'Unknown author' }} • Edited {{ $post['updated_at'] ?: 'TBC' }}
                                    </p>
                                    <p class="mt-2 text-xs text-[var(--color-muted)]">
                                        {{ $post['category'] ?: 'Uncategorized' }} • {{ $post['word_count'] ? number_format($post['word_count']) : 'TBC' }} words • {{ str($post['visibility'] ?: 'unknown')->headline() }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-end sm:self-auto">
                                <a
                                    href="{{ route('posts.index') }}"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-[0.85rem] text-[var(--color-muted)] transition-colors hover:text-[var(--color-accent-strong)]"
                                    aria-label="Edit draft"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="m13.75 4.75 1.5 1.5M5 15l2.75-.5L15.5 6.75a1.06 1.06 0 0 0 0-1.5l-.75-.75a1.06 1.06 0 0 0-1.5 0L5.5 12.25 5 15Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                                <a href="{{ route('posts.index') }}" class="inline-flex items-center rounded-[0.85rem] bg-[var(--color-panel-soft)] px-4 py-2 text-sm font-semibold text-[var(--color-ink)] transition-colors hover:bg-[color-mix(in_srgb,var(--color-panel-soft)_72%,white)]">
                                    Review
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="space-y-6 lg:col-span-4">
            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-accent)_8%,white),white)] p-6 shadow-[var(--shadow-card)]">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Quick Actions</p>
                <h3 class="mt-3 text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Move directly into the next editorial task.</h3>
                <div class="mt-5 flex flex-col gap-3">
                    <x-ui.button as="a" :href="route('posts.index')" size="lg">Review Drafts</x-ui.button>
                    <x-ui.button as="a" :href="route('posts.index')" variant="secondary">Create Post</x-ui.button>
                    <x-ui.button as="a" :href="route('seo.index')" variant="secondary">Open SEO Area</x-ui.button>
                </div>
            </div>

            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Recently Published</p>
                        <h3 class="mt-3 text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Keep an eye on what just went live.</h3>
                    </div>
                    <x-ui.badge tone="success">Live</x-ui.badge>
                </div>

                @if ($recentPublishedPosts === [])
                    <div class="mt-5">
                        <x-ui.empty-state
                            title="No recent published posts returned"
                            message="The service has not returned published records yet, so review should continue in the posts module."
                        >
                            <x-ui.button as="a" :href="route('posts.index')" variant="outline">Review Posts</x-ui.button>
                        </x-ui.empty-state>
                    </div>
                @else
                    <div class="mt-5 space-y-3">
                        @foreach ($recentPublishedPosts as $post)
                            <div class="rounded-[0.95rem] border border-[var(--color-line)] bg-[color-mix(in_srgb,var(--color-panel-soft)_42%,white)] px-4 py-3">
                                <p class="truncate text-sm font-semibold text-[var(--color-ink)]">{{ $post['title'] }}</p>
                                <p class="mt-1 text-xs text-[var(--color-muted)]">
                                    {{ $post['published_at'] ?: 'TBC' }} • {{ $post['author'] ?: 'Unknown author' }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Topic Queue</p>
                <h3 class="mt-3 text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Placeholder only</h3>
                <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">
                    No topic queue endpoints exist in the current service phase, so this widget stays explicitly non-operational.
                </p>
                <div class="mt-4">
                    <x-ui.badge tone="warning">TBC Until Service Support</x-ui.badge>
                </div>
            </div>

            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">AI Jobs</p>
                <h3 class="mt-3 text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Placeholder only</h3>
                <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">
                    AI job monitoring is reserved in the shell, but the current API phase does not expose job endpoints or dashboard aggregates.
                </p>
                <div class="mt-4">
                    <x-ui.badge tone="warning">TBC Until Service Support</x-ui.badge>
                </div>
            </div>
        </div>
    </section>
</div>
