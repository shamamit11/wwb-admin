<x-ui.card class="mx-auto w-full max-w-md">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Admin Sign In</p>
            <h2 class="mt-3 text-3xl font-semibold tracking-[-0.04em]">Welcome back</h2>
            <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">
                Use your admin credentials to access the editorial dashboard.
            </p>
        </div>

        @if (session('auth.error'))
            <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger-strong)_20%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                {{ session('auth.error') }}
            </div>
        @endif

        <form wire:submit="authenticate" class="space-y-5">
            <x-ui.field label="Email" for="email" :error="$errors->first('email')">
                <x-ui.input
                    id="email"
                    type="email"
                    wire:model.blur="email"
                    autocomplete="email"
                    placeholder="editor@example.com"
                    :invalid="$errors->has('email')"
                />
            </x-ui.field>

            <x-ui.field label="Password" for="password" :error="$errors->first('password')">
                <x-ui.input
                    id="password"
                    type="password"
                    wire:model.blur="password"
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    :invalid="$errors->has('password')"
                />
            </x-ui.field>

            <label class="flex items-center gap-3 text-sm text-[var(--color-muted)]">
                <input wire:model="remember" type="checkbox" class="h-4 w-4 rounded border-[var(--color-line-strong)] text-[var(--color-accent)] focus:ring-[var(--color-ring)]">
                <span>Remember me</span>
            </label>

            <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="authenticate">Sign in</span>
                <span wire:loading wire:target="authenticate">Signing in…</span>
            </x-ui.button>
        </form>
    </div>
</x-ui.card>
