<div class="space-y-6">
    <x-admin.page-header
        title="Prompt Templates"
        description="Manage the service-side prompts that shape future topic discovery, brief generation, and drafting behavior."
    >
        <x-ui.button as="a" :href="route('ai-prompts.create')">Create Prompt Template</x-ui.button>
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
                <span class="sr-only">Search prompt templates</span>
                <x-ui.input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search prompts by name, key, type, or description"
                />
            </label>
        </x-slot:search>

        <x-slot:filters>
            <div class="flex items-center gap-3">
                <div class="w-[12rem] shrink-0">
                    <x-ui.select wire:model.live="typeFilter">
                        <option value="all">All types</option>
                        @foreach ($typeOptions as $typeOption)
                            <option value="{{ $typeOption }}">{{ str($typeOption)->headline() }}</option>
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

    <x-ui.table caption="AI prompt templates">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading sortable sort-key="name" :sort-state="$sort">NAME</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="key" :sort-state="$sort">KEY</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="type" :sort-state="$sort">TYPE</x-ui.table-heading>
                <x-ui.table-heading>STATUS</x-ui.table-heading>
                <x-ui.table-heading align="center">ACTIVE VERSION</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="updated_at" :sort-state="$sort">UPDATED</x-ui.table-heading>
                <x-ui.table-heading align="right">ACTIONS</x-ui.table-heading>
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
                    <x-ui.table-cell subdued>{{ $prompt['key'] }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ str($prompt['type'])->headline() }}</x-ui.table-cell>
                    <x-ui.table-cell>
                        <x-admin.status-badge :status="$prompt['status']" />
                    </x-ui.table-cell>
                    <x-ui.table-cell align="center">
                        <x-ui.badge :tone="$prompt['active_version_number'] ? 'success' : 'muted'">
                            {{ $prompt['active_version_number'] ? 'v'.$prompt['active_version_number'] : 'None' }}
                        </x-ui.badge>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $prompt['updated_at'] ?? 'Unknown' }}</x-ui.table-cell>
                    <x-ui.table-cell align="right">
                        <x-admin.row-actions>
                            <x-admin.row-action as="a" :href="route('ai-prompts.show', ['aiPrompt' => $prompt['id']])">Open</x-admin.row-action>
                        </x-admin.row-actions>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-empty
                    colspan="7"
                    title="No prompt templates match the current view"
                    message="Adjust the filters or create a new prompt template for future AI generations."
                />
            @endforelse
        </x-ui.table-body>
    </x-ui.table>

    <x-ui.pagination :pagination="$pagination" item-label="prompt template" />
</div>
