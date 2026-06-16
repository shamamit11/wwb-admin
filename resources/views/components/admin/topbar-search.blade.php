@props([
    'placeholder' => 'Search the admin',
])

<label class="block">
    <span class="sr-only">{{ $placeholder }}</span>
    <div class="flex h-11 items-center gap-3 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-3.5 shadow-sm">
        <svg class="h-4 w-4 shrink-0 text-[var(--color-muted)]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M14.1667 14.1667L17.5 17.5M15.8333 9.16667C15.8333 12.8486 12.8486 15.8333 9.16667 15.8333C5.48477 15.8333 2.5 12.8486 2.5 9.16667C2.5 5.48477 5.48477 2.5 9.16667 2.5C12.8486 2.5 15.8333 5.48477 15.8333 9.16667Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <input
            type="search"
            placeholder="{{ $placeholder }}"
            class="w-full border-0 bg-transparent p-0 text-sm text-[var(--color-ink)] outline-none placeholder:text-[color-mix(in_srgb,var(--color-muted)_80%,white)]"
        >
    </div>
</label>
