@php
    $messages = array_values(array_filter([
        [
            'tone' => 'success',
            'message' => session('status'),
        ],
        [
            'tone' => 'danger',
            'message' => session('error') ?? session('auth.error'),
        ],
        [
            'tone' => 'warning',
            'message' => session('warning'),
        ],
    ], fn (array $item): bool => filled($item['message'])));
@endphp

@if ($messages !== [])
    <div class="space-y-3">
        @foreach ($messages as $message)
            @php
                $styles = [
                    'success' => 'border-[color-mix(in_srgb,var(--color-success)_22%,white)] bg-[color-mix(in_srgb,var(--color-success)_10%,white)] text-[var(--color-success-strong)]',
                    'warning' => 'border-[color-mix(in_srgb,var(--color-warning)_22%,white)] bg-[color-mix(in_srgb,var(--color-warning)_12%,white)] text-[var(--color-warning-strong)]',
                    'danger' => 'border-[color-mix(in_srgb,var(--color-danger)_22%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] text-[var(--color-danger-strong)]',
                ];
            @endphp

            <div class="rounded-[var(--radius-button)] border px-4 py-3 text-sm {{ $styles[$message['tone']] ?? $styles['success'] }}">
                {{ $message['message'] }}
            </div>
        @endforeach
    </div>
@endif
