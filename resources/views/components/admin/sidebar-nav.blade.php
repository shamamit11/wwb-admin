@php($sections = app(\App\Support\Navigation\AdminNavigation::class)->sections())

<nav {{ $attributes->class('space-y-5') }}>
    @foreach ($sections as $section)
        <x-admin.sidebar-section :title="$section['title']">
            @foreach ($section['items'] as $item)
                <x-admin.nav-link
                    :href="route($item['route'])"
                    :active="request()->routeIs($item['active'])"
                    :placeholder="$item['placeholder']"
                    :title="$item['description']"
                >
                    {{ $item['label'] }}
                </x-admin.nav-link>
            @endforeach
        </x-admin.sidebar-section>
    @endforeach
</nav>
