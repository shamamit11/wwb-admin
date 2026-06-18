<div class="space-y-6">
    <x-admin.page-header
        eyebrow="Topic Review"
        :title="$title ?: 'Topic detail'"
        description="Review topic metadata, adjust editorial framing, and move the suggestion through approval states without duplicating workflow logic in Admin."
    >
        <x-ui.button as="a" :href="route('topic-queue.index')" variant="secondary">Back to Queue</x-ui.button>
    </x-admin.page-header>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    @if ($notFound)
        <x-ui.empty-state
            title="Topic not found"
            message="The requested topic is no longer available from the service API."
        />
    @else
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_minmax(20rem,1fr)]">
            <div class="space-y-6">
                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Topic Metadata</p>
                            <h2 class="mt-2 text-xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">Editorial Details</h2>
                        </div>

                        <x-admin.status-badge :status="$status" />
                    </div>

                    @if ($formError)
                        <div class="mt-5 rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                            {{ $formError }}
                        </div>
                    @endif

                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        <x-ui.field label="Title" for="topic-title" :error="$errors->first('title')">
                            <x-ui.input id="topic-title" wire:model.blur="title" :invalid="$errors->has('title')" />
                        </x-ui.field>

                        <x-ui.field label="Slug" for="topic-slug" :error="$errors->first('slug')">
                            <x-ui.input id="topic-slug" wire:model.blur="slug" :invalid="$errors->has('slug')" />
                        </x-ui.field>

                        <x-ui.field label="Cluster" for="topic-cluster" :error="$errors->first('cluster')">
                            <x-ui.select id="topic-cluster" wire:model.live="cluster">
                                @foreach ($clusterOptions as $clusterOption)
                                    <option value="{{ $clusterOption }}">{{ str($clusterOption)->headline() }}</option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>

                        <x-ui.field label="Source" for="topic-source" :error="$errors->first('source')">
                            <x-ui.select id="topic-source" wire:model.live="source">
                                @foreach ($sourceOptions as $sourceOption)
                                    <option value="{{ $sourceOption }}">{{ $sourceOption === 'ai_suggested' ? 'AI Suggested' : 'Manual' }}</option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>

                        <x-ui.field label="Primary Keyword" for="topic-primary-keyword" :error="$errors->first('primaryKeyword')">
                            <x-ui.input id="topic-primary-keyword" wire:model.blur="primaryKeyword" :invalid="$errors->has('primaryKeyword')" />
                        </x-ui.field>

                        <x-ui.field label="Search Intent" for="topic-search-intent" :error="$errors->first('searchIntent')">
                            <x-ui.input id="topic-search-intent" wire:model.blur="searchIntent" :invalid="$errors->has('searchIntent')" />
                        </x-ui.field>

                        <x-ui.field label="Priority Score" for="topic-priority-score" :error="$errors->first('priorityScore')">
                            <x-ui.input id="topic-priority-score" wire:model.blur="priorityScore" :invalid="$errors->has('priorityScore')" />
                        </x-ui.field>

                        <x-ui.field label="Secondary Keywords" for="topic-secondary-keywords" :error="$errors->first('secondaryKeywords')" hint="Comma-separated list">
                            <x-ui.textarea id="topic-secondary-keywords" rows="4" wire:model.blur="secondaryKeywords" :invalid="$errors->has('secondaryKeywords')" />
                        </x-ui.field>
                    </div>

                    <div class="mt-5 grid gap-5">
                        <x-ui.field label="Difficulty Note" for="topic-difficulty-note" :error="$errors->first('difficultyNote')">
                            <x-ui.textarea id="topic-difficulty-note" rows="4" wire:model.blur="difficultyNote" :invalid="$errors->has('difficultyNote')" />
                        </x-ui.field>

                        <x-ui.field label="Editorial Notes" for="topic-notes" :error="$errors->first('notes')">
                            <x-ui.textarea id="topic-notes" rows="5" wire:model.blur="notes" :invalid="$errors->has('notes')" />
                        </x-ui.field>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <x-ui.button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">Save Changes</span>
                            <span wire:loading wire:target="save">Saving…</span>
                        </x-ui.button>
                        @if ($canApprove)
                            <x-ui.button type="button" variant="secondary" wire:click="openTransitionDialog('approve')" wire:loading.attr="disabled" wire:target="openTransitionDialog">
                                <span wire:loading.remove wire:target="openTransitionDialog">Approve Topic</span>
                                <span wire:loading wire:target="openTransitionDialog">Opening…</span>
                            </x-ui.button>
                        @endif
                        @if ($canReject)
                            <x-ui.button type="button" variant="secondary" wire:click="openTransitionDialog('reject')" wire:loading.attr="disabled" wire:target="openTransitionDialog">
                                <span wire:loading.remove wire:target="openTransitionDialog">Reject Topic</span>
                                <span wire:loading wire:target="openTransitionDialog">Opening…</span>
                            </x-ui.button>
                        @endif
                        @if ($canMarkUsed)
                            <x-ui.button type="button" variant="secondary" wire:click="openTransitionDialog('mark-used')" wire:loading.attr="disabled" wire:target="openTransitionDialog">
                                <span wire:loading.remove wire:target="openTransitionDialog">Mark as Used</span>
                                <span wire:loading wire:target="openTransitionDialog">Opening…</span>
                            </x-ui.button>
                        @endif
                        @if ($canGenerateContentBrief)
                            <x-ui.button type="button" variant="secondary" wire:click="openBriefDialog" wire:loading.attr="disabled" wire:target="openBriefDialog">
                                <span wire:loading.remove wire:target="openBriefDialog">Generate Brief</span>
                                <span wire:loading wire:target="openBriefDialog">Opening…</span>
                            </x-ui.button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Workflow State</p>
                    <div class="mt-5 space-y-4 text-sm text-[var(--color-muted)]">
                        <div class="flex items-center justify-between gap-3">
                            <span>Status</span>
                            <x-admin.status-badge :status="$status" />
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Content Brief Ready</span>
                            <x-ui.badge :tone="$canGenerateContentBrief ? 'success' : 'muted'">{{ $canGenerateContentBrief ? 'Yes' : 'No' }}</x-ui.badge>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Approved At</span>
                            <span>{{ $approvedAt ?: 'Not approved' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Rejected At</span>
                            <span>{{ $rejectedAt ?: 'Not rejected' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Used At</span>
                            <span>{{ $usedAt ?: 'Not used' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Created</span>
                            <span>{{ $createdAt ?: 'Unknown' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Updated</span>
                            <span>{{ $updatedAt ?: 'Unknown' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <x-admin.confirm-dialog
        :open="$transitionDialogOpen"
        :title="match ($transitionAction) {
            'approve' => 'Approve topic',
            'reject' => 'Reject topic',
            'mark-used' => 'Mark topic as used',
            default => 'Update topic status',
        }"
        :description="match ($transitionAction) {
            'approve' => 'This topic will become eligible for downstream editorial workflows.',
            'reject' => 'Reject this topic when it no longer fits the editorial direction.',
            'mark-used' => 'Mark this topic as consumed once the editorial path is complete.',
            default => 'Review the status change before continuing.',
        }"
        :destructive="$transitionAction === 'reject'"
    >
        <div class="space-y-4">
            @if ($actionError)
                <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                    {{ $actionError }}
                </div>
            @endif

            <x-ui.field label="Transition Notes" for="transition-notes" :error="$errors->first('transitionNotes')" hint="Optional note stored with the service-side transition.">
                <x-ui.textarea id="transition-notes" rows="4" wire:model.blur="transitionNotes" :invalid="$errors->has('transitionNotes')" />
            </x-ui.field>
        </div>

        <x-slot:cancel>
            <x-ui.button variant="secondary" type="button" wire:click="closeTransitionDialog" wire:loading.attr="disabled" wire:target="closeTransitionDialog,executeTransition">
                Cancel
            </x-ui.button>
        </x-slot:cancel>

        <x-slot:confirm>
            <x-ui.button :variant="$transitionAction === 'reject' ? 'destructive' : 'primary'" type="button" wire:click="executeTransition" wire:loading.attr="disabled" wire:target="executeTransition">
                <span wire:loading.remove wire:target="executeTransition">Confirm</span>
                <span wire:loading wire:target="executeTransition">Saving…</span>
            </x-ui.button>
        </x-slot:confirm>
    </x-admin.confirm-dialog>

    <x-admin.confirm-dialog
        :open="$briefDialogOpen"
        title="Generate Content Brief"
        description="Generate a content brief from this approved topic through the Service API. The resulting brief will still require human review before draft generation."
    >
        <div class="space-y-4">
            @if ($briefError)
                <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                    {{ $briefError }}
                </div>
            @endif

            <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3 text-sm text-[var(--color-muted)]">
                Brief generation stays service-owned. Admin only triggers the workflow and routes the editor into the resulting review screen.
            </div>
        </div>

        <x-slot:cancel>
            <x-ui.button variant="secondary" type="button" wire:click="closeBriefDialog" wire:loading.attr="disabled" wire:target="closeBriefDialog,generateBrief">
                Cancel
            </x-ui.button>
        </x-slot:cancel>

        <x-slot:confirm>
            <x-ui.button type="button" wire:click="generateBrief" wire:loading.attr="disabled" wire:target="generateBrief">
                <span wire:loading.remove wire:target="generateBrief">Generate Brief</span>
                <span wire:loading wire:target="generateBrief">Generating…</span>
            </x-ui.button>
        </x-slot:confirm>
    </x-admin.confirm-dialog>
</div>
