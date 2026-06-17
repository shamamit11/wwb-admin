<div class="space-y-6">
    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-4 shadow-[var(--shadow-card)]">
        <div class="flex flex-col gap-3 2xl:flex-row 2xl:items-center">
            <div class="min-w-0 flex-1 2xl:max-w-[25rem]">
                <label for="media-search" class="sr-only">Search media</label>
                <x-ui.input
                    id="media-search"
                    type="search"
                    wire:model.live.debounce.350ms="search"
                    placeholder="Search media by filename or metadata"
                />
            </div>

            <div class="grid flex-1 gap-3 sm:grid-cols-2 xl:grid-cols-4 2xl:min-w-0">
                <x-ui.select wire:model.live="sourceTypeFilter">
                    <option value="all">All sources</option>
                    <option value="uploaded">Uploaded</option>
                    <option value="ai_generated">AI generated</option>
                    <option value="stock">Stock</option>
                </x-ui.select>

                <x-ui.select wire:model.live="statusFilter">
                    <option value="all">All statuses</option>
                    <option value="pending">Pending</option>
                    <option value="ready">Ready</option>
                    <option value="failed">Failed</option>
                    <option value="archived">Archived</option>
                </x-ui.select>

                <x-ui.select wire:model.live="usageFilter">
                    <option value="all">All usage</option>
                    <option value="used">Used only</option>
                    <option value="unused">Unused only</option>
                </x-ui.select>

                <x-ui.select wire:model.live="imageFilter">
                    <option value="all">All types</option>
                    <option value="images">Images only</option>
                    <option value="non-images">Non-images only</option>
                </x-ui.select>
            </div>

            <div class="shrink-0 text-right text-sm text-[var(--color-muted)] 2xl:min-w-[5.5rem]">
                {{ count($mediaItems) }} {{ str('asset')->plural(count($mediaItems)) }}
            </div>
        </div>
    </div>

    <x-ui.table caption="Media library">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading class="w-[12%]">Preview</x-ui.table-heading>
                <x-ui.table-heading class="w-[34%]">
                    <button type="button" wire:click="sortBy('original_filename')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>File</span>
                        <span class="text-[10px] leading-none">{{ $sortColumn === 'original_filename' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('source_type')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Source</span>
                        <span class="text-[10px] leading-none">{{ $sortColumn === 'source_type' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('status')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Status</span>
                        <span class="text-[10px] leading-none">{{ $sortColumn === 'status' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading align="center">
                    <button type="button" wire:click="sortBy('usage_count')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Usage</span>
                        <span class="text-[10px] leading-none">{{ $sortColumn === 'usage_count' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('created_at')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Created</span>
                        <span class="text-[10px] leading-none">{{ $sortColumn === 'created_at' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading align="right">Actions</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($mediaItems as $item)
                <x-ui.table-row interactive wire:key="media-{{ $item['id'] }}">
                    <x-ui.table-cell>
                        <button type="button" wire:click="openDetailDrawer({{ $item['id'] }})" class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)]">
                            @if ($item['is_image'] && $item['url'])
                                <img src="{{ $item['url'] }}" alt="{{ $item['alt_text'] ?: $item['original_filename'] }}" class="h-full w-full object-cover">
                            @else
                                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">{{ $item['extension'] ?: 'FILE' }}</span>
                            @endif
                        </button>
                    </x-ui.table-cell>
                    <x-ui.table-cell class="w-[34%]">
                        <button type="button" wire:click="openDetailDrawer({{ $item['id'] }})" class="min-w-0 text-left">
                            <p class="truncate font-semibold text-[var(--color-ink)]">{{ $item['original_filename'] }}</p>
                            <p class="mt-1 text-sm text-[var(--color-muted)]">
                                {{ $item['mime_type'] }} · {{ $item['file_size_label'] }}
                                @if ($item['width'] && $item['height'])
                                    · {{ $item['width'] }}×{{ $item['height'] }}
                                @endif
                            </p>
                        </button>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ str($item['source_type'])->replace('_', ' ')->headline() }}</x-ui.table-cell>
                    <x-ui.table-cell>
                        <x-admin.status-badge :status="$item['status']" />
                    </x-ui.table-cell>
                    <x-ui.table-cell align="center">
                        <x-ui.badge :tone="$item['usage_count'] > 0 ? 'warning' : 'muted'">
                            {{ $item['usage_count'] }}
                        </x-ui.badge>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $item['created_at'] ?: 'Unknown' }}</x-ui.table-cell>
                    <x-ui.table-cell align="right">
                        <div class="flex items-center justify-end gap-2">
                            <x-ui.button
                                type="button"
                                variant="ghost"
                                class="h-12 w-12 px-0 bg-[color-mix(in_srgb,var(--color-warning)_12%,white)] text-[var(--color-warning-strong)] ring-1 ring-[color-mix(in_srgb,var(--color-warning)_18%,white)] hover:bg-[color-mix(in_srgb,var(--color-warning)_18%,white)] hover:text-[var(--color-warning-strong)]"
                                wire:click="openDetailDrawer({{ $item['id'] }})"
                                aria-label="Edit media {{ $item['original_filename'] }}"
                                title="Edit metadata"
                            >
                                <svg class="h-6 w-6" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="m13.75 4.75 1.5 1.5M5 15l2.75-.5L15.5 6.75a1.06 1.06 0 0 0 0-1.5l-.75-.75a1.06 1.06 0 0 0-1.5 0L5.5 12.25 5 15Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </x-ui.button>
                            <x-ui.button
                                type="button"
                                variant="ghost"
                                class="h-12 w-12 px-0 bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] text-[var(--color-danger-strong)] ring-1 ring-[color-mix(in_srgb,var(--color-danger)_18%,white)] hover:bg-[color-mix(in_srgb,var(--color-danger)_16%,white)] hover:text-[var(--color-danger-strong)]"
                                wire:click="confirmDelete({{ $item['id'] }})"
                                aria-label="Delete media {{ $item['original_filename'] }}"
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
                    colspan="7"
                    title="No media matches the current view"
                    message="Adjust the search or filters to find an existing asset."
                />
            @endforelse
        </x-ui.table-body>
    </x-ui.table>

    <x-ui.drawer
        :open="$drawerOpen"
        title="Media details"
        description="Inspect usage and maintain metadata without leaving the library."
        width="lg"
    >
        @if ($selectedMedia)
            <div class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
                    <div class="space-y-4">
                        <div class="overflow-hidden rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel-soft)]">
                            @if ($selectedMedia['is_image'] && $selectedMedia['url'])
                                <img src="{{ $selectedMedia['url'] }}" alt="{{ $selectedMedia['alt_text'] ?: $selectedMedia['original_filename'] }}" class="h-72 w-full object-cover">
                            @else
                                <div class="flex h-72 items-center justify-center">
                                    <span class="text-sm font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">{{ $selectedMedia['extension'] ?: 'FILE' }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4">
                            <h3 class="text-sm font-semibold text-[var(--color-ink)]">Asset summary</h3>
                            <dl class="mt-4 space-y-3 text-sm">
                                <div class="flex items-start justify-between gap-4">
                                    <dt class="text-[var(--color-muted)]">Filename</dt>
                                    <dd class="text-right text-[var(--color-ink)]">{{ $selectedMedia['original_filename'] }}</dd>
                                </div>
                                <div class="flex items-start justify-between gap-4">
                                    <dt class="text-[var(--color-muted)]">Type</dt>
                                    <dd class="text-right text-[var(--color-ink)]">{{ $selectedMedia['mime_type'] }}</dd>
                                </div>
                                <div class="flex items-start justify-between gap-4">
                                    <dt class="text-[var(--color-muted)]">Size</dt>
                                    <dd class="text-right text-[var(--color-ink)]">{{ $selectedMedia['file_size_label'] }}</dd>
                                </div>
                                <div class="flex items-start justify-between gap-4">
                                    <dt class="text-[var(--color-muted)]">Dimensions</dt>
                                    <dd class="text-right text-[var(--color-ink)]">
                                        @if ($selectedMedia['width'] && $selectedMedia['height'])
                                            {{ $selectedMedia['width'] }}×{{ $selectedMedia['height'] }}
                                        @else
                                            Not available
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex items-start justify-between gap-4">
                                    <dt class="text-[var(--color-muted)]">Usage count</dt>
                                    <dd class="text-right text-[var(--color-ink)]">{{ $selectedMedia['usage_count'] }}</dd>
                                </div>
                                <div class="flex items-start justify-between gap-4">
                                    <dt class="text-[var(--color-muted)]">Alt text</dt>
                                    <dd class="max-w-[18rem] text-right text-[var(--color-ink)]">{{ $selectedMedia['alt_text'] ?: 'Not set' }}</dd>
                                </div>
                                <div class="flex items-start justify-between gap-4">
                                    <dt class="text-[var(--color-muted)]">Caption</dt>
                                    <dd class="max-w-[18rem] text-right text-[var(--color-ink)]">{{ $selectedMedia['caption'] ?: 'Not set' }}</dd>
                                </div>
                                <div class="flex items-start justify-between gap-4">
                                    <dt class="text-[var(--color-muted)]">Attribution</dt>
                                    <dd class="max-w-[18rem] text-right text-[var(--color-ink)]">{{ $selectedMedia['attribution_text'] ?: 'Not set' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4">
                            <div class="flex items-center justify-between gap-4">
                                <h3 class="text-sm font-semibold text-[var(--color-ink)]">Usage</h3>
                                <x-ui.badge :tone="$selectedMedia['usage_count'] > 0 ? 'warning' : 'muted'">
                                    {{ $selectedMedia['usage_count'] }}
                                </x-ui.badge>
                            </div>

                            @if ($selectedMedia['usage'])
                                <div class="mt-4 space-y-3">
                                    @foreach ($selectedMedia['usage'] as $usage)
                                        <div class="flex items-center justify-between gap-4 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-3 py-3 text-sm">
                                            <span class="text-[var(--color-ink)]">{{ $usage['label'] ?? str($usage['type'] ?? 'usage')->replace('_', ' ')->headline() }}</span>
                                            <span class="text-[var(--color-muted)]">{{ $usage['count'] ?? 0 }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-4 text-sm leading-6 text-[var(--color-muted)]">This asset is currently unused and can be safely replaced or deleted.</p>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-6">
                        @if ($formError)
                            <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                                {{ $formError }}
                            </div>
                        @endif

                        <x-ui.field label="Alt Text" for="media-alt-text" :error="$errors->first('altText')">
                            <x-ui.input id="media-alt-text" wire:model.blur="altText" placeholder="Describe the asset for accessibility" :invalid="$errors->has('altText')" />
                        </x-ui.field>

                        <x-ui.field label="Caption" for="media-caption" :error="$errors->first('caption')">
                            <x-ui.textarea id="media-caption" wire:model.blur="caption" placeholder="Editorial caption" :invalid="$errors->has('caption')" />
                        </x-ui.field>

                        <x-ui.field label="Source Type" for="media-source-type" :error="$errors->first('sourceType')" required>
                            <x-ui.select id="media-source-type" wire:model.live="sourceType" :invalid="$errors->has('sourceType')">
                                <option value="uploaded">Uploaded</option>
                                <option value="ai_generated">AI generated</option>
                                <option value="stock">Stock</option>
                            </x-ui.select>
                        </x-ui.field>

                        <x-ui.field label="Source URL" for="media-source-url" :error="$errors->first('sourceUrl')">
                            <x-ui.input id="media-source-url" wire:model.blur="sourceUrl" placeholder="https://example.com/original" :invalid="$errors->has('sourceUrl')" />
                        </x-ui.field>

                        <x-ui.field label="Attribution Text" for="media-attribution-text" :error="$errors->first('attributionText')">
                            <x-ui.input id="media-attribution-text" wire:model.blur="attributionText" placeholder="Provider or photographer credit" :invalid="$errors->has('attributionText')" />
                        </x-ui.field>
                    </div>
                </div>
            </div>
        @endif

        <x-slot:actions>
            <x-ui.button type="button" variant="secondary" wire:click="closeDrawer">Close</x-ui.button>
            @if ($selectedMedia)
                <x-ui.button type="button" wire:click="saveMetadata" wire:loading.attr="disabled" wire:target="saveMetadata,openDetailDrawer">
                    <span wire:loading.remove wire:target="saveMetadata">Save metadata</span>
                    <span wire:loading wire:target="saveMetadata">Saving…</span>
                </x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.drawer>

    <x-ui.dialog
        :open="$deleteDialogOpen"
        title="Delete media asset"
        :description="$deleteBlocked ? 'This asset is currently in use and cannot be deleted.' : 'Delete only when the asset is no longer needed anywhere in editorial workflows.'"
        tone="destructive"
    >
        <div class="space-y-4 text-sm leading-6 text-[var(--color-muted)]">
            <p>
                <span class="font-semibold text-[var(--color-ink)]">{{ $deleteMediaName }}</span>
                has <span class="font-semibold text-[var(--color-ink)]">{{ $deleteUsageCount }}</span>
                {{ str('usage')->plural($deleteUsageCount) }}.
            </p>

            @if ($deleteUsage)
                <div class="space-y-2">
                    @foreach ($deleteUsage as $usage)
                        <div class="flex items-center justify-between gap-4 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-3 py-3">
                            <span class="text-[var(--color-ink)]">{{ $usage['label'] ?? str($usage['type'] ?? 'usage')->replace('_', ' ')->headline() }}</span>
                            <span>{{ $usage['count'] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($deleteBlocked)
                <p>Remove existing usage references before trying to delete this asset.</p>
            @else
                <p>Confirm deletion only if you are confident this asset is not referenced anywhere important.</p>
            @endif
        </div>

        <x-slot:actions>
            <x-ui.button type="button" variant="secondary" wire:click="cancelDelete">Cancel</x-ui.button>
            @unless ($deleteBlocked)
                <x-ui.button type="button" variant="destructive" wire:click="delete" wire:loading.attr="disabled" wire:target="delete">
                    <span wire:loading.remove wire:target="delete">Delete asset</span>
                    <span wire:loading wire:target="delete">Deleting…</span>
                </x-ui.button>
            @endunless
        </x-slot:actions>
    </x-ui.dialog>
</div>
