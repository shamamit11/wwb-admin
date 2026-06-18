<div class="space-y-6">
    <x-admin.page-header
        title="Media Library"
        description="Browse reusable assets, inspect usage, and maintain metadata for editorial workflows."
    >
        <x-ui.button type="button" wire:click="openUploadDrawer">Upload Media</x-ui.button>
    </x-admin.page-header>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    <x-admin.filter-bar>
        <x-slot:search>
            <label class="block">
                <span class="sr-only">Search media</span>
                <x-ui.input
                    id="media-search"
                    type="search"
                    wire:model.live.debounce.350ms="search"
                    placeholder="Search media by filename or metadata"
                />
            </label>
        </x-slot:search>

        <x-slot:filters>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
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
        </x-slot:filters>

        <x-slot:results>{{ count($mediaItems) }} {{ str('asset')->plural(count($mediaItems)) }}</x-slot:results>
    </x-admin.filter-bar>

    <x-ui.table caption="Media library" density="compact">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading width="asset-preview">PREVIEW</x-ui.table-heading>
                <x-ui.table-heading width="content-primary" sortable sort-key="original_filename" :sort-column="$sortColumn" :sort-direction="$sortDirection">FILE</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="source_type" :sort-column="$sortColumn" :sort-direction="$sortDirection">SOURCE</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="status" :sort-column="$sortColumn" :sort-direction="$sortDirection">STATUS</x-ui.table-heading>
                <x-ui.table-heading align="center" sortable sort-key="usage_count" :sort-column="$sortColumn" :sort-direction="$sortDirection">USAGE</x-ui.table-heading>
                <x-ui.table-heading sortable sort-key="created_at" :sort-column="$sortColumn" :sort-direction="$sortDirection">CREATED</x-ui.table-heading>
                <x-ui.table-heading align="right">ACTIONS</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($mediaItems as $item)
                <x-ui.table-row interactive wire:key="media-{{ $item['id'] }}">
                    <x-ui.table-cell width="asset-preview">
                        <button type="button" wire:click="openDetailDrawer({{ $item['id'] }})" class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)]">
                            @if ($item['is_image'] && $item['url'])
                                <img src="{{ $item['url'] }}" alt="{{ $item['alt_text'] ?: $item['original_filename'] }}" class="h-full w-full object-cover">
                            @else
                                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">{{ $item['extension'] ?: 'FILE' }}</span>
                            @endif
                        </button>
                    </x-ui.table-cell>
                    <x-ui.table-cell width="content-primary">
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
                        <div
                            x-data="{ copied: false, async copyUrl() { const url = @js($item['url'] ?? ''); if (! url) { return; } if (navigator.clipboard && window.isSecureContext) { await navigator.clipboard.writeText(url); } else { const input = document.createElement('input'); input.value = url; document.body.appendChild(input); input.select(); document.execCommand('copy'); input.remove(); } this.copied = true; setTimeout(() => this.copied = false, 1600); } }"
                            class="flex justify-end"
                        >
                            <x-admin.row-actions>
                                <x-admin.row-action
                                    type="button"
                                    class="text-[var(--color-muted)]"
                                    x-on:click="copyUrl()"
                                    x-bind:aria-label="copied ? 'Copied media URL for {{ $item['original_filename'] }}' : 'Copy media URL for {{ $item['original_filename'] }}'"
                                    x-bind:title="copied ? 'Copied' : 'Copy URL'"
                                >
                                    <span x-show="! copied">Copy URL</span>
                                    <span x-cloak x-show="copied">Copied</span>
                                </x-admin.row-action>
                                <x-admin.row-action type="button" wire:click="openDetailDrawer({{ $item['id'] }})">Edit</x-admin.row-action>
                                <x-admin.row-action type="button" tone="danger" wire:click="confirmDelete({{ $item['id'] }})">Delete</x-admin.row-action>
                            </x-admin.row-actions>
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
        :open="$uploadDrawerOpen"
        title="Upload media"
        description="Add one file with full metadata, or upload several files with shared source details."
        width="lg"
    >
        <div class="space-y-5">
            <div class="inline-flex rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-1">
                <button
                    type="button"
                    wire:click="setUploadMode('single')"
                    class="{{ $uploadMode === 'single' ? 'bg-[var(--color-panel)] text-[var(--color-ink)] shadow-sm' : 'text-[var(--color-muted)]' }} rounded-[calc(var(--radius-button)-1px)] px-3 py-2 text-sm font-medium transition-colors"
                >
                    Single upload
                </button>
                <button
                    type="button"
                    wire:click="setUploadMode('batch')"
                    class="{{ $uploadMode === 'batch' ? 'bg-[var(--color-panel)] text-[var(--color-ink)] shadow-sm' : 'text-[var(--color-muted)]' }} rounded-[calc(var(--radius-button)-1px)] px-3 py-2 text-sm font-medium transition-colors"
                >
                    Batch upload
                </button>
            </div>

            @if ($uploadError)
                <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                    {{ $uploadError }}
                </div>
            @endif

            @if ($uploadMode === 'single')
                <form
                    wire:submit="uploadSingle"
                    x-data="{ uploading: false, progress: 0 }"
                    x-on:livewire-upload-start="uploading = true"
                    x-on:livewire-upload-finish="uploading = false; progress = 0"
                    x-on:livewire-upload-error="uploading = false"
                    x-on:livewire-upload-progress="progress = $event.detail.progress"
                    class="space-y-5"
                >
                    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)]">
                        <div class="space-y-5">
                            <x-ui.field label="File" for="media-single-file" :error="$errors->first('singleFile')" required>
                                <input
                                    id="media-single-file"
                                    type="file"
                                    wire:model="singleFile"
                                    accept=".jpg,.jpeg,.png,.webp,.gif,.svg,.pdf"
                                    class="block w-full rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-3.5 py-3 text-sm text-[var(--color-ink)] file:mr-3 file:rounded-[var(--radius-button)] file:border-0 file:bg-[var(--color-panel-soft)] file:px-3 file:py-2 file:text-sm file:font-medium file:text-[var(--color-ink)]"
                                >
                            </x-ui.field>

                            <div x-cloak x-show="uploading" class="space-y-2">
                                <div class="flex items-center justify-between text-xs font-medium uppercase tracking-[0.16em] text-[var(--color-muted)]">
                                    <span>Uploading</span>
                                    <span x-text="`${progress}%`"></span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-[var(--color-panel-soft)]">
                                    <div class="h-full bg-[var(--color-accent)] transition-all duration-150" :style="`width: ${progress}%`"></div>
                                </div>
                            </div>

                            <x-ui.field label="Alt Text" for="media-upload-alt-text" :error="$errors->first('uploadAltText')">
                                <x-ui.input id="media-upload-alt-text" wire:model.blur="uploadAltText" placeholder="Describe the asset for accessibility" :invalid="$errors->has('uploadAltText')" />
                            </x-ui.field>

                            <x-ui.field label="Caption" for="media-upload-caption" :error="$errors->first('uploadCaption')">
                                <x-ui.textarea id="media-upload-caption" wire:model.blur="uploadCaption" placeholder="Editorial caption" :invalid="$errors->has('uploadCaption')" />
                            </x-ui.field>
                        </div>

                        <div class="space-y-5">
                            <x-ui.field label="Source Type" for="media-upload-source-type" :error="$errors->first('uploadSourceType')" required>
                                <x-ui.select id="media-upload-source-type" wire:model.live="uploadSourceType" :invalid="$errors->has('uploadSourceType')">
                                    <option value="uploaded">Uploaded</option>
                                    <option value="ai_generated">AI generated</option>
                                    <option value="stock">Stock</option>
                                </x-ui.select>
                            </x-ui.field>

                            <x-ui.field label="Source URL" for="media-upload-source-url" :error="$errors->first('uploadSourceUrl')">
                                <x-ui.input id="media-upload-source-url" wire:model.blur="uploadSourceUrl" placeholder="https://example.com/original" :invalid="$errors->has('uploadSourceUrl')" />
                            </x-ui.field>

                            <x-ui.field label="Attribution Text" for="media-upload-attribution-text" :error="$errors->first('uploadAttributionText')">
                                <x-ui.input id="media-upload-attribution-text" wire:model.blur="uploadAttributionText" placeholder="Provider or photographer credit" :invalid="$errors->has('uploadAttributionText')" />
                            </x-ui.field>

                            <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3 text-sm text-[var(--color-muted)]">
                                Accepted formats: JPG, PNG, WebP, GIF, SVG, and PDF up to 10 MB.
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-[var(--color-line)] pt-5">
                        <x-ui.button type="button" variant="secondary" wire:click="closeUploadDrawer" x-bind:disabled="uploading">Cancel</x-ui.button>
                        <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="singleFile,uploadSingle" x-bind:disabled="uploading">
                            <span wire:loading.remove wire:target="uploadSingle" x-show="! uploading">Upload file</span>
                            <span x-show="uploading">Preparing upload…</span>
                            <span wire:loading wire:target="uploadSingle">Uploading…</span>
                        </x-ui.button>
                    </div>
                </form>
            @else
                <form
                    wire:submit="uploadBatch"
                    x-data="{ uploading: false, progress: 0 }"
                    x-on:livewire-upload-start="uploading = true"
                    x-on:livewire-upload-finish="uploading = false; progress = 0"
                    x-on:livewire-upload-error="uploading = false"
                    x-on:livewire-upload-progress="progress = $event.detail.progress"
                    class="space-y-5"
                >
                    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)]">
                        <div class="space-y-5">
                            <x-ui.field label="Files" for="media-batch-files" :error="$errors->first('batchFiles')" required>
                                <input
                                    id="media-batch-files"
                                    type="file"
                                    wire:model="batchFiles"
                                    multiple
                                    accept=".jpg,.jpeg,.png,.webp,.gif,.svg,.pdf"
                                    class="block w-full rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-3.5 py-3 text-sm text-[var(--color-ink)] file:mr-3 file:rounded-[var(--radius-button)] file:border-0 file:bg-[var(--color-panel-soft)] file:px-3 file:py-2 file:text-sm file:font-medium file:text-[var(--color-ink)]"
                                >
                            </x-ui.field>

                            @if ($batchFiles)
                                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3 text-sm text-[var(--color-muted)]">
                                    {{ count($batchFiles) }} {{ str('file')->plural(count($batchFiles)) }} selected for upload.
                                </div>
                            @endif

                            <div x-cloak x-show="uploading" class="space-y-2">
                                <div class="flex items-center justify-between text-xs font-medium uppercase tracking-[0.16em] text-[var(--color-muted)]">
                                    <span>Uploading</span>
                                    <span x-text="`${progress}%`"></span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-[var(--color-panel-soft)]">
                                    <div class="h-full bg-[var(--color-accent)] transition-all duration-150" :style="`width: ${progress}%`"></div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <x-ui.field label="Source Type" for="media-batch-source-type" :error="$errors->first('uploadSourceType')" required>
                                <x-ui.select id="media-batch-source-type" wire:model.live="uploadSourceType" :invalid="$errors->has('uploadSourceType')">
                                    <option value="uploaded">Uploaded</option>
                                    <option value="ai_generated">AI generated</option>
                                    <option value="stock">Stock</option>
                                </x-ui.select>
                            </x-ui.field>

                            <x-ui.field label="Source URL" for="media-batch-source-url" :error="$errors->first('uploadSourceUrl')">
                                <x-ui.input id="media-batch-source-url" wire:model.blur="uploadSourceUrl" placeholder="https://example.com/original" :invalid="$errors->has('uploadSourceUrl')" />
                            </x-ui.field>

                            <x-ui.field label="Attribution Text" for="media-batch-attribution-text" :error="$errors->first('uploadAttributionText')">
                                <x-ui.input id="media-batch-attribution-text" wire:model.blur="uploadAttributionText" placeholder="Provider or photographer credit" :invalid="$errors->has('uploadAttributionText')" />
                            </x-ui.field>

                            <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3 text-sm text-[var(--color-muted)]">
                                Batch uploads apply the same source metadata to every selected file.
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-[var(--color-line)] pt-5">
                        <x-ui.button type="button" variant="secondary" wire:click="closeUploadDrawer" x-bind:disabled="uploading">Cancel</x-ui.button>
                        <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="batchFiles,uploadBatch" x-bind:disabled="uploading">
                            <span wire:loading.remove wire:target="uploadBatch" x-show="! uploading">Upload batch</span>
                            <span x-show="uploading">Preparing upload…</span>
                            <span wire:loading wire:target="uploadBatch">Uploading…</span>
                        </x-ui.button>
                    </div>
                </form>
            @endif
        </div>
    </x-ui.drawer>

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
