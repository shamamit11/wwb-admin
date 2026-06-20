<div class="space-y-6">
    <x-admin.page-header
        title="Contact Submissions"
        description="Review inbound contact messages, inspect details, and keep submission status and notes current."
    />

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    <x-ui.table caption="Contact submissions" density="compact">
        <x-ui.table-head>
            <tr>
                <x-ui.table-heading width="content-primary">NAME</x-ui.table-heading>
                <x-ui.table-heading>EMAIL</x-ui.table-heading>
                <x-ui.table-heading>TOPIC</x-ui.table-heading>
                <x-ui.table-heading>STATUS</x-ui.table-heading>
                <x-ui.table-heading>SUBMITTED AT</x-ui.table-heading>
                <x-ui.table-heading align="right">ACTIONS</x-ui.table-heading>
            </tr>
        </x-ui.table-head>

        <x-ui.table-body>
            @forelse ($submissions as $submission)
                <x-ui.table-row interactive wire:key="contact-submission-{{ $submission['id'] }}">
                    <x-ui.table-cell width="content-primary">
                        <p class="font-semibold text-[var(--color-ink)]">{{ $submission['name'] }}</p>
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $submission['email'] }}</x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $submission['topic'] !== '' ? $submission['topic'] : 'No topic provided' }}</x-ui.table-cell>
                    <x-ui.table-cell>
                        <x-admin.status-badge :status="$submission['status']" />
                    </x-ui.table-cell>
                    <x-ui.table-cell subdued>{{ $submission['submitted_at'] ?: 'Unknown' }}</x-ui.table-cell>
                    <x-ui.table-cell align="right">
                        <x-admin.row-actions>
                            <x-admin.row-action as="a" :href="route('contact-submissions.show', ['contactSubmission' => $submission['id']])">Open</x-admin.row-action>
                        </x-admin.row-actions>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-empty
                    colspan="6"
                    title="No contact submissions found"
                    message="Incoming contact form messages will appear here when the service returns them."
                />
            @endforelse
        </x-ui.table-body>
    </x-ui.table>
</div>
