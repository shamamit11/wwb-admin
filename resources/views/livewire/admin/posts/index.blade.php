<div class="space-y-6">
    <x-admin.page-header
        :title="$aiReviewMode ? 'Draft Review' : 'Posts'"
        :description="$aiReviewMode
            ? 'Review AI-generated draft posts, inspect source provenance, and promote only after manual editorial approval.'
            : 'Manage editorial inventory, publish state, category assignment, and entry points into the structured post editor.'"
    >
        @if ($aiReviewMode)
            <x-ui.button as="a" :href="route('posts.index')" variant="secondary">Back to Posts</x-ui.button>
        @else
            <x-ui.button as="a" :href="route('posts.create')">Create Post</x-ui.button>
        @endif
    </x-admin.page-header>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        @foreach ($stats as $stat)
            <x-admin.stat-card
                :label="$stat['label']"
                :value="$stat['value']"
                :suffix="str($stat['suffix'])->headline()"
                :tone="$stat['tone'] ?? 'default'"
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
            </x-admin.stat-card>
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
            @if (! $aiReviewMode)
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
            @endif
        </x-slot:filters>

        <x-slot:results>{{ count($posts) }} {{ str('post')->plural(count($posts)) }}</x-slot:results>
    </x-admin.filter-bar>

    <x-ui.table caption="Posts" density="compact">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading width="content-primary" sortable sort-key="title" :sort-column="$sortColumn" :sort-direction="$sortDirection">TITLE</x-ui.table-heading>
                @if ($aiReviewMode)
                    <x-ui.table-heading>SOURCE BRIEF</x-ui.table-heading>
                    <x-ui.table-heading>SOURCE TOPIC</x-ui.table-heading>
                    <x-ui.table-heading>GENERATED BY</x-ui.table-heading>
                    <x-ui.table-heading sortable sort-key="updated_at" :sort-column="$sortColumn" :sort-direction="$sortDirection">UPDATED</x-ui.table-heading>
                @else
                    <x-ui.table-heading>STATUS</x-ui.table-heading>
                    <x-ui.table-heading>CATEGORY</x-ui.table-heading>
                    <x-ui.table-heading>AUTHOR</x-ui.table-heading>
                    <x-ui.table-heading sortable sort-key="published_at" :sort-column="$sortColumn" :sort-direction="$sortDirection">PUBLISHED</x-ui.table-heading>
                    <x-ui.table-heading sortable sort-key="updated_at" :sort-column="$sortColumn" :sort-direction="$sortDirection">UPDATED</x-ui.table-heading>
                @endif
                <x-ui.table-heading align="right">ACTIONS</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($posts as $post)
                <x-ui.table-row interactive wire:key="post-{{ $post['id'] }}">
                    <x-ui.table-cell width="content-primary">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="truncate font-semibold text-[var(--color-ink)]">{{ $post['title'] }}</p>
                                @if ($post['is_featured'])
                                    <x-ui.badge tone="warning">Featured</x-ui.badge>
                                @endif
                                @if ($post['is_ai_generated'])
                                    <x-ui.badge tone="default">AI Draft</x-ui.badge>
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
                    @if ($aiReviewMode)
                        <x-ui.table-cell subdued>
                            @if ($post['source_content_brief_id'])
                                <a href="{{ route('content-briefs.show', ['contentBrief' => $post['source_content_brief_id']]) }}" class="transition-colors hover:text-[var(--color-ink)]">
                                    Brief #{{ $post['source_content_brief_id'] }}
                                </a>
                            @else
                                Not linked
                            @endif
                        </x-ui.table-cell>
                        <x-ui.table-cell subdued>
                            @if ($post['source_content_topic_id'])
                                <a href="{{ route('topic-queue.show', ['topic' => $post['source_content_topic_id']]) }}" class="transition-colors hover:text-[var(--color-ink)]">
                                    Topic #{{ $post['source_content_topic_id'] }}
                                </a>
                            @else
                                Not linked
                            @endif
                        </x-ui.table-cell>
                        <x-ui.table-cell subdued>
                            <div class="space-y-1">
                                <p>{{ $post['generated_by'] ?: 'Unknown agent' }}</p>
                                @if ($post['generated_by_ai_job_id'])
                                    <a href="{{ route('ai-jobs.show', ['aiJob' => $post['generated_by_ai_job_id']]) }}" class="text-xs transition-colors hover:text-[var(--color-ink)]">
                                        Job #{{ $post['generated_by_ai_job_id'] }}
                                    </a>
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
                    @else
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
                    @endif
                    <x-ui.table-cell align="right">
                        <div class="flex justify-end">
                            <x-admin.row-actions>
                                <x-admin.row-action :href="$aiReviewMode ? route('draft-review.show', ['post' => $post['id']]) : route('posts.edit', ['post' => $post['id']])">
                                    {{ $aiReviewMode ? 'Review' : 'Edit' }}
                                </x-admin.row-action>

                                @if ($post['can_publish'])
                                    <x-admin.row-action wire:click="openActionDialog('publish', {{ $post['id'] }})">
                                        Publish
                                    </x-admin.row-action>
                                @endif

                                @if ($post['can_schedule'])
                                    <x-admin.row-action wire:click="openActionDialog('schedule', {{ $post['id'] }})">
                                        Schedule
                                    </x-admin.row-action>
                                @endif

                                @if ($post['can_unpublish'])
                                    <x-admin.row-action wire:click="openActionDialog('unpublish', {{ $post['id'] }})">
                                        Unpublish
                                    </x-admin.row-action>
                                @endif

                                <x-admin.row-action tone="danger" wire:click="openActionDialog('delete', {{ $post['id'] }})">
                                    Delete
                                </x-admin.row-action>
                            </x-admin.row-actions>
                        </div>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-empty
                    :colspan="$aiReviewMode ? 6 : 7"
                    :title="$aiReviewMode ? 'No AI drafts are waiting for review' : 'No posts match the current view'"
                    :message="$aiReviewMode
                        ? 'AI-generated draft posts will appear here once the Service creates them for manual editorial review.'
                        : 'Adjust the search or filters, or create a new post to start the editorial workflow.'"
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
