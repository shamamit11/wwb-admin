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
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Job Summary</p>
                            <h2 class="mt-2 text-xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">{{ str($job['type'] ?? 'job')->headline() }}</h2>
                        </div>

                        <x-admin.status-badge :status="$job['status'] ?? null" />
                    </div>

                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Provider</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $job['provider'] ?? 'TBC' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Model</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $job['model'] ?? 'TBC' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Attempts</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $job['attempts'] ?? 0 }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Generation Steps</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $job['steps_count'] ?? count($job['steps'] ?? []) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Started</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $job['started_at'] ?? 'Not started' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Completed</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $job['completed_at'] ?? 'Not completed' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Failed</p>
                            <p class="mt-2 text-sm text-[var(--color-ink)]">{{ $job['failed_at'] ?? 'Not failed' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Related Entity</p>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <p class="text-sm text-[var(--color-ink)]">
                                    @if (($job['entity_type'] ?? null) && ($job['entity_id'] ?? null))
                                        {{ class_basename((string) $job['entity_type']) }} #{{ $job['entity_id'] }}
                                    @else
                                        None
                                    @endif
                                </p>

                                @if ($entityLink)
                                    <x-ui.button as="a" :href="$entityLink['href']" variant="outline" size="sm">{{ $entityLink['label'] }}</x-ui.button>
                                @endif
                            </div>
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
                        @forelse (($job['steps'] ?? []) as $step)
                            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-[var(--color-ink)]">{{ $step['agent_name'] }}</p>
                                        <p class="mt-1 text-sm text-[var(--color-muted)]">
                                            Started {{ $step['started_at'] ?? 'TBC' }} · Completed {{ $step['completed_at'] ?? 'TBC' }}
                                        </p>
                                    </div>

                                    <x-admin.status-badge :status="$step['status']" />
                                </div>

                                @if (! empty($step['error_message']))
                                    <p class="mt-3 text-sm text-[var(--color-danger-strong)]">{{ $step['error_message'] }}</p>
                                @endif

                                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Input Payload</p>
                                        <pre class="mt-2 overflow-x-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] p-3 text-xs text-[var(--color-muted)]">{{ json_encode($step['input_payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: 'No payload available.' }}</pre>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-muted)]">Output Payload</p>
                                        <pre class="mt-2 overflow-x-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] p-3 text-xs text-[var(--color-muted)]">{{ json_encode($step['output_payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: 'No payload available.' }}</pre>
                                    </div>
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
                    <div class="mt-5 space-y-4 text-sm text-[var(--color-muted)]">
                        <div class="flex items-center justify-between gap-3">
                            <span>Input Tokens</span>
                            <span>{{ data_get($job, 'cost_summary.input_tokens', 'TBC') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Output Tokens</span>
                            <span>{{ data_get($job, 'cost_summary.output_tokens', 'TBC') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Total Tokens</span>
                            <span>{{ data_get($job, 'cost_summary.total_tokens', 'TBC') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Estimated Cost</span>
                            <span>{{ data_get($job, 'cost_summary.estimated_cost', 'TBC') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Actual Cost</span>
                            <span>{{ data_get($job, 'cost_summary.actual_cost', 'TBC') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Currency</span>
                            <span>{{ data_get($job, 'cost_summary.currency', 'TBC') }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Input Payload</p>
                    <pre class="mt-4 overflow-x-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4 text-xs text-[var(--color-muted)]">{{ $inputPayloadJson }}</pre>
                </div>

                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Output Payload</p>
                    <pre class="mt-4 overflow-x-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4 text-xs text-[var(--color-muted)]">{{ $outputPayloadJson }}</pre>
                </div>

                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Usage Payload</p>
                    <pre class="mt-4 overflow-x-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4 text-xs text-[var(--color-muted)]">{{ $usagePayloadJson }}</pre>
                </div>
            </div>
        </div>
    @endif
</div>
