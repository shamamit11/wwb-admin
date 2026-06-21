@php
    $toneSurfaceClasses = [
        'default' => 'border-[var(--color-line)] bg-[var(--color-panel)]',
        'muted' => 'border-[var(--color-line)] bg-[var(--color-panel)]',
        'success' => 'border-[color-mix(in_srgb,var(--color-success)_18%,white)] bg-[color-mix(in_srgb,var(--color-success)_4%,white)]',
        'warning' => 'border-[color-mix(in_srgb,var(--color-warning)_18%,white)] bg-[color-mix(in_srgb,var(--color-warning)_5%,white)]',
        'danger' => 'border-[color-mix(in_srgb,var(--color-danger)_20%,white)] bg-[color-mix(in_srgb,var(--color-danger)_6%,white)]',
    ];

    $toneBadgeClasses = [
        'default' => 'text-[var(--color-muted)]',
        'muted' => 'text-[var(--color-muted)]',
        'success' => 'text-[var(--color-success-strong)]',
        'warning' => 'text-[var(--color-warning-strong)]',
        'danger' => 'text-[var(--color-danger-strong)]',
    ];

    $toneBorderClasses = [
        'default' => 'border-[var(--color-line)]',
        'muted' => 'border-[var(--color-line)]',
        'success' => 'border-[color-mix(in_srgb,var(--color-success)_12%,white)]',
        'warning' => 'border-[color-mix(in_srgb,var(--color-warning)_24%,white)]',
        'danger' => 'border-[color-mix(in_srgb,var(--color-danger)_28%,white)]',
    ];
@endphp

