<div class="space-y-6">
    <x-admin.page-header
        eyebrow="News Review"
        title="News"
        description="Review discovered articles, inspect scoring and routing outcomes, and trigger the next review step without switching to publishing flows."
    >
        <div class="flex max-w-sm flex-col gap-2 lg:items-end">
            <x-ui.button type="button" wire:click="openDiscoveryDialog" wire:loading.attr="disabled" wire:target="openDiscoveryDialog">
                Discover News
            </x-ui.button>
            <p class="text-sm text-[var(--color-muted)] lg:text-right">Discovery can queue or run synchronously from a category so editors can review fresh intake immediately.</p>
        </div>
    </x-admin.page-header>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    @if ($categoryLoadError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-warning)_22%,white)] bg-[color-mix(in_srgb,var(--color-warning)_8%,white)] px-4 py-3 text-sm text-[var(--color-warning-strong)]">
            {{ $categoryLoadError }}
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        @foreach ($stats as $stat)
            <x-admin.stat-card :label="$stat['label']" :value="$stat['value']" :tone="$stat['tone'] ?? 'default'" />
        @endforeach
    </div>

    <x-admin.filter-bar>
        <x-slot:search>
            <label class="block">
                <span class="sr-only">Search news items</span>
                <x-ui.input type="search" wire:model.live.debounce.300ms="search" placeholder="Search title, publisher, description, or URL" />
            </label>
        </x-slot:search>

        <x-slot:filters>
            <div class="grid w-full gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <x-ui.select wire:model.live="statusFilter">
                    <option value="all">All statuses</option>
                    @foreach ($statusOptions as $statusOption)
                        <option value="{{ $statusOption }}">{{ str($statusOption)->headline() }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.select wire:model.live="categoryFilter">
                    <option value="all">All categories</option>
                    @foreach ($categoryOptions as $categoryOption)
                        <option value="{{ $categoryOption['id'] }}">{{ $categoryOption['name'] }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.select wire:model.live="decisionFilter">
                    <option value="all">All decisions</option>
                    @foreach ($decisionOptions as $decisionOption)
                        <option value="{{ $decisionOption }}">{{ str($decisionOption)->headline() }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.select wire:model.live="routeFilter">
                    <option value="all">All routes</option>
                    @foreach ($routeOptions as $routeOption)
                        <option value="{{ $routeOption }}">{{ str($routeOption)->headline() }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </x-slot:filters>

        <x-slot:results>
            <div class="flex items-center gap-3">
                <span>{{ $pagination['total'] }} {{ str('item')->plural($pagination['total']) }}</span>
                <span wire:loading wire:target="search,statusFilter,categoryFilter,decisionFilter,routeFilter,sortBy,discoverNews" class="text-xs text-[var(--color-muted)]">Refreshing…</span>
            </div>
        </x-slot:results>
    </x-admin.filter-bar>

    <x-ui.table caption="News review items" density="compact">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading width="feed-primary" sortable sort-key="title" :sort-state="$sort">Title</x-ui.table-heading>
                <x-ui.table-heading>Category</x-ui.table-heading>
                <x-ui.table-heading>Publisher</x-ui.table-heading>
                <x-ui.table-heading>Status</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="published_at" :sort-state="$sort">Published</x-ui.table-heading>
                <x-ui.table-heading>Score</x-ui.table-heading>
                <x-ui.table-heading>Decision</x-ui.table-heading>
                <x-ui.table-heading>Route</x-ui.table-heading>
                <x-ui.table-heading align="right">Actions</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($items as $item)
                <x-ui.table-row interactive wire:key="news-item-{{ $item['id'] }}">
                    <x-ui.table-cell width="feed-primary">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-[var(--color-ink)]">{{ $item['title'] }}</p>
                            <p class="mt-1 line-clamp-2 text-sm text-[var(--color-muted)]">{{ $item['description'] ?: 'Description pending' }}</p>
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $item['category_name'] ?: 'Unassigned' }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $item['publisher_name'] ?: 'Unknown' }}</x-ui.table-cell>
                    <x-ui.table-cell><x-admin.status-badge :status="$item['status']" /></x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $item['published_at'] ?: 'Unknown' }}</x-ui.table-cell>
                    <x-ui.table-cell>
                        <div class="space-y-1">
                            <x-ui.badge :tone="($item['latest_score_total_raw'] ?? null) !== null && $item['latest_score_total_raw'] >= 70 ? 'success' : (($item['latest_score_total_raw'] ?? null) !== null ? 'warning' : 'muted')">
                                {{ $item['latest_score_total'] ? $item['latest_score_total'] : 'Pending' }}
                            </x-ui.badge>
                            @if ($item['latest_score_total'])
                                <p class="text-xs text-[var(--color-muted)]">Latest total</p>
                            @endif
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell>
                        @if ($item['latest_decision'])
                            <x-ui.badge :tone="$item['latest_decision'] === 'ignore' ? 'muted' : 'warning'">
                                {{ str($item['latest_decision'])->headline() }}
                            </x-ui.badge>
                        @else
                            <span class="text-sm text-[var(--color-muted)]">Pending</span>
                        @endif
                    </x-ui.table-cell>
                    <x-ui.table-cell>
                        @if ($item['latest_route'])
                            <x-ui.badge :tone="$item['latest_route'] === 'ignore' ? 'muted' : 'success'">
                                {{ str($item['latest_route'])->headline() }}
                            </x-ui.badge>
                        @else
                            <span class="text-sm text-[var(--color-muted)]">Pending</span>
                        @endif
                    </x-ui.table-cell>
                    <x-ui.table-cell align="right">
                        <x-admin.row-actions>
                            <x-admin.row-action as="a" :href="route('news.show', ['news' => $item['id']])">Review</x-admin.row-action>
                        </x-admin.row-actions>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-empty colspan="9" title="No news items match the current view" message="Adjust the filters or run discovery to bring new articles into review." />
            @endforelse
        </x-ui.table-body>
    </x-ui.table>

    <x-ui.pagination :pagination="$pagination" item-label="item" class="border-transparent bg-transparent px-0 py-0" />

    <x-admin.confirm-dialog
        :open="$discoveryDialogOpen"
        title="Discover News"
        description="Select the review category, choose how many items to intake, and decide whether to run synchronously for immediate review."
    >
        <div class="space-y-4">
            @if ($discoveryError)
                <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                    {{ $discoveryError }}
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Category" for="discovery-category" :error="$errors->first('discoveryCategoryId')">
                    <x-ui.select id="discovery-category" wire:model.live="discoveryCategoryId">
                        <option value="">Select category</option>
                        @foreach ($categoryOptions as $categoryOption)
                            <option value="{{ $categoryOption['id'] }}" @disabled(! $categoryOption['is_active'])>
                                {{ $categoryOption['name'] }}{{ $categoryOption['is_active'] ? '' : ' (Inactive)' }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Limit" for="discovery-limit" :error="$errors->first('discoveryLimit')">
                    <x-ui.input id="discovery-limit" wire:model.blur="discoveryLimit" :invalid="$errors->has('discoveryLimit')" />
                </x-ui.field>
            </div>

            <label class="flex items-start gap-3 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                <input type="checkbox" wire:model.live="discoverySync" class="mt-1 h-4 w-4 rounded border-[var(--color-line-strong)] text-[var(--color-accent)] focus:ring-[var(--color-ring)]" />
                <span>
                    <span class="block text-sm font-medium text-[var(--color-ink)]">Run synchronously</span>
                    <span class="mt-1 block text-sm text-[var(--color-muted)]">Create review items immediately instead of queueing the discovery job.</span>
                </span>
            </label>
        </div>

        <x-slot:cancel>
            <x-ui.button variant="secondary" type="button" wire:click="closeDiscoveryDialog" wire:loading.attr="disabled" wire:target="closeDiscoveryDialog,discoverNews">
                Cancel
            </x-ui.button>
        </x-slot:cancel>

        <x-slot:confirm>
            <x-ui.button type="button" wire:click="discoverNews" wire:loading.attr="disabled" wire:target="discoverNews">
                <span wire:loading.remove wire:target="discoverNews">Discover News</span>
                <span wire:loading wire:target="discoverNews">Submitting…</span>
            </x-ui.button>
        </x-slot:confirm>
    </x-admin.confirm-dialog>
</div>
