<div class="space-y-6">
    <x-admin.page-header
        eyebrow="Topic Details"
        :title="$topicRecord['title'] ?? 'Topic detail'"
        description="Review the saved category context, score data, and automation state the backend now uses as the source of truth for this topic."
    >
        <x-ui.button as="a" :href="route('topic-queue.index')" variant="secondary">Back to Topic Queue</x-ui.button>
        @if (($topicRecord['can_generate_draft'] ?? false) === true)
            <x-ui.button type="button" wire:click="generateDraft" wire:loading.attr="disabled" wire:target="generateDraft">
                <span wire:loading.remove wire:target="generateDraft">Generate Draft</span>
                <span wire:loading wire:target="generateDraft">Queueing…</span>
            </x-ui.button>
        @endif
    </x-admin.page-header>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    @if ($actionError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $actionError }}
        </div>
    @endif

    @if ($notFound)
        <x-ui.empty-state title="Topic not found" message="The requested topic is no longer available from the service API." />
    @else
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_22rem]">
            <div class="space-y-6">
                <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Topic Metadata</p>
                            <h2 class="mt-2 text-xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">Scored Topic Record</h2>
                        </div>

                        <x-admin.status-badge :status="$topicRecord['status']" />
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Slug</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $topicRecord['slug'] ?: 'Slug pending' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Category</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $topicRecord['category_name'] }}</p>
                            <p class="mt-1 text-xs text-[var(--color-muted)]">{{ $topicRecord['category_slug'] ?: 'Category slug unavailable' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Cluster</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ str($topicRecord['cluster'])->headline() }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Primary Keyword</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $topicRecord['primary_keyword'] ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Search Intent</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $topicRecord['search_intent'] ?: 'Not set' }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Secondary Keywords</p>
                        @if ($topicRecord['secondary_keywords'] !== [])
                            <div class="flex flex-wrap gap-2">
                                @foreach ($topicRecord['secondary_keywords'] as $keyword)
                                    <x-ui.badge tone="muted">{{ $keyword }}</x-ui.badge>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-[var(--color-muted)]">No secondary keywords were returned.</p>
                        @endif
                    </div>

                    @if ($topicRecord['difficulty_note'] || $topicRecord['notes'])
                        <div class="grid gap-5">
                            @if ($topicRecord['difficulty_note'])
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Difficulty Note</p>
                                    <p class="mt-2 text-sm leading-6 text-[var(--color-ink)]">{{ $topicRecord['difficulty_note'] }}</p>
                                </div>
                            @endif

                            @if ($topicRecord['notes'])
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Notes</p>
                                    <p class="mt-2 text-sm leading-6 text-[var(--color-ink)]">{{ $topicRecord['notes'] }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </section>

                <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Score Breakdown</h2>
                        <p class="text-sm text-[var(--color-muted)]">The queue exists mainly to expose why the backend will keep or prune this topic.</p>
                    </div>

                    @if ($topicRecord['score_breakdown'] !== [])
                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach ($topicRecord['score_breakdown'] as $key => $value)
                                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">{{ str($key)->headline() }}</p>
                                    <p class="mt-2 text-lg font-semibold text-[var(--color-ink)]">{{ is_numeric($value) ? $value : 'N/A' }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-[var(--color-muted)]">No structured score breakdown was returned.</p>
                    @endif
                </section>
            </div>

            <aside class="space-y-6">
                <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                    <div class="space-y-1">
                        <h2 class="text-base font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Automation State</h2>
                        <p class="text-sm text-[var(--color-muted)]">Manual approval and brief gating are no longer the intended admin workflow. Draft generation now defaults to this topic’s saved category.</p>
                    </div>

                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Priority Score</p>
                        <div class="mt-3 flex items-center gap-3">
                            <p class="text-3xl font-semibold tracking-[-0.04em] text-[var(--color-ink)]">{{ $topicRecord['priority_score_label'] }}</p>
                            <x-ui.badge :tone="$automationTone">{{ ($topicRecord['priority_score'] ?? 0) >= 90 ? '90+ threshold met' : 'Below 90' }}</x-ui.badge>
                        </div>
                        <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">{{ $topicRecord['automation_state'] }}</p>
                    </div>

                    <div class="space-y-3 text-sm text-[var(--color-muted)]">
                        <div class="flex items-center justify-between gap-3">
                            <span>Category ID</span>
                            <span>{{ $topicRecord['category_id'] ?: 'Unknown' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Source</span>
                            <span>{{ $topicRecord['source'] === 'ai_suggested' ? 'AI Suggested' : 'Manual' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Backend Draft Flag</span>
                            <x-ui.badge :tone="$topicRecord['can_generate_draft'] ? 'success' : 'muted'">{{ $topicRecord['can_generate_draft'] ? 'Enabled' : 'Disabled' }}</x-ui.badge>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Created</span>
                            <span>{{ $topicRecord['created_at'] ?: 'Unknown' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Updated</span>
                            <span>{{ $topicRecord['updated_at'] ?: 'Unknown' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Approved At</span>
                            <span>{{ $topicRecord['approved_at'] ?: 'Not set' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Rejected At</span>
                            <span>{{ $topicRecord['rejected_at'] ?: 'Not set' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Used At</span>
                            <span>{{ $topicRecord['used_at'] ?: 'Not set' }}</span>
                        </div>
                    </div>
                </section>

                <x-admin.callout title="Editorial Boundary" tone="warning">
                    Topic inspection is useful for understanding automation, but the main editorial task now starts when the generated post draft appears in <a href="{{ route('draft-review.index') }}" class="font-medium text-[var(--color-ink)] underline underline-offset-2">Draft Review</a>.
                </x-admin.callout>
            </aside>
        </div>
    @endif
</div>
