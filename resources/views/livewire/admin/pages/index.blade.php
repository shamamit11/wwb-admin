<div class="space-y-6">
    <x-admin.page-header
        title="Pages"
        description="Manage static and evergreen page content such as privacy policy, FAQ, support content, and marketing pages through the service-backed pages resource."
    >
        <x-ui.button as="a" :href="route('pages.create')">Create Page</x-ui.button>
    </x-admin.page-header>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    <x-admin.filter-bar>
        <x-slot:search>
            <label class="block">
                <span class="sr-only">Search pages</span>
                <x-ui.input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by title, summary, or slug"
                />
            </label>
        </x-slot:search>

        <x-slot:filters>
            <div class="flex flex-wrap items-center gap-3">
                <div class="w-[11rem] shrink-0">
                    <x-ui.select wire:model.live="typeFilter">
                        <option value="all">All types</option>
                        @foreach ($pageTypes as $type)
                            <option value="{{ $type }}">{{ str($type)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="w-[11rem] shrink-0">
                    <x-ui.select wire:model.live="statusFilter">
                        <option value="all">All statuses</option>
                        @foreach ($pageStatuses as $statusOption)
                            <option value="{{ $statusOption }}">{{ str($statusOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="w-[11rem] shrink-0">
                    <x-ui.select wire:model.live="visibilityFilter">
                        <option value="all">All visibility</option>
                        @foreach ($pageVisibilities as $visibilityOption)
                            <option value="{{ $visibilityOption }}">{{ str($visibilityOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>
        </x-slot:filters>

        <x-slot:results>{{ count($pages) }} {{ str('page')->plural(count($pages)) }}</x-slot:results>
    </x-admin.filter-bar>

    <x-ui.table caption="Pages" density="compact">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading width="content-primary" sortable sort-key="title" :sort-column="$sortColumn" :sort-direction="$sortDirection">TITLE</x-ui.table-heading>
                <x-ui.table-heading>TYPE</x-ui.table-heading>
                <x-ui.table-heading>STATUS</x-ui.table-heading>
                <x-ui.table-heading>VISIBILITY</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="published_at" :sort-column="$sortColumn" :sort-direction="$sortDirection">PUBLISHED</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="updated_at" :sort-column="$sortColumn" :sort-direction="$sortDirection">UPDATED</x-ui.table-heading>
                <x-ui.table-heading align="right">ACTIONS</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($pages as $page)
                <x-ui.table-row interactive wire:key="page-{{ $page['id'] }}">
                    <x-ui.table-cell width="content-primary">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-[var(--color-ink)]">{{ $page['title'] }}</p>
                            <p class="mt-1 text-sm text-[var(--color-muted)]">{{ $page['slug'] !== '' ? '/'.$page['slug'] : 'Auto-generated slug' }}</p>
                            @if ($page['summary'])
                                <p class="mt-2 text-sm text-[var(--color-muted)]">{{ $page['summary'] }}</p>
                            @endif
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ str($page['type'])->headline() }}</x-ui.table-cell>
                    <x-ui.table-cell>
                        <x-admin.status-badge :status="$page['status']" />
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ str($page['visibility'])->headline() }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $page['published_at'] ?: 'Not published' }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $page['updated_at'] ?: 'Unknown' }}</x-ui.table-cell>
                    <x-ui.table-cell align="right">
                        <x-admin.row-actions>
                            <x-admin.row-action as="a" :href="route('pages.edit', ['page' => $page['id']])">Edit</x-admin.row-action>
                            <x-admin.row-action type="button" tone="danger" wire:click="confirmDelete({{ $page['id'] }})">Delete</x-admin.row-action>
                        </x-admin.row-actions>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-empty
                    colspan="7"
                    title="No pages match the current view"
                    message="Adjust the filters, or create a service-backed static page to start managing legal, support, or marketing content."
                />
            @endforelse
        </x-ui.table-body>
    </x-ui.table>

    <x-ui.dialog
        :open="$deleteDialogOpen"
        title="Delete page"
        description="Delete the page only when the public content should no longer exist."
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
                Delete <span class="font-semibold text-[var(--color-ink)]">{{ $deletePageTitle }}</span>? This removes the page from the current publishing workflow.
            </p>
        </div>

        <x-slot:actions>
            <x-ui.button type="button" variant="secondary" wire:click="closeDeleteDialog">Cancel</x-ui.button>
            <x-ui.button type="button" variant="destructive" wire:click="delete" wire:loading.attr="disabled" wire:target="delete">
                <span wire:loading.remove wire:target="delete">Delete page</span>
                <span wire:loading wire:target="delete">Deleting…</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.dialog>
</div>
