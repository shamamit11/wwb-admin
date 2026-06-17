<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <x-admin.page-header
            title="Tags"
            description="Manage reusable editorial labels and activation state for post tagging workflows."
        />

        <div class="shrink-0 lg:pt-1">
            <x-ui.button type="button" wire:click="openCreateDrawer">Create Tag</x-ui.button>
        </div>
    </div>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    <x-admin.filter-bar>
        <x-slot:search>
            <label class="block">
                <span class="sr-only">Search tags</span>
                <x-ui.input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search tags by name, slug, or description"
                />
            </label>
        </x-slot:search>

        <x-slot:filters>
            <x-ui.select wire:model.live="statusFilter">
                <option value="all">All statuses</option>
                <option value="active">Active only</option>
                <option value="inactive">Inactive only</option>
            </x-ui.select>
        </x-slot:filters>

        <x-slot:secondary>
            <div class="text-sm text-[var(--color-muted)]">
                {{ count($tags) }} {{ str('tag')->plural(count($tags)) }}
            </div>
        </x-slot:secondary>
    </x-admin.filter-bar>

    <x-ui.table caption="Tags">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading class="w-[44%]">
                    <button type="button" wire:click="sortBy('name')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Name</span>
                        <span class="text-[10px] leading-none">{{ $sortColumn === 'name' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('slug')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Slug</span>
                        <span class="text-[10px] leading-none">{{ $sortColumn === 'slug' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('is_active')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Status</span>
                        <span class="text-[10px] leading-none">{{ $sortColumn === 'is_active' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading align="right">Actions</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($tags as $tag)
                <x-ui.table-row interactive wire:key="tag-{{ $tag['id'] }}">
                    <x-ui.table-cell class="w-[44%]">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-[var(--color-ink)]">{{ $tag['name'] }}</p>
                            @if ($tag['description'])
                                <p class="mt-1 text-sm text-[var(--color-muted)]">{{ $tag['description'] }}</p>
                            @endif
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $tag['slug'] ?: 'Auto-generated' }}</x-ui.table-cell>
                    <x-ui.table-cell>
                        <x-admin.status-badge :status="$tag['is_active'] ? 'active' : 'inactive'" />
                    </x-ui.table-cell>
                    <x-ui.table-cell align="right">
                        <div class="flex items-center justify-end gap-2">
                            <x-ui.button
                                type="button"
                                variant="ghost"
                                class="h-12 w-12 px-0 bg-[color-mix(in_srgb,var(--color-warning)_12%,white)] text-[var(--color-warning-strong)] ring-1 ring-[color-mix(in_srgb,var(--color-warning)_18%,white)] hover:bg-[color-mix(in_srgb,var(--color-warning)_18%,white)] hover:text-[var(--color-warning-strong)]"
                                wire:click="openEditDrawer({{ $tag['id'] }})"
                                aria-label="Edit tag {{ $tag['name'] }}"
                                title="Edit"
                            >
                                <svg class="h-6 w-6" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="m13.75 4.75 1.5 1.5M5 15l2.75-.5L15.5 6.75a1.06 1.06 0 0 0 0-1.5l-.75-.75a1.06 1.06 0 0 0-1.5 0L5.5 12.25 5 15Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </x-ui.button>
                            <x-ui.button
                                type="button"
                                variant="ghost"
                                class="h-12 w-12 px-0 bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] text-[var(--color-danger-strong)] ring-1 ring-[color-mix(in_srgb,var(--color-danger)_18%,white)] hover:bg-[color-mix(in_srgb,var(--color-danger)_16%,white)] hover:text-[var(--color-danger-strong)]"
                                wire:click="confirmDelete({{ $tag['id'] }})"
                                aria-label="Delete tag {{ $tag['name'] }}"
                                title="Delete"
                            >
                                <svg class="h-6 w-6" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M4.75 6.25h10.5M8 8.75v5.5M12 8.75v5.5M6.5 6.25l.5-1.5A1 1 0 0 1 7.95 4h4.1a1 1 0 0 1 .95.75l.5 1.5M6.25 6.25l.4 8.15A1.5 1.5 0 0 0 8.15 15.8h3.7a1.5 1.5 0 0 0 1.5-1.4l.4-8.15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </x-ui.button>
                        </div>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-empty
                    colspan="4"
                    title="No tags match the current view"
                    message="Adjust the search or status filter, or create a new tag to start building editorial labels."
                />
            @endforelse
        </x-ui.table-body>
    </x-ui.table>

    <x-ui.drawer
        :open="$drawerOpen"
        :title="$editingTagId ? 'Edit tag' : 'Create tag'"
        description="Keep list context visible while managing tag details."
        width="md"
    >
        <div class="space-y-6">
            @if ($formError)
                <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                    {{ $formError }}
                </div>
            @endif

            <x-ui.field label="Name" for="tag-name" :error="$errors->first('name')" required>
                <x-ui.input id="tag-name" wire:model.blur="name" placeholder="Tag name" :invalid="$errors->has('name')" />
            </x-ui.field>

            <x-ui.field label="Slug" for="tag-slug" :error="$errors->first('slug')" hint="Leave blank to let the service generate the slug.">
                <x-ui.input id="tag-slug" wire:model.blur="slug" placeholder="tag-slug" :invalid="$errors->has('slug')" />
            </x-ui.field>

            <x-ui.field label="Description" for="tag-description" :error="$errors->first('description')">
                <x-ui.textarea id="tag-description" wire:model.blur="description" placeholder="Short editorial description" :invalid="$errors->has('description')" />
            </x-ui.field>

            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                <label class="flex items-start gap-3">
                    <input wire:model.live="isActive" type="checkbox" class="mt-1 h-4 w-4 rounded border-[var(--color-line-strong)] text-[var(--color-accent)] focus:ring-[var(--color-ring)]">
                    <span>
                        <span class="block text-sm font-semibold text-[var(--color-ink)]">Active tag</span>
                        <span class="mt-1 block text-sm text-[var(--color-muted)]">Inactive tags stay available in admin history but should not be offered in active editorial tagging workflows.</span>
                    </span>
                </label>
            </div>
        </div>

        <x-slot:actions>
            <x-ui.button type="button" variant="secondary" wire:click="closeDrawer">Cancel</x-ui.button>
            <x-ui.button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save,openEditDrawer">
                <span wire:loading.remove wire:target="save">{{ $editingTagId ? 'Save changes' : 'Create tag' }}</span>
                <span wire:loading wire:target="save">Saving…</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.drawer>

    <x-ui.dialog
        :open="$deleteDialogOpen"
        title="Delete tag"
        description="This removes the tag from editorial workflows. Confirm before continuing."
        tone="destructive"
    >
        <p class="text-sm leading-6 text-[var(--color-muted)]">
            Delete <span class="font-semibold text-[var(--color-ink)]">{{ $deleteTagName }}</span>? This action should only be taken when you are confident the tag is no longer needed for labeling posts.
        </p>

        <x-slot:actions>
            <x-ui.button type="button" variant="secondary" wire:click="cancelDelete">Cancel</x-ui.button>
            <x-ui.button type="button" variant="destructive" wire:click="delete" wire:loading.attr="disabled" wire:target="delete">
                <span wire:loading.remove wire:target="delete">Delete tag</span>
                <span wire:loading wire:target="delete">Deleting…</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.dialog>
</div>
