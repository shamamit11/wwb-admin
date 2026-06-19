<div class="space-y-6">
    @if ($dashboardError)
        <x-admin.callout title="Dashboard Data Unavailable" tone="warning">
            {{ $dashboardError }}
        </x-admin.callout>
    @endif

    <x-admin.page-header
        eyebrow="Dashboard"
        title="Admin Overview"
        description="Welcome back. Monitor publishing operations, review editorial queues, and jump directly into the next high-signal workflow."
    >
        <x-ui.button as="a" :href="route('posts.create')">Create Post</x-ui.button>
        <x-ui.button as="a" :href="route('draft-review.index')" variant="secondary">Review Drafts</x-ui.button>
    </x-admin.page-header>

    <section class="space-y-4">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($aiWorkflowCards as $card)
                <a href="{{ $card['href'] }}" class="block transition-transform duration-150 hover:-translate-y-0.5">
                    <x-admin.stat-card :label="$card['label']" :value="$card['value']" :tone="$card['tone']">
                        @if ($card['tone'] === 'success')
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <circle cx="10" cy="10" r="6" stroke="currentColor" stroke-width="1.6" />
                                <path d="m7.6 10 1.55 1.55L12.4 8.3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        @elseif ($card['tone'] === 'danger')
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <circle cx="10" cy="10" r="6" stroke="currentColor" stroke-width="1.6" />
                                <path d="M10 7v3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                <circle cx="10" cy="13.2" r="0.9" fill="currentColor" />
                            </svg>
                        @elseif ($card['tone'] === 'warning')
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M10 4 16 15H4L10 4Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                                <path d="M10 8v3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                <circle cx="10" cy="13.3" r="0.9" fill="currentColor" />
                            </svg>
                        @else
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M5.5 12.75 8.15 10.1l1.95 1.95 4.4-4.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M13.25 7.65h1.95V9.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        @endif
                    </x-admin.stat-card>
                    <p class="mt-3 px-1 text-sm leading-6 text-[var(--color-muted)]">{{ $card['description'] }}</p>
                </a>
            @endforeach
        </div>
    </section>

    <section class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <x-ui.card class="overflow-hidden lg:col-span-8" padded="false">
            <div class="flex items-center justify-between border-b border-[var(--color-line)] px-6 py-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Draft Review</p>
                    <h3 class="mt-2 text-xl font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Drafts waiting for review</h3>
                </div>
                <x-ui.button as="a" :href="route('draft-review.index')" variant="secondary" size="sm">View All</x-ui.button>
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
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="truncate text-sm font-semibold text-[var(--color-ink)] sm:text-base">{{ $post['title'] }}</h4>
                                    <x-ui.badge tone="muted">Draft</x-ui.badge>
                                </div>
                                <p class="mt-1 text-xs text-[var(--color-muted)]">
                                    By {{ $post['author'] ?: 'Unknown author' }} · Edited {{ $post['updated_at'] ?: 'Unknown' }}
                                </p>
                                <p class="mt-2 text-xs text-[var(--color-muted)]">
                                    {{ $post['category'] ?: 'Uncategorized' }} · {{ $post['word_count'] ? number_format($post['word_count']) : 'Unknown' }} words · {{ str($post['visibility'] ?: 'unknown')->headline() }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2 self-end sm:self-auto">
                                <x-ui.button as="a" :href="route('draft-review.show', ['post' => $post['id']])" variant="secondary" size="sm">Review</x-ui.button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ui.card>

        <div class="space-y-6 lg:col-span-4">
            <x-admin.callout title="Quick Actions">
                Move directly into the next editorial task without leaving the shared admin workflow.
            </x-admin.callout>

            <div class="grid gap-3">
                <x-ui.button as="a" :href="route('topic-queue.index', ['status' => 'suggested'])">Review Topics</x-ui.button>
                <x-ui.button as="a" :href="route('content-briefs.index', ['status' => 'draft'])" variant="secondary">Review Briefs</x-ui.button>
                <x-ui.button as="a" :href="route('ai-jobs.index')" variant="secondary">Open AI Jobs</x-ui.button>
            </div>

            <x-ui.card>
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
                    <div class="mt-5 divide-y divide-[var(--color-line)]">
                        @foreach ($recentPublishedPosts as $post)
                            <div class="py-3 first:pt-0 last:pb-0">
                                <p class="truncate text-sm font-semibold text-[var(--color-ink)]">{{ $post['title'] }}</p>
                                <p class="mt-1 text-xs text-[var(--color-muted)]">
                                    {{ $post['published_at'] ?: 'Unknown' }} · {{ $post['author'] ?: 'Unknown author' }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Recent AI Jobs</p>
                        <h3 class="mt-3 text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Watch workflow progress and failures.</h3>
                    </div>
                    <x-ui.badge tone="default">Live</x-ui.badge>
                </div>

                @if ($recentAiJobs === [])
                    <div class="mt-5">
                        <x-ui.empty-state
                            title="No recent AI jobs returned"
                            message="Run topic discovery, brief generation, or draft generation to populate recent workflow activity."
                        >
                            <x-ui.button as="a" :href="route('ai-jobs.index')" variant="outline">Open AI Jobs</x-ui.button>
                        </x-ui.empty-state>
                    </div>
                @else
                    <div class="mt-5 divide-y divide-[var(--color-line)]">
                        @foreach ($recentAiJobs as $job)
                            <a href="{{ route('ai-jobs.show', ['aiJob' => $job['id']]) }}" class="block py-3 transition-colors hover:bg-[var(--color-panel-soft)] first:pt-0 last:pb-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-[var(--color-ink)]">Job #{{ $job['id'] }} · {{ str($job['type'])->headline() }}</p>
                                        <p class="mt-1 text-xs text-[var(--color-muted)]">
                                            {{ $job['provider'] ?: 'Unknown provider' }}{{ $job['model'] ? ' · '.$job['model'] : '' }}
                                        </p>
                                        <p class="mt-1 text-xs text-[var(--color-muted)]">
                                            {{ $job['failed_at'] ?: $job['completed_at'] ?: $job['created_at'] ?: 'Unknown time' }}
                                        </p>
                                    </div>
                                    <x-admin.status-badge :status="$job['status']" />
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        </div>
    </section>
</div>
