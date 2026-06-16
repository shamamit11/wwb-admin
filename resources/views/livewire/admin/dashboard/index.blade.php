<div class="space-y-8">
    <section class="grid gap-6 lg:grid-cols-3">
        <x-admin.stat-card label="Runtime" value="Laravel 13" tone="accent">
            <x-ui.badge tone="success">Installed</x-ui.badge>
        </x-admin.stat-card>
        <x-admin.stat-card label="Interactivity" value="Livewire 4" tone="soft">
            <x-ui.badge tone="success">Mounted</x-ui.badge>
        </x-admin.stat-card>
        <x-admin.stat-card label="Component Direction" value="Blade UI" tone="default">
            <x-ui.badge>Shadcn-inspired</x-ui.badge>
        </x-admin.stat-card>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(320px,0.9fr)]">
        <x-ui.card class="overflow-hidden">
            <div class="space-y-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Current Scope</p>
                    <ul class="mt-5 space-y-3 text-sm text-[var(--color-muted)]">
                        <li class="flex items-start gap-3">
                            <x-ui.badge tone="success" class="mt-0.5">Done</x-ui.badge>
                            <span>Laravel 13 skeleton merged into the repo.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <x-ui.badge tone="success" class="mt-0.5">Done</x-ui.badge>
                            <span>Livewire installed and mounted through a full-page dashboard component.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <x-ui.badge class="mt-0.5">Next</x-ui.badge>
                            <span>Auth flow, API client layer, and reusable CRUD patterns.</span>
                        </li>
                    </ul>
                </div>

                <x-ui.separator />

                <div class="flex flex-wrap gap-3">
                    <x-ui.button as="a" href="https://laravel.com/docs/13.x" target="_blank" rel="noreferrer">Laravel 13 Docs</x-ui.button>
                    <x-ui.button as="a" href="https://livewire.laravel.com" target="_blank" rel="noreferrer" variant="secondary">Livewire Docs</x-ui.button>
                </div>

                <x-ui.separator />

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Search Pattern" hint="Illustrative field styling for the shared component system." for="search-pattern">
                        <x-ui.input id="search-pattern" type="text" placeholder="posts, categories, templates..." />
                    </x-ui.field>

                    <x-ui.field label="Preferred Surface" hint="Use shared select primitives for structured admin forms." for="surface-style">
                        <x-ui.select id="surface-style">
                            <option>Table-first management</option>
                            <option>Editor-first authoring</option>
                        </x-ui.select>
                    </x-ui.field>
                </div>

                <x-ui.field label="Implementation Note" hint="Textarea styling is ready for future editorial and SEO forms." for="implementation-note">
                    <x-ui.textarea id="implementation-note" rows="4" placeholder="Blade-native shadcn-inspired system, no React components..."></x-ui.textarea>
                </x-ui.field>
            </div>
        </x-ui.card>

        <div class="space-y-6">
            <x-ui.card class="bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-panel)_70%,white),var(--color-panel))]">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Technical Boundary</p>
                <h3 class="mt-3 text-xl font-semibold tracking-[-0.02em]">Service API remains the source of truth</h3>
                <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">
                    The admin owns session UX, screens, and API client integration. Business logic and persistence stay in the service application.
                </p>
            </x-ui.card>

            <x-ui.empty-state
                title="No editorial data yet"
                message="The app shell and component system are ready. Auth, API wiring, and CRUD workflows come next."
            >
                <x-ui.button variant="outline" disabled>Content Modules Pending</x-ui.button>
            </x-ui.empty-state>
        </div>
    </section>
</div>
