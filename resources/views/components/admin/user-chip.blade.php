@props([
    'admin' => [],
])

@if ($admin)
    <div class="relative hidden sm:block">
        <div class="group relative">
            <button
                type="button"
                class="flex items-center gap-3 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-3 py-2 text-left transition-colors hover:bg-[var(--color-panel-soft)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-ring)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-page)]"
                aria-haspopup="true"
            >
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-[var(--color-ink)]">{{ $admin['name'] ?? 'Admin' }}</p>
                    @if (! empty($admin['email']))
                        <p class="truncate text-xs text-[var(--color-muted)]">{{ $admin['email'] }}</p>
                    @endif
                </div>
                <svg class="h-4 w-4 shrink-0 text-[var(--color-muted)]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <div class="invisible absolute right-0 top-full z-30 mt-2 w-56 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-2 opacity-0 shadow-[0_20px_48px_rgba(33,27,21,0.12)] transition-all duration-150 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
                <div class="border-b border-[var(--color-line)] px-3 py-2.5">
                    <p class="text-sm font-semibold text-[var(--color-ink)]">{{ $admin['name'] ?? 'Admin' }}</p>
                    @if (! empty($admin['email']))
                        <p class="mt-1 text-xs text-[var(--color-muted)]">{{ $admin['email'] }}</p>
                    @endif
                </div>

                <div class="space-y-1 pt-2">
                    <div class="flex items-center justify-between rounded-[calc(var(--radius-button)-0.1rem)] px-3 py-2 text-sm text-[var(--color-muted)]">
                        <span>Change Password</span>
                        <span class="text-[10px] font-semibold uppercase tracking-[0.18em]">TBC</span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="flex w-full items-center rounded-[calc(var(--radius-button)-0.1rem)] px-3 py-2 text-sm font-medium text-[var(--color-danger-strong)] transition-colors hover:bg-[color-mix(in_srgb,var(--color-danger)_8%,white)]"
                        >
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
