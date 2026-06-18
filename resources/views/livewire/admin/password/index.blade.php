<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <x-admin.page-header
            title="Admin Password"
            description="Change the password for the currently authenticated Admin account through the service-backed security flow."
        />

        <div class="shrink-0 lg:pt-1">
            <x-ui.button as="a" :href="route('dashboard')" variant="secondary">Back to Dashboard</x-ui.button>
        </div>
    </div>

    @if ($formError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $formError }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(18rem,0.85fr)]">
        <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Change Password</h2>
                <p class="text-sm text-[var(--color-muted)]">Enter your current password first, then choose a new password and confirm it before submitting.</p>
            </div>

            <x-ui.field label="Current Password" for="admin-current-password" :error="$errors->first('currentPassword')" required>
                <x-ui.input
                    id="admin-current-password"
                    type="password"
                    wire:model.blur="currentPassword"
                    autocomplete="current-password"
                    :invalid="$errors->has('currentPassword')"
                />
            </x-ui.field>

            <div class="grid gap-5 lg:grid-cols-2">
                <x-ui.field label="New Password" for="admin-new-password" :error="$errors->first('password')" required>
                    <x-ui.input
                        id="admin-new-password"
                        type="password"
                        wire:model.blur="password"
                        autocomplete="new-password"
                        :invalid="$errors->has('password')"
                    />
                </x-ui.field>

                <x-ui.field label="Confirm New Password" for="admin-password-confirmation" :error="$errors->first('passwordConfirmation')" required>
                    <x-ui.input
                        id="admin-password-confirmation"
                        type="password"
                        wire:model.blur="passwordConfirmation"
                        autocomplete="new-password"
                        :invalid="$errors->has('passwordConfirmation')"
                    />
                </x-ui.field>
            </div>

            <div class="flex justify-end">
                <x-ui.button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Update Password</span>
                    <span wire:loading wire:target="save">Updating…</span>
                </x-ui.button>
            </div>
        </section>

        <section class="space-y-4 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Scope</p>
            <div class="space-y-3 text-sm text-[var(--color-muted)]">
                <p>This page changes only the Admin password for the current authenticated account.</p>
                <p>No broader profile, preferences, or account-management fields are exposed here.</p>
                <p>Password changes are sent directly to the existing Service API contract.</p>
            </div>
        </section>
    </div>
</div>
