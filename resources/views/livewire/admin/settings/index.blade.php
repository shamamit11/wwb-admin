<div class="space-y-6">
    <x-admin.page-header
        title="Settings"
        description="Review safe operational configuration summaries without inventing unsupported service-backed settings flows."
    >
        <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-3 text-sm text-[var(--color-muted)]">
            This screen is read-only until broader settings endpoints exist in the service contract.
        </div>
    </x-admin.page-header>

    <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[color-mix(in_srgb,var(--color-warning)_7%,white)] px-5 py-4 text-sm text-[var(--color-muted)] shadow-[var(--shadow-card)]">
        No service-backed settings endpoint exists yet for broad admin configuration. Tabs below only expose safe summaries that are already backed by local config or existing operational modules.
    </div>

    <x-ui.tabs>
        <x-ui.tabs-list>
            @foreach ($tabs as $tab)
                <x-ui.tabs-trigger
                    :href="route('settings.index', $tab['key'] === 'general' ? [] : ['tab' => $tab['key']])"
                    :active="$activeTab === $tab['key']"
                >
                    {{ $tab['label'] }}
                </x-ui.tabs-trigger>
            @endforeach
        </x-ui.tabs-list>
    </x-ui.tabs>

    @if ($activeTab === 'general')
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(18rem,0.8fr)]">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">General Environment</h2>
                    <p class="text-sm text-[var(--color-muted)]">These values describe the running admin surface. They are shown for operational awareness only.</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($generalSummary as $item)
                        <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">{{ $item['label'] }}</p>
                            <p class="mt-2 break-all text-sm text-[var(--color-ink)]">{{ $item['value'] !== '' ? $item['value'] : 'TBC' }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="space-y-6">
                <x-ui.tabs-panel>
                    <div class="space-y-2">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Editable Scope</h2>
                        <p class="text-sm text-[var(--color-muted)]">General settings are not writable here in MVP. This section exists to make the boundary explicit rather than imply missing controls.</p>
                    </div>
                </x-ui.tabs-panel>

                <section class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Current Constraint</p>
                    <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">Branding, locale defaults, maintenance controls, and other application-wide settings should stay config-managed until backend support defines a proper service-backed write contract.</p>
                </section>
            </div>
        </div>
    @elseif ($activeTab === 'publishing')
        <div class="grid gap-6 xl:grid-cols-2">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Operational Publishing Surface</h2>
                    <p class="text-sm text-[var(--color-muted)]">These are the publishing controls the admin already exposes through real module screens and service-backed flows.</p>
                </div>

                <div class="space-y-3">
                    @foreach ($publishingSummary['operational'] as $item)
                        <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3 text-sm text-[var(--color-ink)]">
                            {{ $item }}
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Unsupported Publishing Configuration</h2>
                    <p class="text-sm text-[var(--color-muted)]">This tab does not fabricate settings forms where the service contract has not defined them.</p>
                </div>

                <div class="space-y-3">
                    @foreach ($publishingSummary['unsupported'] as $item)
                        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-warning)_20%,white)] bg-[color-mix(in_srgb,var(--color-warning)_8%,white)] px-4 py-3 text-sm text-[var(--color-warning-strong)]">
                            {{ $item }}
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    @elseif ($activeTab === 'storage')
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(18rem,0.9fr)]">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Storage Configuration Summary</h2>
                    <p class="text-sm text-[var(--color-muted)]">Storage values are shown only at a summary level. Secrets and driver credentials stay hidden.</p>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Default Disk</p>
                        <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $storageSummary['default_disk'] }}</p>
                    </div>
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Media Base URL</p>
                        <p class="mt-2 break-all text-sm text-[var(--color-ink)]">{{ $storageSummary['media_base_url'] }}</p>
                    </div>
                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Public Disk URL</p>
                        <p class="mt-2 break-all text-sm text-[var(--color-ink)]">{{ $storageSummary['public_url'] }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Configured Disks</h3>
                    @foreach ($storageSummary['disks'] as $disk)
                        <div class="flex flex-col gap-2 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-[var(--color-ink)]">{{ $disk['name'] }}</p>
                                <p class="mt-1 text-sm text-[var(--color-muted)]">Driver: {{ $disk['driver'] }}</p>
                            </div>
                            <x-ui.badge tone="{{ $disk['visibility'] === 'public' ? 'success' : 'default' }}">
                                {{ str($disk['visibility'])->headline() }}
                            </x-ui.badge>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Boundary</p>
                <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">Bucket names, access keys, custom endpoints, and any future media-provider credentials remain intentionally hidden until there is an explicit secure management design.</p>
            </section>
        </div>
    @elseif ($activeTab === 'ai')
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(18rem,0.9fr)]">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">AI Configuration</h2>
                    <p class="text-sm text-[var(--color-muted)]">AI configuration stays placeholder-only in MVP. No service-backed settings or job-control endpoints are available here yet.</p>
                </div>

                <x-ui.empty-state
                    title="AI settings are not service-backed yet"
                    message="Do not imply provider selection, prompt policies, model tuning, or budget controls until the backend contract explicitly supports them."
                >
                    <x-ui.button variant="outline" disabled>Await Service Contract</x-ui.button>
                </x-ui.empty-state>
            </section>

            <section class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Current State</p>
                <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">AI jobs and advanced AI settings remain roadmap items. Sensitive provider secrets should not appear in this admin until a dedicated secure flow exists.</p>
            </section>
        </div>
    @else
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(18rem,0.85fr)]">
            <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Service Integration Summary</h2>
                    <p class="text-sm text-[var(--color-muted)]">Integration details are shown at a transport and session-bridge level only. Tokens and secrets are never rendered here.</p>
                </div>

                <div class="space-y-4">
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Service API</h3>
                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach ($integrationSummary['service_api'] as $item)
                                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">{{ $item['label'] }}</p>
                                    <p class="mt-2 break-all text-sm text-[var(--color-ink)]">{{ $item['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Admin Session Bridge</h3>
                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach ($integrationSummary['session_bridge'] as $item)
                                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">{{ $item['label'] }}</p>
                                    <p class="mt-2 break-all text-sm text-[var(--color-ink)]">{{ $item['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Security Note</p>
                <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">This view intentionally stops at non-secret operational metadata. API tokens, cloud credentials, webhook secrets, and provider keys should not be surfaced by the UI without explicit product and backend support.</p>
            </section>
        </div>
    @endif
</div>
