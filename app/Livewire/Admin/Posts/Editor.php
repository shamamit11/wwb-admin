<?php

namespace App\Livewire\Admin\Posts;

use App\Data\Posts\PostBlockData;
use App\Data\Posts\PostEditorData;
use App\Services\WideWebBlogApi\Clients\CategoryClient;
use App\Services\WideWebBlogApi\Clients\MediaClient;
use App\Services\WideWebBlogApi\Clients\PostClient;
use App\Services\WideWebBlogApi\Clients\TagClient;
use App\Services\WideWebBlogApi\Clients\TemplateClient;
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

class Editor extends Component
{
    private const POST_STATUSES = [
        'draft',
        'scheduled',
        'published',
        'unpublished',
        'archived',
    ];

    private const POST_VISIBILITIES = [
        'public',
        'private',
        'internal',
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

    public ?int $editingPostId = null;

    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $categoryId = '';

    public string $templateId = '';

    public string $featuredMediaId = '';

    public string $status = 'draft';

    public string $visibility = 'public';

    public string $publishedAt = '';

    public string $scheduledFor = '';

    public ?int $contentVersion = null;

    public ?int $readingTimeMinutes = null;

    public ?int $wordCount = null;

    public bool $isFeatured = false;

    public string $metaJson = '';

    public array $tagIds = [];

    public array $blocks = [];

    public ?string $canonicalUrl = null;

    public array $categoryOptions = [];

    public array $tagOptions = [];

    public array $templateOptions = [];

    public array $mediaOptions = [];

    public ?string $pageError = null;

    public ?string $formError = null;

    public function mount(
        AdminSessionManager $session,
        CategoryClient $categories,
        TagClient $tags,
        TemplateClient $templates,
        MediaClient $media,
        PostClient $posts,
        mixed $post = null,
    ): mixed {
        $this->fillFromEditorData(PostEditorData::blank());

        $lookupResult = $this->loadLookups($session, $categories, $tags, $templates, $media);

        if ($lookupResult !== null) {
            return $lookupResult;
        }

        if (is_numeric($post)) {
            return $this->loadPost((int) $post, $posts, $session);
        }

        return null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:190'],
            'excerpt' => ['nullable', 'string'],
            'categoryId' => ['required', 'integer'],
            'templateId' => ['nullable', 'integer'],
            'featuredMediaId' => ['nullable', 'integer'],
            'status' => ['required', Rule::in(self::POST_STATUSES)],
            'visibility' => ['required', Rule::in(self::POST_VISIBILITIES)],
            'publishedAt' => ['nullable', 'date'],
            'scheduledFor' => [
                Rule::requiredIf($this->status === 'scheduled'),
                'nullable',
                'date',
                'after:now',
            ],
            'contentVersion' => ['nullable', 'integer', 'min:1'],
            'readingTimeMinutes' => ['nullable', 'integer', 'min:0'],
            'wordCount' => ['nullable', 'integer', 'min:0'],
            'isFeatured' => ['boolean'],
            'metaJson' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                $this->validateJsonStringArrayPayload($attribute, $value, $fail);
            }],
            'tagIds' => ['array'],
            'tagIds.*' => ['integer'],
            'blocks' => ['required', 'array', 'min:1'],
            'blocks.*.blockType' => ['required', Rule::in(self::BLOCK_TYPES)],
            'blocks.*.contentText' => ['required', 'string'],
            'blocks.*.sourceTemplateBlockId' => ['nullable', 'integer'],
        ];
    }

    public function updated(string $property): void
    {
        if (
            in_array($property, [
                'title', 'slug', 'excerpt', 'categoryId', 'templateId', 'featuredMediaId',
                'status', 'visibility', 'publishedAt', 'scheduledFor', 'contentVersion',
                'readingTimeMinutes', 'wordCount', 'isFeatured', 'metaJson',
            ], true)
            || str_starts_with($property, 'tagIds')
            || str_starts_with($property, 'blocks.')
        ) {
            $this->validateOnly($property);
        }
    }

    public function addBlock(): void
    {
        $this->blocks[] = PostBlockData::blank(count($this->blocks) + 1)->toEditorState();
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
            $this->blocks = [PostBlockData::blank(1)->toEditorState()];
        }

        $this->syncBlockOrder();
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

        [$this->blocks[$index], $this->blocks[$index + 1]] = [$this->blocks[$index + 1], $this->blocks[$index]];
        $this->syncBlockOrder();
    }

    public function save(PostClient $posts, AdminSessionManager $session): mixed
    {
        $validated = $this->validate();
        $payload = $this->postPayload($validated);
        $this->formError = null;

        try {
            if ($this->editingPostId) {
                $response = $posts->update($this->token($session), $session->tokenType(), $this->editingPostId, $payload);
                $this->fillFromEditorData(PostEditorData::fromApi(Arr::get($response, 'data', [])));
                session()->flash('status', 'Post updated.');

                return null;
            }

            $response = $posts->store($this->token($session), $session->tokenType(), $payload);
            $createdPostId = Arr::get($response, 'data.id');

            session()->flash('status', 'Post created.');

            if (is_int($createdPostId)) {
                return $this->redirectRoute('posts.edit', ['post' => $createdPostId], navigate: true);
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
            $this->formError = $exception->getMessage() ?: 'Post changes could not be saved.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.posts.editor', [
            'postStatuses' => self::POST_STATUSES,
            'postVisibilities' => self::POST_VISIBILITIES,
            'blockTypes' => self::BLOCK_TYPES,
        ])->layout('layouts.admin', [
            'title' => $this->editingPostId ? 'Edit Post' : 'Create Post',
        ]);
    }

    protected function loadLookups(
        AdminSessionManager $session,
        CategoryClient $categories,
        TagClient $tags,
        TemplateClient $templates,
        MediaClient $media,
    ): mixed {
        $this->pageError = null;

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

            $this->tagOptions = collect(Arr::get($tags->index($token, $tokenType), 'data', []))
                ->map(fn (array $tag): array => [
                    'id' => (int) Arr::get($tag, 'id'),
                    'name' => (string) Arr::get($tag, 'name', 'Tag'),
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

            $this->mediaOptions = collect(Arr::get($media->index($token, $tokenType), 'data', []))
                ->map(fn (array $asset): array => [
                    'id' => (int) Arr::get($asset, 'id'),
                    'name' => (string) (Arr::get($asset, 'original_filename')
                        ?? Arr::get($asset, 'filename')
                        ?? 'Media asset'),
                    'alt_text' => (string) (Arr::get($asset, 'alt_text') ?? ''),
                    'url' => Arr::get($asset, 'url'),
                ])
                ->values()
                ->all();

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'Editor metadata could not be loaded.';
            $this->categoryOptions = [];
            $this->tagOptions = [];
            $this->templateOptions = [];
            $this->mediaOptions = [];

            return null;
        }
    }

    protected function loadPost(int $postId, PostClient $posts, AdminSessionManager $session): mixed
    {
        try {
            $response = $posts->show($this->token($session), $session->tokenType(), $postId);
            $this->fillFromEditorData(PostEditorData::fromApi(Arr::get($response, 'data', [])));

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'Post details could not be loaded.';

            return null;
        }
    }

    protected function fillFromEditorData(PostEditorData $data): void
    {
        $this->resetValidation();
        $this->editingPostId = $data->id;
        $this->title = $data->title;
        $this->slug = $data->slug;
        $this->excerpt = $data->excerpt;
        $this->categoryId = $data->categoryId;
        $this->templateId = $data->templateId;
        $this->featuredMediaId = $data->featuredMediaId;
        $this->status = $data->status;
        $this->visibility = $data->visibility;
        $this->publishedAt = $data->publishedAt;
        $this->scheduledFor = $data->scheduledFor;
        $this->contentVersion = $data->contentVersion;
        $this->readingTimeMinutes = $data->readingTimeMinutes;
        $this->wordCount = $data->wordCount;
        $this->isFeatured = $data->isFeatured;
        $this->metaJson = $data->metaJson;
        $this->tagIds = $data->tagIds;
        $this->blocks = $data->blocks;
        $this->canonicalUrl = $data->canonicalUrl;
    }

    protected function postPayload(array $validated): array
    {
        return [
            'title' => trim($validated['title']),
            'slug' => filled($validated['slug']) ? trim($validated['slug']) : null,
            'excerpt' => filled($validated['excerpt']) ? trim($validated['excerpt']) : null,
            'category_id' => (int) $validated['categoryId'],
            'template_id' => filled($validated['templateId']) ? (int) $validated['templateId'] : null,
            'featured_media_id' => filled($validated['featuredMediaId']) ? (int) $validated['featuredMediaId'] : null,
            'status' => $validated['status'],
            'visibility' => $validated['visibility'],
            'published_at' => filled($validated['publishedAt']) ? Carbon::parse($validated['publishedAt'])->toISOString() : null,
            'scheduled_for' => filled($validated['scheduledFor']) ? Carbon::parse($validated['scheduledFor'])->toISOString() : null,
            'content_version' => $validated['contentVersion'],
            'reading_time_minutes' => $validated['readingTimeMinutes'],
            'word_count' => $validated['wordCount'],
            'is_featured' => (bool) $validated['isFeatured'],
            'meta' => $this->metaPayload(),
            'tag_ids' => collect($validated['tagIds'])
                ->filter(fn (mixed $id): bool => $id !== null && $id !== '')
                ->map(fn (mixed $id): int => (int) $id)
                ->values()
                ->all(),
            'blocks' => collect($this->blocks)
                ->values()
                ->map(fn (array $block, int $index): array => PostBlockData::payloadFromEditorState($block, $index + 1))
                ->all(),
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

    protected function validateJsonStringArrayPayload(string $attribute, mixed $value, \Closure $fail): void
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
                $fail('Each meta item must be a string.');

                return;
            }
        }
    }

    protected function normalizeApiErrors(array $errors): array
    {
        $mapped = [];

        foreach ($errors as $field => $messages) {
            $property = preg_replace_callback('/_([a-z])/', fn (array $matches): string => strtoupper($matches[1]), $field) ?? $field;
            $property = str_replace('.blockType', '.blockType', $property);
            $property = str_replace('.sourceTemplateBlockId', '.sourceTemplateBlockId', $property);
            $property = str_replace('.content', '.contentText', $property);

            $property = match ($property) {
                'categoryId' => 'categoryId',
                'templateId' => 'templateId',
                'featuredMediaId' => 'featuredMediaId',
                'publishedAt' => 'publishedAt',
                'scheduledFor' => 'scheduledFor',
                'contentVersion' => 'contentVersion',
                'readingTimeMinutes' => 'readingTimeMinutes',
                'wordCount' => 'wordCount',
                'isFeatured' => 'isFeatured',
                'tagIds' => 'tagIds',
                default => $property,
            };

            $mapped[$property] = $messages;
        }

        return $mapped;
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
