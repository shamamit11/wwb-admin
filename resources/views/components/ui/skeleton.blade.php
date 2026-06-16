@props([
    'lines' => 1,
])

@if ($lines > 1)
    <div {{ $attributes->class('space-y-2') }} aria-hidden="true">
        @foreach (range(1, $lines) as $line)
            <div class="h-4 rounded-full bg-[color-mix(in_srgb,var(--color-line)_82%,white)] animate-pulse"></div>
        @endforeach
    </div>
@else
    <div {{ $attributes->class('h-4 rounded-full bg-[color-mix(in_srgb,var(--color-line)_82%,white)] animate-pulse') }} aria-hidden="true"></div>
@endif
