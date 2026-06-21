@php
    $summaryItems = [
        ['label' => 'Hero', 'detail' => $hero['title'] ?: 'No hero title yet', 'tone' => $hero['title'] ? 'default' : 'warning'],
        ['label' => 'Contact Form', 'detail' => $contact_form['title'] ?: 'No form title yet', 'tone' => $contact_form['title'] ? 'default' : 'warning'],
        ['label' => 'Contact Reasons', 'detail' => count($contact_reasons['items'] ?? []).' reason items', 'tone' => count($contact_reasons['items'] ?? []) > 0 ? 'default' : 'warning'],
        ['label' => 'SEO', 'detail' => $seo['meta_title'] ?: 'No meta title yet', 'tone' => $seo['meta_title'] ? 'default' : 'warning'],
    ];

    $summaryCardClasses = [
        'default' => 'border-[var(--color-line)] bg-[var(--color-panel-soft)]',
        'warning' => 'border-[color-mix(in_srgb,var(--color-warning)_24%,white)] bg-[color-mix(in_srgb,var(--color-warning)_8%,white)]',
    ];

    $sections = [
        ['id' => 'contact-hero', 'label' => 'Hero', 'detail' => $hero['title'] ?: 'Needs title'],
        ['id' => 'contact-form', 'label' => 'Contact Form', 'detail' => $contact_form['title'] ?: 'Needs title'],
        ['id' => 'contact-reasons', 'label' => 'Contact Reasons', 'detail' => count($contact_reasons['items'] ?? []).' items'],
        ['id' => 'contact-seo', 'label' => 'SEO', 'detail' => $seo['meta_title'] ? 'Configured' : 'Meta title missing'],
    ];
@endphp

