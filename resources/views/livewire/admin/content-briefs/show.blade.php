<div class="space-y-6">
    <x-admin.page-header
        eyebrow="Content Brief Review"
        :title="$title !== '' ? $title : 'Content brief detail'"
        description="Review and refine the structured brief before approval, then trigger blog draft generation through the Service workflow."
    >
        <x-ui.button as="a" :href="route('content-briefs.index')" variant="secondary">Back to Content Briefs</x-ui.button>
    </x-admin.page-header>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    @if ($notFound)
        <x-ui.empty-state
            title="Content brief not found"
            message="The requested brief is no longer available from the service API."
        />
    @else
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_minmax(20rem,1fr)]">
            <div class="space-y-6">
                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Brief Overview</p>
                            <h2 class="mt-2 text-xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">Editorial Structure</h2>
                        </div>

                        <x-admin.status-badge :status="$status" />
                    </div>

                    @if ($formError)
                        <div class="mt-5 rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                            {{ $formError }}
                        </div>
                    @endif

                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        <x-ui.field label="Title" for="brief-title" :error="$errors->first('title')">
                            <x-ui.input id="brief-title" wire:model.blur="title" :invalid="$errors->has('title')" />
                        </x-ui.field>

                        <x-ui.field label="Slug" for="brief-slug" :error="$errors->first('slug')">
                            <x-ui.input id="brief-slug" wire:model.blur="slug" :invalid="$errors->has('slug')" />
                        </x-ui.field>

                        <x-ui.field label="Meta Title" for="brief-meta-title" :error="$errors->first('metaTitle')">
                            <x-ui.input id="brief-meta-title" wire:model.blur="metaTitle" :invalid="$errors->has('metaTitle')" />
                        </x-ui.field>

                        <x-ui.field label="Primary Keyword" for="brief-primary-keyword" :error="$errors->first('primaryKeyword')">
                            <x-ui.input id="brief-primary-keyword" wire:model.blur="primaryKeyword" :invalid="$errors->has('primaryKeyword')" />
                        </x-ui.field>

                        <x-ui.field label="Search Intent" for="brief-search-intent" :error="$errors->first('searchIntent')">
                            <x-ui.input id="brief-search-intent" wire:model.blur="searchIntent" :invalid="$errors->has('searchIntent')" />
                        </x-ui.field>

                        <x-ui.field label="Status" for="brief-status" :error="$errors->first('status')">
                            <x-ui.select id="brief-status" wire:model.live="status">
                                @foreach ($statusOptions as $statusOption)
                                    <option value="{{ $statusOption }}">{{ str($statusOption)->headline() }}</option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>
                    </div>

                    <div class="mt-5 grid gap-5">
                        <x-ui.field label="Meta Description" for="brief-meta-description" :error="$errors->first('metaDescription')">
                            <x-ui.textarea id="brief-meta-description" rows="4" wire:model.blur="metaDescription" :invalid="$errors->has('metaDescription')" />
                        </x-ui.field>

                        <x-ui.field label="Secondary Keywords" for="brief-secondary-keywords" :error="$errors->first('secondaryKeywords')" hint="One keyword per line.">
                            <x-ui.textarea id="brief-secondary-keywords" rows="4" wire:model.blur="secondaryKeywords" :invalid="$errors->has('secondaryKeywords')" />
                        </x-ui.field>
                    </div>

                    <div class="mt-6 grid gap-5">
                        <x-ui.field label="Outline" for="brief-outline" :error="$errors->first('outlineText')" hint="One JSON array per line. Use a pair such as Problem and Angle.">
                            <x-ui.textarea id="brief-outline" rows="7" wire:model.blur="outlineText" :invalid="$errors->has('outlineText')" />
                        </x-ui.field>

                        <x-ui.field label="Headings" for="brief-headings" :error="$errors->first('headingsText')" hint="One heading per line.">
                            <x-ui.textarea id="brief-headings" rows="6" wire:model.blur="headingsText" :invalid="$errors->has('headingsText')" />
                        </x-ui.field>

                        <x-ui.field label="FAQ Suggestions" for="brief-faq" :error="$errors->first('faqSuggestionsText')" hint="One JSON array per line. Use a pair such as Question and Answer direction.">
                            <x-ui.textarea id="brief-faq" rows="7" wire:model.blur="faqSuggestionsText" :invalid="$errors->has('faqSuggestionsText')" />
                        </x-ui.field>

                        <x-ui.field label="Internal Link Suggestions" for="brief-internal-links" :error="$errors->first('internalLinkSuggestionsText')" hint="One JSON array per line.">
                            <x-ui.textarea id="brief-internal-links" rows="6" wire:model.blur="internalLinkSuggestionsText" :invalid="$errors->has('internalLinkSuggestionsText')" />
                        </x-ui.field>

                        <x-ui.field label="Image Suggestions" for="brief-image-suggestions" :error="$errors->first('imageSuggestionsText')" hint="One JSON array per line. Images stay editorial suggestions only.">
                            <x-ui.textarea id="brief-image-suggestions" rows="6" wire:model.blur="imageSuggestionsText" :invalid="$errors->has('imageSuggestionsText')" />
                        </x-ui.field>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <x-ui.button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">Save Changes</span>
                            <span wire:loading wire:target="save">Saving…</span>
                        </x-ui.button>
                        @if ($canApprove)
                            <x-ui.button type="button" variant="secondary" wire:click="approve" wire:loading.attr="disabled" wire:target="approve">
                                <span wire:loading.remove wire:target="approve">Approve Brief</span>
                                <span wire:loading wire:target="approve">Approving…</span>
                            </x-ui.button>
                        @endif
                        @if ($canReject)
                            <x-ui.button type="button" variant="secondary" wire:click="reject" wire:loading.attr="disabled" wire:target="reject">
                                <span wire:loading.remove wire:target="reject">Reject Brief</span>
                                <span wire:loading wire:target="reject">Rejecting…</span>
                            </x-ui.button>
                        @endif
                        @if ($canGenerateDraft)
                            <x-ui.button type="button" variant="secondary" wire:click="openDraftDialog" wire:loading.attr="disabled" wire:target="openDraftDialog">
                                <span wire:loading.remove wire:target="openDraftDialog">Generate Draft</span>
                                <span wire:loading wire:target="openDraftDialog">Opening…</span>
                            </x-ui.button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Source Topic</p>
                    <div class="mt-5 space-y-3 text-sm text-[var(--color-muted)]">
                        <div class="flex items-center justify-between gap-3">
                            <span>Topic</span>
                            <span class="text-right text-[var(--color-ink)]">{{ $topic['title'] ?? 'Unknown' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Cluster</span>
                            <span class="text-right text-[var(--color-ink)]">{{ filled($topic['cluster'] ?? null) ? str($topic['cluster'])->headline() : 'Not set' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Status</span>
                            <x-admin.status-badge :status="$topic['status'] ?? null" />
                        </div>
                        @if ($topicLink)
                            <x-ui.button as="a" :href="$topicLink" variant="outline" size="sm">Review Topic</x-ui.button>
                        @endif
                    </div>
                </div>

                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Workflow State</p>
                    <div class="mt-5 space-y-4 text-sm text-[var(--color-muted)]">
                        <div class="flex items-center justify-between gap-3">
                            <span>Status</span>
                            <x-admin.status-badge :status="$status" />
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Draft Generation Ready</span>
                            <x-ui.badge :tone="$canGenerateDraft ? 'success' : 'muted'">{{ $canGenerateDraft ? 'Yes' : 'No' }}</x-ui.badge>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Approved At</span>
                            <span>{{ $approvedAt ?? 'Not approved' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Created</span>
                            <span>{{ $createdAt ?? 'Unknown' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Updated</span>
                            <span>{{ $updatedAt ?? 'Unknown' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <x-admin.confirm-dialog
        :open="$draftDialogOpen"
        title="Generate Blog Draft"
        description="Create a review-only service-side AI job for draft generation. The resulting post must still be reviewed manually before publish."
    >
        <div class="space-y-4">
            @if ($draftError)
                <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                    {{ $draftError }}
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Category" for="draft-category" :error="$errors->first('draftCategoryId')" required>
                    <x-ui.select id="draft-category" wire:model.live="draftCategoryId" :invalid="$errors->has('draftCategoryId')">
                        <option value="">Select category</option>
                        @foreach ($categoryOptions as $category)
                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Template" for="draft-template" :error="$errors->first('draftTemplateId')">
                    <x-ui.select id="draft-template" wire:model.live="draftTemplateId" :invalid="$errors->has('draftTemplateId')">
                        <option value="">No template</option>
                        @foreach ($templateOptions as $template)
                            <option value="{{ $template['id'] }}">{{ $template['name'] }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.field label="Visibility" for="draft-visibility" :error="$errors->first('draftVisibility')">
                    <x-ui.select id="draft-visibility" wire:model.live="draftVisibility" :invalid="$errors->has('draftVisibility')">
                        <option value="public">Public</option>
                        <option value="private">Private</option>
                        <option value="internal">Internal</option>
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Prompt Template Key" for="draft-prompt-template-key" :error="$errors->first('draftPromptTemplateKey')">
                    <x-ui.input id="draft-prompt-template-key" wire:model.blur="draftPromptTemplateKey" :invalid="$errors->has('draftPromptTemplateKey')" placeholder="blog-writer-editorial" />
                </x-ui.field>
            </div>

            <x-ui.field label="Generation Mode" for="draft-generation-mode" :error="$errors->first('draftGenerationMode')" hint="Optional editorial mode override. Leave blank to keep the existing service behavior.">
                <x-ui.select id="draft-generation-mode" wire:model.live="draftGenerationMode" :invalid="$errors->has('draftGenerationMode')">
                    <option value="">Default service mode</option>
                    @foreach ($draftGenerationModes as $draftGenerationMode)
                        <option value="{{ $draftGenerationMode }}">{{ str($draftGenerationMode)->replace('_', ' ')->title() }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
        </div>

        <x-slot:cancel>
            <x-ui.button variant="secondary" type="button" wire:click="closeDraftDialog" wire:loading.attr="disabled" wire:target="closeDraftDialog,generateDraft">
                Cancel
            </x-ui.button>
        </x-slot:cancel>

        <x-slot:confirm>
            <x-ui.button type="button" wire:click="generateDraft" wire:loading.attr="disabled" wire:target="generateDraft">
                <span wire:loading.remove wire:target="generateDraft">Generate Draft</span>
                <span wire:loading wire:target="generateDraft">Creating…</span>
            </x-ui.button>
        </x-slot:confirm>
    </x-admin.confirm-dialog>
</div>
