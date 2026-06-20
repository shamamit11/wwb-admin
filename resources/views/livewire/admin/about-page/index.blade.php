<div class="space-y-6">
    <x-admin.page-header
        title="About Us"
        description="Manage the singleton About Page content, team presentation, and SEO through one structured service-backed editor."
    >
        <x-ui.button as="a" :href="config('app.url').'/about'" variant="secondary" target="_blank" rel="noreferrer">Preview Page</x-ui.button>
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
        <div class="space-y-6">
            <section id="about-hero" class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Hero</h2>
                    <p class="text-sm text-[var(--color-muted)]">Lead the About Page with a clear editorial promise and a single media reference.</p>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.field label="Eyebrow" for="about-hero-eyebrow" :error="$errors->first('hero.eyebrow')">
                        <x-ui.input id="about-hero-eyebrow" wire:model.blur="hero.eyebrow" placeholder="Who We Are" :invalid="$errors->has('hero.eyebrow')" />
                    </x-ui.field>

                    <x-ui.field label="Media Alt" for="about-hero-media-alt" :error="$errors->first('hero.media_alt')">
                        <x-ui.input id="about-hero-media-alt" wire:model.blur="hero.media_alt" placeholder="Editorial team collaborating" :invalid="$errors->has('hero.media_alt')" />
                    </x-ui.field>
                </div>

                <x-ui.field label="Title" for="about-hero-title" :error="$errors->first('hero.title')">
                    <x-ui.input id="about-hero-title" wire:model.blur="hero.title" placeholder="Building practical publishing systems for modern creators" :invalid="$errors->has('hero.title')" />
                </x-ui.field>

                <x-ui.field label="Description" for="about-hero-description" :error="$errors->first('hero.description')">
                    <x-ui.textarea id="about-hero-description" wire:model.blur="hero.description" rows="4" placeholder="Share the mission, editorial perspective, and audience promise behind the publication." :invalid="$errors->has('hero.description')" />
                </x-ui.field>

                <x-ui.field label="Media URL" for="about-hero-media-url" :error="$errors->first('hero.media_url')">
                    <x-ui.input id="about-hero-media-url" wire:model.blur="hero.media_url" placeholder="https://cdn.example.com/about-hero.jpg" :invalid="$errors->has('hero.media_url')" />
                </x-ui.field>
            </section>

            <section id="about-mission" class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Mission</h2>
                    <p class="text-sm text-[var(--color-muted)]">Explain the purpose behind the publication and capture the defining quote that shapes its voice.</p>
                </div>

                <x-ui.field label="Section Title" for="about-mission-title" :error="$errors->first('mission_section.title')">
                    <x-ui.input id="about-mission-title" wire:model.blur="mission_section.title" placeholder="Our Mission" :invalid="$errors->has('mission_section.title')" />
                </x-ui.field>

                <x-ui.field label="Description" for="about-mission-description" :error="$errors->first('mission_section.description')">
                    <x-ui.textarea id="about-mission-description" wire:model.blur="mission_section.description" rows="5" placeholder="Describe the long-term editorial mission and the value readers should expect." :invalid="$errors->has('mission_section.description')" />
                </x-ui.field>

                <x-ui.field label="Quote" for="about-mission-quote" :error="$errors->first('mission_section.quote')">
                    <x-ui.textarea id="about-mission-quote" wire:model.blur="mission_section.quote" rows="3" placeholder="A short line that captures the publication’s point of view." :invalid="$errors->has('mission_section.quote')" />
                </x-ui.field>
            </section>

            <section id="about-stats" class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Stats</h2>
                        <p class="text-sm text-[var(--color-muted)]">Maintain ordered stat callouts shown on the About Page.</p>
                    </div>

                    <x-ui.button type="button" variant="secondary" size="sm" wire:click="addListItem('stats_section', 'items')">Add Stat</x-ui.button>
                </div>

                @error('stats_section.items')<p class="text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror

                <div class="space-y-3">
                    @forelse ($stats_section['items'] as $index => $item)
                        <div wire:key="about-stat-{{ $index }}" class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                            <div class="flex items-start gap-3">
                                <div class="grid min-w-0 flex-1 gap-3 sm:grid-cols-2">
                                    <x-ui.input wire:model.blur="stats_section.items.{{ $index }}.label" placeholder="Label" :invalid="$errors->has('stats_section.items.'.$index.'.label')" />
                                    <x-ui.input wire:model.blur="stats_section.items.{{ $index }}.value" placeholder="Value" :invalid="$errors->has('stats_section.items.'.$index.'.value')" />
                                </div>
                                <button type="button" wire:click="moveListItem('stats_section', 'items', {{ $index }}, 'up')" class="text-sm text-[var(--color-muted)]">↑</button>
                                <button type="button" wire:click="moveListItem('stats_section', 'items', {{ $index }}, 'down')" class="text-sm text-[var(--color-muted)]">↓</button>
                                <button type="button" wire:click="removeListItem('stats_section', 'items', {{ $index }})" class="text-sm text-[var(--color-danger-strong)]">Remove</button>
                            </div>
                            @error('stats_section.items.'.$index.'.label')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                            @error('stats_section.items.'.$index.'.value')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                        </div>
                    @empty
                        <div class="rounded-[var(--radius-button)] border border-dashed border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4 text-sm text-[var(--color-muted)]">No stats added yet.</div>
                    @endforelse
                </div>
            </section>

            <section id="about-values" class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Values</h2>
                        <p class="text-sm text-[var(--color-muted)]">Define the editorial values or operating principles shown on the page.</p>
                    </div>

                    <x-ui.button type="button" variant="secondary" size="sm" wire:click="addListItem('values_section', 'items')">Add Value</x-ui.button>
                </div>

                <x-ui.field label="Section Title" for="about-values-title" :error="$errors->first('values_section.title')">
                    <x-ui.input id="about-values-title" wire:model.blur="values_section.title" placeholder="Our Values" :invalid="$errors->has('values_section.title')" />
                </x-ui.field>

                @error('values_section.items')<p class="text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror

                <div class="space-y-3">
                    @forelse ($values_section['items'] as $index => $item)
                        <div wire:key="about-value-{{ $index }}" class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                            <div class="flex items-start gap-3">
                                <div class="min-w-0 flex-1 space-y-3">
                                    <div class="grid gap-3 sm:grid-cols-[12rem_minmax(0,1fr)]">
                                        <x-ui.input wire:model.blur="values_section.items.{{ $index }}.icon" placeholder="Icon token" :invalid="$errors->has('values_section.items.'.$index.'.icon')" />
                                        <x-ui.input wire:model.blur="values_section.items.{{ $index }}.title" placeholder="Value title" :invalid="$errors->has('values_section.items.'.$index.'.title')" />
                                    </div>
                                    <x-ui.textarea wire:model.blur="values_section.items.{{ $index }}.description" rows="3" placeholder="Explain what this value means in practice." :invalid="$errors->has('values_section.items.'.$index.'.description')" />
                                </div>
                                <button type="button" wire:click="moveListItem('values_section', 'items', {{ $index }}, 'up')" class="text-sm text-[var(--color-muted)]">↑</button>
                                <button type="button" wire:click="moveListItem('values_section', 'items', {{ $index }}, 'down')" class="text-sm text-[var(--color-muted)]">↓</button>
                                <button type="button" wire:click="removeListItem('values_section', 'items', {{ $index }})" class="text-sm text-[var(--color-danger-strong)]">Remove</button>
                            </div>
                            @error('values_section.items.'.$index.'.icon')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                            @error('values_section.items.'.$index.'.title')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                            @error('values_section.items.'.$index.'.description')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                        </div>
                    @empty
                        <div class="rounded-[var(--radius-button)] border border-dashed border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4 text-sm text-[var(--color-muted)]">No values added yet.</div>
                    @endforelse
                </div>
            </section>

            <section id="about-team" class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Team</h2>
                        <p class="text-sm text-[var(--color-muted)]">Control the team introduction, CTA, and ordered member list presented on the About Page.</p>
                    </div>

                    <x-ui.button type="button" variant="secondary" size="sm" wire:click="addListItem('team_section', 'members')">Add Member</x-ui.button>
                </div>

                <x-ui.field label="Section Title" for="about-team-title" :error="$errors->first('team_section.title')">
                    <x-ui.input id="about-team-title" wire:model.blur="team_section.title" placeholder="Meet the Team" :invalid="$errors->has('team_section.title')" />
                </x-ui.field>

                <x-ui.field label="Description" for="about-team-description" :error="$errors->first('team_section.description')">
                    <x-ui.textarea id="about-team-description" wire:model.blur="team_section.description" rows="4" placeholder="Introduce the people behind the publication and the perspective they bring." :invalid="$errors->has('team_section.description')" />
                </x-ui.field>

                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.field label="Primary CTA Label" for="about-team-cta-label" :error="$errors->first('team_section.primary_cta_label')">
                        <x-ui.input id="about-team-cta-label" wire:model.blur="team_section.primary_cta_label" placeholder="Work With Us" :invalid="$errors->has('team_section.primary_cta_label')" />
                    </x-ui.field>
                    <x-ui.field label="Primary CTA URL" for="about-team-cta-url" :error="$errors->first('team_section.primary_cta_url')">
                        <x-ui.input id="about-team-cta-url" wire:model.blur="team_section.primary_cta_url" placeholder="https://example.com/contact" :invalid="$errors->has('team_section.primary_cta_url')" />
                    </x-ui.field>
                </div>

                @error('team_section.members')<p class="text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror

                <div class="space-y-3">
                    @forelse ($team_section['members'] as $index => $member)
                        <div wire:key="about-member-{{ $index }}" class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                            <div class="flex items-start gap-3">
                                <div class="min-w-0 flex-1 space-y-3">
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <x-ui.input wire:model.blur="team_section.members.{{ $index }}.name" placeholder="Name" :invalid="$errors->has('team_section.members.'.$index.'.name')" />
                                        <x-ui.input wire:model.blur="team_section.members.{{ $index }}.role" placeholder="Role" :invalid="$errors->has('team_section.members.'.$index.'.role')" />
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <x-ui.input wire:model.blur="team_section.members.{{ $index }}.image_url" placeholder="https://cdn.example.com/member.jpg" :invalid="$errors->has('team_section.members.'.$index.'.image_url')" />
                                        <x-ui.input wire:model.blur="team_section.members.{{ $index }}.image_alt" placeholder="Portrait alt text" :invalid="$errors->has('team_section.members.'.$index.'.image_alt')" />
                                    </div>
                                </div>
                                <button type="button" wire:click="moveListItem('team_section', 'members', {{ $index }}, 'up')" class="text-sm text-[var(--color-muted)]">↑</button>
                                <button type="button" wire:click="moveListItem('team_section', 'members', {{ $index }}, 'down')" class="text-sm text-[var(--color-muted)]">↓</button>
                                <button type="button" wire:click="removeListItem('team_section', 'members', {{ $index }})" class="text-sm text-[var(--color-danger-strong)]">Remove</button>
                            </div>
                            @error('team_section.members.'.$index.'.name')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                            @error('team_section.members.'.$index.'.role')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                            @error('team_section.members.'.$index.'.image_url')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                            @error('team_section.members.'.$index.'.image_alt')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                        </div>
                    @empty
                        <div class="rounded-[var(--radius-button)] border border-dashed border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4 text-sm text-[var(--color-muted)]">No team members added yet.</div>
                    @endforelse
                </div>
            </section>

            <section id="about-seo" class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">SEO</h2>
                    <p class="text-sm text-[var(--color-muted)]">Keep About Page metadata explicit and separate from the body content.</p>
                </div>

                <x-ui.field label="Meta Title" for="about-seo-title" :error="$errors->first('seo.meta_title')">
                    <x-ui.input id="about-seo-title" wire:model.blur="seo.meta_title" placeholder="About Wide Web Blog" :invalid="$errors->has('seo.meta_title')" />
                </x-ui.field>

                <x-ui.field label="Meta Description" for="about-seo-description" :error="$errors->first('seo.meta_description')">
                    <x-ui.textarea id="about-seo-description" wire:model.blur="seo.meta_description" rows="4" placeholder="Summarize the publication and the team behind it for search and sharing surfaces." :invalid="$errors->has('seo.meta_description')" />
                </x-ui.field>
            </section>
        </div>

        <aside class="space-y-6 xl:sticky xl:top-6 xl:self-start">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">About Summary</h2>
                    <p class="text-sm text-[var(--color-muted)]">A quick overview of the singleton page sections and list counts.</p>
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
                    <p class="text-sm text-[var(--color-muted)]">The About Page is a singleton editorial resource. Save applies the full structured payload.</p>
                </div>

                <div class="space-y-3">
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Last Updated</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $updated_at ?? 'Unknown' }}</p>
                    </div>
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Updated By</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $updated_by['name'] ?? 'Unknown' }}</p>
                        @if (! empty($updated_by['email']))
                            <p class="mt-1 text-xs text-[var(--color-muted)]">{{ $updated_by['email'] }}</p>
                        @endif
                    </div>
                </div>
            </section>

            <section class="space-y-4 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-accent)_7%,white),white)] px-5 py-5 shadow-[var(--shadow-card)]">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Sections</p>
                <div class="space-y-2">
                    <a href="#about-hero" class="block rounded-[var(--radius-button)] px-3 py-2 text-sm text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)]">Hero</a>
                    <a href="#about-mission" class="block rounded-[var(--radius-button)] px-3 py-2 text-sm text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)]">Mission</a>
                    <a href="#about-stats" class="block rounded-[var(--radius-button)] px-3 py-2 text-sm text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)]">Stats</a>
                    <a href="#about-values" class="block rounded-[var(--radius-button)] px-3 py-2 text-sm text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)]">Values</a>
                    <a href="#about-team" class="block rounded-[var(--radius-button)] px-3 py-2 text-sm text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)]">Team</a>
                    <a href="#about-seo" class="block rounded-[var(--radius-button)] px-3 py-2 text-sm text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)]">SEO</a>
                </div>
            </section>
        </aside>
    </div>
</div>
