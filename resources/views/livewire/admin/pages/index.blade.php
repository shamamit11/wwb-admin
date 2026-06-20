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

    <section class="space-y-4 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Legal Pages</h2>
                <p class="text-sm text-[var(--color-muted)]">Use the existing Pages API flow for Privacy Policy and Terms and Conditions. Filter the list to legal pages or jump straight into one of the recommended public legal entries.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button
                    type="button"
                    variant="{{ $typeFilter === 'legal' ? 'default' : 'secondary' }}"
                    wire:click="$set('typeFilter', 'legal')"
                >
                    Show Legal Only
                </x-ui.button>
                @if ($typeFilter === 'legal')
                    <x-ui.button type="button" variant="secondary" wire:click="$set('typeFilter', 'all')">Show All Pages</x-ui.button>
                @endif
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($legalPageCards as $card)
                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-5 py-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-semibold text-[var(--color-ink)]">{{ $card['title'] }}</h3>
                                @if (is_array($card['page']))
                                    <x-admin.status-badge :status="$card['page']['status']" />
                                @else
                                    <span class="inline-flex items-center rounded-full bg-[var(--color-panel)] px-2.5 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-[var(--color-muted)] ring-1 ring-[var(--color-line)]">Missing</span>
                                @endif
                            </div>
                            <p class="text-sm text-[var(--color-muted)]">/{{ $card['slug'] }}</p>
                            <p class="text-sm text-[var(--color-muted)]">{{ $card['description'] }}</p>
                            @if (is_array($card['page']) && $card['page']['summary'])
                                <p class="text-sm text-[var(--color-muted)]">{{ $card['page']['summary'] }}</p>
                            @endif
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            @if (is_array($card['page']))
                                <x-ui.button as="a" :href="route('pages.edit', ['page' => $card['page']['id']])" size="sm">Edit</x-ui.button>
                            @else
                                <x-ui.button as="a" :href="route('pages.create', ['preset' => $card['key']])" size="sm">Create</x-ui.button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

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

    <x-admin.confirm-dialog
        :open="$deleteDialogOpen"
        title="Delete page"
        description="Delete the page only when the public content should no longer exist."
        destructive
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

        <x-slot:cancel>
            <x-ui.button type="button" variant="secondary" wire:click="closeDeleteDialog">Cancel</x-ui.button>
        </x-slot:cancel>

        <x-slot:confirm>
            <x-ui.button type="button" variant="destructive" wire:click="delete" wire:loading.attr="disabled" wire:target="delete">
                <span wire:loading.remove wire:target="delete">Delete page</span>
                <span wire:loading wire:target="delete">Deleting…</span>
            </x-ui.button>
        </x-slot:confirm>
    </x-admin.confirm-dialog>
</div>
