<div class="space-y-6">
    @php
        $statusOptions = ['draft', 'published', 'unpublished', 'archived'];

        if ($status === 'scheduled') {
            $statusOptions[] = 'scheduled';
        }
    @endphp

    <x-admin.page-header
        :title="$aiReviewMode ? 'Review Draft' : ($editingPostId ? 'Edit Post' : 'Create Post')"
        :description="$aiReviewMode
            ? 'Review the generated article, refine the copy, confirm metadata, and publish manually when it is ready.'
            : 'Create and edit posts as complete articles. The backend now treats the article body as the source of truth.'"
    >
        <x-ui.button as="a" :href="$aiReviewMode ? route('draft-review.index') : route('posts.index')" variant="secondary">
            {{ $aiReviewMode ? 'Back to Draft Review' : 'Back to Posts' }}
        </x-ui.button>
        <x-ui.button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
            <span wire:loading.remove wire:target="save">{{ $editingPostId ? 'Save Post' : 'Create Post' }}</span>
            <span wire:loading wire:target="save">Saving…</span>
        </x-ui.button>
    </x-admin.page-header>

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

    @if ($aiReviewMode && $isAiGenerated)
        <x-admin.callout title="Manual Review Required" tone="warning">
            Draft generation is automated, but publishing is still manual. Review the article body, FAQ, taxonomy, featured media, and SEO metadata before publishing.
        </x-admin.callout>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_22rem]">
        <div class="space-y-6">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Article</h2>
                    <p class="text-sm text-[var(--color-muted)]">Keep the editorial flow article-first. The backend stores the full article body instead of ordered content blocks.</p>
                </div>

                <x-ui.field label="Title" for="post-title" :error="$errors->first('title')" required>
                    <x-ui.input id="post-title" wire:model.blur="title" :invalid="$errors->has('title')" placeholder="Article title" />
                </x-ui.field>

                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.field label="Slug" for="post-slug" :error="$errors->first('slug')" hint="Leave blank to let the backend generate the slug.">
                        <x-ui.input id="post-slug" wire:model.blur="slug" :invalid="$errors->has('slug')" placeholder="article-slug" />
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

                <x-ui.field label="Short Description" for="post-short-description" :error="$errors->first('shortDescription')" hint="Used for concise editorial summaries and list views.">
                    <x-ui.textarea id="post-short-description" wire:model.blur="shortDescription" rows="3" :invalid="$errors->has('shortDescription')" placeholder="Short summary of the article" />
                </x-ui.field>

                <x-ui.field label="Description" for="post-description" :error="$errors->first('description')" hint="Use this for a longer editorial description or production notes that still belong to the post payload.">
                    <x-ui.textarea id="post-description" wire:model.blur="description" rows="4" :invalid="$errors->has('description')" placeholder="Longer description" />
                </x-ui.field>

                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-[var(--color-ink)]" for="post-article-editor">Article Body</label>
                        <p class="mt-1 text-sm text-[var(--color-muted)]">Quill drives the canonical article editing surface. Inline images are inserted from the media library or uploaded through the backend media API so the editor can preserve `data-media-id` for post-media syncing.</p>
                    </div>

                    <div wire:ignore class="overflow-hidden rounded-[var(--radius-button)] border border-[var(--color-line)] bg-white">
                        <div
                            id="post-article-editor"
                            data-quill-editor
                            data-quill-html-field="#post-full-article-html"
                            data-quill-delta-field="#post-full-article-delta"
                            data-quill-initial-html-source="#post-article-editor-initial-html"
                            data-quill-media-library-source="#post-inline-media-options"
                            data-quill-upload-url="{{ route('posts.inline-media.store') }}"
                            class="min-h-[28rem]"
                        ></div>
                    </div>

                    <script id="post-article-editor-initial-html" type="application/json">@json($articleEditorInitialHtml)</script>
                    <script id="post-inline-media-options" type="application/json">@json($mediaOptions)</script>

                    <textarea id="post-full-article-html" wire:model="fullArticleHtml" class="hidden"></textarea>
                    <textarea id="post-full-article-delta" wire:model="fullArticleDelta" class="hidden"></textarea>

                    @error('fullArticleHtml')
                        <p class="text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>
                    @enderror
                    @error('fullArticleDelta')
                        <p class="text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">FAQ</h2>
                        <p class="text-sm text-[var(--color-muted)]">Keep FAQs structured in question and answer pairs. Empty rows are ignored on save.</p>
                    </div>

                    <x-ui.button type="button" size="sm" variant="secondary" wire:click="addFaqItem">Add FAQ</x-ui.button>
                </div>

                <div class="space-y-4">
                    @foreach ($faq as $index => $item)
                        <div wire:key="faq-item-{{ $index }}" class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <p class="text-sm font-semibold text-[var(--color-ink)]">FAQ {{ $index + 1 }}</p>
                                <button type="button" wire:click="removeFaqItem({{ $index }})" class="text-sm text-[var(--color-danger-strong)]">Remove</button>
                            </div>

                            <div class="mt-4 space-y-4">
                                <x-ui.field label="Question" for="faq-question-{{ $index }}" :error="$errors->first('faq.'.$index.'.question')">
                                    <x-ui.input id="faq-question-{{ $index }}" wire:model.blur="faq.{{ $index }}.question" :invalid="$errors->has('faq.'.$index.'.question')" />
                                </x-ui.field>

                                <x-ui.field label="Answer" for="faq-answer-{{ $index }}" :error="$errors->first('faq.'.$index.'.answer')">
                                    <x-ui.textarea id="faq-answer-{{ $index }}" wire:model.blur="faq.{{ $index }}.answer" rows="4" :invalid="$errors->has('faq.'.$index.'.answer')" />
                                </x-ui.field>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">SEO Metadata</h2>
                    <p class="text-sm text-[var(--color-muted)]">Review and save SEO metadata separately so contract-driven validation stays clear.</p>
                </div>

                @if ($seoLoadError)
                    <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-warning)_22%,white)] bg-[color-mix(in_srgb,var(--color-warning)_8%,white)] px-4 py-3 text-sm text-[var(--color-warning-strong)]">
                        {{ $seoLoadError }}
                    </div>
                @endif

                @if ($seoFormError)
                    <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                        {{ $seoFormError }}
                    </div>
                @endif

                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.field label="Meta Title" for="seo-meta-title" :error="$errors->first('metaTitle')">
                        <x-ui.input id="seo-meta-title" wire:model.blur="metaTitle" :invalid="$errors->has('metaTitle')" />
                    </x-ui.field>

                    <x-ui.field label="Focus Keyword" for="seo-focus-keyword" :error="$errors->first('focusKeyword')">
                        <x-ui.input id="seo-focus-keyword" wire:model.blur="focusKeyword" :invalid="$errors->has('focusKeyword')" />
                    </x-ui.field>
                </div>

                <x-ui.field label="Meta Description" for="seo-meta-description" :error="$errors->first('metaDescription')">
                    <x-ui.textarea id="seo-meta-description" wire:model.blur="metaDescription" rows="3" :invalid="$errors->has('metaDescription')" />
                </x-ui.field>

                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.field label="Canonical URL" for="seo-canonical-url" :error="$errors->first('canonicalUrl')">
                        <x-ui.input id="seo-canonical-url" wire:model.blur="canonicalUrl" :invalid="$errors->has('canonicalUrl')" placeholder="https://example.com/article" />
                    </x-ui.field>

                    <x-ui.field label="Open Graph Image" for="seo-og-image" :error="$errors->first('ogImageMediaId')">
                        <x-ui.select id="seo-og-image" wire:model.live="ogImageMediaId" :invalid="$errors->has('ogImageMediaId')">
                            <option value="">No image selected</option>
                            @foreach ($mediaOptions as $asset)
                                <option value="{{ $asset['id'] }}">{{ $asset['name'] }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.field label="Open Graph Title" for="seo-og-title" :error="$errors->first('ogTitle')">
                        <x-ui.input id="seo-og-title" wire:model.blur="ogTitle" :invalid="$errors->has('ogTitle')" />
                    </x-ui.field>

                    <x-ui.field label="Open Graph Description" for="seo-og-description" :error="$errors->first('ogDescription')">
                        <x-ui.textarea id="seo-og-description" wire:model.blur="ogDescription" rows="3" :invalid="$errors->has('ogDescription')" />
                    </x-ui.field>
                </div>

                <div class="flex flex-wrap items-center gap-6">
                    <label class="inline-flex items-center gap-3 text-sm text-[var(--color-ink)]">
                        <input type="checkbox" wire:model.live="robotsIndex" class="h-4 w-4 rounded border-[var(--color-line-strong)] text-[var(--color-accent)] focus:ring-[var(--color-ring)]" />
                        Index this article
                    </label>
                    <label class="inline-flex items-center gap-3 text-sm text-[var(--color-ink)]">
                        <input type="checkbox" wire:model.live="robotsFollow" class="h-4 w-4 rounded border-[var(--color-line-strong)] text-[var(--color-accent)] focus:ring-[var(--color-ring)]" />
                        Follow links
                    </label>
                </div>

                @if ($editingPostId)
                    <div class="flex items-center gap-3">
                        <x-ui.button type="button" wire:click="saveSeo" wire:loading.attr="disabled" wire:target="saveSeo">Save SEO Metadata</x-ui.button>
                    </div>
                @else
                    <x-admin.callout title="Save The Post First">
                        SEO metadata is saved against the persisted post record, so create the post before saving SEO fields.
                    </x-admin.callout>
                @endif
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Provenance</h2>
                    <p class="text-sm text-[var(--color-muted)]">Useful backend-origin details for editorial review.</p>
                </div>

                <div class="space-y-3 text-sm text-[var(--color-muted)]">
                    <div class="flex items-center justify-between gap-3">
                        <span>AI Generated</span>
                        <x-ui.badge :tone="$isAiGenerated ? 'warning' : 'muted'">{{ $isAiGenerated ? 'Yes' : 'No' }}</x-ui.badge>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span>Source Topic</span>
                        <span>{{ $sourceContentTopicId ?: 'None' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span>AI Job</span>
                        <span>{{ $generatedByAiJobId ?: 'None' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span>Generated By</span>
                        <span>{{ $generatedBy ?: 'Unknown' }}</span>
                    </div>
                </div>

                <x-ui.field label="Meta Payload" for="post-meta-json" :error="$errors->first('metaJson')" hint="Optional raw JSON carried through to the backend. Leave blank unless you need to preserve service metadata.">
                    <x-ui.textarea id="post-meta-json" wire:model.blur="metaJson" rows="10" class="font-mono text-xs leading-6" :invalid="$errors->has('metaJson')" placeholder='{"source":"service"}' />
                </x-ui.field>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">SEO Diagnostics</h2>
                    <p class="text-sm text-[var(--color-muted)]">Read-only insights from the service-side SEO endpoints.</p>
                </div>

                @if ($seoScoreLoadError)
                    <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-warning)_22%,white)] bg-[color-mix(in_srgb,var(--color-warning)_8%,white)] px-4 py-3 text-sm text-[var(--color-warning-strong)]">
                        {{ $seoScoreLoadError }}
                    </div>
                @endif

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">SEO Score</p>
                        <p class="mt-2 text-2xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">{{ $seoScoreValue ?? 'N/A' }}</p>
                        <p class="mt-1 text-sm text-[var(--color-muted)]">{{ $seoScoreGrade ?: 'Not graded yet' }}</p>
                    </div>

                    @foreach ($seoSubscores as $subscore)
                        <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                            <p class="text-sm font-medium text-[var(--color-ink)]">{{ $subscore['label'] }}</p>
                            <p class="mt-1 text-sm text-[var(--color-muted)]">
                                {{ $subscore['score'] ?? 'N/A' }}@if($subscore['max_score']) / {{ $subscore['max_score'] }} @endif
                                @if ($subscore['suggestion_count'] !== null)
                                    · {{ $subscore['suggestion_count'] }} suggestions
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>

                @if ($seoRecommendations !== [])
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-[var(--color-ink)]">Recommendations</p>
                        <ul class="space-y-2 text-sm text-[var(--color-muted)]">
                            @foreach ($seoRecommendations as $recommendation)
                                <li>{{ $recommendation }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($seoSchemaLoadError)
                    <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-warning)_22%,white)] bg-[color-mix(in_srgb,var(--color-warning)_8%,white)] px-4 py-3 text-sm text-[var(--color-warning-strong)]">
                        {{ $seoSchemaLoadError }}
                    </div>
                @endif

                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4 text-sm text-[var(--color-muted)]">
                    <p><span class="font-medium text-[var(--color-ink)]">Schema Context:</span> {{ $schemaSummary['context'] ?? 'Unavailable' }}</p>
                    <p class="mt-1"><span class="font-medium text-[var(--color-ink)]">Graph Items:</span> {{ $schemaSummary['graph_count'] ?? 0 }}</p>
                    <p class="mt-1"><span class="font-medium text-[var(--color-ink)]">Graph Types:</span> {{ $schemaSummary['graph_types'] !== [] ? implode(', ', $schemaSummary['graph_types']) : 'None' }}</p>
                </div>

                @if ($prettySchema !== '')
                    <pre class="max-h-72 overflow-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4 text-xs leading-6 text-[var(--color-muted)]">{{ $prettySchema }}</pre>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-base font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Publishing</h2>
                    <p class="text-sm text-[var(--color-muted)]">Manual publish remains the only publishing path.</p>
                </div>

                <div class="space-y-4">
                    <x-ui.field label="Status" for="post-status" :error="$errors->first('status')">
                        <x-ui.select id="post-status" wire:model.live="status" :invalid="$errors->has('status')">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option }}">
                                    {{ $option === 'scheduled' ? 'Legacy Scheduled' : str($option)->headline() }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Visibility" for="post-visibility" :error="$errors->first('visibility')">
                        <x-ui.select id="post-visibility" wire:model.live="visibility" :invalid="$errors->has('visibility')">
                            @foreach (['public', 'private', 'internal'] as $option)
                                <option value="{{ $option }}">{{ str($option)->headline() }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Published At" for="post-published-at" :error="$errors->first('publishedAt')" hint="Optional timestamp stored in the post payload.">
                        <x-ui.input id="post-published-at" type="datetime-local" wire:model.blur="publishedAt" :invalid="$errors->has('publishedAt')" />
                    </x-ui.field>
                </div>

                @if ($editingPostId)
                    <div class="flex flex-wrap gap-2">
                        <x-ui.button type="button" size="sm" wire:click="openActionDialog('publish')">Publish</x-ui.button>
                        <x-ui.button type="button" size="sm" variant="secondary" wire:click="openActionDialog('unpublish')">Unpublish</x-ui.button>
                        <x-ui.button type="button" size="sm" variant="destructive" wire:click="openActionDialog('delete')">Delete</x-ui.button>
                    </div>
                @endif
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-base font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Media And Taxonomy</h2>
                    <p class="text-sm text-[var(--color-muted)]">Keep featured media and article labeling lightweight.</p>
                </div>

                <x-ui.field label="Featured Media" for="post-featured-media" :error="$errors->first('featuredMediaId')">
                    <x-ui.select id="post-featured-media" wire:model.live="featuredMediaId" :invalid="$errors->has('featuredMediaId')">
                        <option value="">No media selected</option>
                        @foreach ($mediaOptions as $asset)
                            <option value="{{ $asset['id'] }}">{{ $asset['name'] }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                @if ($selectedFeaturedMedia)
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-3">
                        <img src="{{ $selectedFeaturedMedia['url'] }}" alt="" class="h-36 w-full rounded-[var(--radius-button)] object-cover" />
                        <p class="mt-3 text-sm font-medium text-[var(--color-ink)]">{{ $selectedFeaturedMedia['name'] }}</p>
                        @if ($selectedFeaturedMedia['alt_text'])
                            <p class="mt-1 text-sm text-[var(--color-muted)]">{{ $selectedFeaturedMedia['alt_text'] }}</p>
                        @endif
                    </div>
                @endif

                <div class="space-y-3">
                    <p class="text-sm font-medium text-[var(--color-ink)]">Tags</p>
                    <div class="grid gap-2">
                        @foreach ($tagOptions as $tag)
                            <label wire:key="tag-option-{{ $tag['id'] }}" class="inline-flex items-center gap-3 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-3 py-2 text-sm text-[var(--color-ink)]">
                                <input type="checkbox" value="{{ $tag['id'] }}" wire:model.live="tagIds" class="h-4 w-4 rounded border-[var(--color-line-strong)] text-[var(--color-accent)] focus:ring-[var(--color-ring)]" />
                                {{ $tag['name'] }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <x-admin.confirm-dialog
        :open="$actionDialogOpen"
        :title="$actionConfig['title']"
        :description="$actionConfig['description']"
        :destructive="$actionConfig['destructive']"
    >
        <div class="space-y-4">
            @if ($actionError)
                <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                    {{ $actionError }}
                </div>
            @endif

            <p class="text-sm text-[var(--color-muted)]">
                {{ $actionMode === 'delete' ? 'This cannot be undone.' : 'The current reviewed article will be updated in the backend.' }}
            </p>
        </div>

        <x-slot:cancel>
            <x-ui.button type="button" variant="secondary" wire:click="closeActionDialog" wire:loading.attr="disabled" wire:target="closeActionDialog,executeAction">
                Cancel
            </x-ui.button>
        </x-slot:cancel>

        <x-slot:confirm>
            <x-ui.button :variant="$actionConfig['destructive'] ? 'destructive' : 'primary'" type="button" wire:click="executeAction" wire:loading.attr="disabled" wire:target="executeAction">
                {{ $actionConfig['confirm'] }}
            </x-ui.button>
        </x-slot:confirm>
    </x-admin.confirm-dialog>
</div>
