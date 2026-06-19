@php
    $normalizeMessage = function (string $tone, mixed $payload): ?array {
        if (blank($payload)) {
            return null;
        }

        if (is_string($payload)) {
            return [
                'tone' => $tone,
                'message' => $payload,
                'link_href' => null,
                'link_label' => null,
            ];
        }

        if (! is_array($payload) || blank($payload['message'] ?? null)) {
            return null;
        }

        return [
            'tone' => $tone,
            'message' => $payload['message'],
            'link_href' => $payload['link_href'] ?? null,
            'link_label' => $payload['link_label'] ?? null,
        ];
    };

    $messages = array_values(array_filter([
        $normalizeMessage('success', session('status')),
        $normalizeMessage('danger', session('error') ?? session('auth.error')),
        $normalizeMessage('warning', session('warning')),
    ]));
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
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p>{{ $message['message'] }}</p>

                    @if (filled($message['link_href']) && filled($message['link_label']))
                        <a
                            href="{{ $message['link_href'] }}"
                            class="inline-flex items-center justify-center rounded-[var(--radius-button)] border border-current px-3 py-2 text-xs font-semibold uppercase tracking-[0.16em] transition-opacity hover:opacity-80"
                        >
                            {{ $message['link_label'] }}
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
