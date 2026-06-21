<div class="space-y-6">
    <x-admin.page-header
        title="Settings"
        description="Review safe operational configuration summaries and jump into the dedicated singleton editor for service-backed site settings."
    >
        <x-ui.button as="a" :href="route('site-settings.index')" variant="secondary">Open Site Settings</x-ui.button>
    </x-admin.page-header>

    <x-admin.callout tone="warning">
        The only writable service-backed settings flow currently available here is the dedicated Site Settings footer editor. Prompt instructions now live in Standard Prompts, not site settings.
    </x-admin.callout>

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
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Writable Settings Surface</h2>
                        <p class="text-sm text-[var(--color-muted)]">Footer-wide brand, social link, and legal link management now lives in the dedicated Site Settings singleton editor.</p>
                        <div class="pt-2">
                            <x-ui.button as="a" :href="route('site-settings.index')">Open Footer Settings</x-ui.button>
                        </div>
                    </div>
                </x-ui.tabs-panel>

                <x-admin.callout title="Current Constraint" tone="warning">
                    Broader application settings such as locale defaults, maintenance controls, and secrets should stay config-managed until backend support defines explicit write contracts.
                </x-admin.callout>
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

            <x-admin.callout title="Boundary" tone="warning">
                Bucket names, access keys, custom endpoints, and any future media-provider credentials remain intentionally hidden until there is an explicit secure management design.
            </x-admin.callout>
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
                    message="Do not imply provider selection, model tuning, budget controls, or hidden prompt configuration until the backend contract explicitly supports them."
                >
                    <x-ui.button variant="outline" disabled>Await Service Contract</x-ui.button>
                </x-ui.empty-state>
            </section>

            <x-admin.callout title="Current State" tone="warning">
                AI jobs and advanced AI settings remain roadmap items. Standard prompt content is managed in the dedicated prompt screens, and sensitive provider secrets should not appear here.
            </x-admin.callout>
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

            <x-admin.callout title="Security Note" tone="warning">
                This view intentionally stops at non-secret operational metadata. API tokens, cloud credentials, webhook secrets, and provider keys should not be surfaced by the UI without explicit product and backend support.
            </x-admin.callout>
        </div>
    @endif
</div>