<div class="space-y-5">
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

    <section class="space-y-3">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Operational Snapshot</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Editorial queues and AI operating signals</h2>
            </div>
            <x-ui.button as="a" :href="route('ai-jobs.index')" variant="secondary" size="sm">Open AI Jobs</x-ui.button>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($aiWorkflowCards as $card)
                <a href="{{ $card['href'] }}" class="block transition-transform duration-150 hover:-translate-y-0.5">
                    <x-ui.card class="h-full {{ $toneSurfaceClasses[$card['tone']] ?? $toneSurfaceClasses['default'] }} {{ $toneBorderClasses[$card['tone']] ?? $toneBorderClasses['default'] }}">
                        <div class="flex h-full flex-col justify-between gap-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--color-muted)]">{{ $card['label'] }}</p>
                                    <p class="mt-2 text-3xl font-semibold tracking-[-0.04em] text-[var(--color-ink)]">{{ $card['value'] }}</p>
                                </div>

                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[var(--radius-button)] bg-white/70 {{ $toneBadgeClasses[$card['tone']] ?? $toneBadgeClasses['default'] }}">
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
                                </div>
                            </div>

                            <div class="space-y-2">
                                <p class="text-sm leading-5 text-[var(--color-muted)]">{{ $card['description'] }}</p>
                                <div class="flex items-center justify-between gap-3">
                                    <x-ui.badge :tone="$card['tone'] === 'danger' ? 'danger' : ($card['tone'] === 'warning' ? 'warning' : ($card['tone'] === 'success' ? 'success' : 'muted'))">
                                        {{ $card['tone'] === 'danger' ? 'Needs attention' : ($card['tone'] === 'warning' ? 'Review queue' : 'Operational') }}
                                    </x-ui.badge>
                                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Open</span>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>
                </a>
            @endforeach
        </div>
    </section>

    <x-ui.card class="overflow-hidden">
        <div class="border-b border-[var(--color-line)] px-6 py-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">AI Pipeline Health</p>
                    <h2 class="mt-2 text-xl font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Topic discovery to publish, in one operational view</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-[var(--color-muted)]">
                        The dashboard reflects the existing service-backed workflow stages and current editorial load without inventing extra backend states.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge tone="success">{{ $jobStatusSummary['completed'] ?? 0 }} Completed</x-ui.badge>
                    <x-ui.badge tone="warning">{{ $jobStatusSummary['in_progress'] ?? 0 }} In Progress</x-ui.badge>
                    <x-ui.badge tone="danger">{{ $jobStatusSummary['failed'] ?? 0 }} Failed</x-ui.badge>
                </div>
            </div>
        </div>

        <div class="grid gap-3 p-3 md:grid-cols-2 xl:grid-cols-6">
            @foreach ($pipelineSteps as $step)
                <a
                    href="{{ $step['href'] }}"
                    class="group rounded-[calc(var(--radius-card)-0.25rem)] border px-3.5 py-3 transition-colors hover:bg-[var(--color-panel-soft)] {{ $toneSurfaceClasses[$step['tone']] ?? $toneSurfaceClasses['default'] }}"
                >
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm font-semibold text-[var(--color-ink)]">{{ $step['label'] }}</p>
                        <x-ui.badge :tone="$step['tone'] === 'danger' ? 'danger' : ($step['tone'] === 'warning' ? 'warning' : ($step['tone'] === 'success' ? 'success' : 'muted'))">
                            {{ $step['state'] }}
                        </x-ui.badge>
                    </div>
                    <p class="mt-3 text-xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">{{ $step['value'] }}</p>
                    <p class="mt-1.5 text-xs leading-5 text-[var(--color-muted)]">{{ $step['description'] }}</p>
                </a>
            @endforeach
        </div>
    </x-ui.card>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">
        <div class="space-y-5 xl:col-span-8">
            <x-ui.card class="overflow-hidden" padded="false">
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
                            <div class="px-6 py-5 transition-colors hover:bg-[var(--color-panel-soft)]">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <h4 class="text-base font-semibold leading-7 tracking-[-0.01em] text-[var(--color-ink)]">{{ $post['title'] }}</h4>
                                        <p class="mt-1 text-sm text-[var(--color-muted)]">
                                            {{ $post['category'] ?: 'Uncategorized' }}
                                            ·
                                            {{ $post['created_at'] ? 'Created '.$post['created_at'] : 'Edited '.($post['updated_at'] ?: 'Unknown') }}
                                            ·
                                            {{ $post['word_count'] ? number_format($post['word_count']) : 'Unknown' }} words
                                        </p>
                                        <p class="mt-1 text-sm text-[var(--color-muted)]">
                                            By {{ $post['author'] ?: 'Unknown author' }}{{ $post['visibility'] ? ' · '.str($post['visibility'])->headline() : '' }}
                                        </p>

                                        <div class="mt-3 flex flex-wrap items-center gap-2">
                                            <x-admin.status-badge :status="$post['status']" />
                                            <x-ui.badge tone="warning">Needs Review</x-ui.badge>
                                            @if ($post['seo_score'] !== null)
                                                <x-ui.badge tone="success">SEO {{ $post['seo_score'] }}</x-ui.badge>
                                            @else
                                                <x-ui.badge tone="muted">SEO Pending</x-ui.badge>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 self-start">
                                        <x-ui.button as="a" :href="route('draft-review.show', ['post' => $post['id']])" variant="secondary" size="sm">Review</x-ui.button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-start justify-between gap-4 border-b border-[var(--color-line)] pb-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Editorial Queues</p>
                        <h3 class="mt-2 text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Topics and briefs that still need action</h3>
                        <p class="mt-2 text-sm leading-6 text-[var(--color-muted)]">Keep editorial review moving before the pipeline widens further.</p>
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach ($editorialQueues as $queue)
                        <a
                            href="{{ $queue['href'] }}"
                            class="flex items-center justify-between gap-4 rounded-[calc(var(--radius-card)-0.25rem)] border p-4 transition-colors hover:bg-[var(--color-panel-soft)] {{ $toneSurfaceClasses[$queue['tone']] ?? $toneSurfaceClasses['default'] }}"
                        >
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[var(--color-ink)]">{{ $queue['label'] }}</p>
                                <p class="mt-1 text-sm leading-6 text-[var(--color-muted)]">{{ $queue['description'] }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-2xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">{{ $queue['value'] }}</p>
                                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Open</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-5 xl:col-span-4">
            <x-ui.card>
                <div class="flex items-start justify-between gap-4 border-b border-[var(--color-line)] pb-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Quick Actions</p>
                        <h3 class="mt-2 text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Jump into the next editorial move</h3>
                    </div>
                    <x-ui.badge tone="warning">Priority</x-ui.badge>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach ($quickActions as $action)
                        <a
                            href="{{ $action['href'] }}"
                            class="block rounded-[calc(var(--radius-card)-0.25rem)] border p-4 transition-colors hover:bg-[var(--color-panel-soft)] {{ $action['variant'] === 'primary' ? 'border-[color-mix(in_srgb,var(--color-accent)_35%,white)] bg-[color-mix(in_srgb,var(--color-accent)_8%,white)]' : 'border-[var(--color-line)] bg-[var(--color-panel)]' }}"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-[var(--color-ink)]">{{ $action['label'] }}</p>
                                    <p class="mt-1 text-sm leading-6 text-[var(--color-muted)]">{{ $action['description'] }}</p>
                                </div>
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--radius-button)] {{ $action['variant'] === 'primary' ? 'bg-[var(--color-accent)] text-[var(--color-accent-contrast)]' : 'bg-[var(--color-panel-soft)] text-[var(--color-muted)]' }}">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M7 5h8v8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M5 15 15 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Recently Published</p>
                        <h3 class="mt-2 text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Keep an eye on what just went live</h3>
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
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-[var(--color-ink)]">{{ $post['title'] }}</p>
                                        <p class="mt-1 text-xs text-[var(--color-muted)]">
                                            {{ $post['category'] ?: 'Uncategorized' }} · {{ $post['published_at'] ?: 'Unknown' }}
                                        </p>
                                        <p class="mt-1 text-xs text-[var(--color-muted)]">By {{ $post['author'] ?: 'Unknown author' }}</p>
                                    </div>
                                    <x-ui.badge tone="success">Live</x-ui.badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Recent AI Jobs</p>
                        <h3 class="mt-2 text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Compact monitoring for active automation</h3>
                    </div>
                    <x-ui.button as="a" :href="route('ai-jobs.index')" variant="secondary" size="sm">View All Jobs</x-ui.button>
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
                    <div class="mt-5 max-h-[28rem] space-y-3 overflow-y-auto pr-1">
                        @foreach ($recentAiJobs as $job)
                            <a
                                href="{{ route('ai-jobs.show', ['aiJob' => $job['id']]) }}"
                                class="block rounded-[calc(var(--radius-card)-0.25rem)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4 transition-colors hover:bg-[color-mix(in_srgb,var(--color-page)_65%,white)]"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-[var(--color-ink)]">Job #{{ $job['id'] }} · {{ str($job['type'])->headline() }}</p>
                                        <p class="mt-1 text-xs text-[var(--color-muted)]">
                                            {{ str($job['status'])->headline() }} · {{ $job['failed_at'] ?: $job['completed_at'] ?: $job['updated_at'] ?: $job['created_at'] ?: 'Unknown time' }}
                                        </p>
                                        <p class="mt-1 text-xs text-[var(--color-muted)]">
                                            {{ $job['entity_type'] ? str($job['entity_type'])->headline() : 'Workflow' }}{{ $job['entity_id'] ? ' #'.$job['entity_id'] : '' }}
                                        </p>
                                        <p class="mt-1 text-xs text-[var(--color-muted)]">
                                            {{ $job['provider'] ?: 'Unknown provider' }}{{ $job['model'] ? ' · '.$job['model'] : '' }}
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
