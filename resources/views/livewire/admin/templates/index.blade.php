<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <x-admin.page-header
            title="Templates"
            description="Manage reusable content structures, defaults, and ordered template blocks for editorial workflows."
        />

        <div class="shrink-0 lg:pt-1">
            <x-ui.button type="button" wire:click="openCreateDrawer">Create Template</x-ui.button>
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
                <span class="sr-only">Search templates</span>
                <x-ui.input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search templates by name, slug, type, or description"
                />
            </label>
        </x-slot:search>

        <x-slot:filters>
            <div class="flex items-center gap-3">
                <div class="w-[11rem] shrink-0">
                    <x-ui.select wire:model.live="typeFilter">
                        <option value="all">All types</option>
                        @foreach ($templateTypes as $type)
                            <option value="{{ $type }}">{{ str($type)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="w-[11rem] shrink-0">
                    <x-ui.select wire:model.live="statusFilter">
                        <option value="all">All statuses</option>
                        @foreach ($templateStatuses as $statusOption)
                            <option value="{{ $statusOption }}">{{ str($statusOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>
        </x-slot:filters>

        <x-slot:secondary>
            <div class="text-sm text-[var(--color-muted)]">
                {{ count($templates) }} {{ str('template')->plural(count($templates)) }}
            </div>
        </x-slot:secondary>
    </x-admin.filter-bar>

    <x-ui.table caption="Templates">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading class="w-[34%]">
                    <button type="button" wire:click="sortBy('name')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Name</span>
                        <span class="text-[10px] leading-none">{{ $sortColumn === 'name' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('template_type')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Type</span>
                        <span class="text-[10px] leading-none">{{ $sortColumn === 'template_type' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('status')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Status</span>
                        <span class="text-[10px] leading-none">{{ $sortColumn === 'status' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading align="center">
                    <button type="button" wire:click="sortBy('blocks_count')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Blocks</span>
                        <span class="text-[10px] leading-none">{{ $sortColumn === 'blocks_count' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('updated_at')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Updated</span>
                        <span class="text-[10px] leading-none">{{ $sortColumn === 'updated_at' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading align="right">Actions</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($templates as $template)
                <x-ui.table-row interactive wire:key="template-{{ $template['id'] }}">
                    <x-ui.table-cell class="w-[34%]">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-[var(--color-ink)]">{{ $template['name'] }}</p>
                            <p class="mt-1 text-sm text-[var(--color-muted)]">{{ $template['slug'] ?: 'Auto-generated slug' }}</p>
                            @if ($template['description'])
                                <p class="mt-2 text-sm text-[var(--color-muted)]">{{ $template['description'] }}</p>
                            @endif
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ str($template['template_type'])->headline() }}</x-ui.table-cell>
                    <x-ui.table-cell>
                        <x-admin.status-badge :status="$template['status']" />
                    </x-ui.table-cell>
                    <x-ui.table-cell align="center">
                        <x-ui.badge tone="muted">{{ $template['blocks_count'] }}</x-ui.badge>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $template['updated_at'] ?: 'Unknown' }}</x-ui.table-cell>
                    <x-ui.table-cell align="right">
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <x-ui.button
                                type="button"
                                variant="secondary"
                                class="h-10 px-3 text-sm"
                                wire:click="openActionDrawer('preview', {{ $template['id'] }})"
                            >
                                Preview
                            </x-ui.button>
                            <x-ui.button
                                type="button"
                                variant="secondary"
                                class="h-10 px-3 text-sm"
                                wire:click="openActionDrawer('seed', {{ $template['id'] }})"
                            >
                                Seed Post
                            </x-ui.button>
                            <x-ui.button
                                type="button"
                                variant="ghost"
                                class="h-12 w-12 px-0 bg-[color-mix(in_srgb,var(--color-warning)_12%,white)] text-[var(--color-warning-strong)] ring-1 ring-[color-mix(in_srgb,var(--color-warning)_18%,white)] hover:bg-[color-mix(in_srgb,var(--color-warning)_18%,white)] hover:text-[var(--color-warning-strong)]"
                                wire:click="openEditDrawer({{ $template['id'] }})"
                                aria-label="Edit template {{ $template['name'] }}"
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
                                wire:click="confirmDelete({{ $template['id'] }})"
                                aria-label="Delete template {{ $template['name'] }}"
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
                    colspan="6"
                    title="No templates match the current view"
                    message="Adjust the search or filters, or create a template to define a reusable editorial structure."
                />
            @endforelse
        </x-ui.table-body>
    </x-ui.table>

    <x-ui.drawer
        :open="$drawerOpen"
        :title="$editingTemplateId ? 'Edit template' : 'Create template'"
        description="Keep the structure explicit: ordered blocks, clear defaults, and lightweight editing controls."
        width="xl"
    >
        <div class="space-y-6">
            @if ($formError)
                <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                    {{ $formError }}
                </div>
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="Name" for="template-name" :error="$errors->first('name')" required>
                    <x-ui.input id="template-name" wire:model.blur="name" placeholder="Template name" :invalid="$errors->has('name')" />
                </x-ui.field>

                <x-ui.field label="Slug" for="template-slug" :error="$errors->first('slug')" hint="Leave blank to let the service generate the slug.">
                    <x-ui.input id="template-slug" wire:model.blur="slug" placeholder="template-slug" :invalid="$errors->has('slug')" />
                </x-ui.field>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="Template Type" for="template-type" :error="$errors->first('templateType')" required>
                    <x-ui.select id="template-type" wire:model.live="templateType" :invalid="$errors->has('templateType')">
                        @foreach ($templateTypes as $type)
                            <option value="{{ $type }}">{{ str($type)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Status" for="template-status" :error="$errors->first('status')" required>
                    <x-ui.select id="template-status" wire:model.live="status" :invalid="$errors->has('status')">
                        @foreach ($templateStatuses as $statusOption)
                            <option value="{{ $statusOption }}">{{ str($statusOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>
            </div>

            <x-ui.field label="Description" for="template-description" :error="$errors->first('description')">
                <x-ui.textarea id="template-description" wire:model.blur="description" placeholder="Short explanation of when editors should use this template" :invalid="$errors->has('description')" />
            </x-ui.field>

            <x-ui.field label="Default Excerpt Prompt" for="template-excerpt-prompt" :error="$errors->first('defaultExcerptPrompt')">
                <x-ui.textarea id="template-excerpt-prompt" wire:model.blur="defaultExcerptPrompt" placeholder="Guide the service on how to summarize seeded posts from this template" :invalid="$errors->has('defaultExcerptPrompt')" />
            </x-ui.field>

            <div class="space-y-3 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div>
                    <h3 class="text-sm font-semibold text-[var(--color-ink)]">Default Meta</h3>
                    <p class="mt-1 text-sm text-[var(--color-muted)]">Store structured JSON defaults such as section guidance or SEO rules. Leave empty if the template does not need shared meta.</p>
                </div>

                <x-ui.field label="JSON" for="template-default-meta" :error="$errors->first('defaultMetaJson')">
                    <x-ui.textarea id="template-default-meta" wire:model.blur="defaultMetaJson" rows="6" placeholder='{"recommended_sections":["introduction","steps","faq"]}' :invalid="$errors->has('defaultMetaJson')" />
                </x-ui.field>
            </div>

            <div class="space-y-4 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-[var(--color-ink)]">Ordered Blocks</h3>
                        <p class="mt-1 text-sm text-[var(--color-muted)]">This editor keeps block order explicit. Use the arrow controls to move blocks instead of dragging.</p>
                    </div>

                    <x-ui.button type="button" variant="secondary" wire:click="addBlock">Add Block</x-ui.button>
                </div>

                @error('blocks')
                    <p class="text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>
                @enderror

                <div class="space-y-4">
                    @foreach ($blocks as $index => $block)
                        <div wire:key="template-block-{{ $block['key'] }}" class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                            <div class="flex flex-col gap-3 border-b border-[var(--color-line)] pb-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-[var(--radius-button)] bg-[var(--color-panel)] px-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">
                                        {{ $block['sortOrder'] }}
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-[var(--color-ink)]">Block {{ $block['sortOrder'] }}</p>
                                        <p class="text-xs text-[var(--color-muted)]">Structured order is saved directly into the payload.</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        wire:click="moveBlockUp({{ $index }})"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)] disabled:cursor-not-allowed disabled:opacity-40"
                                        @disabled($index === 0)
                                        aria-label="Move block up"
                                        title="Move up"
                                    >
                                        ↑
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="moveBlockDown({{ $index }})"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)] disabled:cursor-not-allowed disabled:opacity-40"
                                        @disabled($index === count($blocks) - 1)
                                        aria-label="Move block down"
                                        title="Move down"
                                    >
                                        ↓
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="removeBlock({{ $index }})"
                                        class="inline-flex h-10 items-center justify-center rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_18%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-3 text-sm font-medium text-[var(--color-danger-strong)] transition-colors hover:bg-[color-mix(in_srgb,var(--color-danger)_16%,white)]"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-5 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                                <div class="space-y-5">
                                    <x-ui.field label="Block Type" for="template-block-type-{{ $index }}" :error="$errors->first('blocks.'.$index.'.blockType')" required>
                                        <x-ui.select id="template-block-type-{{ $index }}" wire:model.live="blocks.{{ $index }}.blockType" :invalid="$errors->has('blocks.'.$index.'.blockType')">
                                            @foreach ($blockTypes as $blockType)
                                                <option value="{{ $blockType }}">{{ str($blockType)->headline() }}</option>
                                            @endforeach
                                        </x-ui.select>
                                    </x-ui.field>

                                    <x-ui.field label="Label" for="template-block-label-{{ $index }}" :error="$errors->first('blocks.'.$index.'.label')">
                                        <x-ui.input id="template-block-label-{{ $index }}" wire:model.blur="blocks.{{ $index }}.label" placeholder="Internal editor label" :invalid="$errors->has('blocks.'.$index.'.label')" />
                                    </x-ui.field>

                                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">How seeded posts use this block</p>
                                        <p class="mt-2 text-sm leading-6 text-[var(--color-muted)]">{{ $blockUi[$index]['defaultMarkdownHint'] }}</p>
                                    </div>

                                    <x-ui.field label="Settings JSON" for="template-block-settings-{{ $index }}" :error="$errors->first('blocks.'.$index.'.settingsJson')" :hint="$blockUi[$index]['settingsHint']">
                                        <x-ui.textarea id="template-block-settings-{{ $index }}" wire:model.blur="blocks.{{ $index }}.settingsJson" rows="5" placeholder='{"level":1}' :invalid="$errors->has('blocks.'.$index.'.settingsJson')" />
                                    </x-ui.field>

                                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-4">
                                        <label class="flex items-start gap-3">
                                            <input wire:model.live="blocks.{{ $index }}.isRequired" type="checkbox" class="mt-1 h-4 w-4 rounded border-[var(--color-line-strong)] text-[var(--color-accent)] focus:ring-[var(--color-ring)]">
                                            <span>
                                                <span class="block text-sm font-semibold text-[var(--color-ink)]">Required block</span>
                                                <span class="mt-1 block text-sm text-[var(--color-muted)]">Use this when seeded posts should always include the block.</span>
                                            </span>
                                        </label>
                                        @error('blocks.'.$index.'.isRequired')
                                            <p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    @if ($blockUi[$index]['showsToolbar'])
                                        <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-3 py-3">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="mr-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Markdown tools</span>
                                                @foreach ($blockUi[$index]['toolbar'] as $tool)
                                                    <button
                                                        type="button"
                                                        wire:click="insertBlockSnippet({{ $index }}, '{{ $tool['action'] }}')"
                                                        class="inline-flex min-h-9 items-center justify-center rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-3 text-sm font-medium text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel)]"
                                                    >
                                                        {{ $tool['label'] }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <x-ui.field :label="$blockUi[$index]['defaultMarkdownLabel']" for="template-block-markdown-{{ $index }}" :error="$errors->first('blocks.'.$index.'.defaultMarkdown')" hint="Use placeholders like `@{{title}}` or `@{{topic}}` where the service expects them.">
                                        <x-ui.textarea
                                            id="template-block-markdown-{{ $index }}"
                                            wire:model.blur="blocks.{{ $index }}.defaultMarkdown"
                                            rows="14"
                                            :placeholder="$blockUi[$index]['placeholder']"
                                            :invalid="$errors->has('blocks.'.$index.'.defaultMarkdown')"
                                        />
                                    </x-ui.field>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-ui.button type="button" variant="secondary" wire:click="closeDrawer">Cancel</x-ui.button>
            <x-ui.button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save,openEditDrawer">
                <span wire:loading.remove wire:target="save">{{ $editingTemplateId ? 'Save changes' : 'Create template' }}</span>
                <span wire:loading wire:target="save">Saving…</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.drawer>

    <x-ui.drawer
        :open="$actionDrawerOpen"
        :title="$actionMode === 'seed' ? 'Seed post payload' : 'Template preview'"
        :description="$actionMode === 'seed'
            ? 'Generate the documented seed-post payload for this template. The result is shown as service output only and does not assume editor navigation.'
            : 'Generate a preview payload for this template using the documented preview endpoint.'"
        width="xl"
    >
        <div class="space-y-6">
            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-5 py-4">
                <p class="text-sm font-semibold text-[var(--color-ink)]">{{ $actionTemplateName }}</p>
                <p class="mt-1 text-sm text-[var(--color-muted)]">
                    {{ $actionMode === 'seed' ? 'Seed a post payload from this template.' : 'Preview how this template resolves into content blocks.' }}
                </p>
            </div>

            @if ($actionError)
                <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                    {{ $actionError }}
                </div>
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="Title Override" for="template-action-title" :error="$errors->first('actionContextTitle')">
                    <x-ui.input id="template-action-title" wire:model.blur="actionContextTitle" placeholder="Optional title override" :invalid="$errors->has('actionContextTitle')" />
                </x-ui.field>

                <x-ui.field label="Topic Override" for="template-action-topic" :error="$errors->first('actionContextTopic')">
                    <x-ui.input id="template-action-topic" wire:model.blur="actionContextTopic" placeholder="Optional topic override" :invalid="$errors->has('actionContextTopic')" />
                </x-ui.field>
            </div>

            @if ($actionResult !== [])
                @if ($actionMode === 'preview')
                    <div class="space-y-5">
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-4 shadow-[var(--shadow-card)]">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Title</p>
                                <p class="mt-2 text-sm font-semibold text-[var(--color-ink)]">{{ data_get($actionResult, 'preview.title', 'Not provided') }}</p>
                            </div>
                            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-4 shadow-[var(--shadow-card)]">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Topic</p>
                                <p class="mt-2 text-sm font-semibold text-[var(--color-ink)]">{{ data_get($actionResult, 'preview.topic', 'Not provided') }}</p>
                            </div>
                            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-4 shadow-[var(--shadow-card)]">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Blocks</p>
                                <p class="mt-2 text-sm font-semibold text-[var(--color-ink)]">{{ count(data_get($actionResult, 'preview.blocks', [])) }}</p>
                            </div>
                        </div>

                        <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                            <h3 class="text-sm font-semibold text-[var(--color-ink)]">Meta</h3>
                            <pre class="mt-3 overflow-x-auto rounded-[var(--radius-button)] bg-[var(--color-panel-soft)] px-4 py-4 text-xs leading-6 text-[var(--color-ink)]">{{ json_encode(data_get($actionResult, 'preview.meta', []), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </div>

                        <div class="space-y-4">
                            @foreach (data_get($actionResult, 'preview.blocks', []) as $block)
                                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-ui.badge tone="muted">{{ str((string) data_get($block, 'block_type', 'block'))->headline() }}</x-ui.badge>
                                        <x-ui.badge tone="muted">Order {{ data_get($block, 'sort_order', '?') }}</x-ui.badge>
                                        @if (data_get($block, 'is_required'))
                                            <x-ui.badge tone="warning">Required</x-ui.badge>
                                        @endif
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-[var(--color-ink)]">{{ data_get($block, 'label') ?: 'Unlabeled block' }}</p>
                                    <pre class="mt-3 overflow-x-auto rounded-[var(--radius-button)] bg-[var(--color-panel-soft)] px-4 py-4 text-xs leading-6 text-[var(--color-ink)]">{{ json_encode(data_get($block, 'content', []), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="space-y-5">
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-4 shadow-[var(--shadow-card)]">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Title</p>
                                <p class="mt-2 text-sm font-semibold text-[var(--color-ink)]">{{ data_get($actionResult, 'post.title', 'Not provided') }}</p>
                            </div>
                            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-4 shadow-[var(--shadow-card)]">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Slug</p>
                                <p class="mt-2 text-sm font-semibold text-[var(--color-ink)]">{{ data_get($actionResult, 'post.slug', 'Not provided') }}</p>
                            </div>
                            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-4 shadow-[var(--shadow-card)]">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Status</p>
                                <p class="mt-2 text-sm font-semibold text-[var(--color-ink)]">{{ str((string) data_get($actionResult, 'post.status', 'unknown'))->headline() }}</p>
                            </div>
                            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-4 shadow-[var(--shadow-card)]">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Template ID</p>
                                <p class="mt-2 text-sm font-semibold text-[var(--color-ink)]">{{ data_get($actionResult, 'post.template_id', 'Not provided') }}</p>
                            </div>
                        </div>

                        <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                            <h3 class="text-sm font-semibold text-[var(--color-ink)]">Meta</h3>
                            <p class="mt-1 text-sm text-[var(--color-muted)]">This is the returned seed payload only. It is suitable for later post-editor integration, but this screen does not assume navigation into the post editor yet.</p>
                            <pre class="mt-3 overflow-x-auto rounded-[var(--radius-button)] bg-[var(--color-panel-soft)] px-4 py-4 text-xs leading-6 text-[var(--color-ink)]">{{ json_encode(data_get($actionResult, 'post.meta', []), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </div>

                        <div class="space-y-4">
                            @foreach (data_get($actionResult, 'post.blocks', []) as $block)
                                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-ui.badge tone="muted">{{ str((string) data_get($block, 'block_type', 'block'))->headline() }}</x-ui.badge>
                                        <x-ui.badge tone="muted">Order {{ data_get($block, 'sort_order', '?') }}</x-ui.badge>
                                        @if (data_get($block, 'is_required'))
                                            <x-ui.badge tone="warning">Required</x-ui.badge>
                                        @endif
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-[var(--color-ink)]">{{ data_get($block, 'label') ?: 'Unlabeled block' }}</p>
                                    <pre class="mt-3 overflow-x-auto rounded-[var(--radius-button)] bg-[var(--color-panel-soft)] px-4 py-4 text-xs leading-6 text-[var(--color-ink)]">{{ json_encode(data_get($block, 'content', []), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <x-slot:actions>
            <x-ui.button type="button" variant="secondary" wire:click="closeActionDrawer">Close</x-ui.button>
            <x-ui.button type="button" wire:click="runTemplateAction" wire:loading.attr="disabled" wire:target="runTemplateAction">
                <span wire:loading.remove wire:target="runTemplateAction">{{ $actionMode === 'seed' ? 'Generate seed payload' : 'Generate preview' }}</span>
                <span wire:loading wire:target="runTemplateAction">Generating…</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.drawer>

    <x-ui.dialog
        :open="$deleteDialogOpen"
        title="Delete template"
        description="This will remove the template and its ordered block configuration. Confirm before continuing."
        tone="destructive"
    >
        <p class="text-sm leading-6 text-[var(--color-muted)]">
            Delete <span class="font-semibold text-[var(--color-ink)]">{{ $deleteTemplateName }}</span>? Only continue when you are sure this structured template is no longer needed for editorial workflows.
        </p>

        <x-slot:actions>
            <x-ui.button type="button" variant="secondary" wire:click="cancelDelete">Cancel</x-ui.button>
            <x-ui.button type="button" variant="destructive" wire:click="delete" wire:loading.attr="disabled" wire:target="delete">
                <span wire:loading.remove wire:target="delete">Delete template</span>
                <span wire:loading wire:target="delete">Deleting…</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.dialog>
</div>
