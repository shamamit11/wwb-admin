<div class="space-y-6">
    <x-admin.page-header
        title="Standard Prompts"
        description="Manage the two versioned prompt families the backend now treats as the source of truth for topic and blog generation."
    >
        <x-ui.button as="a" :href="route('ai-prompts.create')">Create Standard Prompt</x-ui.button>
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

    <x-admin.callout title="Prompt Source Of Truth">
        Prompt instructions no longer live in site settings. Only the <span class="font-medium text-[var(--color-ink)]">topic_standard</span> and <span class="font-medium text-[var(--color-ink)]">blog_standard</span> families should be managed here.
    </x-admin.callout>

    <x-admin.filter-bar>
        <x-slot:search>
            <label class="block">
                <span class="sr-only">Search prompts</span>
                <x-ui.input type="search" wire:model.live.debounce.300ms="search" placeholder="Search prompts by name, key, or description" />
            </label>
        </x-slot:search>

        <x-slot:filters>
            <div class="flex flex-wrap items-center gap-3">
                <div class="w-[12rem] shrink-0">
                    <x-ui.select wire:model.live="keyFilter">
                        <option value="all">All families</option>
                        @foreach ($keyOptions as $keyOption)
                            <option value="{{ $keyOption }}">{{ str($keyOption)->replace('_', ' ')->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="w-[11rem] shrink-0">
                    <x-ui.select wire:model.live="statusFilter">
                        <option value="all">All statuses</option>
                        @foreach ($statusOptions as $statusOption)
                            <option value="{{ $statusOption }}">{{ str($statusOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>
        </x-slot:filters>

        <x-slot:results>{{ $pagination['total'] }} {{ str('prompt')->plural($pagination['total']) }}</x-slot:results>
    </x-admin.filter-bar>

    <x-ui.table caption="Standard prompts" density="compact">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading sortable sort-key="name" :sort-state="$sort">Name</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="key" :sort-state="$sort">Family</x-ui.table-heading>
                <x-ui.table-heading>Status</x-ui.table-heading>
                <x-ui.table-heading align="center">Active Version</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="updated_at" :sort-state="$sort">Updated</x-ui.table-heading>
                <x-ui.table-heading align="right">Actions</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($prompts as $prompt)
                <x-ui.table-row interactive wire:key="ai-prompt-{{ $prompt['id'] }}">
                    <x-ui.table-cell>
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-[var(--color-ink)]">{{ $prompt['name'] }}</p>
                            @if ($prompt['description'])
                                <p class="mt-1 text-sm text-[var(--color-muted)]">{{ $prompt['description'] }}</p>
                            @endif
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell>
                        <div class="space-y-1">
                            <p class="font-medium text-[var(--color-ink)]">{{ $prompt['family_label'] }}</p>
                            <p class="text-sm text-[var(--color-muted)]">{{ $prompt['key'] }}</p>
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell><x-admin.status-badge :status="$prompt['status']" /></x-ui.table-cell>
                    <x-ui.table-cell align="center">
                        <x-ui.badge :tone="$prompt['active_version_number'] ? 'success' : 'muted'">
                            {{ $prompt['active_version_number'] ? 'v'.$prompt['active_version_number'] : 'None' }}
                        </x-ui.badge>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $prompt['updated_at'] ?? 'Unknown' }}</x-ui.table-cell>
                    <x-ui.table-cell align="right">
                        <x-admin.row-actions>
                            <x-admin.row-action as="a" :href="route('ai-prompts.show', ['aiPrompt' => $prompt['id']])">Edit</x-admin.row-action>
                        </x-admin.row-actions>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-empty colspan="6" title="No standard prompts match the current view" message="Create the missing prompt family or adjust the filters." />
            @endforelse
        </x-ui.table-body>
    </x-ui.table>

    <x-ui.pagination :pagination="$pagination" item-label="prompt" />
</div>
