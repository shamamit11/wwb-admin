<div class="space-y-6">
    <x-admin.page-header
        title="SEO"
        description="Review per-post score signals and inspect generated schema without inventing unsupported sitewide issue queues."
    >
        <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-3 text-sm text-[var(--color-muted)]">
            The current service supports per-entity reads. Broader review endpoints remain out of scope.
        </div>
    </x-admin.page-header>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    <x-ui.tabs>
        <x-ui.tabs-list>
            <x-ui.tabs-trigger type="button" :active="$activeTab === 'insights'" wire:click="$set('activeTab', 'insights')">SEO Insights</x-ui.tabs-trigger>
            <x-ui.tabs-trigger type="button" :active="$activeTab === 'utilities'" wire:click="$set('activeTab', 'utilities')">Feeds & Sitemap</x-ui.tabs-trigger>
        </x-ui.tabs-list>
    </x-ui.tabs>

    @if ($activeTab === 'insights')
        <div class="grid gap-6 xl:grid-cols-[20rem_minmax(0,1fr)]">
            <aside class="space-y-4">
                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Post Selection</h2>
                            <p class="text-sm text-[var(--color-muted)]">Use the post list as the operational entry point until broader SEO review APIs exist.</p>
                        </div>

                        <label class="block">
                            <span class="sr-only">Search posts</span>
                            <x-ui.input
                                type="search"
                                wire:model.live.debounce.300ms="search"
                                placeholder="Search posts"
                            />
                        </label>

                        <x-ui.select wire:model.live="statusFilter">
                            <option value="all">All statuses</option>
                            <option value="draft">Draft</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="published">Published</option>
                            <option value="unpublished">Unpublished</option>
                            <option value="archived">Archived</option>
                        </x-ui.select>
                    </div>
                </div>

                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] shadow-[var(--shadow-card)]">
                    @if ($posts !== [])
                        <div class="max-h-[34rem] overflow-y-auto">
                            @foreach ($posts as $post)
                                <button
                                    type="button"
                                    wire:key="seo-post-{{ $post['id'] }}"
                                    wire:click="selectPost({{ $post['id'] }})"
                                    @class([
                                        'flex w-full items-start justify-between gap-4 border-b border-[var(--color-line)] px-5 py-4 text-left transition-colors last:border-b-0 hover:bg-[var(--color-panel-soft)]',
                                        'bg-[var(--color-panel-soft)]' => (string) $selectedPostId === (string) $post['id'],
                                    ])
                                >
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-semibold text-[var(--color-ink)]">{{ $post['title'] }}</span>
                                        <span class="mt-1 block text-sm text-[var(--color-muted)]">{{ $post['slug'] !== '' ? '/'.$post['slug'] : 'Slug pending' }}</span>
                                        <span class="mt-2 block text-xs uppercase tracking-[0.14em] text-[var(--color-muted)]">{{ str($post['status'])->headline() }}</span>
                                    </span>

                                    @if ((string) $selectedPostId === (string) $post['id'])
                                        <span class="shrink-0 text-xs font-semibold uppercase tracking-[0.14em] text-[var(--color-accent-strong)]">Selected</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="px-5 py-8">
                            <p class="text-sm font-semibold text-[var(--color-ink)]">No posts available for SEO review.</p>
                            <p class="mt-2 text-sm text-[var(--color-muted)]">Create or publish content first, then return here for per-entity score and schema inspection.</p>
                        </div>
                    @endif
                </div>
            </aside>

            <div class="space-y-6">
                @if ($selectedPost !== [])
                    <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="space-y-1">
                                <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">{{ $selectedPost['title'] }}</h2>
                                <p class="text-sm text-[var(--color-muted)]">
                                    {{ $selectedPost['slug'] !== '' ? '/'.$selectedPost['slug'] : 'Slug pending' }}
                                    @if ($selectedPost['category_name'])
                                        · {{ $selectedPost['category_name'] }}
                                    @endif
                                </p>
                            </div>

                            <x-ui.button as="a" :href="route('posts.edit', ['post' => $selectedPost['id']])" variant="secondary">Edit Post</x-ui.button>
                        </div>

                        <div class="grid gap-4 md:grid-cols-[minmax(0,16rem)_minmax(0,1fr)]">
                            <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Overall SEO Score</p>
                                <div class="mt-3 flex items-center gap-3">
                                    <x-admin.seo-score-badge :score="$scoreValue" />
                                    @if ($scoreGrade)
                                        <span class="text-sm font-medium text-[var(--color-muted)]">{{ $scoreGrade }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Operational Note</p>
                                <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">These insights stay entity-specific. Use them to tighten metadata, content structure, schema coverage, and internal linking on the selected post.</p>
                            </div>
                        </div>

                        @if ($scoreError)
                            <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                                {{ $scoreError }}
                            </div>
                        @endif

                        @if ($scoreSubscores !== [])
                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                @foreach ($scoreSubscores as $subscore)
                                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">{{ $subscore['label'] }}</p>
                                        <p class="mt-2 text-lg font-semibold text-[var(--color-ink)]">
                                            {{ $subscore['score'] ?? 'TBC' }}
                                            @if ($subscore['max_score'] !== null)
                                                <span class="text-sm font-medium text-[var(--color-muted)]">/ {{ $subscore['max_score'] }}</span>
                                            @endif
                                        </p>
                                        @if ($subscore['suggestion_count'] !== null)
                                            <p class="mt-2 text-sm text-[var(--color-muted)]">{{ $subscore['suggestion_count'] }} {{ str('suggestion')->plural($subscore['suggestion_count']) }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="space-y-3">
                            <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Recommendations</h3>
                            @if ($recommendations !== [])
                                <div class="space-y-2">
                                    @foreach ($recommendations as $recommendation)
                                        <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3 text-sm text-[var(--color-ink)]">
                                            {{ $recommendation }}
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3 text-sm text-[var(--color-muted)]">
                                    No specific recommendations were returned for this entity.
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                        <div class="space-y-1">
                            <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Schema Output</h2>
                            <p class="text-sm text-[var(--color-muted)]">Inspect the generated JSON-LD in a read-only operational view. Summary first, raw payload second.</p>
                        </div>

                        @if ($schemaError)
                            <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                                {{ $schemaError }}
                            </div>
                        @endif

                        @if ($schemaSummary['graph_count'] > 0 || $schemaSummary['context'])
                            <div class="grid gap-4 md:grid-cols-3">
                                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Context</p>
                                    <p class="mt-2 break-all text-sm text-[var(--color-ink)]">{{ $schemaSummary['context'] ?: 'TBC' }}</p>
                                </div>
                                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Graph Items</p>
                                    <p class="mt-2 text-sm font-semibold text-[var(--color-ink)]">{{ $schemaSummary['graph_count'] }}</p>
                                </div>
                                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Types</p>
                                    <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $schemaSummary['graph_types'] !== [] ? implode(', ', $schemaSummary['graph_types']) : 'TBC' }}</p>
                                </div>
                            </div>
                        @endif

                        @if ($schemaJson !== '')
                            <div class="overflow-hidden rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)]">
                                <div class="border-b border-[var(--color-line)] px-4 py-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Generated JSON-LD</p>
                                </div>
                                <pre class="max-h-[32rem] overflow-auto px-4 py-4 text-xs leading-6 text-[var(--color-ink)]">{{ $schemaJson }}</pre>
                            </div>
                        @else
                            <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3 text-sm text-[var(--color-muted)]">
                                No schema payload is available for the selected entity.
                            </div>
                        @endif
                    </section>
                @else
                    <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-8 shadow-[var(--shadow-card)]">
                        <p class="text-sm font-semibold text-[var(--color-ink)]">Select a post to inspect score and schema output.</p>
                        <p class="mt-2 text-sm text-[var(--color-muted)]">This screen intentionally stays per entity because the service does not yet expose broader SEO issue review endpoints.</p>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="space-y-6">
            <x-ui.tabs-panel>
                <div class="space-y-2">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Operational Utilities</h2>
                    <p class="text-sm text-[var(--color-muted)]">These views are read-only snapshots of the service-backed RSS and sitemap outputs. They are for inspection, not editing.</p>
                </div>
            </x-ui.tabs-panel>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">RSS Feed</h2>
                    <p class="text-sm text-[var(--color-muted)]">Review what the admin RSS feed is currently exposing for published posts.</p>
                </div>

                @if ($rssError)
                    <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                        {{ $rssError }}
                    </div>
                @endif

                <x-ui.table caption="RSS feed entries" density="compact">
                    <x-ui.table-head>
                        <tr>
                            <x-ui.table-heading width="feed-primary">TITLE</x-ui.table-heading>
                            <x-ui.table-heading>AUTHOR</x-ui.table-heading>
                            <x-ui.table-heading>CATEGORY</x-ui.table-heading>
                            <x-ui.table-heading>PUBLISHED</x-ui.table-heading>
                            <x-ui.table-heading>UPDATED</x-ui.table-heading>
                            <x-ui.table-heading>LINK</x-ui.table-heading>
                        </tr>
                    </x-ui.table-head>
                    <x-ui.table-body>
                        @forelse ($rssEntries as $entry)
                            <x-ui.table-row wire:key="rss-entry-{{ $entry['id'] }}">
                                <x-ui.table-cell width="feed-primary">
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-[var(--color-ink)]">{{ $entry['title'] }}</p>
                                        <p class="mt-1 text-sm text-[var(--color-muted)]">/{{ $entry['slug'] }}</p>
                                        <p class="mt-2 text-sm text-[var(--color-muted)]">{{ $entry['description'] }}</p>
                                    </div>
                                </x-ui.table-cell>
                                <x-ui.table-cell subdued>{{ $entry['author_name'] !== '' ? $entry['author_name'] : 'Unknown' }}</x-ui.table-cell>
                                <x-ui.table-cell subdued>{{ $entry['category_name'] !== '' ? $entry['category_name'] : 'Unassigned' }}</x-ui.table-cell>
                                <x-ui.table-cell subdued>{{ $entry['published_at'] ?: 'Unknown' }}</x-ui.table-cell>
                                <x-ui.table-cell subdued>{{ $entry['last_modified_at'] ?: 'Unknown' }}</x-ui.table-cell>
                                <x-ui.table-cell>
                                    @if ($entry['link'] !== '')
                                        <a href="{{ $entry['link'] }}" target="_blank" rel="noreferrer" class="text-[var(--color-accent-strong)] underline decoration-[color-mix(in_srgb,var(--color-accent)_38%,white)] underline-offset-4">
                                            Open feed URL
                                        </a>
                                    @else
                                        <span class="text-[var(--color-muted)]">Unavailable</span>
                                    @endif
                                </x-ui.table-cell>
                            </x-ui.table-row>
                        @empty
                            <x-ui.table-empty colspan="6" title="No RSS items available" message="The service did not return any feed entries." />
                        @endforelse
                    </x-ui.table-body>
                </x-ui.table>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Sitemap</h2>
                    <p class="text-sm text-[var(--color-muted)]">Inspect the current sitemap entries and the canonical URLs the service is publishing.</p>
                </div>

                @if ($sitemapError)
                    <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                        {{ $sitemapError }}
                    </div>
                @endif

                <x-ui.table caption="Sitemap entries" density="compact">
                    <x-ui.table-head>
                        <tr>
                            <x-ui.table-heading>SLUG</x-ui.table-heading>
                            <x-ui.table-heading>CANONICAL URL</x-ui.table-heading>
                            <x-ui.table-heading>PUBLISHED</x-ui.table-heading>
                            <x-ui.table-heading>LAST MODIFIED</x-ui.table-heading>
                        </tr>
                    </x-ui.table-head>
                    <x-ui.table-body>
                        @forelse ($sitemapEntries as $entry)
                            <x-ui.table-row wire:key="sitemap-entry-{{ $entry['id'] }}">
                                <x-ui.table-cell>
                                    <span class="font-semibold text-[var(--color-ink)]">/{{ $entry['slug'] }}</span>
                                </x-ui.table-cell>
                                <x-ui.table-cell>
                                    @if ($entry['canonical_url'] !== '')
                                        <a href="{{ $entry['canonical_url'] }}" target="_blank" rel="noreferrer" class="text-[var(--color-accent-strong)] underline decoration-[color-mix(in_srgb,var(--color-accent)_38%,white)] underline-offset-4">
                                            {{ $entry['canonical_url'] }}
                                        </a>
                                    @else
                                        <span class="text-[var(--color-muted)]">Unavailable</span>
                                    @endif
                                </x-ui.table-cell>
                                <x-ui.table-cell subdued>{{ $entry['published_at'] ?: 'Unknown' }}</x-ui.table-cell>
                                <x-ui.table-cell subdued>{{ $entry['last_modified_at'] ?: 'Unknown' }}</x-ui.table-cell>
                            </x-ui.table-row>
                        @empty
                            <x-ui.table-empty colspan="4" title="No sitemap items available" message="The service did not return any sitemap entries." />
                        @endforelse
                    </x-ui.table-body>
                </x-ui.table>
            </section>
        </div>
    @endif
</div>
