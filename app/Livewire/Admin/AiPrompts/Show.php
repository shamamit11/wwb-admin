<?php

namespace App\Livewire\Admin\AiPrompts;

use App\Services\WideWebBlogApi\Clients\AiPromptClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiValidationException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Show extends Component
{
    private const PROMPT_TYPES = [
        'topic_discovery',
        'content_brief',
        'blog_writer',
        'editor',
        'seo_optimizer',
        'publishing',
    ];

    private const PROMPT_STATUSES = [
        'draft',
        'active',
        'archived',
    ];

    public ?int $promptId = null;

    public bool $creating = true;

    public bool $notFound = false;

    public ?string $pageError = null;

    public ?string $formError = null;

    public ?string $versionError = null;

    public ?string $actionError = null;

    public string $name = '';

    public string $key = '';

    public string $type = 'topic_discovery';

    public string $description = '';

    public string $status = 'draft';

    public array $prompt = [];

    public string $initialSystemPrompt = '';

    public string $initialUserPrompt = '';

    public string $initialOutputSchemaJson = '';

    public string $initialVariablesText = '';

    public string $initialVersionStatus = 'draft';

    public string $versionSystemPrompt = '';

    public string $versionUserPrompt = '';

    public string $versionOutputSchemaJson = '';

    public string $versionVariablesText = '';

    public string $versionStatus = 'draft';

    public function mount(AdminSessionManager $session, AiPromptClient $prompts, ?int $aiPrompt = null): mixed
    {
        $this->promptId = $aiPrompt;
        $this->creating = $aiPrompt === null;

        if ($this->creating) {
            return null;
        }

        return $this->loadPrompt($prompts, $session);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'key' => ['required', 'string', 'max:180'],
            'type' => ['required', Rule::in(self::PROMPT_TYPES)],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(self::PROMPT_STATUSES)],
            'initialSystemPrompt' => [Rule::requiredIf($this->creating), 'nullable', 'string'],
            'initialUserPrompt' => [Rule::requiredIf($this->creating), 'nullable', 'string'],
            'initialOutputSchemaJson' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                $this->validateJsonArrayPayload($attribute, $value, $fail);
            }],
            'initialVariablesText' => ['nullable', 'string'],
            'initialVersionStatus' => [Rule::requiredIf($this->creating), Rule::in(self::PROMPT_STATUSES)],
            'versionSystemPrompt' => ['nullable', 'string'],
            'versionUserPrompt' => ['nullable', 'string'],
            'versionOutputSchemaJson' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                $this->validateJsonArrayPayload($attribute, $value, $fail);
            }],
            'versionVariablesText' => ['nullable', 'string'],
            'versionStatus' => ['nullable', Rule::in(self::PROMPT_STATUSES)],
        ];
    }

    public function updated(string $property): void
    {
        $validatable = [
            'name', 'key', 'type', 'description', 'status',
            'initialSystemPrompt', 'initialUserPrompt', 'initialOutputSchemaJson', 'initialVariablesText', 'initialVersionStatus',
            'versionSystemPrompt', 'versionUserPrompt', 'versionOutputSchemaJson', 'versionVariablesText', 'versionStatus',
        ];

        if (in_array($property, $validatable, true)) {
            $this->validateOnly($property);
        }
    }

    public function save(AiPromptClient $prompts, AdminSessionManager $session): mixed
    {
        $validated = $this->validate();
        $this->formError = null;

        try {
            if ($this->creating) {
                $response = $prompts->store(
                    $this->token($session),
                    $session->tokenType(),
                    $this->storePayload($validated),
                );

                $id = Arr::get($response, 'data.id');

                session()->flash('status', 'Prompt template created.');

                if (is_numeric($id)) {
                    return $this->redirect(route('ai-prompts.show', ['aiPrompt' => $id]), navigate: true);
                }

                return null;
            }

            $response = $prompts->update(
                $this->token($session),
                $session->tokenType(),
                $this->promptId ?? 0,
                $this->updatePayload($validated),
            );

            $this->fillFromPrompt(Arr::get($response, 'data', []));
            session()->flash('status', 'Prompt template updated.');

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->formError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->formError = $exception->getMessage() ?: 'The prompt template could not be saved.';

            return null;
        }
    }

    public function createVersion(AiPromptClient $prompts, AdminSessionManager $session): mixed
    {
        if ($this->creating || ! $this->promptId) {
            return null;
        }

        $validated = $this->validate([
            'versionSystemPrompt' => ['required', 'string'],
            'versionUserPrompt' => ['required', 'string'],
            'versionOutputSchemaJson' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                $this->validateJsonArrayPayload($attribute, $value, $fail);
            }],
            'versionVariablesText' => ['nullable', 'string'],
            'versionStatus' => ['required', Rule::in(self::PROMPT_STATUSES)],
        ]);

        $this->versionError = null;

        try {
            $prompts->storeVersion(
                $this->token($session),
                $session->tokenType(),
                $this->promptId,
                $this->versionPayload($validated),
            );

            $this->resetVersionForm();
            $this->loadPrompt($prompts, $session);
            session()->flash('status', 'Prompt version created.');

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->versionError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->versionError = $exception->getMessage() ?: 'The prompt version could not be created.';

            return null;
        }
    }

    public function activateVersion(int $versionId, AiPromptClient $prompts, AdminSessionManager $session): mixed
    {
        if ($this->creating || ! $this->promptId) {
            return null;
        }

        $this->actionError = null;

        try {
            $response = $prompts->activateVersion(
                $this->token($session),
                $session->tokenType(),
                $this->promptId,
                $versionId,
            );

            $this->fillFromPrompt(Arr::get($response, 'data', []));
            session()->flash('status', 'Prompt version activated.');

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->actionError = $exception->getMessage() ?: 'The prompt version could not be activated.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.ai-prompts.show', [
            'typeOptions' => self::PROMPT_TYPES,
            'statusOptions' => self::PROMPT_STATUSES,
            'activeVersion' => $this->prompt['active_version'] ?? null,
            'versions' => $this->prompt['versions'] ?? [],
            'workflowItems' => $this->workflowItems(),
            'activeVersionCards' => $this->activeVersionCards(),
            'versionHistoryCards' => $this->versionHistoryCards(),
        ])->layout('layouts.admin', [
            'title' => $this->creating ? 'Create Prompt Template' : 'Prompt Template Detail',
        ]);
    }

    protected function loadPrompt(AiPromptClient $prompts, AdminSessionManager $session): mixed
    {
        if (! $this->promptId) {
            return null;
        }

        $this->pageError = null;
        $this->notFound = false;

        try {
            $response = $prompts->show($this->token($session), $session->tokenType(), $this->promptId);
            $this->fillFromPrompt(Arr::get($response, 'data', []));

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            if ($exception->status() === 404) {
                $this->notFound = true;
                $this->pageError = 'This prompt template could not be found in the service API.';

                return null;
            }

            $this->pageError = $exception->getMessage() ?: 'Prompt template details could not be loaded.';

            return null;
        }
    }

    protected function fillFromPrompt(array $prompt): void
    {
        $this->prompt = $this->mapPrompt($prompt);
        $this->name = (string) ($this->prompt['name'] ?? '');
        $this->key = (string) ($this->prompt['key'] ?? '');
        $this->type = (string) ($this->prompt['type'] ?? 'topic_discovery');
        $this->description = (string) ($this->prompt['description'] ?? '');
        $this->status = (string) ($this->prompt['status'] ?? 'draft');
    }

    protected function mapPrompt(array $prompt): array
    {
        return [
            'id' => Arr::get($prompt, 'id'),
            'name' => Arr::get($prompt, 'name', 'Untitled prompt'),
            'key' => Arr::get($prompt, 'key', ''),
            'type' => Arr::get($prompt, 'type', ''),
            'description' => Arr::get($prompt, 'description'),
            'status' => Arr::get($prompt, 'status', 'draft'),
            'active_version_id' => Arr::get($prompt, 'active_version_id'),
            'versions_count' => (int) Arr::get($prompt, 'versions_count', count(Arr::get($prompt, 'versions', []))),
            'created_at' => $this->formatTimestamp(Arr::get($prompt, 'created_at')),
            'updated_at' => $this->formatTimestamp(Arr::get($prompt, 'updated_at')),
            'active_version' => $this->mapVersion(Arr::get($prompt, 'active_version')),
            'versions' => collect(Arr::get($prompt, 'versions', []))
                ->map(fn (array $version): array => $this->mapVersion($version))
                ->sortByDesc('version')
                ->values()
                ->all(),
        ];
    }

    protected function mapVersion(mixed $version): ?array
    {
        if (! is_array($version)) {
            return null;
        }

        return [
            'id' => Arr::get($version, 'id'),
            'prompt_template_id' => Arr::get($version, 'prompt_template_id'),
            'version' => Arr::get($version, 'version'),
            'system_prompt' => Arr::get($version, 'system_prompt', ''),
            'user_prompt' => Arr::get($version, 'user_prompt', ''),
            'output_schema' => Arr::get($version, 'output_schema'),
            'output_schema_json' => $this->prettyJson(Arr::get($version, 'output_schema')),
            'variables' => Arr::get($version, 'variables', []),
            'variables_text' => collect(Arr::get($version, 'variables', []))
                ->filter(fn (mixed $variable): bool => is_string($variable) && $variable !== '')
                ->implode("\n"),
            'status' => Arr::get($version, 'status', 'draft'),
            'created_at' => $this->formatTimestamp(Arr::get($version, 'created_at')),
            'updated_at' => $this->formatTimestamp(Arr::get($version, 'updated_at')),
        ];
    }

    protected function workflowItems(): array
    {
        if ($this->creating) {
            return [
                ['label' => 'Metadata', 'state' => 'current'],
                ['label' => 'Version v1 Draft', 'state' => 'pending'],
                ['label' => 'Review', 'state' => 'pending'],
                ['label' => 'Activate', 'state' => 'pending'],
            ];
        }

        $activeVersion = $this->prompt['active_version'] ?? null;

        return [
            ['label' => 'Template '.Str::headline((string) ($this->status ?: 'draft')), 'state' => 'completed'],
            ['label' => $activeVersion ? 'Version v'.$activeVersion['version'].' '.Str::headline((string) $activeVersion['status']) : 'No Active Version', 'state' => $activeVersion ? 'current' : 'pending'],
            ['label' => 'New Changes Create Future Versions', 'state' => 'pending'],
        ];
    }

    protected function activeVersionCards(): array
    {
        $activeVersion = $this->prompt['active_version'] ?? null;

        if (! is_array($activeVersion)) {
            return [];
        }

        return [
            $this->promptBlockCard('active-system-prompt', 'System Prompt', $activeVersion['system_prompt'] ?? '', 'Primary instruction set for future generations.'),
            $this->promptBlockCard('active-user-prompt', 'User Prompt', $activeVersion['user_prompt'] ?? '', 'Operator-facing generation template content.'),
            $this->jsonBlockCard('active-output-schema', 'Output Schema JSON', $activeVersion['output_schema_json'] ?? '[]', $activeVersion['output_schema'] ?? []),
        ];
    }

    protected function versionHistoryCards(): array
    {
        return collect($this->prompt['versions'] ?? [])
            ->map(function (array $version): array {
                return [
                    'id' => $version['id'],
                    'version' => $version['version'],
                    'status' => $version['status'],
                    'created_at' => $version['created_at'] ?? 'Unknown',
                    'is_active' => ($this->prompt['active_version_id'] ?? null) === $version['id'],
                    'variables_summary' => $this->variablesSummary($version['variables'] ?? []),
                    'schema_summary' => $this->schemaSummary($version['output_schema'] ?? null),
                    'schema_json' => $version['output_schema_json'] ?? '[]',
                    'can_activate' => ($this->prompt['active_version_id'] ?? null) !== $version['id'],
                ];
            })
            ->values()
            ->all();
    }

    protected function promptBlockCard(string $id, string $title, string $content, string $hint): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'hint' => $hint,
            'preview' => $this->promptPreview($content),
            'content' => $content !== '' ? $content : 'No prompt content available.',
            'copy' => $content,
        ];
    }

    protected function jsonBlockCard(string $id, string $title, string $json, mixed $payload): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'hint' => 'Structured output contract for future AI generations.',
            'preview' => $this->schemaSummary($payload),
            'content' => $json,
            'copy' => $json,
        ];
    }

    protected function promptPreview(string $content): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($content)) ?? trim($content);

        return $normalized !== '' ? Str::limit($normalized, 180) : 'No prompt content available.';
    }

    protected function variablesSummary(array $variables): string
    {
        if ($variables === []) {
            return 'No variables';
        }

        return collect($variables)
            ->filter(fn (mixed $variable): bool => is_string($variable) && trim($variable) !== '')
            ->take(4)
            ->implode(', ');
    }

    protected function schemaSummary(mixed $payload): string
    {
        if (! is_array($payload) || $payload === []) {
            return 'No output schema defined.';
        }

        return collect($payload)
            ->take(5)
            ->map(fn (mixed $item): string => is_scalar($item) ? (string) $item : (json_encode($item, JSON_UNESCAPED_SLASHES) ?: 'item'))
            ->implode(' · ');
    }

    protected function storePayload(array $validated): array
    {
        return [
            'name' => $validated['name'],
            'key' => $validated['key'],
            'type' => $validated['type'],
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
            'status' => $validated['status'],
            'initial_version' => [
                'system_prompt' => $validated['initialSystemPrompt'],
                'user_prompt' => $validated['initialUserPrompt'],
                'output_schema' => $this->decodeJsonArray($validated['initialOutputSchemaJson'] ?? null),
                'variables' => $this->variablesPayload($validated['initialVariablesText'] ?? ''),
                'status' => $validated['initialVersionStatus'],
            ],
        ];
    }

    protected function updatePayload(array $validated): array
    {
        return [
            'name' => $validated['name'],
            'key' => $validated['key'],
            'type' => $validated['type'],
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
            'status' => $validated['status'],
        ];
    }

    protected function versionPayload(array $validated): array
    {
        return [
            'system_prompt' => $validated['versionSystemPrompt'],
            'user_prompt' => $validated['versionUserPrompt'],
            'output_schema' => $this->decodeJsonArray($validated['versionOutputSchemaJson'] ?? null),
            'variables' => $this->variablesPayload($validated['versionVariablesText'] ?? ''),
            'status' => $validated['versionStatus'],
        ];
    }

    protected function variablesPayload(string $value): ?array
    {
        $variables = collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();

        return $variables !== [] ? $variables : null;
    }

    protected function validateJsonArrayPayload(string $attribute, mixed $value, \Closure $fail): void
    {
        if ($value === null || trim((string) $value) === '') {
            return;
        }

        try {
            $decoded = json_decode((string) $value, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $fail('The '.$this->attributeLabel($attribute).' field must be valid JSON.');

            return;
        }

        if (! is_array($decoded) || array_is_list($decoded) === false) {
            $fail('The '.$this->attributeLabel($attribute).' field must be a JSON array.');
        }
    }

    protected function decodeJsonArray(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function prettyJson(mixed $payload): string
    {
        if ($payload === null) {
            return '[]';
        }

        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return $encoded !== false ? $encoded : '[]';
    }

    protected function resetVersionForm(): void
    {
        $this->resetValidation([
            'versionSystemPrompt',
            'versionUserPrompt',
            'versionOutputSchemaJson',
            'versionVariablesText',
            'versionStatus',
        ]);

        $this->versionSystemPrompt = '';
        $this->versionUserPrompt = '';
        $this->versionOutputSchemaJson = '';
        $this->versionVariablesText = '';
        $this->versionStatus = 'draft';
        $this->versionError = null;
    }

    protected function normalizeApiErrors(array $errors): array
    {
        $normalized = [];

        foreach ($errors as $key => $messages) {
            $normalized[$this->normalizeApiErrorKey((string) $key)] = $messages;
        }

        return $normalized;
    }

    protected function normalizeApiErrorKey(string $key): string
    {
        return match ($key) {
            'initial_version.system_prompt' => 'initialSystemPrompt',
            'initial_version.user_prompt' => 'initialUserPrompt',
            'initial_version.output_schema' => 'initialOutputSchemaJson',
            'initial_version.variables' => 'initialVariablesText',
            'initial_version.status' => 'initialVersionStatus',
            'output_schema' => 'versionOutputSchemaJson',
            'variables' => 'versionVariablesText',
            'system_prompt' => 'versionSystemPrompt',
            'user_prompt' => 'versionUserPrompt',
            default => $key,
        };
    }

    protected function attributeLabel(string $attribute): string
    {
        return match ($attribute) {
            'initialOutputSchemaJson' => 'initial output schema',
            'versionOutputSchemaJson' => 'version output schema',
            default => str($attribute)->snake(' ')->toString(),
        };
    }

    protected function formatTimestamp(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('M j, Y g:i A');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function token(AdminSessionManager $session): string
    {
        return $session->token() ?? '';
    }

    protected function expireSession(AdminSessionManager $session): mixed
    {
        $session->clear();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        session()->flash('auth.error', 'Your session has expired. Please sign in again.');

        return $this->redirectRoute('login', navigate: true);
    }

    protected function forbidden(AdminSessionManager $session): mixed
    {
        $session->clear();
        session()->flash('auth.error', 'Your account is not authorized for the admin panel.');

        return $this->redirectRoute('auth.forbidden', navigate: true);
    }
}
