<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <x-admin.page-header
            eyebrow="AI Prompt Templates"
            :title="$creating ? 'Create Prompt Template' : ($name !== '' ? $name : 'Prompt Template Detail')"
            description="Prompt changes affect future AI generations only. Existing jobs and completed outputs remain unchanged."
        >
            <x-ui.button as="a" :href="route('ai-prompts.index')" variant="secondary">Back to Prompt Templates</x-ui.button>
        </x-admin.page-header>
    </div>

    @if ($pageError)
        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
            {{ $pageError }}
        </div>
    @endif

    @if ($notFound)
        <x-ui.empty-state
            title="Prompt template not found"
            message="The requested prompt template is no longer available from the service API."
        />
    @else
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(20rem,0.9fr)]">
            <div class="space-y-6">
                <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Template Details</p>
                            <h2 class="mt-2 text-xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">{{ $creating ? 'Initial Prompt Setup' : 'Metadata and Lifecycle' }}</h2>
                        </div>

                        @unless ($creating)
                            <x-admin.status-badge :status="$status" />
                        @endunless
                    </div>

                    <div class="mt-5 rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-3 text-sm text-[var(--color-muted)]">
                        Changes here alter future prompt executions only. Review active versions carefully before switching production workflows.
                    </div>

                    @if ($formError)
                        <div class="mt-5 rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                            {{ $formError }}
                        </div>
                    @endif

                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        <x-ui.field label="Name" for="prompt-name" :error="$errors->first('name')" required>
                            <x-ui.input id="prompt-name" wire:model.blur="name" :invalid="$errors->has('name')" />
                        </x-ui.field>

                        <x-ui.field label="Key" for="prompt-key" :error="$errors->first('key')" required>
                            <x-ui.input id="prompt-key" wire:model.blur="key" :invalid="$errors->has('key')" />
                        </x-ui.field>

                        <x-ui.field label="Type" for="prompt-type" :error="$errors->first('type')" required>
                            <x-ui.select id="prompt-type" wire:model.live="type">
                                @foreach ($typeOptions as $typeOption)
                                    <option value="{{ $typeOption }}">{{ str($typeOption)->headline() }}</option>
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
                    </div>

                    <div class="mt-5">
                        <x-ui.field label="Description" for="prompt-description" :error="$errors->first('description')">
                            <x-ui.textarea id="prompt-description" rows="4" wire:model.blur="description" :invalid="$errors->has('description')" />
                        </x-ui.field>
                    </div>

                    @if ($creating)
                        <div class="mt-6 space-y-5 border-t border-[var(--color-line)] pt-6">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Initial Version</p>
                                <h3 class="mt-2 text-lg font-semibold tracking-[-0.03em] text-[var(--color-ink)]">Version 1 Payload</h3>
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
                                <x-ui.textarea id="initial-system-prompt" rows="10" wire:model.blur="initialSystemPrompt" :invalid="$errors->has('initialSystemPrompt')" />
                            </x-ui.field>

                            <x-ui.field label="User Prompt" for="initial-user-prompt" :error="$errors->first('initialUserPrompt')" required>
                                <x-ui.textarea id="initial-user-prompt" rows="10" wire:model.blur="initialUserPrompt" :invalid="$errors->has('initialUserPrompt')" />
                            </x-ui.field>

                            <x-ui.field label="Output Schema JSON" for="initial-output-schema" :error="$errors->first('initialOutputSchemaJson')" hint="Optional JSON array payload passed to the service.">
                                <x-ui.textarea id="initial-output-schema" rows="8" wire:model.blur="initialOutputSchemaJson" placeholder='["title","sections","faq"]' :invalid="$errors->has('initialOutputSchemaJson')" />
                            </x-ui.field>
                        </div>
                    @endif

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <x-ui.button type="button" wire:click="save">{{ $creating ? 'Create Prompt Template' : 'Save Metadata' }}</x-ui.button>
                    </div>
                </div>

                @unless ($creating)
                    <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Create New Version</p>
                            <h2 class="mt-2 text-xl font-semibold tracking-[-0.03em] text-[var(--color-ink)]">Future Generation Prompt Update</h2>
                        </div>

                        @if ($versionError)
                            <div class="mt-5 rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                                {{ $versionError }}
                            </div>
                        @endif

                        <div class="mt-5 grid gap-5 md:grid-cols-2">
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

                        <div class="mt-5 grid gap-5">
                            <x-ui.field label="System Prompt" for="version-system-prompt" :error="$errors->first('versionSystemPrompt')" required>
                                <x-ui.textarea id="version-system-prompt" rows="10" wire:model.blur="versionSystemPrompt" :invalid="$errors->has('versionSystemPrompt')" />
                            </x-ui.field>

                            <x-ui.field label="User Prompt" for="version-user-prompt" :error="$errors->first('versionUserPrompt')" required>
                                <x-ui.textarea id="version-user-prompt" rows="10" wire:model.blur="versionUserPrompt" :invalid="$errors->has('versionUserPrompt')" />
                            </x-ui.field>

                            <x-ui.field label="Output Schema JSON" for="version-output-schema" :error="$errors->first('versionOutputSchemaJson')" hint="Optional JSON array payload passed to the service.">
                                <x-ui.textarea id="version-output-schema" rows="8" wire:model.blur="versionOutputSchemaJson" placeholder='["title","sections","faq"]' :invalid="$errors->has('versionOutputSchemaJson')" />
                            </x-ui.field>
                        </div>

                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <x-ui.button type="button" wire:click="createVersion">Create New Version</x-ui.button>
                        </div>
                    </div>
                @endunless
            </div>

            <div class="space-y-6">
                @unless ($creating)
                    <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Workflow State</p>
                        <div class="mt-5 space-y-4 text-sm text-[var(--color-muted)]">
                            <div class="flex items-center justify-between gap-3">
                                <span>Status</span>
                                <x-admin.status-badge :status="$status" />
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Active Version</span>
                                <x-ui.badge :tone="$activeVersion ? 'success' : 'muted'">{{ $activeVersion ? 'v'.$activeVersion['version'] : 'None' }}</x-ui.badge>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Versions</span>
                                <span>{{ $prompt['versions_count'] ?? count($versions) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Created</span>
                                <span>{{ $prompt['created_at'] ?? 'Unknown' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span>Updated</span>
                                <span>{{ $prompt['updated_at'] ?? 'Unknown' }}</span>
                            </div>
                        </div>
                    </div>

                    @if ($actionError)
                        <div class="rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]">
                            {{ $actionError }}
                        </div>
                    @endif

                    @if ($activeVersion)
                        <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Active Version</p>
                                    <h3 class="mt-2 text-lg font-semibold tracking-[-0.03em] text-[var(--color-ink)]">Version {{ $activeVersion['version'] }}</h3>
                                </div>

                                <x-admin.status-badge :status="$activeVersion['status']" />
                            </div>

                            <div class="mt-5 space-y-5 text-sm">
                                <div>
                                    <p class="font-semibold text-[var(--color-ink)]">System Prompt</p>
                                    <pre class="mt-2 overflow-x-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-3 text-xs text-[var(--color-muted)]">{{ $activeVersion['system_prompt'] }}</pre>
                                </div>
                                <div>
                                    <p class="font-semibold text-[var(--color-ink)]">User Prompt</p>
                                    <pre class="mt-2 overflow-x-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-3 text-xs text-[var(--color-muted)]">{{ $activeVersion['user_prompt'] }}</pre>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Version History</p>
                        <div class="mt-5 space-y-4">
                            @forelse ($versions as $version)
                                <div class="rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="font-semibold text-[var(--color-ink)]">Version {{ $version['version'] }}</p>
                                                @if (($prompt['active_version_id'] ?? null) === $version['id'])
                                                    <x-ui.badge tone="success">Active</x-ui.badge>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-sm text-[var(--color-muted)]">Created {{ $version['created_at'] ?? 'Unknown' }}</p>
                                            @if ($version['variables_text'] !== '')
                                                <p class="mt-2 text-sm text-[var(--color-muted)]">Variables: {{ str($version['variables_text'])->replace("\n", ', ') }}</p>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <x-admin.status-badge :status="$version['status']" />
                                            @if (($prompt['active_version_id'] ?? null) !== $version['id'])
                                                <x-ui.button type="button" variant="secondary" wire:click="activateVersion({{ $version['id'] }})">Activate</x-ui.button>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-4 grid gap-4">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Output Schema</p>
                                            <pre class="mt-2 overflow-x-auto rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] p-3 text-xs text-[var(--color-muted)]">{{ $version['output_schema_json'] }}</pre>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-[var(--radius-button)] border border-dashed border-[var(--color-line)] bg-[var(--color-panel-soft)] px-4 py-5 text-sm text-[var(--color-muted)]">
                                    No prompt versions are available yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] p-6 shadow-[var(--shadow-card)]">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-muted)]">Creation Notes</p>
                        <div class="mt-4 space-y-3 text-sm text-[var(--color-muted)]">
                            <p>The initial version becomes the baseline for future AI generations that reference this template.</p>
                            <p>Keep the first version explicit and editorially reviewable so later changes can be compared cleanly.</p>
                        </div>
                    </div>
                @endunless
            </div>
        </div>
    @endif
</div>
