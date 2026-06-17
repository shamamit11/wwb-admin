<?php

namespace App\Livewire\Admin\Templates;

use App\Services\WideWebBlogApi\Clients\TemplateClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiValidationException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    private const TEMPLATE_TYPES = [
        'standard',
        'tutorial',
        'listicle',
        'comparison',
        'news',
    ];

    private const TEMPLATE_STATUSES = [
        'draft',
        'active',
        'archived',
    ];

    private const BLOCK_TYPES = [
        'heading',
        'paragraph',
        'image',
        'quote',
        'list',
        'code',
        'faq',
        'callout',
    ];

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'type', except: 'all')]
    public string $typeFilter = 'all';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'sort', except: 'updated_at')]
    public string $sortColumn = 'updated_at';

    #[Url(as: 'dir', except: 'desc')]
    public string $sortDirection = 'desc';

    public array $templates = [];

    public bool $drawerOpen = false;

    public ?int $editingTemplateId = null;

    public string $name = '';

    public string $slug = '';

    public string $templateType = 'standard';

    public string $description = '';

    public string $status = 'draft';

    public string $defaultExcerptPrompt = '';

    public string $defaultMetaJson = '';

    public array $blocks = [];

    public int $blockSequence = 0;

    public bool $deleteDialogOpen = false;

    public ?int $deleteTemplateId = null;

    public string $deleteTemplateName = '';

    public ?string $pageError = null;

    public ?string $formError = null;

    public function mount(AdminSessionManager $session, TemplateClient $templates): mixed
    {
        $this->resetBlockEditor();

        return $this->loadTemplates($templates, $session);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180'],
            'templateType' => ['required', 'in:'.implode(',', self::TEMPLATE_TYPES)],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:'.implode(',', self::TEMPLATE_STATUSES)],
            'defaultExcerptPrompt' => ['nullable', 'string'],
            'defaultMetaJson' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                $this->validateJsonArrayPayload($attribute, $value, $fail);
            }],
            'blocks' => ['required', 'array', 'min:1'],
            'blocks.*.blockType' => ['required', 'in:'.implode(',', self::BLOCK_TYPES)],
            'blocks.*.label' => ['nullable', 'string', 'max:160'],
            'blocks.*.defaultMarkdown' => ['nullable', 'string'],
            'blocks.*.settingsJson' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                $this->validateJsonArrayPayload($attribute, $value, $fail);
            }],
            'blocks.*.isRequired' => ['boolean'],
        ];
    }

    public function updated(string $property): void
    {
        if (
            in_array($property, ['name', 'slug', 'templateType', 'description', 'status', 'defaultExcerptPrompt', 'defaultMetaJson'], true)
            || str_starts_with($property, 'blocks.')
        ) {
            $this->validateOnly($property);
        }
    }

    public function sortBy(string $column): void
    {
        if (! in_array($column, ['name', 'template_type', 'status', 'blocks_count', 'updated_at'], true)) {
            return;
        }

        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortColumn = $column;
        $this->sortDirection = $column === 'updated_at' ? 'desc' : 'asc';
    }

    public function openCreateDrawer(): void
    {
        $this->resetForm();
        $this->drawerOpen = true;
    }

    public function openEditDrawer(int $templateId, TemplateClient $templates, AdminSessionManager $session): mixed
    {
        $this->resetValidation();
        $this->formError = null;

        try {
            $response = $templates->show($this->token($session), $session->tokenType(), $templateId);

            $this->fillForm($this->mapTemplate(Arr::get($response, 'data', [])));
            $this->drawerOpen = true;

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'Template details could not be loaded.';

            return null;
        }
    }

    public function closeDrawer(): void
    {
        $this->resetForm();
    }

    public function addBlock(): void
    {
        $this->blocks[] = $this->makeBlockEditorState('paragraph');
        $this->syncBlockOrder();
    }

    public function removeBlock(int $index): void
    {
        if (! array_key_exists($index, $this->blocks)) {
            return;
        }

        unset($this->blocks[$index]);
        $this->blocks = array_values($this->blocks);

        if ($this->blocks === []) {
            $this->resetBlockEditor();
        } else {
            $this->syncBlockOrder();
        }
    }

    public function moveBlockUp(int $index): void
    {
        if ($index <= 0 || ! isset($this->blocks[$index], $this->blocks[$index - 1])) {
            return;
        }

        [$this->blocks[$index - 1], $this->blocks[$index]] = [$this->blocks[$index], $this->blocks[$index - 1]];

        $this->syncBlockOrder();
    }

    public function moveBlockDown(int $index): void
    {
        if (! isset($this->blocks[$index], $this->blocks[$index + 1])) {
            return;
        }

        [$this->blocks[$index + 1], $this->blocks[$index]] = [$this->blocks[$index], $this->blocks[$index + 1]];

        $this->syncBlockOrder();
    }

    public function save(TemplateClient $templates, AdminSessionManager $session): mixed
    {
        $validated = $this->validate();
        $payload = $this->templatePayload($validated);
        $this->formError = null;

        try {
            if ($this->editingTemplateId) {
                $templates->update($this->token($session), $session->tokenType(), $this->editingTemplateId, $payload);
                session()->flash('status', 'Template updated.');
            } else {
                $templates->store($this->token($session), $session->tokenType(), $payload);
                session()->flash('status', 'Template created.');
            }

            $this->loadTemplates($templates, $session);
            $this->resetForm();

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->formError = $exception->getMessage();
            $this->addApiErrors($this->normalizeApiErrors($exception->errors()));

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->formError = $exception->getMessage() ?: 'Template changes could not be saved.';

            return null;
        }
    }

    public function confirmDelete(int $templateId): void
    {
        $template = collect($this->templates)->firstWhere('id', $templateId);

        $this->deleteTemplateId = $templateId;
        $this->deleteTemplateName = (string) ($template['name'] ?? 'this template');
        $this->deleteDialogOpen = true;
        $this->pageError = null;
    }

    public function cancelDelete(): void
    {
        $this->deleteDialogOpen = false;
        $this->deleteTemplateId = null;
        $this->deleteTemplateName = '';
    }

    public function delete(TemplateClient $templates, AdminSessionManager $session): mixed
    {
        if (! $this->deleteTemplateId) {
            return null;
        }

        try {
            $deletedTemplateId = $this->deleteTemplateId;

            $templates->delete($this->token($session), $session->tokenType(), $deletedTemplateId);
            session()->flash('status', 'Template deleted.');

            $this->cancelDelete();
            $this->loadTemplates($templates, $session);

            if ($this->editingTemplateId === $deletedTemplateId) {
                $this->resetForm();
            }

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'Template deletion failed.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.templates.index', [
            'templates' => $this->visibleTemplates(),
            'templateTypes' => self::TEMPLATE_TYPES,
            'templateStatuses' => self::TEMPLATE_STATUSES,
            'blockTypes' => self::BLOCK_TYPES,
        ])->layout('layouts.admin', [
            'title' => 'Templates',
        ]);
    }

    protected function loadTemplates(TemplateClient $templates, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $templates->index($this->token($session), $session->tokenType());
            $this->templates = collect(Arr::get($response, 'data', []))
                ->map(fn (array $template): array => $this->mapTemplate($template))
                ->values()
                ->all();

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->templates = [];
            $this->pageError = $exception->getMessage() ?: 'Templates could not be loaded from the service API.';

            return null;
        }
    }

    protected function visibleTemplates(): array
    {
        $filtered = collect($this->templates)
            ->filter(function (array $template): bool {
                if ($this->typeFilter !== 'all' && $template['template_type'] !== $this->typeFilter) {
                    return false;
                }

                if ($this->statusFilter !== 'all' && $template['status'] !== $this->statusFilter) {
                    return false;
                }

                if ($this->search === '') {
                    return true;
                }

                $needle = mb_strtolower(trim($this->search));
                $haystack = mb_strtolower(implode(' ', array_filter([
                    $template['name'],
                    $template['slug'],
                    $template['description'],
                    $template['template_type'],
                    $template['status'],
                ])));

                return str_contains($haystack, $needle);
            });

        return $filtered
            ->sortBy(
                fn (array $template): mixed => $this->sortValue($template),
                SORT_NATURAL,
                $this->sortDirection === 'desc'
            )
            ->values()
            ->all();
    }

    protected function mapTemplate(array $template): array
    {
        $blocks = collect(Arr::get($template, 'blocks', []))
            ->map(fn (array $block): array => $this->mapTemplateBlock($block))
            ->sortBy('sort_order')
            ->values()
            ->all();

        $updatedAtRaw = Arr::get($template, 'updated_at');

        return [
            'id' => Arr::get($template, 'id'),
            'name' => Arr::get($template, 'name', 'Untitled template'),
            'slug' => Arr::get($template, 'slug', ''),
            'template_type' => Arr::get($template, 'template_type', 'standard'),
            'description' => Arr::get($template, 'description'),
            'status' => Arr::get($template, 'status', 'draft'),
            'default_excerpt_prompt' => Arr::get($template, 'default_excerpt_prompt'),
            'default_meta' => Arr::get($template, 'default_meta', []),
            'blocks' => $blocks,
            'blocks_count' => count($blocks),
            'updated_at' => $this->formatTimestamp($updatedAtRaw),
            'updated_at_raw' => $updatedAtRaw,
        ];
    }

    protected function mapTemplateBlock(array $block): array
    {
        return [
            'id' => Arr::get($block, 'id'),
            'block_key' => Arr::get($block, 'block_key'),
            'block_type' => Arr::get($block, 'block_type', 'paragraph'),
            'sort_order' => (int) Arr::get($block, 'sort_order', 1),
            'label' => Arr::get($block, 'label'),
            'default_markdown' => Arr::get($block, 'default_markdown'),
            'settings' => Arr::get($block, 'settings'),
            'is_required' => (bool) Arr::get($block, 'is_required', false),
        ];
    }

    protected function fillForm(array $template): void
    {
        $this->resetValidation();
        $this->editingTemplateId = $template['id'];
        $this->name = $template['name'];
        $this->slug = $template['slug'];
        $this->templateType = $template['template_type'];
        $this->description = (string) ($template['description'] ?? '');
        $this->status = $template['status'];
        $this->defaultExcerptPrompt = (string) ($template['default_excerpt_prompt'] ?? '');
        $this->defaultMetaJson = $this->encodeJson($template['default_meta'] ?? null);
        $this->blocks = collect($template['blocks'])
            ->map(function (array $block): array {
                return [
                    'key' => 'existing-'.($block['id'] ?? $block['block_key'] ?? $this->nextBlockKey()),
                    'sortOrder' => (int) $block['sort_order'],
                    'blockType' => $block['block_type'],
                    'label' => (string) ($block['label'] ?? ''),
                    'defaultMarkdown' => (string) ($block['default_markdown'] ?? ''),
                    'settingsJson' => $this->encodeJson($block['settings'] ?? null),
                    'isRequired' => (bool) $block['is_required'],
                ];
            })
            ->values()
            ->all();

        $this->syncBlockOrder();
    }

    protected function resetForm(): void
    {
        $this->resetValidation();
        $this->drawerOpen = false;
        $this->editingTemplateId = null;
        $this->name = '';
        $this->slug = '';
        $this->templateType = 'standard';
        $this->description = '';
        $this->status = 'draft';
        $this->defaultExcerptPrompt = '';
        $this->defaultMetaJson = '';
        $this->formError = null;
        $this->resetBlockEditor();
    }

    protected function resetBlockEditor(): void
    {
        $this->blocks = [$this->makeBlockEditorState('heading')];
        $this->syncBlockOrder();
    }

    protected function makeBlockEditorState(string $blockType): array
    {
        return [
            'key' => $this->nextBlockKey(),
            'sortOrder' => count($this->blocks) + 1,
            'blockType' => $blockType,
            'label' => '',
            'defaultMarkdown' => '',
            'settingsJson' => '',
            'isRequired' => false,
        ];
    }

    protected function syncBlockOrder(): void
    {
        $this->blocks = collect($this->blocks)
            ->values()
            ->map(function (array $block, int $index): array {
                $block['sortOrder'] = $index + 1;

                return $block;
            })
            ->all();
    }

    protected function templatePayload(array $validated): array
    {
        return [
            'name' => trim($validated['name']),
            'slug' => filled($validated['slug']) ? trim($validated['slug']) : null,
            'template_type' => $validated['templateType'],
            'description' => filled($validated['description']) ? trim($validated['description']) : null,
            'status' => $validated['status'],
            'default_excerpt_prompt' => filled($validated['defaultExcerptPrompt']) ? trim($validated['defaultExcerptPrompt']) : null,
            'default_meta' => $this->decodeJsonArray($validated['defaultMetaJson'] ?? ''),
            'blocks' => collect($validated['blocks'])
                ->values()
                ->map(function (array $block, int $index): array {
                    return [
                        'block_type' => $block['blockType'],
                        'sort_order' => $index + 1,
                        'label' => filled($block['label']) ? trim($block['label']) : null,
                        'default_markdown' => filled($block['defaultMarkdown']) ? trim($block['defaultMarkdown']) : null,
                        'settings' => $this->decodeJsonArray($block['settingsJson'] ?? ''),
                        'is_required' => (bool) ($block['isRequired'] ?? false),
                    ];
                })
                ->all(),
        ];
    }

    protected function normalizeApiErrors(array $errors): array
    {
        $mapped = [];

        foreach ($errors as $field => $messages) {
            $property = match (true) {
                $field === 'template_type' => 'templateType',
                $field === 'default_excerpt_prompt' => 'defaultExcerptPrompt',
                $field === 'default_meta' => 'defaultMetaJson',
                preg_match('/^blocks\.(\d+)\.block_type$/', $field) === 1 => preg_replace('/\.block_type$/', '.blockType', $field) ?: $field,
                preg_match('/^blocks\.(\d+)\.default_markdown$/', $field) === 1 => preg_replace('/\.default_markdown$/', '.defaultMarkdown', $field) ?: $field,
                preg_match('/^blocks\.(\d+)\.is_required$/', $field) === 1 => preg_replace('/\.is_required$/', '.isRequired', $field) ?: $field,
                preg_match('/^blocks\.(\d+)\.settings$/', $field) === 1 => preg_replace('/\.settings$/', '.settingsJson', $field) ?: $field,
                preg_match('/^blocks\.(\d+)\.sort_order$/', $field) === 1 => preg_replace('/\.sort_order$/', '.blockType', $field) ?: $field,
                default => $field,
            };

            $mapped[$property] = $messages;
        }

        return $mapped;
    }

    protected function addApiErrors(array $errors): void
    {
        foreach ($errors as $field => $messages) {
            foreach ((array) $messages as $message) {
                $this->addError($field, (string) $message);
            }
        }
    }

    protected function sortValue(array $template): mixed
    {
        return match ($this->sortColumn) {
            'name' => mb_strtolower($template['name']),
            'template_type' => mb_strtolower($template['template_type']),
            'status' => mb_strtolower($template['status']),
            'blocks_count' => $template['blocks_count'],
            default => $template['updated_at_raw'] ?? '',
        };
    }

    protected function validateJsonArrayPayload(string $attribute, mixed $value, \Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $fail('The '.str_replace('_', ' ', str_replace('.', ' ', $attribute)).' must be valid JSON.');

            return;
        }

        if (! is_array($decoded)) {
            $fail('The '.str_replace('_', ' ', str_replace('.', ' ', $attribute)).' must decode to a JSON object or array.');
        }
    }

    protected function decodeJsonArray(?string $value): ?array
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : null;
    }

    protected function encodeJson(mixed $value): string
    {
        if (! is_array($value) || $value === []) {
            return '';
        }

        return (string) json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function nextBlockKey(): string
    {
        $this->blockSequence++;

        return 'new-'.$this->blockSequence;
    }

    protected function formatTimestamp(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('M j, Y');
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
