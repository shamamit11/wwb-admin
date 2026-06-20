<div class="space-y-6">
    <x-admin.page-header
        title="Site Settings"
        description="Manage the singleton footer configuration through the dedicated service-backed site settings contract."
    >
        <x-ui.button as="a" :href="route('settings.index')" variant="secondary">Back to Settings</x-ui.button>
        <x-ui.button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
            <span wire:loading.remove wire:target="save">Save Footer Settings</span>
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
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Footer Overview</h2>
                    <p class="text-sm text-[var(--color-muted)]">Set the footer brand name and supporting description shown across the public site.</p>
                </div>

                <x-ui.field label="Brand Name" for="footer-brand-name" :error="$errors->first('footer.brand_name')">
                    <x-ui.input id="footer-brand-name" wire:model.blur="footer.brand_name" placeholder="Wide Web Blog" :invalid="$errors->has('footer.brand_name')" />
                </x-ui.field>

                <x-ui.field label="Description" for="footer-description" :error="$errors->first('footer.description')">
                    <x-ui.textarea id="footer-description" wire:model.blur="footer.description" rows="4" placeholder="Summarize the editorial focus and value of the publication." :invalid="$errors->has('footer.description')" />
                </x-ui.field>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Social Links</h2>
                        <p class="text-sm text-[var(--color-muted)]">Maintain the ordered set of social or distribution links shown in the footer. URL values stay flexible so mailto links and custom routes can be stored unchanged.</p>
                    </div>

                    <x-ui.button type="button" variant="secondary" size="sm" wire:click="addListItem('footer', 'social_links')">Add Social Link</x-ui.button>
                </div>

                @error('footer.social_links')<p class="text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror

                <div class="space-y-3">
                    @forelse ($footer['social_links'] as $index => $item)
                        <div wire:key="footer-social-link-{{ $index }}" class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                            <div class="flex items-start gap-3">
                                <div class="min-w-0 flex-1 space-y-3">
                                    <div class="grid gap-3 lg:grid-cols-2">
                                        <x-ui.input wire:model.blur="footer.social_links.{{ $index }}.label" placeholder="Label" :invalid="$errors->has('footer.social_links.'.$index.'.label')" />
                                        <x-ui.input wire:model.blur="footer.social_links.{{ $index }}.icon" placeholder="Icon" :invalid="$errors->has('footer.social_links.'.$index.'.icon')" />
                                    </div>
                                    <x-ui.input wire:model.blur="footer.social_links.{{ $index }}.url" placeholder="https://..., mailto:..., or /route" :invalid="$errors->has('footer.social_links.'.$index.'.url')" />
                                </div>
                                <button type="button" wire:click="moveListItem('footer', 'social_links', {{ $index }}, 'up')" class="text-sm text-[var(--color-muted)]">↑</button>
                                <button type="button" wire:click="moveListItem('footer', 'social_links', {{ $index }}, 'down')" class="text-sm text-[var(--color-muted)]">↓</button>
                                <button type="button" wire:click="removeListItem('footer', 'social_links', {{ $index }})" class="text-sm text-[var(--color-danger-strong)]">Remove</button>
                            </div>
                            @error('footer.social_links.'.$index.'.label')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                            @error('footer.social_links.'.$index.'.url')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                            @error('footer.social_links.'.$index.'.icon')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                        </div>
                    @empty
                        <div class="rounded-[var(--radius-button)] border border-dashed border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4 text-sm text-[var(--color-muted)]">No social links added yet.</div>
                    @endforelse
                </div>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Legal Links</h2>
                        <p class="text-sm text-[var(--color-muted)]">Manage footer legal links. Use <span class="font-medium text-[var(--color-ink)]">slug</span> for internal page links and <span class="font-medium text-[var(--color-ink)]">url</span> for external or explicit links.</p>
                    </div>

                    <x-ui.button type="button" variant="secondary" size="sm" wire:click="addListItem('footer', 'legal_links')">Add Legal Link</x-ui.button>
                </div>

                @error('footer.legal_links')<p class="text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror

                <div class="space-y-3">
                    @forelse ($footer['legal_links'] as $index => $item)
                        <div wire:key="footer-legal-link-{{ $index }}" class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                            <div class="flex items-start gap-3">
                                <div class="min-w-0 flex-1 space-y-3">
                                    <x-ui.input wire:model.blur="footer.legal_links.{{ $index }}.label" placeholder="Label" :invalid="$errors->has('footer.legal_links.'.$index.'.label')" />
                                    <div class="grid gap-3 lg:grid-cols-2">
                                        <x-ui.input wire:model.blur="footer.legal_links.{{ $index }}.slug" placeholder="privacy-policy" :invalid="$errors->has('footer.legal_links.'.$index.'.slug')" />
                                        <x-ui.input wire:model.blur="footer.legal_links.{{ $index }}.url" placeholder="https://..., /terms, or leave blank" :invalid="$errors->has('footer.legal_links.'.$index.'.url')" />
                                    </div>
                                    <p class="text-xs text-[var(--color-muted)]">Leave either field blank when it does not apply. If both are provided, both are saved.</p>
                                </div>
                                <button type="button" wire:click="moveListItem('footer', 'legal_links', {{ $index }}, 'up')" class="text-sm text-[var(--color-muted)]">↑</button>
                                <button type="button" wire:click="moveListItem('footer', 'legal_links', {{ $index }}, 'down')" class="text-sm text-[var(--color-muted)]">↓</button>
                                <button type="button" wire:click="removeListItem('footer', 'legal_links', {{ $index }})" class="text-sm text-[var(--color-danger-strong)]">Remove</button>
                            </div>
                            @error('footer.legal_links.'.$index.'.label')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                            @error('footer.legal_links.'.$index.'.slug')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                            @error('footer.legal_links.'.$index.'.url')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                        </div>
                    @empty
                        <div class="rounded-[var(--radius-button)] border border-dashed border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4 text-sm text-[var(--color-muted)]">No legal links added yet.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="space-y-6 xl:sticky xl:top-6 xl:self-start">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Footer Summary</h2>
                    <p class="text-sm text-[var(--color-muted)]">A quick overview of the singleton footer settings payload.</p>
                </div>

                <div class="space-y-3">
                    @foreach ($sectionSummary as $item)
                        <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">{{ $item['label'] }}</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $item['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Revision Context</h2>
                    <p class="text-sm text-[var(--color-muted)]">Site settings are a singleton resource. Save applies the full footer payload in order.</p>
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
        </aside>
    </div>
</div>
