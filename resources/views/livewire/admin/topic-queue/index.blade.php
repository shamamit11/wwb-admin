<div class="space-y-6">
    <x-admin.page-header
        title="Topic Queue"
        description="Review service-generated topic suggestions, filter editorial opportunities, and move approved topics toward the briefing workflow."
    >
        <x-ui.button type="button" wire:click="openDiscoveryDialog" wire:loading.attr="disabled" wire:target="openDiscoveryDialog">
            <span wire:loading.remove wire:target="openDiscoveryDialog">Run Topic Discovery</span>
            <span wire:loading wire:target="openDiscoveryDialog">Opening…</span>
        </x-ui.button>
    </x-admin.page-header>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
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
                <span class="sr-only">Search topics</span>
                <x-ui.input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search topics by title or primary keyword"
                />
            </label>
        </x-slot:search>

        <x-slot:filters>
            <div class="flex flex-wrap items-center gap-3">
                <div class="w-[11rem] shrink-0">
                    <x-ui.select wire:model.live="statusFilter">
                        <option value="all">All statuses</option>
                        @foreach ($statusOptions as $statusOption)
                            <option value="{{ $statusOption }}">{{ str($statusOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="w-[13rem] shrink-0">
                    <x-ui.select wire:model.live="clusterFilter">
                        <option value="all">All clusters</option>
                        @foreach ($clusterOptions as $clusterOption)
                            <option value="{{ $clusterOption }}">{{ str($clusterOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="w-[11rem] shrink-0">
                    <x-ui.select wire:model.live="sourceFilter">
                        <option value="all">All sources</option>
                        @foreach ($sourceOptions as $sourceOption)
                            <option value="{{ $sourceOption }}">{{ $sourceOption === 'ai_suggested' ? 'AI Suggested' : 'Manual' }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>
        </x-slot:filters>

        <x-slot:results>{{ $pagination['total'] }} {{ str('topic')->plural($pagination['total']) }}</x-slot:results>
    </x-admin.filter-bar>

    <x-ui.table caption="Topic queue" density="compact">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading width="workflow-primary" sortable sort-key="title" :sort-state="$sort">TITLE</x-ui.table-heading>
                <x-ui.table-heading>CLUSTER</x-ui.table-heading>
                <x-ui.table-heading>PRIMARY KEYWORD</x-ui.table-heading>
                <x-ui.table-heading>SEARCH INTENT</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="priority_score" :sort-state="$sort">PRIORITY</x-ui.table-heading>
                <x-ui.table-heading>STATUS</x-ui.table-heading>
                <x-ui.table-heading>SOURCE</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="created_at" :sort-state="$sort">CREATED</x-ui.table-heading>
                <x-ui.table-heading align="right">ACTIONS</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($topics as $topic)
                <x-ui.table-row interactive wire:key="topic-{{ $topic['id'] }}">
                    <x-ui.table-cell width="workflow-primary">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-[var(--color-ink)]">{{ $topic['title'] }}</p>
                            <p class="mt-1 text-sm text-[var(--color-muted)]">{{ $topic['slug'] ?: 'Slug pending' }}</p>
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ str($topic['cluster'])->headline() }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $topic['primary_keyword'] ?: 'Not set' }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $topic['search_intent'] ?: 'Not set' }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $topic['priority_score_label'] }}</x-ui.table-cell>
                    <x-ui.table-cell><x-admin.status-badge :status="$topic['status']" /></x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $topic['source'] === 'ai_suggested' ? 'AI Suggested' : 'Manual' }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $topic['created_at'] ?: 'Unknown' }}</x-ui.table-cell>
                    <x-ui.table-cell align="right">
                        <x-admin.row-actions>
                            <x-admin.row-action as="a" :href="route('topic-queue.show', ['topic' => $topic['id']])">Review</x-admin.row-action>
                        </x-admin.row-actions>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-empty
                    colspan="9"
                    title="No topics match the current view"
                    message="Adjust the filters or search terms to broaden the editorial queue."
                />
            @endforelse
        </x-ui.table-body>
    </x-ui.table>

    <x-ui.pagination :pagination="$pagination" item-label="topic" />

    <x-admin.confirm-dialog
        :open="$discoveryDialogOpen"
        title="Run Topic Discovery"
        description="Create a service-side AI job that generates suggested topics only. Review and approval still stay in the Admin workflow."
    >
        <div class="space-y-4">
            @if ($discoveryError)
                <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                    {{ $discoveryError }}
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Cluster" for="discovery-cluster" :error="$errors->first('discoveryCluster')">
                    <x-ui.select id="discovery-cluster" wire:model.live="discoveryCluster">
                        @foreach ($clusterOptions as $clusterOption)
                            <option value="{{ $clusterOption }}">{{ str($clusterOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Target Count" for="discovery-count" :error="$errors->first('discoveryCount')">
                    <x-ui.input id="discovery-count" wire:model.blur="discoveryCount" :invalid="$errors->has('discoveryCount')" />
                </x-ui.field>
            </div>

            <x-ui.field label="Audience" for="discovery-audience" :error="$errors->first('discoveryAudience')">
                <x-ui.input id="discovery-audience" wire:model.blur="discoveryAudience" :invalid="$errors->has('discoveryAudience')" placeholder="Founders, editors, technical marketers..." />
            </x-ui.field>

            <x-ui.field label="Prompt Template Key" for="discovery-template-key" :error="$errors->first('discoveryPromptTemplateKey')" hint="Optional override when the Service should use a non-default prompt template.">
                <x-ui.input id="discovery-template-key" wire:model.blur="discoveryPromptTemplateKey" :invalid="$errors->has('discoveryPromptTemplateKey')" placeholder="topic-discovery-editorial" />
            </x-ui.field>

            <x-ui.field label="Metadata" for="discovery-metadata" :error="$errors->first('discoveryMetadata')" hint="Optional comma-separated tags passed to the Service job payload.">
                <x-ui.textarea id="discovery-metadata" rows="4" wire:model.blur="discoveryMetadata" :invalid="$errors->has('discoveryMetadata')" placeholder="newsletter, q3-campaign, editorial-focus" />
            </x-ui.field>

            <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3 text-sm text-[var(--color-muted)]">
                Topic discovery creates suggested topics only. Approval and downstream workflow decisions still require human review in Admin.
            </div>
        </div>

        <x-slot:cancel>
            <x-ui.button variant="secondary" type="button" wire:click="closeDiscoveryDialog" wire:loading.attr="disabled" wire:target="closeDiscoveryDialog,runTopicDiscovery">
                Cancel
            </x-ui.button>
        </x-slot:cancel>

        <x-slot:confirm>
            <x-ui.button type="button" wire:click="runTopicDiscovery" wire:loading.attr="disabled" wire:target="runTopicDiscovery">
                <span wire:loading.remove wire:target="runTopicDiscovery">Run Topic Discovery</span>
                <span wire:loading wire:target="runTopicDiscovery">Creating…</span>
            </x-ui.button>
        </x-slot:confirm>
    </x-admin.confirm-dialog>
</div>
