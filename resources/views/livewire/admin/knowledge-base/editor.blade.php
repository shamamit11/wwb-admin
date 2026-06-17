<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <x-admin.page-header
            :title="$editingEntryId ? 'Edit Knowledge Entry' : 'Create Knowledge Entry'"
            description="Capture practical editorial context in markdown, keep metadata lightweight, and avoid overbuilding the writing flow."
        />

        <div class="flex flex-wrap items-center gap-3 lg:pt-1">
            <x-ui.button as="a" :href="route('knowledge-base.index')" variant="secondary">Back to Knowledge Base</x-ui.button>
            <x-ui.button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">{{ $editingEntryId ? 'Save Entry' : 'Create Entry' }}</span>
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
                    <p class="text-sm text-[var(--color-muted)]">Keep entries practical and legible. The service stores markdown directly, so this editor stays intentionally lightweight.</p>
                </div>

                <x-ui.field label="Title" for="knowledge-title" :error="$errors->first('title')" required>
                    <x-ui.input id="knowledge-title" wire:model.blur="title" placeholder="Entry title" :invalid="$errors->has('title')" />
                </x-ui.field>

                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.field label="Slug" for="knowledge-slug" :error="$errors->first('slug')" hint="Leave blank to let the service generate the slug.">
                        <x-ui.input id="knowledge-slug" wire:model.blur="slug" placeholder="entry-slug" :invalid="$errors->has('slug')" />
                    </x-ui.field>

                    <x-ui.field label="Source URL" for="knowledge-source-url" :error="$errors->first('sourceUrl')" hint="Optional reference source for the editorial note.">
                        <x-ui.input id="knowledge-source-url" wire:model.blur="sourceUrl" placeholder="https://example.com/source" :invalid="$errors->has('sourceUrl')" />
                    </x-ui.field>
                </div>

                <x-ui.field label="Summary" for="knowledge-summary" :error="$errors->first('summary')" hint="Short operational summary for list views and quick scanning.">
                    <x-ui.textarea id="knowledge-summary" wire:model.blur="summary" rows="4" placeholder="Compact editorial summary" :invalid="$errors->has('summary')" />
                </x-ui.field>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Markdown Content</h2>
                        <p class="text-sm text-[var(--color-muted)]">Write directly in markdown. The helper buttons only insert small snippets; they do not try to become a full editor.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.button type="button" variant="secondary" size="sm" wire:click="insertMarkdownSnippet('heading')">Heading</x-ui.button>
                        <x-ui.button type="button" variant="secondary" size="sm" wire:click="insertMarkdownSnippet('link')">Link</x-ui.button>
                        <x-ui.button type="button" variant="secondary" size="sm" wire:click="insertMarkdownSnippet('list')">List</x-ui.button>
                        <x-ui.button type="button" variant="secondary" size="sm" wire:click="insertMarkdownSnippet('quote')">Quote</x-ui.button>
                        <x-ui.button type="button" variant="secondary" size="sm" wire:click="insertMarkdownSnippet('code')">Code</x-ui.button>
                    </div>
                </div>

                <x-ui.field label="Content Markdown" for="knowledge-content" :error="$errors->first('contentMarkdown')" hint="Preserve clear headings, concise bullets, and readable reference snippets." required>
                    <x-ui.textarea id="knowledge-content" wire:model.blur="contentMarkdown" rows="20" placeholder="# Research note&#10;&#10;- Key fact&#10;- Supporting source&#10;&#10;## Recommendation&#10;Actionable editorial guidance." :invalid="$errors->has('contentMarkdown')" />
                </x-ui.field>
            </section>
        </div>

        <aside class="space-y-6 xl:sticky xl:top-6 xl:self-start">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Status</h2>
                    <p class="text-sm text-[var(--color-muted)]">Type and status stay visible while writing.</p>
                </div>

                <x-ui.field label="Entry Type" for="knowledge-entry-type" :error="$errors->first('entryType')" required>
                    <x-ui.select id="knowledge-entry-type" wire:model.live="entryType" :invalid="$errors->has('entryType')">
                        @foreach ($entryTypes as $type)
                            <option value="{{ $type }}">{{ str($type)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Status" for="knowledge-status" :error="$errors->first('status')" required>
                    <x-ui.select id="knowledge-status" wire:model.live="status" :invalid="$errors->has('status')">
                        @foreach ($entryStatuses as $statusOption)
                            <option value="{{ $statusOption }}">{{ str($statusOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Slug Preview</p>
                    <p class="mt-2 text-sm text-[var(--color-ink)]">/knowledge-base/{{ $slug !== '' ? $slug : 'generated-on-save' }}</p>
                </div>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Metadata</h2>
                    <p class="text-sm text-[var(--color-muted)]">Use a simple JSON array of strings for lightweight reference tags or notes.</p>
                </div>

                <x-ui.field label="Metadata JSON Array" for="knowledge-metadata-json" :error="$errors->first('metadataJson')" hint='Example: ["agent-memory","source:research"]'>
                    <x-ui.textarea id="knowledge-metadata-json" wire:model.blur="metadataJson" rows="6" placeholder='["editorial-note","research"]' :invalid="$errors->has('metadataJson')" />
                </x-ui.field>
            </section>

            @if ($editingEntryId)
                <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                    <div class="space-y-1">
                        <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Linked Context</h2>
                        <p class="text-sm text-[var(--color-muted)]">Linking remains read-only here until explicit linking flows are implemented.</p>
                    </div>

                    <div class="space-y-3">
                        <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Linked Posts</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ count($linkedPosts) }}</p>
                        </div>
                        <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Linked Topics</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ count($linkedTopics) }}</p>
                        </div>
                    </div>
                </section>
            @endif
        </aside>
    </div>
</div>