<div class="space-y-6">
    <x-admin.page-header
        title="Contact Page"
        description="Manage the singleton Contact Page copy, reasons list, and SEO through one structured service-backed editor."
    >
        <div class="hidden items-center sm:flex">
            <span wire:dirty.remove class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-3 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">
                All changes saved
            </span>
            <span wire:dirty class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-warning)_22%,white)] bg-[color-mix(in_srgb,var(--color-warning)_10%,white)] px-3 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-warning-strong)]">
                Unsaved changes
            </span>
        </div>
        <x-ui.button as="a" :href="config('app.url').'/contact'" variant="secondary" target="_blank" rel="noreferrer">Preview Contact Page</x-ui.button>
        <x-ui.button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
            <span wire:loading.remove wire:target="save">Save All Changes</span>
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
        <div class="space-y-4">
            <section id="contact-hero" x-data="{ open: true }" class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] shadow-[var(--shadow-card)]">
                <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Hero</h2>
                            <x-ui.badge :tone="$hero['title'] ? 'success' : 'warning'">{{ $hero['title'] ? 'Configured' : 'Needs title' }}</x-ui.badge>
                        </div>
                        <p class="text-sm text-[var(--color-muted)]">Set the top-level invitation and framing for the Contact Page.</p>
                        <p class="text-sm text-[var(--color-muted)]">{{ $hero['title'] ?: 'No hero title yet' }}</p>
                    </div>
                    <button type="button" x-on:click="open = ! open" class="inline-flex items-center justify-center gap-2 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-3 py-2 text-sm font-medium text-[var(--color-ink)] transition-colors hover:bg-[var(--color-page)]">
                        <span x-text="open ? 'Collapse' : 'Expand'"></span>
                        <svg class="h-4 w-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </button>
                </div>
                <div x-cloak x-show="open" x-transition.opacity.duration.150ms class="space-y-5 border-t border-[var(--color-line)] px-6 py-6">
                    <x-ui.field label="Eyebrow" for="contact-hero-eyebrow" :error="$errors->first('hero.eyebrow')">
                        <x-ui.input id="contact-hero-eyebrow" wire:model.blur="hero.eyebrow" placeholder="Get in Touch" :invalid="$errors->has('hero.eyebrow')" />
                    </x-ui.field>
                    <x-ui.field label="Title" for="contact-hero-title" :error="$errors->first('hero.title')">
                        <x-ui.input id="contact-hero-title" wire:model.blur="hero.title" placeholder="Talk to the Wide Web Blog team" :invalid="$errors->has('hero.title')" />
                    </x-ui.field>
                    <x-ui.field label="Description" for="contact-hero-description" :error="$errors->first('hero.description')">
                        <x-ui.textarea id="contact-hero-description" wire:model.blur="hero.description" rows="4" placeholder="Explain who should reach out and what they can expect from the response process." :invalid="$errors->has('hero.description')" />
                    </x-ui.field>
                </div>
            </section>

            <section id="contact-form" x-data="{ open: false }" class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] shadow-[var(--shadow-card)]">
                <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Contact Form</h2>
                            <x-ui.badge :tone="$contact_form['title'] ? 'success' : 'warning'">{{ $contact_form['title'] ? 'Configured' : 'Needs title' }}</x-ui.badge>
                        </div>
                        <p class="text-sm text-[var(--color-muted)]">Control the form heading, supporting copy, CTA label, and success response shown on the public Contact Page.</p>
                        <p class="text-sm text-[var(--color-muted)]">{{ $contact_form['title'] ?: 'No form title yet' }}</p>
                    </div>
                    <button type="button" x-on:click="open = ! open" class="inline-flex items-center justify-center gap-2 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-3 py-2 text-sm font-medium text-[var(--color-ink)] transition-colors hover:bg-[var(--color-page)]">
                        <span x-text="open ? 'Collapse' : 'Expand'"></span>
                        <svg class="h-4 w-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </button>
                </div>
                <div x-cloak x-show="open" x-transition.opacity.duration.150ms class="space-y-5 border-t border-[var(--color-line)] px-6 py-6">
                    <div class="grid gap-5 lg:grid-cols-2">
                        <x-ui.field label="Eyebrow" for="contact-form-eyebrow" :error="$errors->first('contact_form.eyebrow')">
                            <x-ui.input id="contact-form-eyebrow" wire:model.blur="contact_form.eyebrow" placeholder="Contact Form" :invalid="$errors->has('contact_form.eyebrow')" />
                        </x-ui.field>
                        <x-ui.field label="Submit Label" for="contact-form-submit-label" :error="$errors->first('contact_form.submit_label')">
                            <x-ui.input id="contact-form-submit-label" wire:model.blur="contact_form.submit_label" placeholder="Send Message" :invalid="$errors->has('contact_form.submit_label')" />
                        </x-ui.field>
                    </div>
                    <x-ui.field label="Title" for="contact-form-title" :error="$errors->first('contact_form.title')">
                        <x-ui.input id="contact-form-title" wire:model.blur="contact_form.title" placeholder="Tell us what you need" :invalid="$errors->has('contact_form.title')" />
                    </x-ui.field>
                    <x-ui.field label="Description" for="contact-form-description" :error="$errors->first('contact_form.description')">
                        <x-ui.textarea id="contact-form-description" wire:model.blur="contact_form.description" rows="4" placeholder="Explain what kinds of requests are welcome and how quickly editors usually respond." :invalid="$errors->has('contact_form.description')" />
                    </x-ui.field>
                    <x-ui.field label="Success Message" for="contact-form-success-message" :error="$errors->first('contact_form.success_message')">
                        <x-ui.textarea id="contact-form-success-message" wire:model.blur="contact_form.success_message" rows="3" placeholder="Thanks for reaching out. We’ll review your message and reply soon." :invalid="$errors->has('contact_form.success_message')" />
                    </x-ui.field>
                </div>
            </section>

            <section id="contact-reasons" x-data="{ open: false }" class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] shadow-[var(--shadow-card)]">
                <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Contact Reasons</h2>
                            <x-ui.badge :tone="count($contact_reasons['items'] ?? []) > 0 ? 'success' : 'warning'">{{ count($contact_reasons['items'] ?? []) }} items</x-ui.badge>
                        </div>
                        <p class="text-sm text-[var(--color-muted)]">Maintain the ordered list of common reasons visitors might contact the team.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-ui.button type="button" variant="secondary" size="sm" wire:click="addListItem('contact_reasons', 'items')">Add Reason</x-ui.button>
                        <button type="button" x-on:click="open = ! open" class="inline-flex items-center justify-center gap-2 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-3 py-2 text-sm font-medium text-[var(--color-ink)] transition-colors hover:bg-[var(--color-page)]">
                            <span x-text="open ? 'Collapse' : 'Expand'"></span>
                            <svg class="h-4 w-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </button>
                    </div>
                </div>
                <div x-cloak x-show="open" x-transition.opacity.duration.150ms class="space-y-5 border-t border-[var(--color-line)] px-6 py-6">
                    @error('contact_reasons.items')<p class="text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                    <div class="space-y-3">
                        @forelse ($contact_reasons['items'] as $index => $item)
                            <div wire:key="contact-reason-{{ $index }}" class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="min-w-0 flex-1 space-y-3">
                                        <x-ui.input wire:model.blur="contact_reasons.items.{{ $index }}.title" placeholder="Reason title" :invalid="$errors->has('contact_reasons.items.'.$index.'.title')" />
                                        <x-ui.textarea wire:model.blur="contact_reasons.items.{{ $index }}.description" rows="3" placeholder="Explain when this reason applies and what detail visitors should include." :invalid="$errors->has('contact_reasons.items.'.$index.'.description')" />
                                    </div>
                                    <button type="button" wire:click="moveListItem('contact_reasons', 'items', {{ $index }}, 'up')" class="text-sm text-[var(--color-muted)]">↑</button>
                                    <button type="button" wire:click="moveListItem('contact_reasons', 'items', {{ $index }}, 'down')" class="text-sm text-[var(--color-muted)]">↓</button>
                                    <button type="button" wire:click="removeListItem('contact_reasons', 'items', {{ $index }})" class="text-sm text-[var(--color-danger-strong)]">Remove</button>
                                </div>
                                @error('contact_reasons.items.'.$index.'.title')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                                @error('contact_reasons.items.'.$index.'.description')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                            </div>
                        @empty
                            <div class="rounded-[var(--radius-button)] border border-dashed border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4 text-sm text-[var(--color-muted)]">No contact reasons added yet.</div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section id="contact-seo" x-data="{ open: false }" class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] shadow-[var(--shadow-card)]">
                <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">SEO</h2>
                            <x-ui.badge :tone="$seo['meta_title'] ? 'success' : 'warning'">{{ $seo['meta_title'] ? 'Configured' : 'Meta title missing' }}</x-ui.badge>
                        </div>
                        <p class="text-sm text-[var(--color-muted)]">Keep Contact Page metadata explicit and separate from the page body copy.</p>
                        <p class="text-sm text-[var(--color-muted)]">{{ $seo['meta_title'] ?: 'No meta title yet' }}</p>
                    </div>
                    <button type="button" x-on:click="open = ! open" class="inline-flex items-center justify-center gap-2 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-3 py-2 text-sm font-medium text-[var(--color-ink)] transition-colors hover:bg-[var(--color-page)]">
                        <span x-text="open ? 'Collapse' : 'Expand'"></span>
                        <svg class="h-4 w-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </button>
                </div>
                <div x-cloak x-show="open" x-transition.opacity.duration.150ms class="space-y-5 border-t border-[var(--color-line)] px-6 py-6">
                    <x-ui.field label="Meta Title" for="contact-seo-title" :error="$errors->first('seo.meta_title')">
                        <x-ui.input id="contact-seo-title" wire:model.blur="seo.meta_title" placeholder="Contact Wide Web Blog" :invalid="$errors->has('seo.meta_title')" />
                    </x-ui.field>
                    <x-ui.field label="Meta Description" for="contact-seo-description" :error="$errors->first('seo.meta_description')">
                        <x-ui.textarea id="contact-seo-description" wire:model.blur="seo.meta_description" rows="4" placeholder="Summarize who should contact the team and what kinds of requests the page supports." :invalid="$errors->has('seo.meta_description')" />
                    </x-ui.field>
                </div>
            </section>
        </div>

        <aside class="space-y-6 xl:sticky xl:top-[5.5rem] xl:self-start">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Contact Summary</h2>
                    <p class="text-sm text-[var(--color-muted)]">A quick overview of singleton page readiness and key content sections.</p>
                </div>
                <div class="space-y-3">
                    @foreach ($summaryItems as $item)
                        <div class="rounded-[var(--radius-button)] border px-4 py-3 {{ $summaryCardClasses[$item['tone']] ?? $summaryCardClasses['default'] }}">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">{{ $item['label'] }}</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $item['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Revision Context</h2>
                    <p class="text-sm text-[var(--color-muted)]">The Contact Page is a singleton editorial resource. Save applies the full structured payload.</p>
                </div>
                <div class="space-y-3">
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Last Updated</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $updated_at ?? 'Unknown' }}</p>
                    </div>
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Updated By</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $updated_by['name'] ?? 'Unknown' }}</p>
                    </div>
                </div>
            </section>

            <section class="space-y-4 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-accent)_7%,white),white)] px-5 py-5 shadow-[var(--shadow-card)]">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Sections</p>
                <div class="space-y-2">
                    @foreach ($sections as $section)
                        <a href="#{{ $section['id'] }}" class="block rounded-[var(--radius-button)] border border-transparent px-3 py-2 transition-colors hover:border-[var(--color-line)] hover:bg-[var(--color-panel-soft)]">
                            <p class="text-sm font-medium text-[var(--color-ink)]">{{ $section['label'] }}</p>
                            <p class="mt-1 text-xs text-[var(--color-muted)]">{{ $section['detail'] }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>
</div>
