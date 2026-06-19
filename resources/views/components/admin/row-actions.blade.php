<x-ui.dropdown {{ $attributes }}>
    <x-slot:trigger>
        <span class="inline-flex h-8 w-8 items-center justify-center rounded-[var(--radius-button)] text-[var(--color-muted)] transition-colors hover:bg-[var(--color-panel-soft)] hover:text-[var(--color-ink)]">
            <span class="sr-only">Open row actions</span>
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <circle cx="4" cy="10" r="1.5" fill="currentColor" />
                <circle cx="10" cy="10" r="1.5" fill="currentColor" />
                <circle cx="16" cy="10" r="1.5" fill="currentColor" />
            </svg>
        </span>
    </x-slot:trigger>

    {{ $slot }}
</x-ui.dropdown>
