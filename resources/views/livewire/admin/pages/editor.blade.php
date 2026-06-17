<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <x-admin.page-header
            :title="$editingPageId ? 'Edit Page' : 'Create Page'"
            description="Manage service-backed static and evergreen page content with a practical markdown editing flow."
        />

        <div class="flex flex-wrap items-center gap-3 lg:pt-1">
            <x-ui.button as="a" :href="route('pages.index')" variant="secondary">Back to Pages</x-ui.button>
            <x-ui.button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">{{ $editingPageId ? 'Save Page' : 'Create Page' }}</span>
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
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Core Details</h2>
                    <p class="text-sm text-[var(--color-muted)]">Use this for evergreen and static content without treating it like application settings.</p>
                </div>

                <x-ui.field label="Title" for="page-title" :error="$errors->first('title')" required>
                    <x-ui.input id="page-title" wire:model.blur="title" placeholder="Page title" :invalid="$errors->has('title')" />
                </x-ui.field>

                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.field label="Slug" for="page-slug" :error="$errors->first('slug')" hint="Leave blank to let the service generate the slug.">
                        <x-ui.input id="page-slug" wire:model.blur="slug" placeholder="privacy-policy" :invalid="$errors->has('slug')" />
                    </x-ui.field>
                </div>

                <x-ui.field label="Summary" for="page-summary" :error="$errors->first('summary')" hint="Short summary for list views and operational scanning.">
                    <x-ui.textarea id="page-summary" wire:model.blur="summary" rows="4" placeholder="Short page summary" :invalid="$errors->has('summary')" />
                </x-ui.field>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Markdown Content</h2>
                        <p class="text-sm text-[var(--color-muted)]">Write page content directly in markdown. Helper buttons stay lightweight on purpose.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.button type="button" variant="secondary" size="sm" wire:click="insertMarkdownSnippet('heading')">Heading</x-ui.button>
                        <x-ui.button type="button" variant="secondary" size="sm" wire:click="insertMarkdownSnippet('link')">Link</x-ui.button>
                        <x-ui.button type="button" variant="secondary" size="sm" wire:click="insertMarkdownSnippet('list')">List</x-ui.button>
                        <x-ui.button type="button" variant="secondary" size="sm" wire:click="insertMarkdownSnippet('quote')">Quote</x-ui.button>
                        <x-ui.button type="button" variant="secondary" size="sm" wire:click="insertMarkdownSnippet('faq')">FAQ Snippet</x-ui.button>
                    </div>
                </div>

                <x-ui.field label="Content Markdown" for="page-content" :error="$errors->first('contentMarkdown')" hint="Keep legal, support, and evergreen page content clear and easy to review." required>
                    <x-ui.textarea id="page-content" wire:model.blur="contentMarkdown" rows="22" placeholder="# Privacy Policy&#10;&#10;## Overview&#10;Explain the policy clearly here." :invalid="$errors->has('contentMarkdown')" />
                </x-ui.field>
            </section>
        </div>

        <aside class="space-y-6 xl:sticky xl:top-6 xl:self-start">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Status</h2>
                    <p class="text-sm text-[var(--color-muted)]">Type, visibility, and scheduling stay visible while editing.</p>
                </div>

                <x-ui.field label="Page Type" for="page-type" :error="$errors->first('pageType')" required>
                    <x-ui.select id="page-type" wire:model.live="pageType" :invalid="$errors->has('pageType')">
                        @foreach ($pageTypes as $type)
                            <option value="{{ $type }}">{{ str($type)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Status" for="page-status" :error="$errors->first('status')" required>
                    <x-ui.select id="page-status" wire:model.live="status" :invalid="$errors->has('status')">
                        @foreach ($pageStatuses as $statusOption)
                            <option value="{{ $statusOption }}">{{ str($statusOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Visibility" for="page-visibility" :error="$errors->first('visibility')" required>
                    <x-ui.select id="page-visibility" wire:model.live="visibility" :invalid="$errors->has('visibility')">
                        @foreach ($pageVisibilities as $visibilityOption)
                            <option value="{{ $visibilityOption }}">{{ str($visibilityOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Published At" for="page-published-at" :error="$errors->first('publishedAt')" hint="Optional explicit publish timestamp.">
                    <x-ui.input id="page-published-at" type="datetime-local" wire:model.blur="publishedAt" :invalid="$errors->has('publishedAt')" />
                </x-ui.field>

                <x-ui.field label="Scheduled For" for="page-scheduled-for" :error="$errors->first('scheduledFor')" hint="Use this when the page should be scheduled rather than immediately live.">
                    <x-ui.input id="page-scheduled-for" type="datetime-local" wire:model.blur="scheduledFor" :invalid="$errors->has('scheduledFor')" />
                </x-ui.field>

                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Slug Preview</p>
                    <p class="mt-2 text-sm text-[var(--color-ink)]">/{{ $slug !== '' ? $slug : 'generated-on-save' }}</p>
                </div>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Metadata</h2>
                    <p class="text-sm text-[var(--color-muted)]">Use a simple JSON array of strings for light annotations or page tags.</p>
                </div>

                <x-ui.field label="Meta JSON Array" for="page-meta-json" :error="$errors->first('metaJson')" hint='Example: ["legal","footer-link"]'>
                    <x-ui.textarea id="page-meta-json" wire:model.blur="metaJson" rows="6" placeholder='["legal","footer-link"]' :invalid="$errors->has('metaJson')" />
                </x-ui.field>
            </section>

            @if ($editingPageId)
                <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                    <div class="space-y-1">
                        <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Revision Context</h2>
                        <p class="text-sm text-[var(--color-muted)]">Read-only audit context returned by the service.</p>
                    </div>

                    <div class="space-y-3">
                        <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Created By</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $createdBy['name'] ?? 'Unknown' }}</p>
                            @if (! empty($createdBy['email']))
                                <p class="mt-1 text-xs text-[var(--color-muted)]">{{ $createdBy['email'] }}</p>
                            @endif
                        </div>

                        <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Updated By</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $updatedBy['name'] ?? 'No later update recorded' }}</p>
                            @if (! empty($updatedBy['email']))
                                <p class="mt-1 text-xs text-[var(--color-muted)]">{{ $updatedBy['email'] }}</p>
                            @endif
                        </div>

                        <div class="grid gap-3">
                            <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Created At</p>
                                <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $createdAt ?? 'Unknown' }}</p>
                            </div>
                            <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Updated At</p>
                                <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $updatedAt ?? 'Unknown' }}</p>
                            </div>
                            <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Canonical URL</p>
                                <p class="mt-2 break-all text-sm text-[var(--color-ink)]">{{ $canonicalUrlDisplay !== '' ? $canonicalUrlDisplay : 'No canonical URL returned.' }}</p>
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        </aside>
    </div>
</div>
