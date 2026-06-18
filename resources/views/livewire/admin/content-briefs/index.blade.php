<div class="space-y-6">
    <x-admin.page-header
        title="Content Briefs"
        description="Review AI-generated brief inventory, inspect source topics, and route approved briefs into draft generation without bypassing editorial review."
    />

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
                <span class="sr-only">Search briefs</span>
                <x-ui.input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search briefs by title or keyword"
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

                <div class="w-[11rem] shrink-0">
                    <x-ui.input wire:model.live.debounce.300ms="topicFilter" placeholder="Filter by topic ID" />
                </div>
            </div>
        </x-slot:filters>

        <x-slot:secondary>
            <div class="text-sm text-[var(--color-muted)]">
                {{ $pagination['total'] }} {{ str('brief')->plural($pagination['total']) }}
            </div>
        </x-slot:secondary>
    </x-admin.filter-bar>

    <x-ui.table caption="Content briefs">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading class="w-[24%]" sortable sort-key="title" :sort-state="$sort">TITLE</x-ui.table-heading>
                <x-ui.table-heading>TOPIC</x-ui.table-heading>
                <x-ui.table-heading>PRIMARY KEYWORD</x-ui.table-heading>
                <x-ui.table-heading>SEARCH INTENT</x-ui.table-heading>
                <x-ui.table-heading>STATUS</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="created_at" :sort-state="$sort">CREATED</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="approved_at" :sort-state="$sort">APPROVED</x-ui.table-heading>
                <x-ui.table-heading align="right">ACTIONS</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($briefs as $brief)
                <x-ui.table-row interactive wire:key="brief-{{ $brief['id'] }}">
                    <x-ui.table-cell class="w-[24%]">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-[var(--color-ink)]">{{ $brief['title'] }}</p>
                            <p class="mt-1 text-sm text-[var(--color-muted)]">{{ $brief['slug'] ?: 'Slug pending' }}</p>
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>
                        @if ($brief['topic']['id'])
                            <div class="space-y-1">
                                <p>{{ $brief['topic']['title'] ?: 'Topic #'.$brief['topic']['id'] }}</p>
                                @if ($brief['topic']['cluster'])
                                    <p class="text-xs text-[var(--color-muted)]">{{ str($brief['topic']['cluster'])->headline() }}</p>
                                @endif
                            </div>
                        @else
                            Unknown
                        @endif
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $brief['primary_keyword'] ?: 'TBC' }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $brief['search_intent'] ?: 'TBC' }}</x-ui.table-cell>
                    <x-ui.table-cell><x-admin.status-badge :status="$brief['status']" /></x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $brief['created_at'] ?: 'Unknown' }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $brief['approved_at'] ?: 'Not approved' }}</x-ui.table-cell>
                    <x-ui.table-cell align="right">
                        <x-admin.row-actions>
                            <x-admin.row-action as="a" :href="route('content-briefs.show', ['contentBrief' => $brief['id']])">Review</x-admin.row-action>
                        </x-admin.row-actions>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-empty
                    colspan="8"
                    title="No content briefs match the current view"
                    message="Adjust the filters or generate briefs from approved topics to build the editorial review queue."
                />
            @endforelse
        </x-ui.table-body>
    </x-ui.table>

    <x-ui.pagination :pagination="$pagination" item-label="brief" />
</div>
