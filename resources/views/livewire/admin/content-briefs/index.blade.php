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
                    class="w-full"
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

                <div class="w-[9rem] shrink-0">
                    <x-ui.input wire:model.live.debounce.300ms="topicFilter" inputmode="numeric" placeholder="Topic ID" />
                </div>
            </div>
        </x-slot:filters>

        <x-slot:results>{{ $pagination['total'] }} {{ str('brief')->plural($pagination['total']) }}</x-slot:results>
    </x-admin.filter-bar>

    <x-ui.table caption="Content briefs" density="compact">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading width="content-primary" sortable sort-key="title" :sort-state="$sort">BRIEF</x-ui.table-heading>
                <x-ui.table-heading class="w-[16%]">PRIMARY KEYWORD</x-ui.table-heading>
                <x-ui.table-heading class="w-[10%]">INTENT</x-ui.table-heading>
                <x-ui.table-heading class="w-[10%]">STATUS</x-ui.table-heading>
                <x-ui.table-heading class="w-[12%]" sortable sort-key="created_at" :sort-state="$sort">CREATED</x-ui.table-heading>
                <x-ui.table-heading class="w-[12%]" sortable sort-key="approved_at" :sort-state="$sort">APPROVED</x-ui.table-heading>
                <x-ui.table-heading class="w-[1%] whitespace-nowrap" align="right">ACTIONS</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($briefs as $brief)
                <x-ui.table-row interactive wire:key="brief-{{ $brief['id'] }}">
                    <x-ui.table-cell width="content-primary">
                        <div class="min-w-0 space-y-2">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-[var(--color-ink)]" title="{{ $brief['title'] }}">{{ $brief['title'] }}</p>
                                <p class="mt-1 truncate text-xs text-[var(--color-muted)]" title="{{ $brief['slug'] ?: 'Slug pending' }}">{{ $brief['slug'] ?: 'Slug pending' }}</p>
                            </div>

                            <div class="min-w-0 rounded-[var(--radius-button)] bg-[var(--color-panel-soft)] px-3 py-2 text-sm text-[var(--color-muted)]">
                                <p class="truncate" title="{{ $brief['topic']['title'] ?: ($brief['topic']['id'] ? 'Topic #'.$brief['topic']['id'] : 'Unknown') }}">
                                    <span class="font-medium text-[var(--color-ink)]">Topic:</span>
                                    {{ $brief['topic']['title'] ?: ($brief['topic']['id'] ? 'Topic #'.$brief['topic']['id'] : 'Unknown') }}
                                </p>

                                @if ($brief['topic']['cluster'])
                                    <p class="mt-1 truncate text-xs uppercase tracking-[0.14em] text-[var(--color-muted)]">
                                        {{ str($brief['topic']['cluster'])->headline() }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued class="align-top">
                        <p class="truncate text-sm font-medium text-[var(--color-ink)]" title="{{ $brief['primary_keyword'] ?: 'Not set' }}">
                            {{ $brief['primary_keyword'] ?: 'Not set' }}
                        </p>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued class="align-top">
                        <span class="inline-flex rounded-full bg-[var(--color-panel-soft)] px-2.5 py-1 text-xs font-medium capitalize text-[var(--color-muted)]">
                            {{ $brief['search_intent'] ?: 'Not set' }}
                        </span>
                    </x-ui.table-cell>
                    <x-ui.table-cell class="align-top"><x-admin.status-badge :status="$brief['status']" /></x-ui.table-cell>
                    <x-ui.table-cell subdued class="align-top whitespace-nowrap">{{ $brief['created_at'] ?: 'Unknown' }}</x-ui.table-cell>
                    <x-ui.table-cell subdued class="align-top whitespace-nowrap">{{ $brief['approved_at'] ?: 'Not approved' }}</x-ui.table-cell>
                    <x-ui.table-cell align="right" class="align-top whitespace-nowrap">
                        <x-admin.row-actions>
                            <x-admin.row-action as="a" :href="route('content-briefs.show', ['contentBrief' => $brief['id']])">Review</x-admin.row-action>
                        </x-admin.row-actions>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-empty
                    colspan="7"
                    title="No content briefs match the current view"
                    message="Adjust the filters or generate briefs from approved topics to build the editorial review queue."
                />
            @endforelse
        </x-ui.table-body>
    </x-ui.table>

    <x-ui.pagination :pagination="$pagination" item-label="brief" />
</div>
