<div class="space-y-6">
    <x-admin.page-header
        title="Categories"
        description="Manage category structure, ordering, activation state, and parent relationships for editorial workflows."
    >
        <x-ui.button type="button" wire:click="openCreateDrawer">Create Category</x-ui.button>
    </x-admin.page-header>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    <x-admin.filter-bar>
        <x-slot:search>
            <label class="block">
                <span class="sr-only">Search categories</span>
                <x-ui.input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search categories by name, slug, or description"
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

        <x-slot:results>{{ count($categories) }} {{ str('category')->plural(count($categories)) }}</x-slot:results>
    </x-admin.filter-bar>

    <x-ui.table caption="Categories">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading class="w-[36%]" sortable sort-key="name" :sort-column="$sortColumn" :sort-direction="$sortDirection">NAME</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="slug" :sort-column="$sortColumn" :sort-direction="$sortDirection">SLUG</x-ui.table-heading>
                <x-ui.table-heading>PARENT</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="is_active" :sort-column="$sortColumn" :sort-direction="$sortDirection">STATUS</x-ui.table-heading>
                <x-ui.table-heading align="center" sortable sort-key="sort_order" :sort-column="$sortColumn" :sort-direction="$sortDirection">SORT ORDER</x-ui.table-heading>
                <x-ui.table-heading align="right">ACTIONS</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($categories as $category)
                <x-ui.table-row interactive wire:key="category-{{ $category['id'] }}">
                    <x-ui.table-cell class="w-[36%]">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-[var(--color-ink)]">{{ $category['name'] }}</p>
                            @if ($category['description'])
                                <p class="mt-1 text-sm text-[var(--color-muted)]">{{ $category['description'] }}</p>
                            @endif
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $category['slug'] ?: 'Auto-generated' }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $category['parent_name'] ?: 'Top level' }}</x-ui.table-cell>
                    <x-ui.table-cell>
                        <x-admin.status-badge :status="$category['is_active'] ? 'active' : 'inactive'" />
                    </x-ui.table-cell>
                    <x-ui.table-cell align="center">{{ $category['sort_order'] }}</x-ui.table-cell>
                    <x-ui.table-cell align="right">
                        <x-admin.row-actions>
                            <x-admin.row-action type="button" wire:click="openEditDrawer({{ $category['id'] }})">Edit</x-admin.row-action>
                            <x-admin.row-action type="button" tone="danger" wire:click="confirmDelete({{ $category['id'] }})">Delete</x-admin.row-action>
                        </x-admin.row-actions>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-empty
                    colspan="6"
                    title="No categories match the current view"
                    message="Adjust the search or status filter, or create a new category to start structuring editorial taxonomy."
                />
            @endforelse
        </x-ui.table-body>
    </x-ui.table>

    <x-ui.drawer
        :open="$drawerOpen"
        :title="$editingCategoryId ? 'Edit category' : 'Create category'"
        description="Keep list context visible while managing category details."
        width="md"
    >
        <div class="space-y-6">
            @if ($formError)
                <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                    {{ $formError }}
                </div>
            @endif

            <x-ui.field label="Name" for="category-name" :error="$errors->first('name')" required>
                <x-ui.input id="category-name" wire:model.blur="name" placeholder="Category name" :invalid="$errors->has('name')" />
            </x-ui.field>

            <x-ui.field label="Slug" for="category-slug" :error="$errors->first('slug')" hint="Leave blank to let the service generate the slug.">
                <x-ui.input id="category-slug" wire:model.blur="slug" placeholder="category-slug" :invalid="$errors->has('slug')" />
            </x-ui.field>

            <x-ui.field label="Description" for="category-description" :error="$errors->first('description')">
                <x-ui.textarea id="category-description" wire:model.blur="description" placeholder="Short editorial description" :invalid="$errors->has('description')" />
            </x-ui.field>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="Parent Category" for="category-parent" :error="$errors->first('parentId')">
                    <x-ui.select id="category-parent" wire:model.live="parentId" :invalid="$errors->has('parentId')">
                        <option value="">Top level</option>
                        @foreach ($parentOptions as $parent)
                            <option value="{{ $parent['id'] }}">{{ $parent['name'] }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Sort Order" for="category-sort-order" :error="$errors->first('sortOrder')">
                    <x-ui.input id="category-sort-order" type="number" min="0" wire:model.blur="sortOrder" :invalid="$errors->has('sortOrder')" />
                </x-ui.field>
            </div>

            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                <label class="flex items-start gap-3">
                    <input wire:model.live="isActive" type="checkbox" class="mt-1 h-4 w-4 rounded border-[var(--color-line-strong)] text-[var(--color-accent)] focus:ring-[var(--color-ring)]">
                    <span>
                        <span class="block text-sm font-semibold text-[var(--color-ink)]">Active category</span>
                        <span class="mt-1 block text-sm text-[var(--color-muted)]">Inactive categories remain in admin taxonomy but should not surface in public category endpoints.</span>
                    </span>
                </label>
            </div>
        </div>

        <x-slot:actions>
            <x-ui.button type="button" variant="secondary" wire:click="closeDrawer">Cancel</x-ui.button>
            <x-ui.button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save,openEditDrawer">
                <span wire:loading.remove wire:target="save">{{ $editingCategoryId ? 'Save changes' : 'Create category' }}</span>
                <span wire:loading wire:target="save">Saving…</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.drawer>

    <x-ui.dialog
        :open="$deleteDialogOpen"
        title="Delete category"
        description="This will soft-delete the category. Confirm before continuing."
        tone="destructive"
    >
        <p class="text-sm leading-6 text-[var(--color-muted)]">
            Delete <span class="font-semibold text-[var(--color-ink)]">{{ $deleteCategoryName }}</span>? This action should only be taken when you are confident the category is no longer needed in editorial workflows.
        </p>

        <x-slot:actions>
            <x-ui.button type="button" variant="secondary" wire:click="cancelDelete">Cancel</x-ui.button>
            <x-ui.button type="button" variant="destructive" wire:click="delete" wire:loading.attr="disabled" wire:target="delete">
                <span wire:loading.remove wire:target="delete">Delete category</span>
                <span wire:loading wire:target="delete">Deleting…</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.dialog>
</div>
