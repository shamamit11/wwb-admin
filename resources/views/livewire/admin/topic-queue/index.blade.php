<div class="space-y-6">
    <x-admin.page-header
        eyebrow="AI Content"
        title="Topic Queue"
        description="Review category-owned topics, their editorial fit, and the backend automation thresholds that decide whether a topic stays in queue or moves downstream."
    >
        <div class="flex max-w-sm flex-col gap-2 lg:items-end">
            <x-ui.button type="button" wire:click="openDiscoveryDialog" wire:loading.attr="disabled" wire:target="openDiscoveryDialog">
                Run Topic Discovery
            </x-ui.button>
            <p class="text-sm text-[var(--color-muted)] lg:text-right">Discovery now starts from a category selection. Cluster context is derived by the backend from that category.</p>
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

    <x-admin.callout title="Automation Model">
        Topics scoring below <span class="font-medium text-[var(--color-ink)]">70</span> are auto-deleted by backend automation. Topics scoring from <span class="font-medium text-[var(--color-ink)]">70 to 84.99</span> stay in Topic Queue for editorial review. Topics scoring <span class="font-medium text-[var(--color-ink)]">85 or above</span> auto-queue blog draft generation.
    </x-admin.callout>

    <x-admin.filter-bar>
        <x-slot:search>
            <label class="block">
                <span class="sr-only">Search topics</span>
                <x-ui.input type="search" wire:model.live.debounce.300ms="search" placeholder="Search topics by title or primary keyword" />
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

                <x-ui.select wire:model.live="clusterFilter">
                    <option value="all">All clusters</option>
                    @foreach ($clusterOptions as $clusterOption)
                        <option value="{{ $clusterOption }}">{{ str($clusterOption)->headline() }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.select wire:model.live="sourceFilter">
                    <option value="all">All sources</option>
                    @foreach ($sourceOptions as $sourceOption)
                        <option value="{{ $sourceOption }}">{{ $sourceOption === 'ai_suggested' ? 'AI Suggested' : 'Manual' }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </x-slot:filters>

        <x-slot:results>{{ $pagination['total'] }} {{ str('topic')->plural($pagination['total']) }}</x-slot:results>
    </x-admin.filter-bar>

    <x-ui.table caption="Topic queue" density="compact">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading width="workflow-primary" sortable sort-key="title" :sort-state="$sort">Title</x-ui.table-heading>
                <x-ui.table-heading>Category</x-ui.table-heading>
                <x-ui.table-heading>Cluster</x-ui.table-heading>
                <x-ui.table-heading>Primary Keyword</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="priority_score" :sort-state="$sort">Score</x-ui.table-heading>
                <x-ui.table-heading>Automation</x-ui.table-heading>
                <x-ui.table-heading>Status</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="created_at" :sort-state="$sort">Created</x-ui.table-heading>
                <x-ui.table-heading align="right">Actions</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($topics as $topic)
                <x-ui.table-row interactive wire:key="topic-{{ $topic['id'] }}">
                    <x-ui.table-cell width="workflow-primary">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-[var(--color-ink)]">{{ $topic['title'] }}</p>
                            @if ($topic['is_ai_tools'])
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <x-ui.badge tone="default">AI Tools</x-ui.badge>
                                    <x-ui.badge tone="muted">Commercial Intent</x-ui.badge>
                                    @if ($topic['ai_tools_fit_label'])
                                        <x-ui.badge :tone="$topic['ai_tools_fit_tone']">{{ $topic['ai_tools_fit_label'] }}</x-ui.badge>
                                    @endif
                                </div>
                            @endif
                            <p class="mt-1 text-sm text-[var(--color-muted)]">{{ $topic['slug'] ?: 'Slug pending' }}</p>
                            @if ($topic['is_ai_tools'] && $topic['ai_tools_fit_note'])
                                <p class="mt-2 text-xs text-[var(--color-muted)]">{{ $topic['ai_tools_fit_note'] }}</p>
                            @endif
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p>{{ $topic['category_name'] }}</p>
                                @if ($topic['is_ai_tools'])
                                    <x-ui.badge tone="warning">Tool-Focused</x-ui.badge>
                                @endif
                            </div>
                            <p class="text-xs text-[var(--color-muted)]">{{ $topic['category_slug'] ?: 'Category slug unavailable' }}</p>
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ str($topic['cluster'])->headline() }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $topic['primary_keyword'] ?: 'Not set' }}</x-ui.table-cell>
                    <x-ui.table-cell>
                        <div class="space-y-1">
                            <x-ui.badge :tone="$topic['automation_tone']">
                                {{ $topic['priority_score_label'] }}
                            </x-ui.badge>
                            @if ($topic['score_breakdown_summary'])
                                <p class="text-xs text-[var(--color-muted)]">{{ $topic['score_breakdown_summary'] }}</p>
                            @endif
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell>
                        <x-ui.badge :tone="$topic['automation_tone']">{{ $topic['automation_state'] }}</x-ui.badge>
                    </x-ui.table-cell>
                    <x-ui.table-cell><x-admin.status-badge :status="$topic['status']" /></x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $topic['created_at'] ?: 'Unknown' }}</x-ui.table-cell>
                    <x-ui.table-cell align="right">
                        <x-admin.row-actions>
                            <x-admin.row-action as="a" :href="route('topic-queue.show', ['topic' => $topic['id']])">Details</x-admin.row-action>
                        </x-admin.row-actions>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-empty colspan="9" title="No topics match the current view" message="Adjust the filters or run discovery to generate new candidate topics." />
            @endforelse
        </x-ui.table-body>
    </x-ui.table>

    <x-ui.pagination :pagination="$pagination" item-label="topic" class="border-transparent bg-transparent px-0 py-0" />

    <x-admin.confirm-dialog
        :open="$discoveryDialogOpen"
        title="Run Topic Discovery"
        description="Create a service-side discovery job from a real category. The backend resolves cluster context from that category and handles scoring, pruning, and draft queueing after discovery completes."
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

                <x-ui.field label="Target Count" for="discovery-count" :error="$errors->first('discoveryCount')">
                    <x-ui.input id="discovery-count" wire:model.blur="discoveryCount" :invalid="$errors->has('discoveryCount')" />
                </x-ui.field>
            </div>

            <x-admin.callout title="Derived Cluster">
                The backend resolves cluster context from the selected category, so cluster is no longer the primary discovery input here.
            </x-admin.callout>

            <x-ui.field label="Audience" for="discovery-audience" :error="$errors->first('discoveryAudience')">
                <x-ui.input id="discovery-audience" wire:model.blur="discoveryAudience" :invalid="$errors->has('discoveryAudience')" placeholder="Founders, editors, technical marketers..." />
            </x-ui.field>

            <x-ui.field label="Metadata" for="discovery-metadata" :error="$errors->first('discoveryMetadata')" hint="Optional comma-separated metadata passed to the discovery job payload.">
                <x-ui.textarea id="discovery-metadata" rows="4" wire:model.blur="discoveryMetadata" :invalid="$errors->has('discoveryMetadata')" placeholder="newsletter, q3-campaign, editorial-focus" />
            </x-ui.field>
        </div>

        <x-slot:cancel>
            <x-ui.button variant="secondary" type="button" wire:click="closeDiscoveryDialog" wire:loading.attr="disabled" wire:target="closeDiscoveryDialog,runTopicDiscovery">
                Cancel
            </x-ui.button>
        </x-slot:cancel>

        <x-slot:confirm>
            <x-ui.button type="button" wire:click="runTopicDiscovery" wire:loading.attr="disabled" wire:target="runTopicDiscovery">
                Run Topic Discovery
            </x-ui.button>
        </x-slot:confirm>
    </x-admin.confirm-dialog>
</div>
