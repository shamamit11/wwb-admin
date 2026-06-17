<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <x-admin.page-header
            :title="$editingPostId ? 'Edit Post' : 'Create Post'"
            description="Build structured editorial content in the main canvas while keeping status, taxonomy, media, and publishing metadata visible in the side panel."
        />

        <div class="flex flex-wrap items-center gap-3 lg:pt-1">
            <x-ui.button as="a" :href="route('posts.index')" variant="secondary">Back to Posts</x-ui.button>
            <x-ui.button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">{{ $editingPostId ? 'Save Post' : 'Create Post' }}</span>
                <span wire:loading wire:target="save">Saving…</span>
            </x-ui.button>
        </div>
    </div>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    @if ($formError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $formError }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_22rem]">
        <div class="space-y-6">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Core Content</h2>
                    <p class="text-sm text-[var(--color-muted)]">The editor is structured rather than drag-heavy. Save explicitly now; the layout is ready for future autosave work.</p>
                </div>

                <x-ui.field label="Title" for="post-title" :error="$errors->first('title')" required>
                    <x-ui.input id="post-title" wire:model.blur="title" placeholder="Post title" :invalid="$errors->has('title')" />
                </x-ui.field>

                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.field label="Slug" for="post-slug" :error="$errors->first('slug')" hint="Leave blank to let the service generate the slug.">
                        <x-ui.input id="post-slug" wire:model.blur="slug" placeholder="post-slug" :invalid="$errors->has('slug')" />
                    </x-ui.field>

                    <x-ui.field label="Category" for="post-category" :error="$errors->first('categoryId')" required>
                        <x-ui.select id="post-category" wire:model.live="categoryId" :invalid="$errors->has('categoryId')">
                            <option value="">Select category</option>
                            @foreach ($categoryOptions as $category)
                                <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>
                </div>

                <x-ui.field label="Excerpt" for="post-excerpt" :error="$errors->first('excerpt')" hint="Keep this concise. It supports list views and later SEO snippets.">
                    <x-ui.textarea id="post-excerpt" wire:model.blur="excerpt" rows="4" placeholder="Short editorial summary" :invalid="$errors->has('excerpt')" />
                </x-ui.field>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Blocks</h2>
                        <p class="text-sm text-[var(--color-muted)]">Each block turns into the documented `blocks[*]` payload. Non-empty lines in content become content items on save.</p>
                    </div>

                    <x-ui.button type="button" variant="secondary" class="whitespace-nowrap" wire:click="addBlock">Add Block</x-ui.button>
                </div>

                @error('blocks')
                    <p class="text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>
                @enderror

                <div class="space-y-4">
                    @foreach ($blocks as $index => $block)
                        <div wire:key="post-block-{{ $block['key'] }}" class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-5 py-5">
                            <div class="flex flex-col gap-3 border-b border-[var(--color-line)] pb-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-[var(--radius-button)] bg-[var(--color-panel)] px-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">
                                        {{ $block['sortOrder'] }}
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-[var(--color-ink)]">Block {{ $block['sortOrder'] }}</p>
                                        <p class="text-xs text-[var(--color-muted)]">Ordered output is preserved directly in the API payload.</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        wire:click="moveBlockUp({{ $index }})"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)] disabled:cursor-not-allowed disabled:opacity-40"
                                        @disabled($index === 0)
                                        title="Move up"
                                    >
                                        ↑
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="moveBlockDown({{ $index }})"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)] disabled:cursor-not-allowed disabled:opacity-40"
                                        @disabled($index === count($blocks) - 1)
                                        title="Move down"
                                    >
                                        ↓
                                    </button>
                                    <x-ui.button type="button" variant="ghost" class="text-[var(--color-danger-strong)] hover:bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] hover:text-[var(--color-danger-strong)]" wire:click="removeBlock({{ $index }})">
                                        Remove
                                    </x-ui.button>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-5 lg:grid-cols-[13rem_minmax(0,1fr)]">
                                <div class="space-y-5">
                                    <x-ui.field label="Block Type" for="post-block-type-{{ $index }}" :error="$errors->first('blocks.'.$index.'.blockType')" required>
                                        <x-ui.select id="post-block-type-{{ $index }}" wire:model.live="blocks.{{ $index }}.blockType" :invalid="$errors->has('blocks.'.$index.'.blockType')">
                                            @foreach ($blockTypes as $blockType)
                                                <option value="{{ $blockType }}">{{ str($blockType)->headline() }}</option>
                                            @endforeach
                                        </x-ui.select>
                                    </x-ui.field>

                                    <x-ui.field label="Source Template Block ID" for="post-block-source-{{ $index }}" :error="$errors->first('blocks.'.$index.'.sourceTemplateBlockId')" hint="Optional. Preserve linkage when a block originated from a template.">
                                        <x-ui.input id="post-block-source-{{ $index }}" type="number" min="1" wire:model.blur="blocks.{{ $index }}.sourceTemplateBlockId" :invalid="$errors->has('blocks.'.$index.'.sourceTemplateBlockId')" />
                                    </x-ui.field>
                                </div>

                                <x-ui.field
                                    label="Content"
                                    for="post-block-content-{{ $index }}"
                                    :error="$errors->first('blocks.'.$index.'.contentText')"
                                    hint="Use one line per content item when you want multiple API content entries."
                                    required
                                >
                                    <x-ui.textarea id="post-block-content-{{ $index }}" rows="8" wire:model.blur="blocks.{{ $index }}.contentText" placeholder="Write the block content here" :invalid="$errors->has('blocks.'.$index.'.contentText')" />
                                </x-ui.field>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <aside class="space-y-6 xl:sticky xl:top-6 xl:self-start">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Status</h2>
                    <p class="text-sm text-[var(--color-muted)]">Publishing state stays visible while editing.</p>
                </div>

                <x-ui.field label="Status" for="post-status" :error="$errors->first('status')" required>
                    <x-ui.select id="post-status" wire:model.live="status" :invalid="$errors->has('status')">
                        @foreach ($postStatuses as $postStatus)
                            <option value="{{ $postStatus }}">{{ str($postStatus)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Visibility" for="post-visibility" :error="$errors->first('visibility')" required>
                    <x-ui.select id="post-visibility" wire:model.live="visibility" :invalid="$errors->has('visibility')">
                        @foreach ($postVisibilities as $postVisibility)
                            <option value="{{ $postVisibility }}">{{ str($postVisibility)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Scheduled Publish" for="post-scheduled-for" :error="$errors->first('scheduledFor')" hint="Required when the status is scheduled.">
                    <x-ui.input id="post-scheduled-for" type="datetime-local" wire:model.blur="scheduledFor" :invalid="$errors->has('scheduledFor')" />
                </x-ui.field>

                @if ($publishedAt)
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Published At</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $publishedAt }}</p>
                    </div>
                @endif

                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                    <label class="flex items-start gap-3">
                        <input wire:model.live="isFeatured" type="checkbox" class="mt-1 h-4 w-4 rounded border-[var(--color-line-strong)] text-[var(--color-accent)] focus:ring-[var(--color-ring)]">
                        <span>
                            <span class="block text-sm font-semibold text-[var(--color-ink)]">Featured post</span>
                            <span class="mt-1 block text-sm text-[var(--color-muted)]">Use this when the post should receive elevated editorial emphasis.</span>
                        </span>
                    </label>
                </div>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Taxonomy & Media</h2>
                    <p class="text-sm text-[var(--color-muted)]">Keep related entities close to the editor, but secondary to the main writing flow.</p>
                </div>

                <x-ui.field label="Template" for="post-template" :error="$errors->first('templateId')">
                    <x-ui.select id="post-template" wire:model.live="templateId" :invalid="$errors->has('templateId')">
                        <option value="">No template</option>
                        @foreach ($templateOptions as $template)
                            <option value="{{ $template['id'] }}">{{ $template['name'] }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Featured Media" for="post-featured-media" :error="$errors->first('featuredMediaId')">
                    <x-ui.select id="post-featured-media" wire:model.live="featuredMediaId" :invalid="$errors->has('featuredMediaId')">
                        <option value="">No featured media</option>
                        @foreach ($mediaOptions as $asset)
                            <option value="{{ $asset['id'] }}">{{ $asset['name'] }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <div class="space-y-3">
                    <p class="text-sm font-semibold text-[var(--color-ink)]">Tags</p>
                    <div class="space-y-2 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                        @forelse ($tagOptions as $tag)
                            <label class="flex items-center gap-3 text-sm text-[var(--color-ink)]">
                                <input type="checkbox" value="{{ $tag['id'] }}" wire:model.live="tagIds" class="h-4 w-4 rounded border-[var(--color-line-strong)] text-[var(--color-accent)] focus:ring-[var(--color-ring)]">
                                <span>{{ $tag['name'] }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-[var(--color-muted)]">No tags available yet.</p>
                        @endforelse
                    </div>
                    @error('tagIds')
                        <p class="text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>
                    @enderror
                    @error('tagIds.*')
                        <p class="text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">SEO & Metadata</h2>
                    <p class="text-sm text-[var(--color-muted)]">The editor shows the current SEO context without inventing unsupported post payload fields.</p>
                </div>

                <div class="space-y-3 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Canonical URL</p>
                        <p class="mt-2 break-all text-sm text-[var(--color-ink)]">{{ $canonicalUrl ?: 'Set by the service or SEO metadata endpoints later.' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Slug Preview</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">/{{ $slug !== '' ? $slug : 'generated-on-save' }}</p>
                    </div>
                </div>

                <x-ui.field label="Meta JSON Array" for="post-meta-json" :error="$errors->first('metaJson')" hint="Optional JSON array of strings stored in the post meta payload.">
                    <x-ui.textarea id="post-meta-json" rows="5" wire:model.blur="metaJson" placeholder='["editorial-note","campaign:summer"]' :invalid="$errors->has('metaJson')" />
                </x-ui.field>

                <div class="grid gap-3 sm:grid-cols-3 xl:grid-cols-1">
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Content Version</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $contentVersion ?? 'TBC' }}</p>
                    </div>
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Reading Time</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $readingTimeMinutes !== null ? $readingTimeMinutes.' min' : 'TBC' }}</p>
                    </div>
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Word Count</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $wordCount !== null ? number_format($wordCount) : 'TBC' }}</p>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
