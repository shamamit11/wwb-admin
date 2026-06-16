<div class="space-y-8">
    @if ($dashboardError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-warning)_24%,white)] bg-[color-mix(in_srgb,var(--color-warning)_10%,white)] px-4 py-3 text-sm text-[var(--color-warning-strong)]">
            {{ $dashboardError }}
        </div>
    @endif

    <section class="grid gap-6 xl:grid-cols-4">
        <x-admin.stat-card label="Recent Drafts" :value="(string) count($recentDrafts)" tone="accent">
            <x-ui.badge tone="success">Live</x-ui.badge>
        </x-admin.stat-card>
        <x-admin.stat-card label="Recently Published" :value="(string) count($recentPublishedPosts)" tone="soft">
            <x-ui.badge tone="success">Live</x-ui.badge>
        </x-admin.stat-card>
        <x-admin.stat-card label="Topic Queue" value="TBC" tone="default">
            <x-ui.badge tone="warning">Placeholder</x-ui.badge>
        </x-admin.stat-card>
        <x-admin.stat-card label="AI Jobs" value="TBC" tone="default">
            <x-ui.badge tone="warning">Placeholder</x-ui.badge>
        </x-admin.stat-card>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(0,1.25fr)_minmax(320px,0.85fr)]">
        <x-ui.card>
            <div class="space-y-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Recent Drafts</p>
                    <h2 class="mt-3 text-xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">Continue editorial work already in motion.</h2>
                    <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">
                        Drafts are loaded from the current posts API using the documented draft status filter.
                    </p>
                </div>

                @if ($recentDrafts === [])
                    <x-ui.empty-state
                        title="No recent drafts returned"
                        message="Either there are no draft posts yet, or the service has not returned draft records for this account."
                    >
                        <x-ui.button as="a" :href="route('posts.index')" variant="outline">Open Posts</x-ui.button>
                    </x-ui.empty-state>
                @else
                    <div class="space-y-3">
                        @foreach ($recentDrafts as $post)
                            <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[color-mix(in_srgb,var(--color-panel)_88%,white)] px-4 py-3">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-[var(--color-ink)]">{{ $post['title'] }}</p>
                                        <p class="mt-1 text-xs text-[var(--color-muted)]">
                                            {{ $post['category'] ?: 'Uncategorized' }} · {{ $post['author'] ?: 'Unknown author' }}
                                        </p>
                                    </div>
                                    <x-admin.status-badge :status="$post['status']" />
                                </div>
                                <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-[var(--color-muted)]">
                                    <span>Updated {{ $post['updated_at'] ?: 'TBC' }}</span>
                                    <span>Visibility {{ str($post['visibility'] ?: 'unknown')->headline() }}</span>
                                    <span>{{ $post['word_count'] ? number_format($post['word_count']) : 'TBC' }} words</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="space-y-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Recently Published</p>
                    <h2 class="mt-3 text-xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">Keep an eye on what just went live.</h2>
                    <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">
                        Published posts are loaded through the same posts API using the documented published status filter.
                    </p>
                </div>

                @if ($recentPublishedPosts === [])
                    <x-ui.empty-state
                        title="No recent published posts returned"
                        message="The service has not returned published records yet, so review should continue in the posts module."
                    >
                        <x-ui.button as="a" :href="route('posts.index')" variant="outline">Review Posts</x-ui.button>
                    </x-ui.empty-state>
                @else
                    <div class="space-y-3">
                        @foreach ($recentPublishedPosts as $post)
                            <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[color-mix(in_srgb,var(--color-panel)_88%,white)] px-4 py-3">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-[var(--color-ink)]">{{ $post['title'] }}</p>
                                        <p class="mt-1 text-xs text-[var(--color-muted)]">
                                            {{ $post['category'] ?: 'Uncategorized' }} · {{ $post['author'] ?: 'Unknown author' }}
                                        </p>
                                    </div>
                                    <x-admin.status-badge :status="$post['status']" />
                                </div>
                                <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-[var(--color-muted)]">
                                    <span>Published {{ $post['published_at'] ?: 'TBC' }}</span>
                                    <span>Updated {{ $post['updated_at'] ?: 'TBC' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-ui.card>

        <div class="space-y-6">
            <x-ui.card class="bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-panel)_70%,white),var(--color-panel))]">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Quick Actions</p>
                <div class="mt-4 flex flex-col gap-3">
                    <x-ui.button as="a" :href="route('posts.index')">Review Drafts</x-ui.button>
                    <x-ui.button as="a" :href="route('categories.index')" variant="secondary">Manage Categories</x-ui.button>
                    <x-ui.button as="a" :href="route('seo.index')" variant="secondary">Open SEO Area</x-ui.button>
                </div>
            </x-ui.card>

            <x-ui.card>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Topic Queue</p>
                <h3 class="mt-3 text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Placeholder only</h3>
                <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">
                    No topic queue endpoints exist in the current service phase, so this widget stays explicitly non-operational.
                </p>
                <div class="mt-4">
                    <x-ui.badge tone="warning">TBC Until Service Support</x-ui.badge>
                </div>
            </x-ui.card>

            <x-ui.card>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">AI Jobs</p>
                <h3 class="mt-3 text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Placeholder only</h3>
                <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">
                    AI job monitoring is reserved in the shell, but the current API phase does not expose job endpoints or dashboard aggregates.
                </p>
                <div class="mt-4">
                    <x-ui.badge tone="warning">TBC Until Service Support</x-ui.badge>
                </div>
            </x-ui.card>
        </div>
    </section>
</div>
