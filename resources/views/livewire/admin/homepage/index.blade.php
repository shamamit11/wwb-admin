<div class="space-y-6">
    <x-admin.page-header
        title="Homepage"
        description="Configure the hero, automatic homepage sections, promotional content, and homepage SEO through one structured editorial surface."
    >
        <x-ui.button as="a" :href="config('app.url')" variant="secondary" target="_blank" rel="noreferrer">Preview Site</x-ui.button>
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
            <section id="homepage-hero" class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Hero</h2>
                    <p class="text-sm text-[var(--color-muted)]">Lead with the homepage’s main promise, CTAs, and media reference.</p>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.field label="Eyebrow" for="homepage-hero-eyebrow" :error="$errors->first('hero.eyebrow')">
                        <x-ui.input id="homepage-hero-eyebrow" wire:model.blur="hero.eyebrow" placeholder="The Knowledge Hub" :invalid="$errors->has('hero.eyebrow')" />
                    </x-ui.field>

                    <x-ui.field label="Media Alt" for="homepage-hero-media-alt" :error="$errors->first('hero.media_alt')">
                        <x-ui.input id="homepage-hero-media-alt" wire:model.blur="hero.media_alt" placeholder="Laptop with analytics dashboard" :invalid="$errors->has('hero.media_alt')" />
                    </x-ui.field>
                </div>

                <x-ui.field label="Title" for="homepage-hero-title" :error="$errors->first('hero.title')">
                    <x-ui.input id="homepage-hero-title" wire:model.blur="hero.title" placeholder="Learn AI, SEO, Blogging, and Digital Growth" :invalid="$errors->has('hero.title')" />
                </x-ui.field>

                <x-ui.field label="Description" for="homepage-hero-description" :error="$errors->first('hero.description')">
                    <x-ui.textarea id="homepage-hero-description" wire:model.blur="hero.description" rows="4" placeholder="Authority-led insights and practical tutorials." :invalid="$errors->has('hero.description')" />
                </x-ui.field>

                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.field label="Primary CTA Label" for="homepage-hero-primary-label" :error="$errors->first('hero.primary_cta_label')">
                        <x-ui.input id="homepage-hero-primary-label" wire:model.blur="hero.primary_cta_label" placeholder="Start Reading" :invalid="$errors->has('hero.primary_cta_label')" />
                    </x-ui.field>
                    <x-ui.field label="Primary CTA URL" for="homepage-hero-primary-url" :error="$errors->first('hero.primary_cta_url')">
                        <x-ui.input id="homepage-hero-primary-url" wire:model.blur="hero.primary_cta_url" placeholder="/guides" :invalid="$errors->has('hero.primary_cta_url')" />
                    </x-ui.field>
                    <x-ui.field label="Secondary CTA Label" for="homepage-hero-secondary-label" :error="$errors->first('hero.secondary_cta_label')">
                        <x-ui.input id="homepage-hero-secondary-label" wire:model.blur="hero.secondary_cta_label" placeholder="View AI Tools" :invalid="$errors->has('hero.secondary_cta_label')" />
                    </x-ui.field>
                    <x-ui.field label="Secondary CTA URL" for="homepage-hero-secondary-url" :error="$errors->first('hero.secondary_cta_url')">
                        <x-ui.input id="homepage-hero-secondary-url" wire:model.blur="hero.secondary_cta_url" placeholder="/tools" :invalid="$errors->has('hero.secondary_cta_url')" />
                    </x-ui.field>
                </div>

                <x-ui.field label="Media URL" for="homepage-hero-media-url" :error="$errors->first('hero.media_url')">
                    <x-ui.input id="homepage-hero-media-url" wire:model.blur="hero.media_url" placeholder="https://cdn.example.com/home-hero.jpg" :invalid="$errors->has('hero.media_url')" />
                </x-ui.field>
            </section>

            <section id="homepage-featured" class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Featured Editorial</h2>
                    <p class="text-sm text-[var(--color-muted)]">This section is auto-populated from featured published posts. Editors can only adjust the label, supporting copy, and item limit.</p>
                </div>

                <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_11rem]">
                    <div class="space-y-5">
                        <x-ui.field label="Section Title" for="homepage-featured-title" :error="$errors->first('featured_editorial.title')">
                            <x-ui.input id="homepage-featured-title" wire:model.blur="featured_editorial.title" placeholder="Featured Editorial" :invalid="$errors->has('featured_editorial.title')" />
                        </x-ui.field>
                        <x-ui.field label="Description" for="homepage-featured-description" :error="$errors->first('featured_editorial.description')">
                            <x-ui.textarea id="homepage-featured-description" wire:model.blur="featured_editorial.description" rows="3" placeholder="Expert analysis on the evolving digital landscape." :invalid="$errors->has('featured_editorial.description')" />
                        </x-ui.field>
                    </div>
                    <x-ui.field label="Limit" for="homepage-featured-limit" :error="$errors->first('featured_editorial.limit')" hint="Controls how many featured published posts the service returns automatically.">
                        <x-ui.input id="homepage-featured-limit" wire:model.blur="featured_editorial.limit" placeholder="3" :invalid="$errors->has('featured_editorial.limit')" />
                    </x-ui.field>
                </div>
            </section>

            <section id="homepage-guides" class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Recent Articles</h2>
                    <p class="text-sm text-[var(--color-muted)]">This section is auto-populated from recent published posts. Editors can adjust the section copy and item limit only.</p>
                </div>

                <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_11rem]">
                    <div class="space-y-5">
                        <x-ui.field label="Section Title" for="homepage-guides-title" :error="$errors->first('guide_section.title')">
                            <x-ui.input id="homepage-guides-title" wire:model.blur="guide_section.title" placeholder="Recent Articles" :invalid="$errors->has('guide_section.title')" />
                        </x-ui.field>
                        <x-ui.field label="Description" for="homepage-guides-description" :error="$errors->first('guide_section.description')">
                            <x-ui.textarea id="homepage-guides-description" wire:model.blur="guide_section.description" rows="3" placeholder="Fresh analysis and practical reads from the latest published work." :invalid="$errors->has('guide_section.description')" />
                        </x-ui.field>
                    </div>
                    <x-ui.field label="Limit" for="homepage-guides-limit" :error="$errors->first('guide_section.limit')" hint="Controls how many recent published posts the service returns automatically.">
                        <x-ui.input id="homepage-guides-limit" wire:model.blur="guide_section.limit" placeholder="4" :invalid="$errors->has('guide_section.limit')" />
                    </x-ui.field>
                </div>
            </section>

            <section id="homepage-topics" class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Explore Core Topics</h2>
                    <p class="text-sm text-[var(--color-muted)]">This section is auto-populated from all active categories. Editors can update only the section label and supporting copy.</p>
                </div>

                <x-ui.field label="Section Title" for="homepage-topics-title" :error="$errors->first('topic_section.title')">
                    <x-ui.input id="homepage-topics-title" wire:model.blur="topic_section.title" placeholder="Explore Core Topics" :invalid="$errors->has('topic_section.title')" />
                </x-ui.field>

                <x-ui.field label="Description" for="homepage-topics-description" :error="$errors->first('topic_section.description')">
                    <x-ui.textarea id="homepage-topics-description" wire:model.blur="topic_section.description" rows="3" placeholder="Explore live category collections across the site’s active topics." :invalid="$errors->has('topic_section.description')" />
                </x-ui.field>
            </section>

            <section id="homepage-promo" class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Promo Section</h2>
                        <p class="text-sm text-[var(--color-muted)]">Configure the promoted resource block and its ordered bullets and stats.</p>
                    </div>

                    <label class="flex items-center gap-3 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                        <input wire:model.live="promo_section.enabled" type="checkbox" class="h-4 w-4 rounded border-[var(--color-line-strong)] text-[var(--color-accent)] focus:ring-[var(--color-ring)]">
                        <span class="text-sm font-medium text-[var(--color-ink)]">Enabled</span>
                    </label>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <x-ui.field label="Eyebrow" for="homepage-promo-eyebrow" :error="$errors->first('promo_section.eyebrow')">
                        <x-ui.input id="homepage-promo-eyebrow" wire:model.blur="promo_section.eyebrow" placeholder="Exclusive Resources" :invalid="$errors->has('promo_section.eyebrow')" />
                    </x-ui.field>
                    <x-ui.field label="Primary CTA Label" for="homepage-promo-primary-label" :error="$errors->first('promo_section.primary_cta_label')">
                        <x-ui.input id="homepage-promo-primary-label" wire:model.blur="promo_section.primary_cta_label" placeholder="Claim Your Free Kit" :invalid="$errors->has('promo_section.primary_cta_label')" />
                    </x-ui.field>
                </div>

                <x-ui.field label="Title" for="homepage-promo-title" :error="$errors->first('promo_section.title')">
                    <x-ui.input id="homepage-promo-title" wire:model.blur="promo_section.title" placeholder="Free Digital Creator Kit" :invalid="$errors->has('promo_section.title')" />
                </x-ui.field>

                <x-ui.field label="Description" for="homepage-promo-description" :error="$errors->first('promo_section.description')">
                    <x-ui.textarea id="homepage-promo-description" wire:model.blur="promo_section.description" rows="4" placeholder="Download our professional hub of free assets." :invalid="$errors->has('promo_section.description')" />
                </x-ui.field>

                <x-ui.field label="Primary CTA URL" for="homepage-promo-primary-url" :error="$errors->first('promo_section.primary_cta_url')">
                    <x-ui.input id="homepage-promo-primary-url" wire:model.blur="promo_section.primary_cta_url" placeholder="/resources/creator-kit" :invalid="$errors->has('promo_section.primary_cta_url')" />
                </x-ui.field>

                <div class="grid gap-5 lg:grid-cols-2">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Bullet Points</h3>
                            <x-ui.button type="button" variant="secondary" size="sm" wire:click="addListItem('promo_section', 'bullet_points')">Add Bullet</x-ui.button>
                        </div>
                        @error('promo_section.bullet_points')<p class="text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                        <div class="space-y-2">
                            @foreach ($promo_section['bullet_points'] as $index => $bullet)
                                <div wire:key="promo-bullet-{{ $index }}" class="flex items-center gap-2 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-3 py-3">
                                    <x-ui.input wire:model.blur="promo_section.bullet_points.{{ $index }}" placeholder="Bullet point" :invalid="$errors->has('promo_section.bullet_points.'.$index)" />
                                    <button type="button" wire:click="moveListItem('promo_section', 'bullet_points', {{ $index }}, 'up')" class="text-sm text-[var(--color-muted)]">↑</button>
                                    <button type="button" wire:click="moveListItem('promo_section', 'bullet_points', {{ $index }}, 'down')" class="text-sm text-[var(--color-muted)]">↓</button>
                                    <button type="button" wire:click="removeListItem('promo_section', 'bullet_points', {{ $index }})" class="text-sm text-[var(--color-danger-strong)]">Remove</button>
                                </div>
                                @error('promo_section.bullet_points.'.$index)<p class="text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Stats</h3>
                            <x-ui.button type="button" variant="secondary" size="sm" wire:click="addStat">Add Stat</x-ui.button>
                        </div>
                        @error('promo_section.stats')<p class="text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                        <div class="space-y-2">
                            @foreach ($promo_section['stats'] as $index => $stat)
                                <div wire:key="promo-stat-{{ $index }}" class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-3 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="grid min-w-0 flex-1 gap-2 sm:grid-cols-2">
                                            <x-ui.input wire:model.blur="promo_section.stats.{{ $index }}.label" placeholder="Label" :invalid="$errors->has('promo_section.stats.'.$index.'.label')" />
                                            <x-ui.input wire:model.blur="promo_section.stats.{{ $index }}.value" placeholder="Value" :invalid="$errors->has('promo_section.stats.'.$index.'.value')" />
                                        </div>
                                        <button type="button" wire:click="moveStat({{ $index }}, 'up')" class="text-sm text-[var(--color-muted)]">↑</button>
                                        <button type="button" wire:click="moveStat({{ $index }}, 'down')" class="text-sm text-[var(--color-muted)]">↓</button>
                                        <button type="button" wire:click="removeStat({{ $index }})" class="text-sm text-[var(--color-danger-strong)]">Remove</button>
                                    </div>
                                    @error('promo_section.stats.'.$index.'.label')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                                    @error('promo_section.stats.'.$index.'.value')<p class="mt-2 text-sm text-[var(--color-danger-strong)]">{{ $message }}</p>@enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section id="homepage-newsletter" class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Newsletter Section</h2>
                        <p class="text-sm text-[var(--color-muted)]">Manage the newsletter CTA copy without mixing it into generic settings.</p>
                    </div>

                    <label class="flex items-center gap-3 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3">
                        <input wire:model.live="newsletter_section.enabled" type="checkbox" class="h-4 w-4 rounded border-[var(--color-line-strong)] text-[var(--color-accent)] focus:ring-[var(--color-ring)]">
                        <span class="text-sm font-medium text-[var(--color-ink)]">Enabled</span>
                    </label>
                </div>

                <x-ui.field label="Title" for="homepage-newsletter-title" :error="$errors->first('newsletter_section.title')">
                    <x-ui.input id="homepage-newsletter-title" wire:model.blur="newsletter_section.title" placeholder="Stay Ahead of the Curve" :invalid="$errors->has('newsletter_section.title')" />
                </x-ui.field>

                <x-ui.field label="Description" for="homepage-newsletter-description" :error="$errors->first('newsletter_section.description')">
                    <x-ui.textarea id="homepage-newsletter-description" wire:model.blur="newsletter_section.description" rows="4" placeholder="Join 25,000+ digital architects." :invalid="$errors->has('newsletter_section.description')" />
                </x-ui.field>
            </section>

            <section id="homepage-seo" class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Homepage SEO</h2>
                    <p class="text-sm text-[var(--color-muted)]">Keep homepage-specific search metadata explicit and separate from page-builder concerns.</p>
                </div>

                <x-ui.field label="Meta Title" for="homepage-seo-title" :error="$errors->first('seo.meta_title')">
                    <x-ui.input id="homepage-seo-title" wire:model.blur="seo.meta_title" placeholder="Wide Web Blog" :invalid="$errors->has('seo.meta_title')" />
                </x-ui.field>

                <x-ui.field label="Meta Description" for="homepage-seo-description" :error="$errors->first('seo.meta_description')">
                    <x-ui.textarea id="homepage-seo-description" wire:model.blur="seo.meta_description" rows="4" placeholder="Technical SEO, AI, and digital growth insights." :invalid="$errors->has('seo.meta_description')" />
                </x-ui.field>
            </section>
        </div>

        <aside class="space-y-6 xl:sticky xl:top-6 xl:self-start">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Homepage Summary</h2>
                    <p class="text-sm text-[var(--color-muted)]">A quick overview of the current section state and automatic content sources.</p>
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
                    <p class="text-sm text-[var(--color-muted)]">The homepage is a singleton editorial resource. Save applies the full structured payload while featured posts, recent articles, and core topics are populated automatically by the service.</p>
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
                    <a href="#homepage-hero" class="block rounded-[var(--radius-button)] px-3 py-2 text-sm text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)]">Hero</a>
                    <a href="#homepage-featured" class="block rounded-[var(--radius-button)] px-3 py-2 text-sm text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)]">Featured Editorial</a>
                    <a href="#homepage-guides" class="block rounded-[var(--radius-button)] px-3 py-2 text-sm text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)]">Recent Articles</a>
                    <a href="#homepage-topics" class="block rounded-[var(--radius-button)] px-3 py-2 text-sm text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)]">Explore Core Topics</a>
                    <a href="#homepage-promo" class="block rounded-[var(--radius-button)] px-3 py-2 text-sm text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)]">Promo Block</a>
                    <a href="#homepage-newsletter" class="block rounded-[var(--radius-button)] px-3 py-2 text-sm text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)]">Newsletter</a>
                    <a href="#homepage-seo" class="block rounded-[var(--radius-button)] px-3 py-2 text-sm text-[var(--color-ink)] transition-colors hover:bg-[var(--color-panel-soft)]">Homepage SEO</a>
                </div>
            </section>
        </aside>
    </div>
</div>
