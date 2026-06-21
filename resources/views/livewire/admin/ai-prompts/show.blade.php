<div class="space-y-6">
    <x-admin.page-header
        :title="$creating ? 'Create Standard Prompt' : 'Edit Standard Prompt'"
        :description="$creating
            ? 'Create one of the two standard prompt families the backend supports.'
            : 'Update prompt metadata and version future prompt content without using site settings as a prompt source.'"
    >
        <x-ui.button as="a" :href="route('ai-prompts.index')" variant="secondary">Back to Standard Prompts</x-ui.button>
    </x-admin.page-header>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    @if ($notFound)
        <x-ui.empty-state title="Prompt not found" message="The requested prompt is no longer available from the service API." />
    @else
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_22rem]">
            <div class="space-y-6">
                <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Prompt Family</h2>
                        <p class="text-sm text-[var(--color-muted)]">Only the two supported prompt families should be created or edited here.</p>
                    </div>

                    @if ($formError)
                        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                            {{ $formError }}
                        </div>
                    @endif

                    <div class="grid gap-5 md:grid-cols-2">
                        <x-ui.field label="Family" for="prompt-key" :error="$errors->first('key')" required>
                            <x-ui.select id="prompt-key" wire:model.live="key">
                                @foreach ($familyOptions as $familyKey => $option)
                                    <option value="{{ $familyKey }}">{{ $option['label'] }}</option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>

                        <x-ui.field label="Status" for="prompt-status" :error="$errors->first('status')" required>
                            <x-ui.select id="prompt-status" wire:model.live="status">
                                @foreach ($statusOptions as $statusOption)
                                    <option value="{{ $statusOption }}">{{ str($statusOption)->headline() }}</option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>

                        <x-ui.field label="Name" for="prompt-name" :error="$errors->first('name')" required>
                            <x-ui.input id="prompt-name" wire:model.blur="name" :invalid="$errors->has('name')" />
                        </x-ui.field>

                        <x-ui.field label="Backend Type" for="prompt-type" :error="$errors->first('type')">
                            <x-ui.input id="prompt-type" wire:model="type" disabled />
                        </x-ui.field>
                    </div>

                    <x-ui.field label="Description" for="prompt-description" :error="$errors->first('description')">
                        <x-ui.textarea id="prompt-description" rows="4" wire:model.blur="description" :invalid="$errors->has('description')" />
                    </x-ui.field>

                    <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4 text-sm text-[var(--color-muted)]">
                        {{ $family['description'] ?? 'No description available.' }}
                    </div>

                    @if ($creating)
                        <div class="space-y-5 border-t border-[var(--color-line)] pt-6">
                            <div class="space-y-1">
                                <h3 class="text-base font-semibold text-[var(--color-ink)]">Initial Version</h3>
                                <p class="text-sm text-[var(--color-muted)]">The first version becomes the starting prompt payload for this family.</p>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <x-ui.field label="Version Status" for="initial-version-status" :error="$errors->first('initialVersionStatus')">
                                    <x-ui.select id="initial-version-status" wire:model.live="initialVersionStatus">
                                        @foreach ($statusOptions as $statusOption)
                                            <option value="{{ $statusOption }}">{{ str($statusOption)->headline() }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </x-ui.field>

                                <x-ui.field label="Variables" for="initial-variables" :error="$errors->first('initialVariablesText')" hint="One variable per line.">
                                    <x-ui.textarea id="initial-variables" rows="4" wire:model.blur="initialVariablesText" :invalid="$errors->has('initialVariablesText')" />
                                </x-ui.field>
                            </div>

                            <x-ui.field label="System Prompt" for="initial-system-prompt" :error="$errors->first('initialSystemPrompt')" required>
                                <x-ui.textarea id="initial-system-prompt" rows="12" wire:model.blur="initialSystemPrompt" class="font-mono leading-6" :invalid="$errors->has('initialSystemPrompt')" />
                            </x-ui.field>

                            <x-ui.field label="User Prompt" for="initial-user-prompt" :error="$errors->first('initialUserPrompt')" required>
                                <x-ui.textarea id="initial-user-prompt" rows="12" wire:model.blur="initialUserPrompt" class="font-mono leading-6" :invalid="$errors->has('initialUserPrompt')" />
                            </x-ui.field>

                            <x-ui.field label="Output Schema JSON" for="initial-output-schema" :error="$errors->first('initialOutputSchemaJson')" hint="Optional JSON array passed to the service.">
                                <x-ui.textarea id="initial-output-schema" rows="8" wire:model.blur="initialOutputSchemaJson" class="font-mono leading-6" :invalid="$errors->has('initialOutputSchemaJson')" />
                            </x-ui.field>
                        </div>
                    @endif

                    <div class="flex items-center gap-3">
                        <x-ui.button type="button" wire:click="save">{{ $creating ? 'Create Prompt' : 'Save Metadata' }}</x-ui.button>
                    </div>
                </section>

                @unless ($creating)
                    <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-6 py-6 shadow-[var(--shadow-card)]">
                        <div class="space-y-1">
                            <h2 class="text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Create New Version</h2>
                            <p class="text-sm text-[var(--color-muted)]">Version updates affect future generations only.</p>
                        </div>

                        @if ($versionError)
                            <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                                {{ $versionError }}
                            </div>
                        @endif

                        <div class="grid gap-5 md:grid-cols-2">
                            <x-ui.field label="Version Status" for="version-status" :error="$errors->first('versionStatus')">
                                <x-ui.select id="version-status" wire:model.live="versionStatus">
                                    @foreach ($statusOptions as $statusOption)
                                        <option value="{{ $statusOption }}">{{ str($statusOption)->headline() }}</option>
                                    @endforeach
                                </x-ui.select>
                            </x-ui.field>

                            <x-ui.field label="Variables" for="version-variables" :error="$errors->first('versionVariablesText')" hint="One variable per line.">
                                <x-ui.textarea id="version-variables" rows="4" wire:model.blur="versionVariablesText" :invalid="$errors->has('versionVariablesText')" />
                            </x-ui.field>
                        </div>

                        <x-ui.field label="System Prompt" for="version-system-prompt" :error="$errors->first('versionSystemPrompt')" required>
                            <x-ui.textarea id="version-system-prompt" rows="12" wire:model.blur="versionSystemPrompt" class="font-mono leading-6" :invalid="$errors->has('versionSystemPrompt')" />
                        </x-ui.field>

                        <x-ui.field label="User Prompt" for="version-user-prompt" :error="$errors->first('versionUserPrompt')" required>
                            <x-ui.textarea id="version-user-prompt" rows="12" wire:model.blur="versionUserPrompt" class="font-mono leading-6" :invalid="$errors->has('versionUserPrompt')" />
                        </x-ui.field>

                        <x-ui.field label="Output Schema JSON" for="version-output-schema" :error="$errors->first('versionOutputSchemaJson')" hint="Optional JSON array passed to the service.">
                            <x-ui.textarea id="version-output-schema" rows="8" wire:model.blur="versionOutputSchemaJson" class="font-mono leading-6" :invalid="$errors->has('versionOutputSchemaJson')" />
                        </x-ui.field>

                        <div class="flex items-center gap-3">
                            <x-ui.button type="button" wire:click="createVersion">Create New Version</x-ui.button>
                        </div>
                    </section>
                @endunless
            </div>

            <aside class="space-y-6">
                @unless ($creating)
                    <section class="space-y-5 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                        <div class="space-y-1">
                            <h2 class="text-base font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Prompt State</h2>
                            <p class="text-sm text-[var(--color-muted)]">Prompt metadata and the active version the backend will use.</p>
                        </div>

                        <div class="space-y-3 text-sm text-[var(--color-muted)]">
                            <div class="flex items-center justify-between gap-3">
                                <span>Status</span>
                                <x-admin.status-badge :status="$status" />
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Active Version</span>
                                <span>{{ $activeVersion ? 'v'.$activeVersion['version'] : 'None' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Total Versions</span>
                                <span>{{ count($versions) }}</span>
                            </div>
                        </div>
                    </section>

                    @if ($actionError)
                        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                            {{ $actionError }}
                        </div>
                    @endif

                    @if ($activeVersion)
                        <section class="space-y-4 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Active Version</p>
                                    <h3 class="mt-2 text-lg font-semibold text-[var(--color-ink)]">Version {{ $activeVersion['version'] }}</h3>
                                </div>

                                <x-admin.status-badge :status="$activeVersion['status']" />
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">System Prompt</p>
                                    <pre class="mt-2 max-h-56 overflow-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4 text-xs leading-6 text-[var(--color-muted)]">{{ $activeVersion['system_prompt'] }}</pre>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">User Prompt</p>
                                    <pre class="mt-2 max-h-56 overflow-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4 text-xs leading-6 text-[var(--color-muted)]">{{ $activeVersion['user_prompt'] }}</pre>
                                </div>
                            </div>
                        </section>
                    @endif

                    <section class="space-y-4 rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] px-5 py-5 shadow-[var(--shadow-card)]">
                        <div class="space-y-1">
                            <h2 class="text-base font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Version History</h2>
                            <p class="text-sm text-[var(--color-muted)]">Activate the version that should drive future backend generations.</p>
                        </div>

                        <div class="space-y-3">
                            @foreach ($versions as $version)
                                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-semibold text-[var(--color-ink)]">Version {{ $version['version'] }}</p>
                                            <p class="mt-1 text-sm text-[var(--color-muted)]">{{ $version['updated_at'] ?: 'Unknown update time' }}</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <x-admin.status-badge :status="$version['status']" />
                                            @if (($activeVersion['id'] ?? null) !== $version['id'])
                                                <x-ui.button type="button" size="sm" variant="secondary" wire:click="activateVersion({{ $version['id'] }})">Activate</x-ui.button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @else
                    <x-admin.callout title="Two Families Only">
                        The backend supports only <span class="font-medium text-[var(--color-ink)]">topic_standard</span> and <span class="font-medium text-[var(--color-ink)]">blog_standard</span>. Do not create custom prompt families here.
                    </x-admin.callout>
                @endunless
            </aside>
        </div>
    @endif
</div>
