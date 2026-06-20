<div class="space-y-6">
    <x-admin.page-header
        title="Contact Submission"
        description="Review the full inbound message, inspect metadata, and update the submission workflow state."
    >
        <x-ui.button as="a" :href="route('contact-submissions.index')" variant="secondary">Back to Submissions</x-ui.button>
        <x-ui.button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
            <span wire:loading.remove wire:target="save">Save Changes</span>
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

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.42fr)_21rem]">
        <div class="space-y-6">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Submission</h2>
                    <p class="text-sm text-[var(--color-muted)]">Read the full message exactly as it was submitted.</p>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Name</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $name }}</p>
                    </div>
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Email</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $email }}</p>
                    </div>
                </div>

                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Topic</p>
                    <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $topic !== '' ? $topic : 'No topic provided' }}</p>
                </div>

                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Message</p>
                    <div class="mt-2 whitespace-pre-wrap text-sm leading-6 text-[var(--color-ink)]">{{ $message }}</div>
                </div>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Review</h2>
                    <p class="text-sm text-[var(--color-muted)]">Update the workflow status and internal notes without changing the original submission.</p>
                </div>

                <x-ui.field label="Status" for="contact-submission-status" :error="$errors->first('status')">
                    <x-ui.select id="contact-submission-status" wire:model.live="status" :invalid="$errors->has('status')">
                        @foreach ($statusOptions as $statusOption)
                            <option value="{{ $statusOption }}">{{ str($statusOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Admin Notes" for="contact-submission-notes" :error="$errors->first('admin_notes')">
                    <x-ui.textarea id="contact-submission-notes" wire:model.blur="admin_notes" rows="6" placeholder="Capture follow-up notes, routing context, or internal resolution detail." :invalid="$errors->has('admin_notes')" />
                </x-ui.field>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Metadata</h2>
                    <p class="text-sm text-[var(--color-muted)]">Read-only metadata returned by the service for this submission.</p>
                </div>

                <pre class="overflow-x-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4 text-sm leading-6 text-[var(--color-ink)]">{{ $metadataJson }}</pre>
            </section>
        </div>

        <aside class="space-y-6 xl:sticky xl:top-6 xl:self-start">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Submission State</h2>
                    <p class="text-sm text-[var(--color-muted)]">The current workflow state and review audit returned by the service.</p>
                </div>

                <div class="space-y-3">
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Current Status</p>
                        <div class="mt-2">
                            <x-admin.status-badge :status="$status" />
                        </div>
                    </div>
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Submitted At</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $submitted_at ?? 'Unknown' }}</p>
                    </div>
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Reviewed At</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $reviewed_at ?? 'Not reviewed yet' }}</p>
                    </div>
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Reviewed By</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $reviewed_by['name'] ?? 'Not reviewed yet' }}</p>
                    </div>
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Created At</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $created_at ?? 'Unknown' }}</p>
                    </div>
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Updated At</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $updated_at ?? 'Unknown' }}</p>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
