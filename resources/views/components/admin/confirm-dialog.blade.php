@props([
    'open' => false,
    'title' => 'Confirm action',
    'description' => 'Review this action before continuing.',
    'confirmLabel' => 'Confirm',
    'cancelLabel' => 'Cancel',
    'destructive' => false,
])

<x-ui.dialog
    :open="$open"
    :title="$title"
    :description="$description"
    :tone="$destructive ? 'destructive' : 'default'"
    {{ $attributes }}
>
    {{ $slot }}

    <x-slot:actions>
        @isset($cancel)
            {{ $cancel }}
        @else
            <x-ui.button variant="secondary" type="button">{{ $cancelLabel }}</x-ui.button>
        @endisset

        @isset($confirm)
            {{ $confirm }}
        @else
            <x-ui.button :variant="$destructive ? 'destructive' : 'primary'" type="button">{{ $confirmLabel }}</x-ui.button>
        @endisset
    </x-slot:actions>
</x-ui.dialog>
