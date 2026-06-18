<?php

namespace App\Livewire\Admin\ContentBriefs;

use App\Services\WideWebBlogApi\Clients\CategoryClient;
use App\Services\WideWebBlogApi\Clients\ContentBriefClient;
use App\Services\WideWebBlogApi\Clients\TemplateClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiValidationException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Show extends Component
{
    private const BRIEF_EDIT_STATUSES = [
        'draft',
        'rejected',
        'used',
    ];

    public int $briefId;

    public string $title = '';
    public string $slug = '';
    public string $metaTitle = '';
    public string $metaDescription = '';
    public string $primaryKeyword = '';
    public string $secondaryKeywords = '';
    public string $searchIntent = '';
    public string $status = 'draft';
    public string $outlineText = '';
    public string $headingsText = '';
    public string $faqSuggestionsText = '';
    public string $internalLinkSuggestionsText = '';
    public string $imageSuggestionsText = '';

    public array $topic = [];
    public bool $canGenerateDraft = false;
    public ?string $approvedAt = null;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    public bool $draftDialogOpen = false;
    public string $draftCategoryId = '';
    public string $draftTemplateId = '';
    public string $draftVisibility = 'public';
    public string $draftPromptTemplateKey = '';
    public array $categoryOptions = [];
    public array $templateOptions = [];

    public ?string $pageError = null;
    public ?string $formError = null;
    public ?string $draftError = null;
    public bool $notFound = false;

    public function mount(
        int $contentBrief,
        AdminSessionManager $session,
        ContentBriefClient $briefs,
        CategoryClient $categories,
        TemplateClient $templates,
    ): mixed {
        $this->briefId = $contentBrief;

        $load = $this->loadBrief($briefs, $session);
        if ($load) {
            return $load;
        }

        return $this->loadLookups($session, $categories, $templates);
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:190'],
            'metaTitle' => ['nullable', 'string', 'max:255'],
            'metaDescription' => ['nullable', 'string'],
            'primaryKeyword' => ['nullable', 'string', 'max:255'],
            'secondaryKeywords' => ['nullable', 'string'],
            'searchIntent' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:'.implode(',', self::BRIEF_EDIT_STATUSES)],
            'outlineText' => ['nullable', 'string'],
            'headingsText' => ['nullable', 'string'],
            'faqSuggestionsText' => ['nullable', 'string'],
            'internalLinkSuggestionsText' => ['nullable', 'string'],
            'imageSuggestionsText' => ['nullable', 'string'],
        ];
    }

    public function draftRules(): array
    {
        return [
            'draftCategoryId' => ['required', 'integer', 'min:1'],
            'draftTemplateId' => ['nullable', 'integer', 'min:1'],
            'draftVisibility' => ['required', 'in:public,private,internal'],
            'draftPromptTemplateKey' => ['nullable', 'string', 'max:190'],
        ];
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['draftCategoryId', 'draftTemplateId', 'draftVisibility', 'draftPromptTemplateKey'], true)) {
            $this->validateOnly($property, $this->draftRules());

            return;
        }

        if (in_array($property, ['title', 'slug', 'metaTitle', 'metaDescription', 'primaryKeyword', 'secondaryKeywords', 'searchIntent', 'status', 'outlineText', 'headingsText', 'faqSuggestionsText', 'internalLinkSuggestionsText', 'imageSuggestionsText'], true)) {
            $this->validateOnly($property);
        }
    }

    public function save(ContentBriefClient $briefs, AdminSessionManager $session): mixed
    {
        $validated = $this->validate();
        $this->formError = null;

        try {
            $response = $briefs->update($this->token($session), $session->tokenType(), $this->briefId, $this->briefPayload($validated));
            $this->fillBrief(Arr::get($response, 'data', []));

            session()->flash('status', 'Content brief updated.');

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->formError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->formError = $exception->getMessage() ?: 'Content brief changes could not be saved.';

            return null;
        }
    }

    public function approve(ContentBriefClient $briefs, AdminSessionManager $session): mixed
    {
        try {
            $response = $briefs->approve($this->token($session), $session->tokenType(), $this->briefId);
            $this->fillBrief(Arr::get($response, 'data', []));

            session()->flash('status', 'Content brief approved.');

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->formError = $exception->getMessage() ?: 'Content brief approval failed.';

            return null;
        }
    }

    public function reject(ContentBriefClient $briefs, AdminSessionManager $session): mixed
    {
        $this->status = 'rejected';

        return $this->save($briefs, $session);
    }

    public function openDraftDialog(): void
    {
        $this->resetValidation();
        $this->draftDialogOpen = true;
        $this->draftError = null;
        $this->draftCategoryId = $this->draftCategoryId !== '' ? $this->draftCategoryId : '';
        $this->draftTemplateId = '';
        $this->draftVisibility = 'public';
        $this->draftPromptTemplateKey = '';
    }

    public function closeDraftDialog(): void
    {
        $this->resetValidation();
        $this->draftDialogOpen = false;
        $this->draftError = null;
    }

    public function generateDraft(ContentBriefClient $briefs, AdminSessionManager $session): mixed
    {
        $validated = $this->validate($this->draftRules());
        $this->draftError = null;

        try {
            $response = $briefs->generateDraft($this->token($session), $session->tokenType(), $this->briefId, [
                'category_id' => (int) $validated['draftCategoryId'],
                'template_id' => filled($validated['draftTemplateId']) ? (int) $validated['draftTemplateId'] : null,
                'visibility' => $validated['draftVisibility'],
                'prompt_template_key' => filled($validated['draftPromptTemplateKey']) ? trim($validated['draftPromptTemplateKey']) : null,
            ]);

            $jobId = Arr::get($response, 'data.id');

            $this->closeDraftDialog();
            session()->flash('status', 'Blog draft generation job created.');

            if (is_int($jobId) || ctype_digit((string) $jobId)) {
                return $this->redirectRoute('ai-jobs.show', ['aiJob' => (int) $jobId], navigate: true);
            }

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->draftError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->draftError = $exception->getMessage() ?: 'Blog draft generation failed.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.content-briefs.show', [
            'statusOptions' => self::BRIEF_EDIT_STATUSES,
            'canApprove' => in_array($this->status, ['draft', 'rejected'], true),
            'canReject' => $this->status === 'draft',
            'topicLink' => filled($this->topic['id'] ?? null)
                ? route('topic-queue.show', ['topic' => $this->topic['id']])
                : null,
        ])->layout('layouts.admin', [
            'title' => 'Content Brief Review',
        ]);
    }

    protected function loadBrief(ContentBriefClient $briefs, AdminSessionManager $session): mixed
    {
        $this->pageError = null;
        $this->notFound = false;

        try {
            $response = $briefs->show($this->token($session), $session->tokenType(), $this->briefId);
            $this->fillBrief(Arr::get($response, 'data', []));

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            if ($exception->status() === 404) {
                $this->notFound = true;
                $this->pageError = 'This content brief could not be found in the service API.';

                return null;
            }

            $this->pageError = $exception->getMessage() ?: 'Content brief details could not be loaded.';

            return null;
        }
    }

    protected function loadLookups(AdminSessionManager $session, CategoryClient $categories, TemplateClient $templates): mixed
    {
        try {
            $token = $this->token($session);
            $tokenType = $session->tokenType();

            $this->categoryOptions = collect(Arr::get($categories->index($token, $tokenType), 'data', []))
                ->map(fn (array $category): array => [
                    'id' => (int) Arr::get($category, 'id'),
                    'name' => (string) Arr::get($category, 'name', 'Category'),
                ])
                ->values()
                ->all();

            $this->templateOptions = collect(Arr::get($templates->index($token, $tokenType), 'data', []))
                ->map(fn (array $template): array => [
                    'id' => (int) Arr::get($template, 'id'),
                    'name' => (string) Arr::get($template, 'name', 'Template'),
                ])
                ->values()
                ->all();

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'Draft generation options could not be loaded.';
            $this->categoryOptions = [];
            $this->templateOptions = [];

            return null;
        }
    }

    protected function fillBrief(array $brief): void
    {
        $this->title = (string) Arr::get($brief, 'title', '');
        $this->slug = (string) Arr::get($brief, 'slug', '');
        $this->metaTitle = (string) Arr::get($brief, 'meta_title', '');
        $this->metaDescription = (string) Arr::get($brief, 'meta_description', '');
        $this->primaryKeyword = (string) Arr::get($brief, 'primary_keyword', '');
        $this->secondaryKeywords = implode("\n", Arr::get($brief, 'secondary_keywords', []));
        $this->searchIntent = (string) Arr::get($brief, 'search_intent', '');
        $this->outlineText = $this->formatNestedLines(Arr::get($brief, 'outline', []));
        $this->headingsText = implode("\n", Arr::get($brief, 'headings', []));
        $this->faqSuggestionsText = $this->formatNestedLines(Arr::get($brief, 'faq_suggestions', []));
        $this->internalLinkSuggestionsText = $this->formatNestedLines(Arr::get($brief, 'internal_link_suggestions', []));
        $this->imageSuggestionsText = $this->formatNestedLines(Arr::get($brief, 'image_suggestions', []));
        $this->status = (string) Arr::get($brief, 'status', 'draft');
        $this->canGenerateDraft = (bool) Arr::get($brief, 'can_generate_draft', false);
        $this->approvedAt = $this->formatTimestamp(Arr::get($brief, 'approved_at'));
        $this->createdAt = $this->formatTimestamp(Arr::get($brief, 'created_at'));
        $this->updatedAt = $this->formatTimestamp(Arr::get($brief, 'updated_at'));
        $this->topic = [
            'id' => Arr::get($brief, 'topic.id'),
            'title' => Arr::get($brief, 'topic.title'),
            'slug' => Arr::get($brief, 'topic.slug'),
            'cluster' => Arr::get($brief, 'topic.cluster'),
            'status' => Arr::get($brief, 'topic.status'),
        ];
    }

    protected function briefPayload(array $validated): array
    {
        return array_filter([
            'title' => filled($validated['title']) ? trim($validated['title']) : null,
            'slug' => filled($validated['slug']) ? trim($validated['slug']) : null,
            'meta_title' => filled($validated['metaTitle']) ? trim($validated['metaTitle']) : null,
            'meta_description' => filled($validated['metaDescription']) ? trim($validated['metaDescription']) : null,
            'primary_keyword' => filled($validated['primaryKeyword']) ? trim($validated['primaryKeyword']) : null,
            'secondary_keywords' => $this->stringList($validated['secondaryKeywords'] ?? ''),
            'search_intent' => filled($validated['searchIntent']) ? trim($validated['searchIntent']) : null,
            'status' => $validated['status'],
            'outline' => $this->nestedList($validated['outlineText'] ?? ''),
            'headings' => $this->stringList($validated['headingsText'] ?? ''),
            'faq_suggestions' => $this->nestedList($validated['faqSuggestionsText'] ?? ''),
            'internal_link_suggestions' => $this->nestedList($validated['internalLinkSuggestionsText'] ?? ''),
            'image_suggestions' => $this->nestedList($validated['imageSuggestionsText'] ?? ''),
        ], fn (mixed $value): bool => $value !== null);
    }

    protected function stringList(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    protected function nestedList(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->map(function (string $line): array {
                $decoded = json_decode($line, true);

                if (is_array($decoded)) {
                    return array_values(array_map(
                        fn (mixed $item): string => is_string($item)
                            ? $item
                            : (json_encode($item, JSON_UNESCAPED_SLASHES) ?: (string) $item),
                        $decoded
                    ));
                }

                return [$line];
            })
            ->values()
            ->all();
    }

    protected function formatNestedLines(array $items): string
    {
        return collect($items)
            ->map(function (mixed $item): string {
                if (is_array($item)) {
                    return json_encode(array_values($item), JSON_UNESCAPED_SLASHES) ?: '[]';
                }

                return json_encode([$item], JSON_UNESCAPED_SLASHES) ?: '[]';
            })
            ->implode("\n");
    }

    protected function normalizeApiErrors(array $errors): array
    {
        $mapped = [];

        foreach ($errors as $field => $messages) {
            $property = match ($field) {
                'meta_title' => 'metaTitle',
                'meta_description' => 'metaDescription',
                'primary_keyword' => 'primaryKeyword',
                'secondary_keywords' => 'secondaryKeywords',
                'search_intent' => 'searchIntent',
                'faq_suggestions' => 'faqSuggestionsText',
                'internal_link_suggestions' => 'internalLinkSuggestionsText',
                'image_suggestions' => 'imageSuggestionsText',
                'draftCategoryId', 'category_id' => 'draftCategoryId',
                'draftTemplateId', 'template_id' => 'draftTemplateId',
                'draftVisibility', 'visibility' => 'draftVisibility',
                'draftPromptTemplateKey', 'prompt_template_key' => 'draftPromptTemplateKey',
                default => $field,
            };

            $mapped[$property] = $messages;
        }

        return $mapped;
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
