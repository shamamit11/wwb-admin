@props([
    'as' => 'button',
    'href' => null,
    'tone' => 'default',
])

<x-ui.dropdown-item
    :href="$href"
    :destructive="$tone === 'danger'"
    {{ $attributes }}
>
    {{ $slot }}
</x-ui.dropdown-item>
