<div class="space-y-6">
    <x-admin.page-header
        title="Knowledge Base"
        description="Manage editorial reference entries, practical markdown notes, and structured context for future workflow tooling."
    >
        <x-ui.button as="a" :href="route('knowledge-base.create')">Create Knowledge Entry</x-ui.button>
    </x-admin.page-header>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    <x-admin.filter-bar>
        <x-slot:search>
            <label class="block">
                <span class="sr-only">Search knowledge entries</span>
                <x-ui.input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by title, summary, or markdown context"
                />
            </label>
        </x-slot:search>

        <x-slot:filters>
            <div class="flex flex-wrap items-center gap-3">
                <div class="w-[11rem] shrink-0">
                    <x-ui.select wire:model.live="typeFilter">
                        <option value="all">All types</option>
                        @foreach ($entryTypes as $type)
                            <option value="{{ $type }}">{{ str($type)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="w-[11rem] shrink-0">
                    <x-ui.select wire:model.live="statusFilter">
                        <option value="all">All statuses</option>
                        @foreach ($entryStatuses as $statusOption)
                            <option value="{{ $statusOption }}">{{ str($statusOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>
        </x-slot:filters>

        <x-slot:results>{{ count($entries) }} {{ str('entry')->plural(count($entries)) }}</x-slot:results>
    </x-admin.filter-bar>

    <x-ui.table caption="Knowledge base entries">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading class="w-[35%]" sortable sort-key="title" :sort-column="$sortColumn" :sort-direction="$sortDirection">TITLE</x-ui.table-heading>
                <x-ui.table-heading>TYPE</x-ui.table-heading>
                <x-ui.table-heading>STATUS</x-ui.table-heading>
                <x-ui.table-heading>SOURCE</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="updated_at" :sort-column="$sortColumn" :sort-direction="$sortDirection">UPDATED</x-ui.table-heading>
                <x-ui.table-heading align="right">ACTIONS</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($entries as $entry)
                <x-ui.table-row interactive wire:key="knowledge-entry-{{ $entry['id'] }}">
                    <x-ui.table-cell class="w-[35%]">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-[var(--color-ink)]">{{ $entry['title'] }}</p>
                            <p class="mt-1 text-sm text-[var(--color-muted)]">{{ $entry['slug'] ?: 'Auto-generated slug' }}</p>
                            @if ($entry['summary'])
                                <p class="mt-2 text-sm text-[var(--color-muted)]">{{ $entry['summary'] }}</p>
                            @endif
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ str($entry['entry_type'])->headline() }}</x-ui.table-cell>
                    <x-ui.table-cell>
                        <x-admin.status-badge :status="$entry['status']" />
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>
                        @if ($entry['source_url'])
                            <a href="{{ $entry['source_url'] }}" target="_blank" rel="noreferrer" class="text-[var(--color-accent-strong)] underline decoration-[color-mix(in_srgb,var(--color-accent)_38%,white)] underline-offset-4">
                                Source link
                            </a>
                        @else
                            No source URL
                        @endif
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $entry['updated_at'] ?: 'Unknown' }}</x-ui.table-cell>
                    <x-ui.table-cell align="right">
                        <x-admin.row-actions>
                            <x-admin.row-action as="a" :href="route('knowledge-base.edit', ['knowledgeBaseEntry' => $entry['id']])">Edit</x-admin.row-action>
                            <x-admin.row-action type="button" tone="danger" wire:click="confirmDelete({{ $entry['id'] }})">Delete</x-admin.row-action>
                        </x-admin.row-actions>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-empty
                    colspan="6"
                    title="No knowledge entries match the current view"
                    message="Adjust the search or filters, or create a reference entry to start building editorial context."
                />
            @endforelse
        </x-ui.table-body>
    </x-ui.table>

    <x-ui.dialog
        :open="$deleteDialogOpen"
        title="Delete knowledge entry"
        description="Delete the entry only when the editorial context is no longer needed."
        tone="destructive"
        maxWidth="lg"
    >
        <div class="space-y-5">
            @if ($deleteError)
                <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                    {{ $deleteError }}
                </div>
            @endif

            <p class="text-sm leading-6 text-[var(--color-muted)]">
                Delete <span class="font-semibold text-[var(--color-ink)]">{{ $deleteEntryTitle }}</span>? This removes the entry from the current editorial workflow.
            </p>
        </div>

        <x-slot:actions>
            <x-ui.button type="button" variant="secondary" wire:click="closeDeleteDialog">Cancel</x-ui.button>
            <x-ui.button type="button" variant="destructive" wire:click="delete" wire:loading.attr="disabled" wire:target="delete">
                <span wire:loading.remove wire:target="delete">Delete entry</span>
                <span wire:loading wire:target="delete">Deleting…</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.dialog>
</div>
