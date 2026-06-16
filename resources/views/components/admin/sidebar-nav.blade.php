<nav {{ $attributes->class('space-y-1') }}>
    <x-admin.nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-admin.nav-link>
    <x-admin.nav-link href="#" disabled>Posts</x-admin.nav-link>
    <x-admin.nav-link href="#" disabled>Categories</x-admin.nav-link>
    <x-admin.nav-link href="#" disabled>Media Library</x-admin.nav-link>
    <x-admin.nav-link href="#" disabled>Templates</x-admin.nav-link>
    <x-admin.nav-link href="#" disabled>Knowledge Base</x-admin.nav-link>
    <x-admin.nav-link href="#" disabled>SEO</x-admin.nav-link>
    <x-admin.nav-link href="#" disabled>Settings</x-admin.nav-link>
</nav>
