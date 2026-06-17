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

                                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">How this block saves</p>
                                        <p class="mt-2 text-sm leading-6 text-[var(--color-muted)]">{{ $blockUi[$index]['contentHint'] }}</p>
                                    </div>

                                    @if ($blockUi[$index]['sourceTemplateBlockId'] !== '')
                                        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-accent)_18%,white)] bg-[color-mix(in_srgb,var(--color-accent)_8%,white)] px-4 py-4">
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-accent-strong)]">Template Linkage</p>
                                            <p class="mt-2 text-sm font-semibold text-[var(--color-ink)]">Template block #{{ $blockUi[$index]['sourceTemplateBlockId'] }}</p>
                                            <p class="mt-1 text-sm text-[var(--color-muted)]">{{ $blockUi[$index]['sourceTemplateHint'] }}</p>
                                        </div>
                                    @endif
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

                                    <x-ui.field
                                        :label="$blockUi[$index]['contentLabel']"
                                        for="post-block-content-{{ $index }}"
                                        :error="$errors->first('blocks.'.$index.'.contentText')"
                                        hint="The editor keeps this contract-aligned: non-empty lines save as ordered content items."
                                        required
                                    >
                                        <x-ui.textarea
                                            id="post-block-content-{{ $index }}"
                                            rows="8"
                                            wire:model.blur="blocks.{{ $index }}.contentText"
                                            :placeholder="$blockUi[$index]['placeholder']"
                                            :invalid="$errors->has('blocks.'.$index.'.contentText')"
                                        />
                                    </x-ui.field>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">SEO & Metadata</h2>
                    <p class="text-sm text-[var(--color-muted)]">This panel edits per-entity SEO metadata only. Sitewide defaults are not shown because the service does not expose them here.</p>
                </div>

                @if ($seoLoadError)
                    <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                        {{ $seoLoadError }}
                    </div>
                @endif

                @if ($seoFormError)
                    <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                        {{ $seoFormError }}
                    </div>
                @endif

                @if ($editingPostId)
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-2">
                        <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">SEO Score</p>
                            <div class="mt-3 flex items-center gap-3">
                                <x-admin.seo-score-badge :score="$seoScoreValue" />
                                @if ($seoScoreGrade)
                                    <span class="text-sm font-medium text-[var(--color-muted)]">{{ $seoScoreGrade }}</span>
                                @endif
                            </div>

                            @if ($seoScoreLoadError)
                                <p class="mt-3 text-sm text-[var(--color-danger-strong)]">{{ $seoScoreLoadError }}</p>
                            @elseif ($seoRecommendations !== [])
                                <p class="mt-3 text-sm text-[var(--color-muted)]">{{ $seoRecommendations[0] }}</p>
                            @else
                                <p class="mt-3 text-sm text-[var(--color-muted)]">No score recommendations were returned for this post.</p>
                            @endif
                        </div>

                        <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Schema Output</p>
                            @if ($seoSchemaLoadError)
                                <p class="mt-3 text-sm text-[var(--color-danger-strong)]">{{ $seoSchemaLoadError }}</p>
                            @elseif ($seoSchemaSummary['graph_count'] > 0 || $seoSchemaSummary['context'])
                                <p class="mt-2 text-sm font-semibold text-[var(--color-ink)]">{{ $seoSchemaSummary['graph_count'] }} graph {{ str('item')->plural($seoSchemaSummary['graph_count']) }}</p>
                                <p class="mt-2 text-sm text-[var(--color-muted)]">
                                    {{ $seoSchemaSummary['graph_types'] !== [] ? implode(', ', $seoSchemaSummary['graph_types']) : 'Schema types are present but could not be summarized.' }}
                                </p>
                            @else
                                <p class="mt-3 text-sm text-[var(--color-muted)]">No generated schema payload is currently available.</p>
                            @endif
                        </div>
                    </div>

                    @if ($seoScoreSubscores !== [])
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($seoScoreSubscores as $subscore)
                                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">{{ $subscore['label'] }}</p>
                                    <p class="mt-2 text-sm font-semibold text-[var(--color-ink)]">
                                        {{ $subscore['score'] ?? 'TBC' }}
                                        @if ($subscore['max_score'] !== null)
                                            <span class="font-medium text-[var(--color-muted)]">/ {{ $subscore['max_score'] }}</span>
                                        @endif
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($seoSchemaJson !== '')
                        <div class="overflow-hidden rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)]">
                            <div class="border-b border-[var(--color-line)] px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Generated JSON-LD</p>
                            </div>
                            <pre class="max-h-[18rem] overflow-auto px-4 py-4 text-xs leading-6 text-[var(--color-ink)]">{{ $seoSchemaJson }}</pre>
                        </div>
                    @endif
                @endif

                <div class="grid gap-3 lg:grid-cols-2">
                    <div class="space-y-3 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Slug Preview</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">/{{ $slug !== '' ? $slug : 'generated-on-save' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Canonical URL</p>
                            <p class="mt-2 break-all text-sm text-[var(--color-ink)]">{{ $canonicalUrl !== '' ? $canonicalUrl : 'Save the post first, then set a canonical URL if needed.' }}</p>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
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
                </div>

                @if ($editingPostId)
                    <div class="flex items-center justify-end">
                        <x-ui.button type="button" size="sm" wire:click="saveSeo" wire:loading.attr="disabled" wire:target="saveSeo">
                            <span wire:loading.remove wire:target="saveSeo">Update SEO</span>
                            <span wire:loading wire:target="saveSeo">Saving…</span>
                        </x-ui.button>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-2">
                        <x-ui.field label="Meta Title" for="post-meta-title" :error="$errors->first('metaTitle')" hint="Keep it concise and specific to the post.">
                            <x-ui.input id="post-meta-title" wire:model.blur="metaTitle" placeholder="SEO title" :invalid="$errors->has('metaTitle')" />
                        </x-ui.field>

                        <x-ui.field label="Focus Keyword" for="post-focus-keyword" :error="$errors->first('focusKeyword')" hint="Editorial guidance only; this stays per entity.">
                            <x-ui.input id="post-focus-keyword" wire:model.blur="focusKeyword" placeholder="primary topic phrase" :invalid="$errors->has('focusKeyword')" />
                        </x-ui.field>
                    </div>

                    <x-ui.field label="Meta Description" for="post-meta-description" :error="$errors->first('metaDescription')" hint="Aim for a compact search snippet.">
                        <x-ui.textarea id="post-meta-description" rows="4" wire:model.blur="metaDescription" placeholder="Search description" :invalid="$errors->has('metaDescription')" />
                    </x-ui.field>

                    <x-ui.field label="Canonical URL" for="post-canonical-url" :error="$errors->first('canonicalUrl')" hint="Use a full absolute URL when canonicalization is needed.">
                        <x-ui.input id="post-canonical-url" wire:model.blur="canonicalUrl" placeholder="https://example.com/posts/post-slug" :invalid="$errors->has('canonicalUrl')" />
                    </x-ui.field>

                    <div class="grid gap-3 lg:grid-cols-2">
                        <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                            <label class="flex items-start gap-3">
                                <input wire:model.live="robotsIndex" type="checkbox" class="mt-1 h-4 w-4 rounded border-[var(--color-line-strong)] text-[var(--color-accent)] focus:ring-[var(--color-ring)]">
                                <span>
                                    <span class="block text-sm font-semibold text-[var(--color-ink)]">Allow indexing</span>
                                    <span class="mt-1 block text-sm text-[var(--color-muted)]">Turn this off only when the post should not appear in search indexes.</span>
                                </span>
                            </label>
                        </div>

                        <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                            <label class="flex items-start gap-3">
                                <input wire:model.live="robotsFollow" type="checkbox" class="mt-1 h-4 w-4 rounded border-[var(--color-line-strong)] text-[var(--color-accent)] focus:ring-[var(--color-ring)]">
                                <span>
                                    <span class="block text-sm font-semibold text-[var(--color-ink)]">Allow link following</span>
                                    <span class="mt-1 block text-sm text-[var(--color-muted)]">Keep this on unless the service should mark outbound link crawling as disallowed.</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-2">
                        <x-ui.field label="OpenGraph Title" for="post-og-title" :error="$errors->first('ogTitle')" hint="Optional override for social share cards.">
                            <x-ui.input id="post-og-title" wire:model.blur="ogTitle" placeholder="Social share title" :invalid="$errors->has('ogTitle')" />
                        </x-ui.field>

                        <x-ui.field label="OpenGraph Image" for="post-og-image" :error="$errors->first('ogImageMediaId')" hint="Choose a media asset to use for social previews.">
                            <x-ui.select id="post-og-image" wire:model.live="ogImageMediaId" :invalid="$errors->has('ogImageMediaId')">
                                <option value="">No OpenGraph image override</option>
                                @foreach ($mediaOptions as $asset)
                                    <option value="{{ $asset['id'] }}">{{ $asset['name'] }}</option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>
                    </div>

                    <x-ui.field label="OpenGraph Description" for="post-og-description" :error="$errors->first('ogDescription')" hint="Optional override for social share descriptions.">
                        <x-ui.textarea id="post-og-description" rows="4" wire:model.blur="ogDescription" placeholder="Social share description" :invalid="$errors->has('ogDescription')" />
                    </x-ui.field>
                @else
                    <div class="rounded-[var(--radius-button)] border border-dashed border-[var(--color-line-strong)] bg-[var(--color-panel-soft)] px-4 py-4">
                        <p class="text-sm font-semibold text-[var(--color-ink)]">SEO editing starts after the post exists.</p>
                        <p class="mt-1 text-sm text-[var(--color-muted)]">Create the post first, then return here to manage per-entity metadata through the SEO service endpoints.</p>
                    </div>
                @endif

                <x-ui.field label="Meta JSON Array" for="post-meta-json" :error="$errors->first('metaJson')" hint="Optional JSON array of strings stored in the post meta payload.">
                    <x-ui.textarea id="post-meta-json" rows="5" wire:model.blur="metaJson" placeholder='["editorial-note","campaign:summer"]' :invalid="$errors->has('metaJson')" />
                </x-ui.field>
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

                @if ($editingPostId)
                    <div class="space-y-3 border-t border-[var(--color-line)] pt-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">State Actions</p>
                        <div class="flex flex-wrap gap-2">
                            @if ($status !== 'published' && $status !== 'archived')
                                <x-ui.button type="button" size="sm" variant="secondary" wire:click="openActionDialog('publish')">Publish</x-ui.button>
                            @endif

                            @if (in_array($status, ['draft', 'unpublished', 'scheduled'], true))
                                <x-ui.button type="button" size="sm" variant="secondary" wire:click="openActionDialog('schedule')">Schedule</x-ui.button>
                            @endif

                            @if (in_array($status, ['published', 'scheduled'], true))
                                <x-ui.button type="button" size="sm" variant="secondary" wire:click="openActionDialog('unpublish')">Unpublish</x-ui.button>
                            @endif

                            <x-ui.button type="button" size="sm" variant="ghost" class="text-[var(--color-danger-strong)] hover:bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] hover:text-[var(--color-danger-strong)]" wire:click="openActionDialog('delete')">Delete</x-ui.button>
                        </div>
                    </div>
                @endif
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

                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-[var(--color-ink)]">Featured Media</p>
                            <p class="mt-1 text-sm text-[var(--color-muted)]">Select an asset visually instead of using a raw ID list.</p>
                        </div>

                        <x-ui.button type="button" variant="secondary" size="sm" wire:click="openMediaPicker">
                            {{ $selectedFeaturedMedia ? 'Change' : 'Choose' }}
                        </x-ui.button>
                    </div>

                    @if ($selectedFeaturedMedia)
                        <div class="overflow-hidden rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)]">
                            <div class="aspect-[16/9] bg-[var(--color-panel)]">
                                @if ($selectedFeaturedMedia['url'])
                                    <img
                                        src="{{ $selectedFeaturedMedia['url'] }}"
                                        alt="{{ $selectedFeaturedMedia['alt_text'] ?: $selectedFeaturedMedia['name'] }}"
                                        class="h-full w-full object-cover"
                                    >
                                @else
                                    <div class="flex h-full items-center justify-center text-sm text-[var(--color-muted)]">No preview available</div>
                                @endif
                            </div>
                            <div class="flex items-start justify-between gap-3 px-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-[var(--color-ink)]">{{ $selectedFeaturedMedia['name'] }}</p>
                                    <p class="mt-1 text-xs text-[var(--color-muted)]">
                                        {{ $selectedFeaturedMedia['alt_text'] !== '' ? $selectedFeaturedMedia['alt_text'] : 'No alt text yet' }}
                                    </p>
                                </div>

                                <x-ui.button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="shrink-0 text-[var(--color-danger-strong)] hover:bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] hover:text-[var(--color-danger-strong)]"
                                    wire:click="clearFeaturedMedia"
                                >
                                    Remove
                                </x-ui.button>
                            </div>
                        </div>
                    @else
                        <button
                            type="button"
                            wire:click="openMediaPicker"
                            class="flex w-full items-center justify-between gap-4 rounded-[var(--radius-button)] border border-dashed border-[var(--color-line-strong)] bg-[var(--color-panel-soft)] px-4 py-4 text-left transition-colors hover:border-[var(--color-accent)] hover:bg-[var(--color-panel)]"
                        >
                            <span>
                                <span class="block text-sm font-semibold text-[var(--color-ink)]">No featured media selected</span>
                                <span class="mt-1 block text-sm text-[var(--color-muted)]">Open the media picker to choose a thumbnail or hero image.</span>
                            </span>
                            <span class="text-sm font-medium text-[var(--color-accent)]">Browse</span>
                        </button>
                    @endif

                    @error('featuredMediaId')
                        <p class="text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>
                    @enderror
                </div>

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

        </aside>
    </div>

    <x-ui.dialog
        :open="$mediaPickerOpen"
        title="Select featured media"
        description="Choose from the loaded media library assets and apply the selection directly to this post."
        maxWidth="lg"
    >
        <div class="space-y-5">
            <x-ui.field label="Search media" for="post-media-search">
                <x-ui.input
                    id="post-media-search"
                    wire:model.live.debounce.250ms="mediaSearch"
                    placeholder="Search by filename or alt text"
                />
            </x-ui.field>

            @if ($visibleMediaOptions !== [])
                <div class="grid max-h-[26rem] gap-4 overflow-y-auto pr-1 sm:grid-cols-2">
                    @foreach ($visibleMediaOptions as $asset)
                        <button
                            type="button"
                            wire:key="featured-media-option-{{ $asset['id'] }}"
                            wire:click="selectFeaturedMedia({{ $asset['id'] }})"
                            class="@class([
                                'overflow-hidden rounded-[var(--radius-button)] border bg-[var(--color-panel-soft)] text-left transition-all hover:-translate-y-0.5 hover:border-[var(--color-accent)] hover:bg-[var(--color-panel)]',
                                'border-[var(--color-accent)] ring-1 ring-[color-mix(in_srgb,var(--color-accent)_22%,white)]' => (string) $featuredMediaId === (string) $asset['id'],
                                'border-[var(--color-line)]' => (string) $featuredMediaId !== (string) $asset['id'],
                            ])"
                        >
                            <div class="aspect-[16/10] bg-[var(--color-panel)]">
                                @if ($asset['url'])
                                    <img
                                        src="{{ $asset['url'] }}"
                                        alt="{{ $asset['alt_text'] ?: $asset['name'] }}"
                                        class="h-full w-full object-cover"
                                    >
                                @else
                                    <div class="flex h-full items-center justify-center text-sm text-[var(--color-muted)]">No preview</div>
                                @endif
                            </div>

                            <div class="space-y-2 px-4 py-3">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="min-w-0 truncate text-sm font-semibold text-[var(--color-ink)]">{{ $asset['name'] }}</p>
                                    @if ((string) $featuredMediaId === (string) $asset['id'])
                                        <span class="inline-flex shrink-0 rounded-[var(--radius-button)] bg-[color-mix(in_srgb,var(--color-accent)_12%,white)] px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--color-accent-strong)]">Selected</span>
                                    @endif
                                </div>

                                <p class="text-sm text-[var(--color-muted)]">
                                    {{ $asset['alt_text'] !== '' ? $asset['alt_text'] : 'No alt text yet' }}
                                </p>
                            </div>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="rounded-[var(--radius-button)] border border-dashed border-[var(--color-line)] bg-[var(--color-panel-soft)] px-5 py-10 text-center">
                    <p class="text-sm font-semibold text-[var(--color-ink)]">No media matches this search.</p>
                    <p class="mt-2 text-sm text-[var(--color-muted)]">Try a different filename or alt-text term.</p>
                </div>
            @endif
        </div>

        <x-slot:actions>
            @if ($selectedFeaturedMedia)
                <x-ui.button type="button" variant="ghost" class="mr-auto text-[var(--color-danger-strong)] hover:bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] hover:text-[var(--color-danger-strong)]" wire:click="clearFeaturedMedia">
                    Remove selection
                </x-ui.button>
            @endif
            <x-ui.button type="button" variant="secondary" wire:click="closeMediaPicker">Close</x-ui.button>
        </x-slot:actions>
    </x-ui.dialog>

    <x-ui.dialog
        :open="$actionDialogOpen"
        :title="$actionConfig['title']"
        :description="$actionConfig['description']"
        :tone="$actionConfig['tone']"
        maxWidth="lg"
    >
        <div class="space-y-5">
            @if ($actionError)
                <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                    {{ $actionError }}
                </div>
            @endif

            <p class="text-sm leading-6 text-[var(--color-muted)]">
                @if ($actionMode === 'publish')
                    {{ $actionConfig['body'] }} <span class="font-semibold text-[var(--color-ink)]">{{ $actionPostTitle }}</span> now?
                @elseif ($actionMode === 'schedule')
                    {{ $actionConfig['body'] }} a publish time for <span class="font-semibold text-[var(--color-ink)]">{{ $actionPostTitle }}</span>.
                @elseif ($actionMode === 'unpublish')
                    {{ $actionConfig['body'] }} <span class="font-semibold text-[var(--color-ink)]">{{ $actionPostTitle }}</span> out of the live state?
                @else
                    {{ $actionConfig['body'] }} <span class="font-semibold text-[var(--color-ink)]">{{ $actionPostTitle }}</span>? This is a destructive action and should be confirmed carefully.
                @endif
            </p>

            @if ($actionMode === 'schedule')
                <x-ui.field label="Publish At" for="editor-post-scheduled-for" :error="$errors->first('scheduledFor')" required>
                    <x-ui.input
                        id="editor-post-scheduled-for"
                        type="datetime-local"
                        wire:model.defer="scheduledFor"
                        :invalid="$errors->has('scheduledFor')"
                    />
                </x-ui.field>
            @endif
        </div>

        <x-slot:actions>
            <x-ui.button type="button" variant="secondary" wire:click="closeActionDialog">Cancel</x-ui.button>
            <x-ui.button
                type="button"
                :variant="$actionConfig['tone'] === 'destructive' ? 'destructive' : 'primary'"
                wire:click="executeAction"
                wire:loading.attr="disabled"
                wire:target="executeAction"
            >
                <span wire:loading.remove wire:target="executeAction">{{ $actionConfig['confirm'] }}</span>
                <span wire:loading wire:target="executeAction">Saving…</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.dialog>
</div>
