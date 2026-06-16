@props([
    'admin' => [],
])

@if ($admin)
    <div class="flex items-center gap-2">
        <div class="hidden rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-3 py-2 text-right sm:block">
            <p class="text-sm font-semibold text-[var(--color-ink)]">{{ $admin['name'] ?? 'Admin' }}</p>
            @if (! empty($admin['email']))
                <p class="text-xs text-[var(--color-muted)]">{{ $admin['email'] }}</p>
            @endif
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="secondary" size="sm">Sign out</x-ui.button>
        </form>
    </div>
@endif
