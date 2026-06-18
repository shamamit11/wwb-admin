<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <x-admin.page-header
            title="Prompt Templates"
            description="Manage the service-side prompts that shape future topic discovery, brief generation, and drafting behavior."
        />

        <div class="shrink-0 lg:pt-1">
            <x-ui.button as="a" :href="route('ai-prompts.create')">Create Prompt Template</x-ui.button>
        </div>
    </div>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        @foreach ($stats as $stat)
            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-5 shadow-[var(--shadow-card)]">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">{{ $stat['label'] }}</p>
                <p class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-[var(--color-ink)]">{{ $stat['value'] }}</p>
            </div>
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

        <x-slot:secondary>
            <div class="text-sm text-[var(--color-muted)]">
                {{ $pagination['total'] }} {{ str('prompt')->plural($pagination['total']) }}
            </div>
        </x-slot:secondary>
    </x-admin.filter-bar>

    <x-ui.table caption="AI prompt templates">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('{{ $sort === 'name' ? '-name' : 'name' }}')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Name</span>
                        <span class="text-[10px] leading-none">{{ str($sort)->after('-') === 'name' ? ($sort === 'name' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('{{ $sort === 'key' ? '-key' : 'key' }}')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Key</span>
                        <span class="text-[10px] leading-none">{{ str($sort)->after('-') === 'key' ? ($sort === 'key' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('{{ $sort === 'type' ? '-type' : 'type' }}')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Type</span>
                        <span class="text-[10px] leading-none">{{ str($sort)->after('-') === 'type' ? ($sort === 'type' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading>Status</x-ui.table-heading>
                <x-ui.table-heading align="center">Active Version</x-ui.table-heading>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('{{ $sort === 'updated_at' ? '-updated_at' : 'updated_at' }}')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Updated</span>
                        <span class="text-[10px] leading-none">{{ str($sort)->after('-') === 'updated_at' ? ($sort === 'updated_at' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
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
                        <x-ui.button as="a" :href="route('ai-prompts.show', ['aiPrompt' => $prompt['id']])" variant="secondary">
                            Open
                        </x-ui.button>
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

    @if ($pagination['has_pages'])
        <div class="flex flex-col gap-3 border-t border-[var(--color-line)] pt-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-[var(--color-muted)]">
                Showing {{ $pagination['first_item'] }}-{{ $pagination['last_item'] }} of {{ $pagination['total'] }} prompt templates
            </p>

            <div class="flex items-center gap-2">
                <x-ui.button type="button" variant="secondary" wire:click="previousPage" :disabled="$pagination['page'] === 1">Previous</x-ui.button>
                <span class="min-w-20 text-center text-sm text-[var(--color-muted)]">Page {{ $pagination['page'] }} of {{ $pagination['last_page'] }}</span>
                <x-ui.button type="button" variant="secondary" wire:click="nextPage" :disabled="$pagination['page'] === $pagination['last_page']">Next</x-ui.button>
            </div>
        </div>
    @endif
</div>
