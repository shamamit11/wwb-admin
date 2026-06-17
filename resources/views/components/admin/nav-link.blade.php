@props([
    'active' => false,
    'disabled' => false,
    'placeholder' => false,
    'icon' => 'square',
])

@php
    $classes = $active
        ? 'border-l-[3px] border-[var(--color-accent)] bg-[color-mix(in_srgb,var(--color-panel-soft)_72%,white)] pl-[calc(0.625rem-3px)] text-[var(--color-accent-strong)]'
        : 'border-l-[3px] border-transparent text-[var(--color-muted)] hover:bg-[color-mix(in_srgb,var(--color-panel-soft)_52%,white)] hover:text-[var(--color-ink)]';

    if ($disabled) {
        $classes = 'cursor-not-allowed text-[var(--color-muted)] opacity-60';
    }
@endphp

<a
    {{ $attributes->class('flex items-center justify-between rounded-[0.85rem] px-2.5 py-2 text-sm font-medium transition-colors '.$classes) }}
    @if ($disabled) aria-disabled="true" @endif
>
    <span class="flex items-center gap-2.5">
        <span class="flex h-8 w-8 items-center justify-center rounded-[0.8rem] bg-white/70 text-current">
            @switch($icon)
                @case('dashboard')
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M3 3.75h6.25V9.5H3V3.75Zm7.75 0H17v3H10.75v-3ZM3 11h6.25v5.25H3V11Zm7.75-1.5H17v6.75h-6.25V9.5Z" fill="currentColor"/>
                    </svg>
                    @break
                @case('homepage')
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M4.75 8.25 10 4l5.25 4.25V15a1.25 1.25 0 0 1-1.25 1.25h-8A1.25 1.25 0 0 1 4.75 15V8.25Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M8 16.25v-4h4v4" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                    </svg>
                    @break
                @case('posts')
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M5 3.75h10A1.25 1.25 0 0 1 16.25 5v10A1.25 1.25 0 0 1 15 16.25H5A1.25 1.25 0 0 1 3.75 15V5A1.25 1.25 0 0 1 5 3.75Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M7 7h6M7 10h6M7 13h3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    @break
                @case('pages')
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M6 3.75h5.75L15.5 7.5V15A1.25 1.25 0 0 1 14.25 16.25H6A1.25 1.25 0 0 1 4.75 15V5A1.25 1.25 0 0 1 6 3.75Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M11.75 3.75V7.5H15.5" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M7.5 10h5M7.5 13h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    @break
                @case('categories')
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M4.75 5.25h4.5v4.5h-4.5v-4.5Zm6 0h4.5v4.5h-4.5v-4.5Zm-6 6h4.5v4.5h-4.5v-4.5Zm6 2.25h4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    @break
                @case('tags')
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="m10.5 4 5.5 5.5-5.75 5.75a1.5 1.5 0 0 1-2.12 0L4.75 11.87a1.5 1.5 0 0 1 0-2.12L10.5 4Z" stroke="currentColor" stroke-width="1.5"/>
                        <circle cx="12.5" cy="7.5" r="1" fill="currentColor"/>
                    </svg>
                    @break
                @case('media')
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <rect x="3.75" y="4.25" width="12.5" height="11.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                        <path d="m6.5 13 2.25-2.25 1.75 1.75 2.75-3L14.5 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="7" cy="7.5" r="1" fill="currentColor"/>
                    </svg>
                    @break
                @case('templates')
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <rect x="4" y="4.25" width="12" height="11.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M4 8.25h12M8.25 8.25v7.5" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    @break
                @case('knowledge')
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M5 4.5h8.5A1.5 1.5 0 0 1 15 6v9.5H6.5A1.5 1.5 0 0 0 5 17V4.5Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M6.5 7H12M6.5 10H12M6.5 13H10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    @break
                @case('seo')
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <circle cx="8.5" cy="8.5" r="4.75" stroke="currentColor" stroke-width="1.5"/>
                        <path d="m12 12 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    @break
                @case('settings')
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M10 5.25v2M10 12.75v2M5.25 10h2M12.75 10h2M6.64 6.64l1.41 1.41m3.9 3.9 1.41 1.41m0-6.72-1.41 1.41m-3.9 3.9-1.41 1.41" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <circle cx="10" cy="10" r="2.25" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    @break
                @case('queue')
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M5.25 5.75h9.5M5.25 10h9.5M5.25 14.25h6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    @break
                @case('spark')
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="m10 3.5 1.6 3.9 3.9 1.6-3.9 1.6-1.6 3.9-1.6-3.9-3.9-1.6 3.9-1.6L10 3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                    </svg>
                    @break
                @default
                    <span class="block h-2.5 w-2.5 rounded-sm bg-current"></span>
            @endswitch
        </span>
        <span>{{ $slot }}</span>
    </span>
    @if ($placeholder || $disabled)
        <span class="text-[10px] font-semibold uppercase tracking-[0.18em]">{{ $disabled ? 'Soon' : 'Placeholder' }}</span>
    @endif
</a>
