<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <x-admin.page-header
            title="Posts"
            description="Manage editorial inventory, publish state, category assignment, and entry points into the structured post editor."
        />

        <div class="shrink-0 lg:pt-1">
            <x-ui.button as="a" :href="route('posts.create')">Create Post</x-ui.button>
        </div>
    </div>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        @foreach ($stats as $stat)
            <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-4 shadow-[var(--shadow-card)]">
                <div class="flex items-center gap-4">
                    <div
                        @class([
                            'flex h-11 w-11 shrink-0 items-center justify-center rounded-[var(--radius-button)]',
                            'bg-[color-mix(in_srgb,var(--color-success)_12%,white)] text-[var(--color-success-strong)]' => $stat['tone'] === 'success',
                            'bg-[color-mix(in_srgb,#3b82f6_12%,white)] text-[#2563eb]' => $stat['tone'] === 'info',
                            'bg-[color-mix(in_srgb,var(--color-warning)_16%,white)] text-[var(--color-warning-strong)]' => $stat['tone'] === 'warning',
                        ])
                    >
                        @if ($stat['tone'] === 'success')
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <circle cx="10" cy="10" r="6" stroke="currentColor" stroke-width="1.6"/>
                                <path d="m7.6 10 1.55 1.55L12.4 8.3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @elseif ($stat['tone'] === 'info')
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <circle cx="10" cy="10" r="6" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M10 6.8v3.6l2.2 1.3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @else
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M5.5 12.75 8.15 10.1l1.95 1.95 4.4-4.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M13.25 7.65h1.95V9.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @endif
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs font-medium text-[var(--color-muted)]">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-2xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">
                            {{ $stat['value'] }}
                            <span class="text-base font-semibold">{{ str($stat['suffix'])->headline() }}</span>
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <x-admin.filter-bar>
        <x-slot:search>
            <label class="block">
                <span class="sr-only">Search posts</span>
                <x-ui.input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search posts by title, slug, or excerpt"
                />
            </label>
        </x-slot:search>

        <x-slot:filters>
            <div class="flex flex-wrap items-center gap-3">
                <div class="w-[11rem] shrink-0">
                    <x-ui.select wire:model.live="statusFilter">
                        <option value="all">All statuses</option>
                        @foreach ($statusOptions as $statusOption)
                            <option value="{{ $statusOption }}">{{ str($statusOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="w-[11rem] shrink-0">
                    <x-ui.select wire:model.live="visibilityFilter">
                        <option value="all">All visibility</option>
                        @foreach ($visibilityOptions as $visibilityOption)
                            <option value="{{ $visibilityOption }}">{{ str($visibilityOption)->headline() }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="w-[11rem] shrink-0">
                    <x-ui.select wire:model.live="featuredFilter">
                        <option value="all">All posts</option>
                        <option value="featured">Featured only</option>
                        <option value="standard">Standard only</option>
                    </x-ui.select>
                </div>
            </div>
        </x-slot:filters>

        <x-slot:secondary>
            <div class="text-sm text-[var(--color-muted)]">
                {{ count($posts) }} {{ str('post')->plural(count($posts)) }}
            </div>
        </x-slot:secondary>
    </x-admin.filter-bar>

    <x-ui.table caption="Posts">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading class="w-[31%]">
                    <button type="button" wire:click="sortBy('title')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Title</span>
                        <span class="text-[10px] leading-none">{{ $sortColumn === 'title' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading>Status</x-ui.table-heading>
                <x-ui.table-heading>Category</x-ui.table-heading>
                <x-ui.table-heading>Author</x-ui.table-heading>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('published_at')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Published</span>
                        <span class="text-[10px] leading-none">{{ $sortColumn === 'published_at' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading>
                    <button type="button" wire:click="sortBy('updated_at')" class="inline-flex items-center gap-2 transition-colors hover:text-[var(--color-ink)]">
                        <span>Updated</span>
                        <span class="text-[10px] leading-none">{{ $sortColumn === 'updated_at' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                    </button>
                </x-ui.table-heading>
                <x-ui.table-heading align="right">Actions</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($posts as $post)
                <x-ui.table-row interactive wire:key="post-{{ $post['id'] }}">
                    <x-ui.table-cell class="w-[31%]">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="truncate font-semibold text-[var(--color-ink)]">{{ $post['title'] }}</p>
                                @if ($post['is_featured'])
                                    <x-ui.badge tone="warning">Featured</x-ui.badge>
                                @endif
                            </div>

                            <p class="mt-1 text-sm text-[var(--color-muted)]">
                                {{ $post['slug'] ?: 'Slug pending' }}
                            </p>

                            @if ($post['excerpt'])
                                <p class="mt-2 text-sm text-[var(--color-muted)]">{{ $post['excerpt'] }}</p>
                            @endif
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell>
                        <div class="space-y-2">
                            <x-admin.status-badge :status="$post['status']" />
                            <x-ui.badge tone="muted">{{ str($post['visibility'])->headline() }}</x-ui.badge>
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>
                        {{ $post['category_name'] ?: 'Unassigned' }}
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>
                        {{ $post['author_name'] ?: 'Unknown' }}
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>
                        <div class="space-y-1">
                            <p>{{ $post['published_at'] ?: 'Not published' }}</p>
                            @if ($post['scheduled_for'])
                                <p class="text-xs text-[var(--color-muted)]">Scheduled: {{ $post['scheduled_for'] }}</p>
                            @endif
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>
                        <div class="space-y-1">
                            <p>{{ $post['updated_at'] ?: 'Unknown' }}</p>
                            @if ($post['reading_time_minutes'] || $post['word_count'])
                                <p class="text-xs text-[var(--color-muted)]">
                                    {{ $post['reading_time_minutes'] ? $post['reading_time_minutes'].' min read' : 'Reading time TBC' }}
                                    @if ($post['word_count'])
                                        · {{ number_format($post['word_count']) }} words
                                    @endif
                                </p>
                            @endif
                        </div>
                    </x-ui.table-cell>
                    <x-ui.table-cell align="right">
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <x-ui.button as="a" :href="route('posts.edit', ['post' => $post['id']])" variant="outline" size="sm">Edit</x-ui.button>

                            @if ($post['can_publish'])
                                <x-ui.button type="button" variant="secondary" size="sm" wire:click="openActionDialog('publish', {{ $post['id'] }})">
                                    Publish
                                </x-ui.button>
                            @endif

                            @if ($post['can_schedule'])
                                <x-ui.button type="button" variant="secondary" size="sm" wire:click="openActionDialog('schedule', {{ $post['id'] }})">
                                    Schedule
                                </x-ui.button>
                            @endif

                            @if ($post['can_unpublish'])
                                <x-ui.button type="button" variant="secondary" size="sm" wire:click="openActionDialog('unpublish', {{ $post['id'] }})">
                                    Unpublish
                                </x-ui.button>
                            @endif

                            <x-ui.button type="button" variant="ghost" size="sm" class="text-[var(--color-danger-strong)] hover:bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] hover:text-[var(--color-danger-strong)]" wire:click="openActionDialog('delete', {{ $post['id'] }})">
                                Delete
                            </x-ui.button>
                        </div>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-empty
                    colspan="7"
                    title="No posts match the current view"
                    message="Adjust the search or filters, or create a new post to start the editorial workflow."
                />
            @endforelse
        </x-ui.table-body>
    </x-ui.table>

    <x-ui.dialog
        :open="$actionDialogOpen"
        :title="match ($actionMode) {
            'publish' => 'Publish post',
            'schedule' => 'Schedule post',
            'unpublish' => 'Unpublish post',
            'delete' => 'Delete post',
            default => 'Post action',
        }"
        :description="match ($actionMode) {
            'publish' => 'Make the current version live immediately.',
            'schedule' => 'Choose when this post should go live.',
            'unpublish' => 'Take this post out of the published state without deleting it.',
            'delete' => 'Delete the post only when it is no longer needed.',
            default => null,
        }"
        :tone="$actionMode === 'delete' ? 'destructive' : 'default'"
        maxWidth="lg"
    >
        <div class="space-y-5">
            @if ($actionError)
                <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                    {{ $actionError }}
                </div>
            @endif

            <p class="text-sm leading-6 text-[var(--color-muted)]">
                @if ($actionMode === 'publish')
                    Publish <span class="font-semibold text-[var(--color-ink)]">{{ $actionPostTitle }}</span> now?
                @elseif ($actionMode === 'schedule')
                    Set a publish time for <span class="font-semibold text-[var(--color-ink)]">{{ $actionPostTitle }}</span>.
                @elseif ($actionMode === 'unpublish')
                    Move <span class="font-semibold text-[var(--color-ink)]">{{ $actionPostTitle }}</span> out of the live state?
                @else
                    Delete <span class="font-semibold text-[var(--color-ink)]">{{ $actionPostTitle }}</span>? This is a destructive action and should be confirmed carefully.
                @endif
            </p>

            @if ($actionMode === 'schedule')
                <x-ui.field label="Publish At" for="post-schedule-for" :error="$errors->first('scheduleFor')" required>
                    <x-ui.input
                        id="post-schedule-for"
                        type="datetime-local"
                        wire:model.defer="scheduleFor"
                        :invalid="$errors->has('scheduleFor')"
                    />
                </x-ui.field>
            @endif
        </div>

        <x-slot:actions>
            <x-ui.button type="button" variant="secondary" wire:click="closeActionDialog">Cancel</x-ui.button>
            <x-ui.button
                type="button"
                :variant="$actionMode === 'delete' ? 'destructive' : 'primary'"
                wire:click="executeAction"
                wire:loading.attr="disabled"
                wire:target="executeAction"
            >
                <span wire:loading.remove wire:target="executeAction">
                    {{ match ($actionMode) {
                        'publish' => 'Publish post',
                        'schedule' => 'Schedule post',
                        'unpublish' => 'Unpublish post',
                        'delete' => 'Delete post',
                        default => 'Confirm action',
                    } }}
                </span>
                <span wire:loading wire:target="executeAction">Saving…</span>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.dialog>
</div>
