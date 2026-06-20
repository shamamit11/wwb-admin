<?php

namespace App\Livewire\Admin\Pages;

use App\Services\WideWebBlogApi\Clients\PageClient;
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
    private const LEGAL_PAGE_PRESETS = [
        'privacy-policy' => [
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'summary' => 'Public legal page describing how data is collected, used, and protected.',
            'content_markdown' => "# Privacy Policy\n\n## Overview\n\nDescribe how the site collects, uses, stores, and protects personal data.\n",
        ],
        'terms-and-conditions' => [
            'title' => 'Terms and Conditions',
            'slug' => 'terms-and-conditions',
            'summary' => 'Public legal page covering site usage terms, obligations, and limitations.',
            'content_markdown' => "# Terms and Conditions\n\n## Overview\n\nDescribe the rules, responsibilities, and conditions for using the site.\n",
        ],
    ];

    private const PAGE_TYPES = [
        'legal',
        'marketing',
        'support',
        'faq',
        'standard',
    ];

    private const PAGE_STATUSES = [
        'draft',
        'scheduled',
        'published',
        'unpublished',
        'archived',
    ];

    private const PAGE_VISIBILITIES = [
        'public',
        'private',
        'internal',
    ];

    public ?int $editingPageId = null;

    public string $title = '';

    public string $slug = '';

    public string $pageType = 'standard';

    public string $status = 'draft';

    public string $summary = '';

    public string $contentMarkdown = '';

    public string $visibility = 'public';

    public string $publishedAt = '';

    public string $scheduledFor = '';

    public string $canonicalUrlDisplay = '';

    public string $metaJson = '';

    public array $createdBy = [];

    public array $updatedBy = [];

    public ?string $createdAt = null;

    public ?string $updatedAt = null;

    public ?string $pageError = null;

    public ?string $formError = null;

    public function mount(PageClient $pages, AdminSessionManager $session, mixed $page = null): mixed
    {
        if (is_numeric($page)) {
            return $this->loadPage((int) $page, $pages, $session);
        }

        $this->applyLegalPreset((string) request()->query('preset', ''));

        return null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:190'],
            'pageType' => ['required', 'in:'.implode(',', self::PAGE_TYPES)],
            'status' => ['required', 'in:'.implode(',', self::PAGE_STATUSES)],
            'summary' => ['nullable', 'string'],
            'contentMarkdown' => ['required', 'string'],
            'visibility' => ['required', 'in:'.implode(',', self::PAGE_VISIBILITIES)],
            'publishedAt' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                $this->validateDateTimeInput($value, 'published date', $fail);
            }],
            'scheduledFor' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                $this->validateDateTimeInput($value, 'scheduled publish', $fail);
            }],
            'metaJson' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                $this->validateJsonStringArrayPayload($value, $fail);
            }],
        ];
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['title', 'slug', 'pageType', 'status', 'summary', 'contentMarkdown', 'visibility', 'publishedAt', 'scheduledFor', 'metaJson'], true)) {
            $this->validateOnly($property);
        }
    }

    public function insertMarkdownSnippet(string $snippet): void
    {
        $snippets = [
            'heading' => "## Section heading",
            'link' => '[Reference link](https://example.com)',
            'list' => "- First point\n- Second point",
            'quote' => '> Highlighted statement',
            'faq' => "### Question\nAnswer",
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

    public function save(PageClient $pages, AdminSessionManager $session): mixed
    {
        $validated = $this->validate();
        $payload = $this->payload($validated);
        $this->formError = null;

        try {
            if ($this->editingPageId) {
                $response = $pages->update($this->token($session), $session->tokenType(), $this->editingPageId, $payload);
                $this->fillForm(Arr::get($response, 'data', []));
                session()->flash('status', 'Page updated.');

                return null;
            }

            $response = $pages->store($this->token($session), $session->tokenType(), $payload);
            $createdId = Arr::get($response, 'data.id');
            session()->flash('status', 'Page created.');

            if (is_int($createdId)) {
                return $this->redirectRoute('pages.edit', ['page' => $createdId], navigate: true);
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
            $this->formError = $exception->getMessage() ?: 'Page changes could not be saved.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.pages.editor', [
            'pageTypes' => self::PAGE_TYPES,
            'pageStatuses' => self::PAGE_STATUSES,
            'pageVisibilities' => self::PAGE_VISIBILITIES,
        ])->layout('layouts.admin', [
            'title' => $this->editingPageId ? 'Edit Page' : 'Create Page',
        ]);
    }

    protected function loadPage(int $pageId, PageClient $pages, AdminSessionManager $session): mixed
    {
        try {
            $response = $pages->show($this->token($session), $session->tokenType(), $pageId);
            $this->fillForm(Arr::get($response, 'data', []));

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'Page details could not be loaded.';

            return null;
        }
    }

    protected function fillForm(array $page): void
    {
        $this->editingPageId = Arr::get($page, 'id');
        $this->title = (string) Arr::get($page, 'title', '');
        $this->slug = (string) Arr::get($page, 'slug', '');
        $this->pageType = (string) Arr::get($page, 'type', 'standard');
        $this->status = (string) Arr::get($page, 'status', 'draft');
        $this->summary = (string) (Arr::get($page, 'summary') ?? '');
        $this->contentMarkdown = (string) Arr::get($page, 'content_markdown', '');
        $this->visibility = (string) Arr::get($page, 'visibility', 'public');
        $this->publishedAt = $this->toDateTimeLocalInput(Arr::get($page, 'published_at'));
        $this->scheduledFor = $this->toDateTimeLocalInput(Arr::get($page, 'scheduled_for'));
        $this->canonicalUrlDisplay = (string) (Arr::get($page, 'canonical_url') ?? '');
        $this->metaJson = $this->jsonArray(Arr::get($page, 'meta'));
        $this->createdBy = is_array(Arr::get($page, 'created_by')) ? Arr::get($page, 'created_by') : [];
        $this->updatedBy = is_array(Arr::get($page, 'updated_by')) ? Arr::get($page, 'updated_by') : [];
        $this->createdAt = $this->formatTimestamp(Arr::get($page, 'created_at'));
        $this->updatedAt = $this->formatTimestamp(Arr::get($page, 'updated_at'));
    }

    protected function applyLegalPreset(string $preset): void
    {
        $definition = self::LEGAL_PAGE_PRESETS[$preset] ?? null;

        if (! is_array($definition)) {
            return;
        }

        $this->title = $definition['title'];
        $this->slug = $definition['slug'];
        $this->pageType = 'legal';
        $this->status = 'published';
        $this->visibility = 'public';
        $this->summary = $definition['summary'];
        $this->contentMarkdown = $definition['content_markdown'];
        $this->metaJson = "[\n    \"legal\"\n]";
    }

    protected function payload(array $validated): array
    {
        return [
            'title' => trim($validated['title']),
            'slug' => filled($validated['slug']) ? trim($validated['slug']) : null,
            'type' => $validated['pageType'],
            'status' => $validated['status'],
            'summary' => filled($validated['summary']) ? trim($validated['summary']) : null,
            'content_markdown' => $validated['contentMarkdown'],
            'visibility' => $validated['visibility'],
            'published_at' => $this->toApiDateTime($validated['publishedAt'] ?? ''),
            'scheduled_for' => $this->toApiDateTime($validated['scheduledFor'] ?? ''),
            'meta' => $this->metaPayload(),
        ];
    }

    protected function metaPayload(): ?array
    {
        if (trim($this->metaJson) === '') {
            return null;
        }

        $decoded = json_decode($this->metaJson, true);

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

    protected function normalizeApiErrors(array $errors): array
    {
        $mapped = [];

        foreach ($errors as $field => $messages) {
            $property = preg_replace_callback('/_([a-z])/', fn (array $matches): string => strtoupper($matches[1]), $field) ?? $field;

            $property = match ($property) {
                'type' => 'pageType',
                'contentMarkdown' => 'contentMarkdown',
                'publishedAt' => 'publishedAt',
                'scheduledFor' => 'scheduledFor',
                'meta' => 'metaJson',
                default => $property,
            };

            $mapped[$property] = $messages;
        }

        return $mapped;
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

    protected function validateDateTimeInput(mixed $value, string $label, \Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        try {
            Carbon::createFromFormat('Y-m-d\TH:i', $value);
        } catch (\Throwable) {
            $fail(sprintf('The %s field must be a valid date and time.', $label));
        }
    }

    protected function toApiDateTime(string $value): ?string
    {
        if (trim($value) === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d\TH:i', $value)->toISOString();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function toDateTimeLocalInput(mixed $value): string
    {
        if (! is_string($value) || $value === '') {
            return '';
        }

        try {
            return Carbon::parse($value)->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            return '';
        }
    }

    protected function formatTimestamp(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i');
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
