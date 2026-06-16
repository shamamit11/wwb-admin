@props([
    'open' => false,
    'title' => null,
    'description' => null,
    'width' => 'md',
])

@php
    $widths = [
        'sm' => 'max-w-md',
        'md' => 'max-w-xl',
        'lg' => 'max-w-2xl',
    ];
@endphp

@if ($open)
    <div class="fixed inset-0 z-40 flex justify-end bg-[rgba(23,20,16,0.32)]" role="presentation">
        <div
            {{ $attributes->class('flex h-full w-full flex-col border-l border-[var(--color-line)] bg-[var(--color-panel)] shadow-[-18px_0_48px_rgba(33,27,21,0.14)] '.$widths[$width]) }}
            role="dialog"
            aria-modal="true"
            @if ($title) aria-labelledby="drawer-title" @endif
            @if ($description) aria-describedby="drawer-description" @endif
        >
            <div class="border-b border-[var(--color-line)] px-6 py-5">
                @if ($title)
                    <h2 id="drawer-title" class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">{{ $title }}</h2>
                @endif

                @if ($description)
                    <p id="drawer-description" class="mt-2 text-sm leading-6 text-[var(--color-muted)]">{{ $description }}</p>
                @endif
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-5">
                {{ $slot }}
            </div>

            @isset($actions)
                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-[var(--color-line)] px-6 py-4">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    </div>
@endif
