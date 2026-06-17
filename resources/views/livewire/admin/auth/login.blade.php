<div class="mx-auto w-full max-w-[440px]">
    <div class="text-center lg:hidden">
        <div class="mx-auto inline-flex h-16 w-16 items-center justify-center rounded-[1.25rem] bg-[var(--color-accent)] text-white shadow-[0_18px_35px_rgba(249,115,22,0.28)]">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M7 17.25V6.75A1.75 1.75 0 0 1 8.75 5h8.5A1.75 1.75 0 0 1 19 6.75v10.5A1.75 1.75 0 0 1 17.25 19h-8.5A1.75 1.75 0 0 1 7 17.25Z" stroke="currentColor" stroke-width="1.8"/>
                <path d="M10 9.5h6M10 12.5h6M10 15.5h3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </div>
        <p class="mt-6 text-sm font-semibold uppercase tracking-[0.28em] text-[var(--color-muted)]">Wide Web Blog</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-[var(--color-ink)]">CMS Administrator Portal</h1>
        <p class="mt-2 text-sm text-[var(--color-muted)]">Editorial control for a modern publishing desk.</p>
    </div>

    <x-ui.card class="mt-8 border-[var(--color-line)] bg-white/96 lg:mt-0" padding="lg">
        <div class="space-y-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Admin Sign In</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-[var(--color-ink)]">Welcome back</h2>
                <p class="mt-3 text-sm leading-6 text-[var(--color-muted)]">
                    Use your admin credentials to access the editorial dashboard.
                </p>
            </div>

            <form wire:submit="authenticate" class="space-y-5">
                <x-ui.field label="Email Address" for="email" :error="$errors->first('email')">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[var(--color-muted)]">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M3.75 5.75A1.75 1.75 0 0 1 5.5 4h9A1.75 1.75 0 0 1 16.25 5.75v8.5A1.75 1.75 0 0 1 14.5 16h-9a1.75 1.75 0 0 1-1.75-1.75v-8.5Z" stroke="currentColor" stroke-width="1.5"/>
                                <path d="m4.5 6 5.03 4.2a.75.75 0 0 0 .96 0L15.5 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <x-ui.input
                            id="email"
                            type="email"
                            wire:model.blur="email"
                            autocomplete="email"
                            placeholder="admin@widewebblog.com"
                            class="pl-11 pr-4"
                            :invalid="$errors->has('email')"
                        />
                    </div>
                </x-ui.field>

                <x-ui.field for="password" :error="$errors->first('password')">
                    <div class="flex items-center justify-between gap-4">
                        <label for="password" class="block text-sm font-semibold tracking-[-0.01em] text-[var(--color-ink)]">Password</label>
                        <span class="text-xs font-medium text-[var(--color-accent-strong)]">Forgot password?</span>
                    </div>

                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[var(--color-muted)]">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M6.5 8V6.75a3.5 3.5 0 1 1 7 0V8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <rect x="4.5" y="8" width="11" height="8" rx="1.75" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </span>
                        <x-ui.input
                            id="password"
                            type="password"
                            wire:model.blur="password"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="pl-11 pr-4"
                            :invalid="$errors->has('password')"
                        />
                    </div>
                </x-ui.field>

                <label class="flex items-center gap-3 text-sm text-[var(--color-muted)]">
                    <input wire:model="remember" type="checkbox" class="h-4 w-4 rounded border-[var(--color-line-strong)] text-[var(--color-accent)] focus:ring-[var(--color-ring)]">
                    <span>Remember this device</span>
                </label>

                <x-ui.button type="submit" class="w-full" size="lg" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="authenticate">Sign In</span>
                    <span wire:loading wire:target="authenticate">Signing in…</span>
                </x-ui.button>
            </form>

            <div class="border-t border-[var(--color-line)] pt-6 text-center">
                <p class="text-sm text-[var(--color-muted)]">
                    Not an administrator?
                    <span class="font-medium text-[var(--color-accent-strong)]">Contact Support</span>
                </p>
            </div>
        </div>
    </x-ui.card>

    <div class="mt-6 flex items-center justify-center gap-4 text-xs font-medium text-[var(--color-muted)]">
        <span class="inline-flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-[var(--color-success)]"></span>
            Systems Operational
        </span>
        <span class="text-[var(--color-line-strong)]">|</span>
        <span>v2.4.1 Build</span>
    </div>
</div>
