<?php

namespace App\Livewire\Admin\KnowledgeBase;

use App\Services\WideWebBlogApi\Clients\KnowledgeBaseClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiValidationException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Editor extends Component
{
    private const ENTRY_TYPES = [
        'note',
        'research',
        'experience',
        'architecture',
        'code',
        'reference',
        'idea',
    ];

    private const ENTRY_STATUSES = [
        'draft',
        'active',
        'archived',
    ];

    public ?int $editingEntryId = null;

    public string $title = '';

    public string $slug = '';

    public string $entryType = 'note';

    public string $status = 'draft';

    public string $summary = '';

    public string $contentMarkdown = '';

    public string $sourceUrl = '';

    public string $metadataJson = '';

    public array $linkedPosts = [];

    public array $linkedTopics = [];

    public ?string $pageError = null;

    public ?string $formError = null;

    public function mount(KnowledgeBaseClient $knowledgeBase, AdminSessionManager $session, mixed $knowledgeBaseEntry = null): mixed
    {
        if (is_numeric($knowledgeBaseEntry)) {
            return $this->loadEntry((int) $knowledgeBaseEntry, $knowledgeBase, $session);
        }

        return null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:190'],
            'entryType' => ['required', 'in:'.implode(',', self::ENTRY_TYPES)],
            'status' => ['required', 'in:'.implode(',', self::ENTRY_STATUSES)],
            'summary' => ['nullable', 'string'],
            'contentMarkdown' => ['required', 'string'],
            'sourceUrl' => ['nullable', 'string', 'max:500', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || trim($value) === '') {
                    return;
                }

                if (filter_var($value, FILTER_VALIDATE_URL) === false) {
                    $fail('The source url field must be a valid URL.');
                }
            }],
            'metadataJson' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                $this->validateJsonStringArrayPayload($value, $fail);
            }],
        ];
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['title', 'slug', 'entryType', 'status', 'summary', 'contentMarkdown', 'sourceUrl', 'metadataJson'], true)) {
            $this->validateOnly($property);
        }
    }

    public function insertMarkdownSnippet(string $snippet): void
    {
        $snippets = [
            'heading' => "## Section heading",
            'link' => '[Reference link](https://example.com)',
            'list' => "- First point\n- Second point",
            'quote' => '> Key excerpt',
            'code' => "```text\nReference snippet\n```",
        ];

        if (! array_key_exists($snippet, $snippets)) {
            return;
        }

        $current = rtrim($this->contentMarkdown);
        $this->contentMarkdown = $current === ''
            ? $snippets[$snippet]
            : $current."\n".$snippets[$snippet];

        $this->validateOnly('contentMarkdown');
    }

    public function save(KnowledgeBaseClient $knowledgeBase, AdminSessionManager $session): mixed
    {
        $validated = $this->validate();
        $payload = $this->payload($validated);
        $this->formError = null;

        try {
            if ($this->editingEntryId) {
                $response = $knowledgeBase->update($this->token($session), $session->tokenType(), $this->editingEntryId, $payload);
                $this->fillForm(Arr::get($response, 'data', []));
                session()->flash('status', 'Knowledge entry updated.');

                return null;
            }

            $response = $knowledgeBase->store($this->token($session), $session->tokenType(), $payload);
            $createdId = Arr::get($response, 'data.id');
            session()->flash('status', 'Knowledge entry created.');

            if (is_int($createdId)) {
                return $this->redirectRoute('knowledge-base.edit', ['knowledgeBaseEntry' => $createdId], navigate: true);
            }

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->formError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->formError = $exception->getMessage() ?: 'Knowledge entry changes could not be saved.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.knowledge-base.editor', [
            'entryTypes' => self::ENTRY_TYPES,
            'entryStatuses' => self::ENTRY_STATUSES,
        ])->layout('layouts.admin', [
            'title' => $this->editingEntryId ? 'Edit Knowledge Entry' : 'Create Knowledge Entry',
        ]);
    }

    protected function loadEntry(int $entryId, KnowledgeBaseClient $knowledgeBase, AdminSessionManager $session): mixed
    {
        try {
            $response = $knowledgeBase->show($this->token($session), $session->tokenType(), $entryId);
            $this->fillForm(Arr::get($response, 'data', []));

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'Knowledge entry details could not be loaded.';

            return null;
        }
    }

    protected function fillForm(array $entry): void
    {
        $this->editingEntryId = Arr::get($entry, 'id');
        $this->title = (string) Arr::get($entry, 'title', '');
        $this->slug = (string) Arr::get($entry, 'slug', '');
        $this->entryType = (string) Arr::get($entry, 'entry_type', 'note');
        $this->status = (string) Arr::get($entry, 'status', 'draft');
        $this->summary = (string) (Arr::get($entry, 'summary') ?? '');
        $this->contentMarkdown = (string) Arr::get($entry, 'content_markdown', '');
        $this->sourceUrl = (string) (Arr::get($entry, 'source_url') ?? '');
        $this->metadataJson = $this->jsonArray(Arr::get($entry, 'metadata'));
        $this->linkedPosts = collect(Arr::get($entry, 'linked_posts', []))
            ->filter(fn (mixed $item): bool => is_array($item))
            ->values()
            ->all();
        $this->linkedTopics = collect(Arr::get($entry, 'linked_topics', []))
            ->filter(fn (mixed $item): bool => is_array($item))
            ->values()
            ->all();
    }

    protected function payload(array $validated): array
    {
        return [
            'title' => trim($validated['title']),
            'slug' => filled($validated['slug']) ? trim($validated['slug']) : null,
            'entry_type' => $validated['entryType'],
            'status' => $validated['status'],
            'summary' => filled($validated['summary']) ? trim($validated['summary']) : null,
            'content_markdown' => $validated['contentMarkdown'],
            'source_url' => filled($validated['sourceUrl']) ? trim($validated['sourceUrl']) : null,
            'metadata' => $this->metadataPayload(),
        ];
    }

    protected function metadataPayload(): ?array
    {
        if (trim($this->metadataJson) === '') {
            return null;
        }

        $decoded = json_decode($this->metadataJson, true);

        return is_array($decoded) ? array_values($decoded) : null;
    }

    protected function jsonArray(mixed $value): string
    {
        if (! is_array($value) || $value === []) {
            return '';
        }

        $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : '';
    }

    protected function validateJsonStringArrayPayload(mixed $value, \Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $fail('Use a valid JSON array of strings.');

            return;
        }

        if (! is_array($decoded) || array_is_list($decoded) === false) {
            $fail('Use a JSON array of strings.');

            return;
        }

        foreach ($decoded as $item) {
            if (! is_string($item)) {
                $fail('Each metadata item must be a string.');

                return;
            }
        }
    }

    protected function normalizeApiErrors(array $errors): array
    {
        $mapped = [];

        foreach ($errors as $field => $messages) {
            $property = preg_replace_callback('/_([a-z])/', fn (array $matches): string => strtoupper($matches[1]), $field) ?? $field;

            $property = match ($property) {
                'entryType' => 'entryType',
                'contentMarkdown' => 'contentMarkdown',
                'sourceUrl' => 'sourceUrl',
                'metadata' => 'metadataJson',
                default => $property,
            };

            $mapped[$property] = $messages;
        }

        return $mapped;
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
