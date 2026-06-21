<div class="space-y-6">
    <x-admin.page-header
        eyebrow="AI Job Detail"
        :title="filled($job['id'] ?? null) ? 'Job #'.$job['id'] : 'AI job detail'"
        description="Inspect job lifecycle, generation steps, payload summaries, and retry state from the service-owned AI orchestration pipeline."
    >
        <x-ui.button as="a" :href="route('ai-jobs.index')" variant="secondary">Back to AI Jobs</x-ui.button>
        <x-ui.button type="button" variant="secondary" wire:click="refreshJob" wire:loading.attr="disabled" wire:target="refreshJob">
            <span wire:loading.remove wire:target="refreshJob">Refresh Status</span>
            <span wire:loading wire:target="refreshJob">Refreshing…</span>
        </x-ui.button>
        @if (($job['can_retry'] ?? false) === true)
            <x-ui.button type="button" wire:click="retry" wire:loading.attr="disabled" wire:target="retry">
                <span wire:loading.remove wire:target="retry">Retry Failed Job</span>
                <span wire:loading wire:target="retry">Retrying…</span>
            </x-ui.button>
        @endif
    </x-admin.page-header>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    @if ($actionError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $actionError }}
        </div>
    @endif

    @if ($notFound)
        <x-ui.empty-state
            title="AI job not found"
            message="The requested job is no longer available from the service API."
        />
    @else
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_minmax(20rem,1fr)]">
            <div class="space-y-6">
                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                    <div class="flex flex-wrap items-center gap-3">
                        @foreach ($lifecycleItems as $index => $item)
                            <div class="flex items-center gap-3">
                                <div @class([
                                    'flex min-w-[8.5rem] items-start gap-3 rounded-[var(--radius-button)] border px-4 py-3',
                                    'border-[color-mix(in_srgb,var(--color-success)_22%,white)] bg-[color-mix(in_srgb,var(--color-success)_8%,white)]' => $item['state'] === 'success',
                                    'border-[color-mix(in_srgb,var(--color-danger)_22%,white)] bg-[color-mix(in_srgb,var(--color-danger)_8%,white)]' => $item['state'] === 'danger',
                                    'border-[color-mix(in_srgb,var(--color-accent)_22%,white)] bg-[color-mix(in_srgb,var(--color-accent)_8%,white)]' => $item['state'] === 'current',
                                    'border-[var(--color-line)] bg-[var(--color-panel-soft)]' => $item['state'] === 'completed',
                                ])>
                                    <span @class([
                                        'mt-1 h-2.5 w-2.5 rounded-full',
                                        'bg-[var(--color-success)]' => $item['state'] === 'success',
                                        'bg-[var(--color-danger)]' => $item['state'] === 'danger',
                                        'bg-[var(--color-accent)]' => $item['state'] === 'current',
                                        'bg-[var(--color-ink)]' => $item['state'] === 'completed',
                                    ])></span>
                                    <span>
                                        <span class="block text-sm font-semibold text-[var(--color-ink)]">{{ $item['label'] }}</span>
                                        <span class="mt-1 block text-sm text-[var(--color-muted)]">{{ $item['timestamp'] ?? 'Pending' }}</span>
                                    </span>
                                </div>

                                @if (! $loop->last)
                                    <span class="text-sm font-medium text-[var(--color-muted)]">→</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Job Summary</p>
                            <h2 class="mt-2 text-xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">{{ str($job['type'] ?? 'job')->headline() }}</h2>
                        </div>

                        <x-admin.status-badge :status="$job['status'] ?? null" />
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @foreach ($summaryItems as $item)
                            <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">{{ $item['label'] }}</p>
                                <p class="mt-2 text-sm font-medium text-[var(--color-ink)]">{{ $item['value'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Related Entity</p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <p class="text-sm font-medium text-[var(--color-ink)]">
                                @if (($job['entity_type'] ?? null) && ($job['entity_id'] ?? null))
                                    {{ class_basename((string) $job['entity_type']) }} #{{ $job['entity_id'] }}
                                @else
                                    None
                                @endif
                            </p>

                            @if ($entityLink)
                                <x-ui.button as="a" :href="$entityLink['href']" variant="secondary" size="sm" class="whitespace-nowrap">{{ $entityLink['label'] }}</x-ui.button>
                            @endif
                        </div>
                    </div>

                    @if (! empty($job['error_message']))
                        <div class="mt-6 rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                            {{ $job['error_message'] }}
                        </div>
                    @endif
                </div>

                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Generation Steps</p>
                    <div class="mt-5 space-y-4">
                        @forelse ($stepCards as $step)
                            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-[var(--color-ink)]">{{ $step['agent_name'] }}</p>
                                        <p class="mt-1 text-sm text-[var(--color-muted)]">
                                            Started {{ $step['started_at'] ?? 'Unknown' }}
                                            @if ($step['completed_at'])
                                                · Completed {{ $step['completed_at'] }}
                                            @elseif ($step['failed_at'])
                                                · Failed {{ $step['failed_at'] }}
                                            @endif
                                            @if ($step['duration'])
                                                · Duration {{ $step['duration'] }}
                                            @endif
                                        </p>
                                    </div>

                                    <x-admin.status-badge :status="$step['status']" />
                                </div>

                                @if (! empty($step['error_message']))
                                    <p class="mt-3 text-sm text-[var(--color-danger-strong)]">{{ $step['error_message'] }}</p>
                                @endif

                                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Input Summary</p>
                                        <p class="mt-2 text-sm leading-6 text-[var(--color-ink)]">{{ $step['input_summary'] }}</p>
                                    </div>
                                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Output Summary</p>
                                        <p class="mt-2 text-sm leading-6 text-[var(--color-ink)]">{{ $step['output_summary'] }}</p>
                                    </div>
                                </div>

                                @if ($step['usage_summary'] !== 'No payload available.')
                                    <div class="mt-4 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-4 py-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Usage Summary</p>
                                        <p class="mt-2 text-sm leading-6 text-[var(--color-ink)]">{{ $step['usage_summary'] }}</p>
                                    </div>
                                @endif

                                <div class="mt-4 space-y-3">
                                    @foreach ([$step['input_payload_card'], $step['output_payload_card']] as $payloadCard)
                                        <div
                                            x-data="{ open: false, copied: false, copy() { navigator.clipboard.writeText(@js($payloadCard['copy'])); this.copied = true; setTimeout(() => this.copied = false, 1600); } }"
                                            class="overflow-hidden rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)]"
                                        >
                                            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                                                <div class="min-w-0">
                                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">{{ $payloadCard['title'] }}</p>
                                                    <p class="mt-1 text-sm text-[var(--color-ink)]">{{ $payloadCard['summary'] }}</p>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <x-ui.button type="button" size="sm" variant="secondary" x-on:click="open = ! open">
                                                        <span x-text="open ? 'Collapse' : 'View JSON'"></span>
                                                    </x-ui.button>
                                                    <x-ui.button type="button" size="sm" variant="secondary" x-on:click="copy()">
                                                        <span x-show="! copied">Copy</span>
                                                        <span x-cloak x-show="copied">Copied</span>
                                                    </x-ui.button>
                                                </div>
                                            </div>

                                            <div x-cloak x-show="open" class="border-t border-[var(--color-line)] px-4 py-4">
                                                <pre class="max-h-[22rem] overflow-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4 text-xs leading-6 text-[var(--color-muted)]">{{ $payloadCard['json'] }}</pre>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <x-ui.empty-state
                                title="No generation steps recorded"
                                message="This job does not currently expose per-step lifecycle details."
                            />
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Token & Cost Usage</p>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach ($costItems as $item)
                            <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">{{ $item['label'] }}</p>
                                <p class="mt-2 text-sm font-medium text-[var(--color-ink)]">{{ $item['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                @foreach ($payloadCards as $payloadCard)
                    <div
                        x-data="{ open: false, copied: false, copy() { navigator.clipboard.writeText(@js($payloadCard['copy'])); this.copied = true; setTimeout(() => this.copied = false, 1600); } }"
                        class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">{{ $payloadCard['title'] }}</p>
                                <p class="mt-2 text-sm leading-6 text-[var(--color-ink)]">{{ $payloadCard['summary'] }}</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <x-ui.button type="button" size="sm" variant="secondary" x-on:click="open = ! open">
                                    <span x-text="open ? 'Collapse' : 'View JSON'"></span>
                                </x-ui.button>
                                <x-ui.button type="button" size="sm" variant="secondary" x-on:click="copy()">
                                    <span x-show="! copied">Copy</span>
                                    <span x-cloak x-show="copied">Copied</span>
                                </x-ui.button>
                            </div>
                        </div>

                        <div x-cloak x-show="open" class="mt-4">
                            <pre class="max-h-[24rem] overflow-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4 text-xs leading-6 text-[var(--color-muted)]">{{ $payloadCard['json'] }}</pre>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
