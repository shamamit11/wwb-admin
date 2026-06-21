<div class="space-y-6">
    <x-admin.page-header
        eyebrow="News Review"
        :title="$newsItem['title'] ?? 'News detail'"
        description="Inspect article metadata, score reasoning, extraction output, and routing results before deciding what should happen next."
    >
        <x-ui.button as="a" :href="route('news.index')" variant="secondary">Back to News</x-ui.button>
        <x-ui.button type="button" variant="secondary" wire:click="refreshNews" wire:loading.attr="disabled" wire:target="refreshNews">
            <span wire:loading.remove wire:target="refreshNews">Refresh</span>
            <span wire:loading wire:target="refreshNews">Refreshing…</span>
        </x-ui.button>
        <x-ui.button type="button" variant="secondary" wire:click="score" wire:loading.attr="disabled" wire:target="score">
            <span wire:loading.remove wire:target="score">Score</span>
            <span wire:loading wire:target="score">Scoring…</span>
        </x-ui.button>
        <x-ui.button type="button" variant="secondary" wire:click="extract" wire:loading.attr="disabled" wire:target="extract">
            <span wire:loading.remove wire:target="extract">Extract</span>
            <span wire:loading wire:target="extract">Extracting…</span>
        </x-ui.button>
        <x-ui.button type="button" wire:click="routeNews" wire:loading.attr="disabled" wire:target="routeNews">
            <span wire:loading.remove wire:target="routeNews">Route</span>
            <span wire:loading wire:target="routeNews">Routing…</span>
        </x-ui.button>
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
        <x-ui.empty-state title="News item not found" message="The requested article is no longer available from the service API." />
    @else
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_minmax(20rem,1fr)]">
            <div class="space-y-6">
                <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Article Metadata</p>
                            <h2 class="mt-2 text-xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">{{ $newsItem['title'] }}</h2>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <x-admin.status-badge :status="$newsItem['status'] ?? null" />
                            @if (filled(data_get($newsItem, 'latest_score.decision')))
                                <x-ui.badge :tone="data_get($newsItem, 'latest_score.decision') === 'ignore' ? 'muted' : 'warning'">
                                    {{ str(data_get($newsItem, 'latest_score.decision'))->headline() }}
                                </x-ui.badge>
                            @endif
                            @if (filled(data_get($newsItem, 'latest_route.route')))
                                <x-ui.badge :tone="data_get($newsItem, 'latest_route.route') === 'ignore' ? 'muted' : 'success'">
                                    {{ str(data_get($newsItem, 'latest_route.route'))->headline() }}
                                </x-ui.badge>
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Category</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ data_get($newsItem, 'category.name') ?: 'Unassigned' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Publisher</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $newsItem['publisher_name'] ?: 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Published</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $newsItem['published_at'] ?: 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Discovered</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $newsItem['discovered_at'] ?: 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Author</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $newsItem['author'] ?: 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Provider</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $newsItem['provider'] ?: 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Language</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $newsItem['language'] ?: 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Country</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $newsItem['country'] ?: 'Unknown' }}</p>
                        </div>
                    </div>

                    @if ($newsItem['description'])
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Description</p>
                            <p class="mt-2 text-sm leading-6 text-[var(--color-ink)]">{{ $newsItem['description'] }}</p>
                        </div>
                    @endif

                    <div class="grid gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Article URL</p>
                            @if ($newsItem['url'])
                                <a href="{{ $newsItem['url'] }}" target="_blank" rel="noreferrer" class="mt-2 inline-flex text-sm text-[var(--color-accent-strong)] underline decoration-[color-mix(in_srgb,var(--color-accent)_38%,white)] underline-offset-4">
                                    Open original article
                                </a>
                            @else
                                <p class="mt-2 text-sm text-[var(--color-muted)]">Unavailable</p>
                            @endif
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Canonical URL</p>
                            @if ($newsItem['canonical_url'])
                                <a href="{{ $newsItem['canonical_url'] }}" target="_blank" rel="noreferrer" class="mt-2 inline-flex text-sm text-[var(--color-accent-strong)] underline decoration-[color-mix(in_srgb,var(--color-accent)_38%,white)] underline-offset-4">
                                    Open canonical URL
                                </a>
                            @else
                                <p class="mt-2 text-sm text-[var(--color-muted)]">Not set</p>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Score Breakdown</h2>
                        <p class="text-sm text-[var(--color-muted)]">Use the latest scoring pass to decide whether this article should be ignored, routed into knowledge, or escalated into a topic.</p>
                    </div>

                    @if (data_get($newsItem, 'latest_score'))
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            @foreach ($scoreCards as $card)
                                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">{{ $card['label'] }}</p>
                                    <p class="mt-2 text-lg font-semibold text-[var(--color-ink)]">{{ $card['value'] }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Decision</p>
                                <div class="mt-2">
                                    <x-ui.badge :tone="data_get($newsItem, 'latest_score.decision') === 'ignore' ? 'muted' : 'warning'">
                                        {{ str(data_get($newsItem, 'latest_score.decision'))->headline() }}
                                    </x-ui.badge>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Scored At</p>
                                <p class="mt-2 text-sm text-[var(--color-ink)]">{{ \Illuminate\Support\Carbon::parse(data_get($newsItem, 'latest_score.scored_at'))->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Reasoning</p>
                            <p class="mt-2 text-sm leading-6 text-[var(--color-ink)]">{{ data_get($newsItem, 'latest_score.reasoning') ?: 'No reasoning returned.' }}</p>
                        </div>
                    @else
                        <p class="text-sm text-[var(--color-muted)]">No score has been generated yet.</p>
                    @endif
                </section>

                <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Extraction Review</h2>
                        <p class="text-sm text-[var(--color-muted)]">Validate the excerpt and structured extraction output before trusting downstream routing.</p>
                    </div>

                    @if (data_get($newsItem, 'latest_extraction'))
                        <div class="grid gap-5">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Excerpt</p>
                                <p class="mt-2 text-sm leading-6 text-[var(--color-ink)]">{{ data_get($newsItem, 'latest_extraction.excerpt') ?: 'No excerpt returned.' }}</p>
                            </div>

                            <div class="grid gap-5 lg:grid-cols-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Facts</p>
                                    @if (count(data_get($newsItem, 'latest_extraction.facts_json', [])) > 0)
                                        <ul class="mt-2 space-y-2 text-sm text-[var(--color-ink)]">
                                            @foreach (data_get($newsItem, 'latest_extraction.facts_json', []) as $fact)
                                                <li class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-3 py-2">{{ is_array($fact) ? json_encode($fact, JSON_UNESCAPED_SLASHES) : $fact }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="mt-2 text-sm text-[var(--color-muted)]">None</p>
                                    @endif
                                </div>

                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Entities</p>
                                    @if (count(data_get($newsItem, 'latest_extraction.entities_json', [])) > 0)
                                        <ul class="mt-2 space-y-2 text-sm text-[var(--color-ink)]">
                                            @foreach (data_get($newsItem, 'latest_extraction.entities_json', []) as $entity)
                                                <li class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-3 py-2">{{ is_array($entity) ? json_encode($entity, JSON_UNESCAPED_SLASHES) : $entity }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="mt-2 text-sm text-[var(--color-muted)]">None</p>
                                    @endif
                                </div>

                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Claims</p>
                                    @if (count(data_get($newsItem, 'latest_extraction.claims_json', [])) > 0)
                                        <ul class="mt-2 space-y-2 text-sm text-[var(--color-ink)]">
                                            @foreach (data_get($newsItem, 'latest_extraction.claims_json', []) as $claim)
                                                <li class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-3 py-2">{{ is_array($claim) ? json_encode($claim, JSON_UNESCAPED_SLASHES) : $claim }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="mt-2 text-sm text-[var(--color-muted)]">None</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-[var(--color-muted)]">No extraction has been generated yet.</p>
                    @endif
                </section>

                <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Route Result</h2>
                        <p class="text-sm text-[var(--color-muted)]">Review the latest routing output and any linked records created or matched by the backend.</p>
                    </div>

                    @if (data_get($newsItem, 'latest_route'))
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Latest Route</p>
                                <div class="mt-2">
                                    <x-ui.badge :tone="data_get($newsItem, 'latest_route.route') === 'ignore' ? 'muted' : 'success'">
                                        {{ str(data_get($newsItem, 'latest_route.route'))->headline() }}
                                    </x-ui.badge>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Routed At</p>
                                <p class="mt-2 text-sm text-[var(--color-ink)]">{{ data_get($newsItem, 'latest_route.routed_at') ? \Illuminate\Support\Carbon::parse(data_get($newsItem, 'latest_route.routed_at'))->format('M j, Y g:i A') : 'Unknown' }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Linked Records</p>
                            @if ($linkedRecords !== [])
                                <div class="mt-3 grid gap-3">
                                    @foreach ($linkedRecords as $record)
                                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                                            <div>
                                                <p class="text-sm font-semibold text-[var(--color-ink)]">{{ $record['title'] }}</p>
                                                <p class="mt-1 text-xs uppercase tracking-[0.16em] text-[var(--color-muted)]">
                                                    {{ $record['label'] }}{{ $record['meta'] ? ' · '.str($record['meta'])->headline() : '' }}
                                                </p>
                                            </div>

                                            <x-ui.button as="a" :href="$record['href']" variant="secondary" size="sm">Open</x-ui.button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-2 text-sm text-[var(--color-muted)]">None</p>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-[var(--color-muted)]">No route result has been generated yet.</p>
                    @endif
                </section>
            </div>

            <aside class="space-y-6">
                <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                    <div class="space-y-1">
                        <h2 class="text-base font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Record Summary</h2>
                        <p class="text-sm text-[var(--color-muted)]">Keep this view review-oriented: intake, score, extract, route, then open linked knowledge or topic records when needed.</p>
                    </div>

                    <div class="space-y-3 text-sm text-[var(--color-muted)]">
                        <div class="flex items-center justify-between gap-3">
                            <span>News ID</span>
                            <span>#{{ $newsItem['id'] }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>External ID</span>
                            <span>{{ $newsItem['external_id'] ?: 'Not set' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Normalized Title</span>
                            <span class="text-right">{{ $newsItem['normalized_title'] ?: 'Not set' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Source</span>
                            <span>{{ data_get($newsItem, 'source.name') ?: 'Unknown' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Trust Score</span>
                            <span>{{ data_get($newsItem, 'source.trust_score') ?? 'Unknown' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Created</span>
                            <span>{{ $newsItem['created_at'] ?: 'Unknown' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Updated</span>
                            <span>{{ $newsItem['updated_at'] ?: 'Unknown' }}</span>
                        </div>
                    </div>
                </section>

                <div class="space-y-3">
                    <details class="overflow-hidden rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] shadow-[var(--shadow-card)]">
                        <summary class="cursor-pointer list-none px-5 py-4 text-sm font-semibold text-[var(--color-ink)]">Raw Article Metadata</summary>
                        <div class="border-t border-[var(--color-line)] px-5 py-5">
                            <pre class="max-h-[18rem] overflow-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4 text-xs leading-6 text-[var(--color-muted)]">{{ $articleMetadataJson }}</pre>
                        </div>
                    </details>

                    <details class="overflow-hidden rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] shadow-[var(--shadow-card)]">
                        <summary class="cursor-pointer list-none px-5 py-4 text-sm font-semibold text-[var(--color-ink)]">Raw Extraction Metadata</summary>
                        <div class="border-t border-[var(--color-line)] px-5 py-5">
                            <pre class="max-h-[18rem] overflow-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4 text-xs leading-6 text-[var(--color-muted)]">{{ $extractionMetadataJson }}</pre>
                        </div>
                    </details>

                    <details class="overflow-hidden rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] shadow-[var(--shadow-card)]">
                        <summary class="cursor-pointer list-none px-5 py-4 text-sm font-semibold text-[var(--color-ink)]">Raw Route Metadata</summary>
                        <div class="border-t border-[var(--color-line)] px-5 py-5">
                            <pre class="max-h-[18rem] overflow-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4 text-xs leading-6 text-[var(--color-muted)]">{{ $routeMetadataJson }}</pre>
                        </div>
                    </details>
                </div>
            </aside>
        </div>
    @endif
</div>
