<x-ui.dropdown {{ $attributes }}>
    <x-slot:trigger>
        <x-ui.button variant="ghost" size="sm" aria-label="Open row actions">Actions</x-ui.button>
    </x-slot:trigger>

    {{ $slot }}
</x-ui.dropdown>
