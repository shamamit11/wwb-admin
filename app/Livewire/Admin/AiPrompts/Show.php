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
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Show extends Component
{
    private const PROMPT_FAMILIES = [
        'topic_standard' => [
            'label' => 'Topic Standard',
            'type' => 'topic_discovery',
            'description' => 'Used for topic discovery from categories and knowledge base context.',
        ],
        'blog_standard' => [
            'label' => 'Blog Standard',
            'type' => 'blog_writer',
            'description' => 'Used for full article draft generation once a topic passes the score threshold.',
        ],
    ];

    private const PROMPT_STATUSES = ['draft', 'active', 'archived'];

    public ?int $promptId = null;

    public bool $creating = true;

    public bool $notFound = false;

    public ?string $pageError = null;

    public ?string $formError = null;

    public ?string $versionError = null;

    public ?string $actionError = null;

    public string $name = '';

    public string $key = 'topic_standard';

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
        $this->applyFamilyDefaults($this->key);

        if ($this->creating) {
            return null;
        }

        return $this->loadPrompt($prompts, $session);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'key' => ['required', Rule::in(array_keys(self::PROMPT_FAMILIES))],
            'type' => ['required', Rule::in(array_column(self::PROMPT_FAMILIES, 'type'))],
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
        if ($property === 'key') {
            $this->applyFamilyDefaults($this->key);
        }

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
        $this->applyFamilyDefaults($this->key);
        $validated = $this->validate();
        $this->formError = null;

        try {
            if ($this->creating) {
                $response = $prompts->store($this->token($session), $session->tokenType(), $this->storePayload($validated));
                $id = Arr::get($response, 'data.id');

                session()->flash('status', 'Standard prompt created.');

                if (is_numeric($id)) {
                    return $this->redirect(route('ai-prompts.show', ['aiPrompt' => $id]), navigate: true);
                }

                return null;
            }

            $response = $prompts->update($this->token($session), $session->tokenType(), $this->promptId ?? 0, $this->updatePayload($validated));
            $this->fillFromPrompt(Arr::get($response, 'data', []));
            session()->flash('status', 'Prompt metadata updated.');

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->formError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->formError = $exception->getMessage() ?: 'The prompt could not be saved.';

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
            $prompts->storeVersion($this->token($session), $session->tokenType(), $this->promptId, $this->versionPayload($validated));
            $this->resetVersionDraft();

            return $this->loadPrompt($prompts, $session);
        } catch (WideWebBlogApiValidationException $exception) {
            $this->versionError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->versionError = $exception->getMessage() ?: 'A new prompt version could not be created.';

            return null;
        }
    }

    public function activateVersion(AiPromptClient $prompts, AdminSessionManager $session, int $versionId): mixed
    {
        if ($this->creating || ! $this->promptId) {
            return null;
        }

        $this->actionError = null;

        try {
            $response = $prompts->activateVersion($this->token($session), $session->tokenType(), $this->promptId, $versionId);
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
        $family = self::PROMPT_FAMILIES[$this->key] ?? null;

        return view('livewire.admin.ai-prompts.show', [
            'familyOptions' => self::PROMPT_FAMILIES,
            'statusOptions' => self::PROMPT_STATUSES,
            'activeVersion' => $this->activeVersion(),
            'versions' => $this->versions(),
            'family' => $family,
        ])->layout('layouts.admin', [
            'title' => $this->creating ? 'Create Standard Prompt' : 'Edit Standard Prompt',
        ]);
    }

    protected function loadPrompt(AiPromptClient $prompts, AdminSessionManager $session): mixed
    {
        $this->pageError = null;
        $this->notFound = false;

        try {
            $response = $prompts->show($this->token($session), $session->tokenType(), $this->promptId ?? 0);
            $this->fillFromPrompt(Arr::get($response, 'data', []));

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            if ($exception->status() === 404) {
                $this->notFound = true;
                $this->pageError = 'This prompt could not be found in the service API.';

                return null;
            }

            $this->pageError = $exception->getMessage() ?: 'Prompt details could not be loaded.';

            return null;
        }
    }

    protected function fillFromPrompt(array $prompt): void
    {
        $this->prompt = $prompt;
        $this->name = (string) Arr::get($prompt, 'name', '');
        $this->key = (string) Arr::get($prompt, 'key', 'topic_standard');
        $this->applyFamilyDefaults($this->key);
        $this->description = (string) Arr::get($prompt, 'description', '');
        $this->status = (string) Arr::get($prompt, 'status', 'draft');
    }

    protected function activeVersion(): ?array
    {
        $version = Arr::get($this->prompt, 'active_version');

        return is_array($version) ? $this->mapVersion($version) : null;
    }

    protected function versions(): array
    {
        return collect(Arr::get($this->prompt, 'versions', []))
            ->filter(fn (mixed $version): bool => is_array($version))
            ->map(fn (array $version): array => $this->mapVersion($version))
            ->sortByDesc('version')
            ->values()
            ->all();
    }

    protected function mapVersion(array $version): array
    {
        return [
            'id' => (int) Arr::get($version, 'id'),
            'version' => (int) Arr::get($version, 'version', 1),
            'system_prompt' => (string) Arr::get($version, 'system_prompt', ''),
            'user_prompt' => (string) Arr::get($version, 'user_prompt', ''),
            'output_schema_json' => $this->prettyJson(Arr::get($version, 'output_schema')),
            'variables_text' => collect(Arr::get($version, 'variables', []))->join("\n"),
            'status' => (string) Arr::get($version, 'status', 'draft'),
            'created_at' => $this->formatTimestamp(Arr::get($version, 'created_at')),
            'updated_at' => $this->formatTimestamp(Arr::get($version, 'updated_at')),
        ];
    }

    protected function applyFamilyDefaults(string $key): void
    {
        $family = self::PROMPT_FAMILIES[$key] ?? self::PROMPT_FAMILIES['topic_standard'];
        $this->key = array_key_exists($key, self::PROMPT_FAMILIES) ? $key : 'topic_standard';
        $this->type = $family['type'];

        if (trim($this->description) === '' && $this->creating) {
            $this->description = $family['description'];
        }
    }

    protected function storePayload(array $validated): array
    {
        return [
            'name' => trim($validated['name']),
            'key' => $validated['key'],
            'type' => $this->type,
            'description' => filled($validated['description']) ? trim($validated['description']) : null,
            'status' => $validated['status'],
            'initial_version' => [
                'system_prompt' => trim((string) $validated['initialSystemPrompt']),
                'user_prompt' => trim((string) $validated['initialUserPrompt']),
                'output_schema' => $this->decodedJsonArrayPayload($validated['initialOutputSchemaJson'] ?? ''),
                'variables' => $this->variablesPayload($validated['initialVariablesText'] ?? ''),
                'status' => $validated['initialVersionStatus'],
            ],
        ];
    }

    protected function updatePayload(array $validated): array
    {
        return [
            'name' => trim($validated['name']),
            'key' => $validated['key'],
            'type' => $this->type,
            'description' => filled($validated['description']) ? trim($validated['description']) : null,
            'status' => $validated['status'],
        ];
    }

    protected function versionPayload(array $validated): array
    {
        return [
            'system_prompt' => trim((string) $validated['versionSystemPrompt']),
            'user_prompt' => trim((string) $validated['versionUserPrompt']),
            'output_schema' => $this->decodedJsonArrayPayload($validated['versionOutputSchemaJson'] ?? ''),
            'variables' => $this->variablesPayload($validated['versionVariablesText'] ?? ''),
            'status' => $validated['versionStatus'],
        ];
    }

    protected function variablesPayload(string $value): array
    {
        return collect(preg_split("/\r\n|\n|\r/", $value) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    protected function validateJsonArrayPayload(string $attribute, mixed $value, \Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $fail('The field must contain valid JSON.');

            return;
        }

        if (! is_array($decoded)) {
            $fail('The field must decode to a JSON array.');
        }
    }

    protected function decodedJsonArrayPayload(string $value): ?array
    {
        if (trim($value) === '') {
            return null;
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? array_values($decoded) : null;
        } catch (\JsonException) {
            return null;
        }
    }

    protected function normalizeApiErrors(array $errors): array
    {
        return collect($errors)
            ->mapWithKeys(fn (array $messages, string $key): array => [
                match ($key) {
                    'initial_version.system_prompt' => 'initialSystemPrompt',
                    'initial_version.user_prompt' => 'initialUserPrompt',
                    'initial_version.output_schema' => 'initialOutputSchemaJson',
                    'initial_version.variables' => 'initialVariablesText',
                    'initial_version.status' => 'initialVersionStatus',
                    'output_schema' => 'versionOutputSchemaJson',
                    default => str($key)->replace('.', ' ')->camel()->value(),
                } => $messages,
            ])
            ->all();
    }

    protected function resetVersionDraft(): void
    {
        $this->versionSystemPrompt = '';
        $this->versionUserPrompt = '';
        $this->versionOutputSchemaJson = '';
        $this->versionVariablesText = '';
        $this->versionStatus = 'draft';
    }

    protected function prettyJson(mixed $value): string
    {
        if (! is_array($value) || $value === []) {
            return '';
        }

        $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return is_string($encoded) ? $encoded : '';
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
        session()->flash('auth.error', 'You no longer have access to the admin.');

        return $this->redirectRoute('auth.forbidden', navigate: true);
    }
}
