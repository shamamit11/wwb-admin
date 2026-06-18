<div class="space-y-6">
    <x-admin.page-header
        title="AI Jobs"
        description="Monitor workflow execution, inspect failed jobs quickly, and route editors to the detailed lifecycle view when AI work needs attention."
    />

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        @foreach ($stats as $stat)
            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-4 shadow-[var(--shadow-card)]">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">{{ $stat['label'] }}</p>
                <p class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-[var(--color-ink)]">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    <x-admin.filter-bar>
        <x-slot:search>
            <div class="flex flex-wrap items-center gap-3">
                <div class="w-[12rem] shrink-0">
                    <x-ui.select wire:model.live="statusFilter">
                        <option value="all">All statuses</option>
                        @foreach ($statusOptions as $statusOption)
                            <option value="{{ $statusOption }}">{{ str($statusOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="w-[12rem] shrink-0">
                    <x-ui.select wire:model.live="typeFilter">
                        <option value="all">All types</option>
                        @foreach ($typeOptions as $typeOption)
                            <option value="{{ $typeOption }}">{{ str($typeOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>
        </x-slot:search>

        <x-slot:filters>
            <div class="flex flex-wrap items-center gap-3">
                <div class="w-[12rem] shrink-0">
                    <x-ui.input wire:model.live.debounce.300ms="providerFilter" placeholder="Filter by provider" />
                </div>

                <div class="w-[12rem] shrink-0">
                    <x-ui.input wire:model.live.debounce.300ms="modelFilter" placeholder="Filter by model" />
                </div>
            </div>
        </x-slot:filters>

        <x-slot:secondary>
            <div class="text-sm text-[var(--color-muted)]">
                {{ $pagination['total'] }} {{ str('job')->plural($pagination['total']) }}
            </div>
        </x-slot:secondary>
    </x-admin.filter-bar>

    <x-ui.table caption="AI jobs">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading>JOB ID</x-ui.table-heading>
                <x-ui.table-heading>TYPE</x-ui.table-heading>
                <x-ui.table-heading>STATUS</x-ui.table-heading>
                <x-ui.table-heading>ENTITY</x-ui.table-heading>
                <x-ui.table-heading>PROVIDER</x-ui.table-heading>
                <x-ui.table-heading>MODEL</x-ui.table-heading>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('{{ $sort === 'started_at' ? '-started_at' : 'started_at' }}')" class="inline-flex items-center gap-2 uppercase tracking-[0.18em] transition-colors hover:text-[var(--color-ink)]">
                        <span>STARTED</span>
                        <span class="text-[10px] leading-none">{{ str($sort)->contains('started_at') ? (str($sort)->startsWith('-') ? '↓' : '↑') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('{{ $sort === 'completed_at' ? '-completed_at' : 'completed_at' }}')" class="inline-flex items-center gap-2 uppercase tracking-[0.18em] transition-colors hover:text-[var(--color-ink)]">
                        <span>COMPLETED</span>
                        <span class="text-[10px] leading-none">{{ str($sort)->contains('completed_at') ? (str($sort)->startsWith('-') ? '↓' : '↑') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('{{ $sort === 'failed_at' ? '-failed_at' : 'failed_at' }}')" class="inline-flex items-center gap-2 uppercase tracking-[0.18em] transition-colors hover:text-[var(--color-ink)]">
                        <span>FAILED</span>
                        <span class="text-[10px] leading-none">{{ str($sort)->contains('failed_at') ? (str($sort)->startsWith('-') ? '↓' : '↑') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading align="right">ACTIONS</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($jobs as $job)
                <x-ui.table-row interactive wire:key="job-{{ $job['id'] }}">
                    <x-ui.table-cell class="font-semibold text-[var(--color-ink)]">#{{ $job['id'] }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ str($job['type'])->headline() }}</x-ui.table-cell>
                    <x-ui.table-cell><x-admin.status-badge :status="$job['status']" /></x-ui.table-cell>
                    <x-ui.table-cell subdued>
                        @if ($job['entity_type'] && $job['entity_id'])
                            {{ class_basename((string) $job['entity_type']) }} #{{ $job['entity_id'] }}
                        @else
                            None
                        @endif
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $job['provider'] ?: 'TBC' }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $job['model'] ?: 'TBC' }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $job['started_at'] ?: 'Not started' }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $job['completed_at'] ?: 'Not completed' }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $job['failed_at'] ?: 'Not failed' }}</x-ui.table-cell>
                    <x-ui.table-cell align="right">
                        <x-admin.row-actions>
                            <x-admin.row-action as="a" :href="route('ai-jobs.show', ['aiJob' => $job['id']])">Inspect</x-admin.row-action>
                        </x-admin.row-actions>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-empty
                    colspan="10"
                    title="No AI jobs match the current view"
                    message="Adjust the filters to review a broader execution history."
                />
            @endforelse
        </x-ui.table-body>
    </x-ui.table>

    @if ($pagination['has_pages'])
        <div class="flex flex-col gap-3 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm text-[var(--color-muted)]">
                Showing {{ $pagination['first_item'] }}-{{ $pagination['last_item'] }} of {{ $pagination['total'] }} results
            </div>

            <div class="flex items-center gap-2">
                <x-ui.button type="button" variant="secondary" size="sm" wire:click="previousPage" :disabled="$pagination['page'] <= 1">Previous</x-ui.button>
                <span class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-3 py-2 text-sm text-[var(--color-muted)]">
                    Page {{ $pagination['page'] }} of {{ $pagination['last_page'] }}
                </span>
                <x-ui.button type="button" variant="secondary" size="sm" wire:click="nextPage" :disabled="$pagination['page'] >= $pagination['last_page']">Next</x-ui.button>
            </div>
        </div>
    @endif
</div>
