<div class="space-y-6">
    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">
        <x-ui.card>
            <div class="space-y-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">{{ $eyebrow }}</p>
                    <h2 class="mt-3 text-2xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">{{ $moduleLabel }}</h2>
                    <p class="mt-3 text-sm leading-7 text-[var(--color-muted)]">{{ $moduleDescription }}</p>
                </div>

                <x-ui.separator />

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Primary Action" hint="{{ $primaryActionHint }}" for="placeholder-action">
                        <x-ui.input id="placeholder-action" type="text" :value="$primaryActionLabel" disabled />
                    </x-ui.field>

                    <x-ui.field label="Module State" hint="Placeholder modules are intentionally explicit until service-backed flows land." for="placeholder-state">
                        <x-ui.input
                            id="placeholder-state"
                            type="text"
                            :value="$roadmap ? 'Roadmap placeholder' : 'Route skeleton ready'"
                            disabled
                        />
                    </x-ui.field>
                </div>

                <x-ui.empty-state
                    :title="$roadmap ? 'Roadmap-only module' : 'Screen scaffolded'"
                    :message="$roadmap
                        ? 'This section is visible in navigation for planning continuity, but no service endpoints exist yet.'
                        : 'The route, page shell, and navigation entry are ready. Data integration and CRUD flows belong to later tasks.'"
                >
                    <x-ui.button variant="outline" disabled>
                        {{ $roadmap ? 'API Not Available Yet' : 'CRUD Build Next' }}
                    </x-ui.button>
                </x-ui.empty-state>
            </div>
        </x-ui.card>

        <div class="space-y-6">
            <x-ui.card class="bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-panel)_70%,white),var(--color-panel))]">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Immediate Build Path</p>
                <ul class="mt-4 space-y-3 text-sm text-[var(--color-muted)]">
                    @foreach ($nextSteps as $step)
                        <li class="flex items-start gap-3">
                            <x-ui.badge tone="{{ $roadmap ? 'warning' : 'default' }}" class="mt-0.5">
                                {{ $loop->iteration }}
                            </x-ui.badge>
                            <span>{{ $step }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>

            <x-ui.card>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Route Skeleton</p>
                <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">
                    Current-section highlighting, protected access, and a stable navigation surface are now available for this module.
                </p>
            </x-ui.card>
        </div>
    </section>
</div>
