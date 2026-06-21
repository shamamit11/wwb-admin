<?php

namespace App\Livewire\Admin\Posts;

use App\Services\WideWebBlogApi\Clients\CategoryClient;
use App\Services\WideWebBlogApi\Clients\MediaClient;
use App\Services\WideWebBlogApi\Clients\PostClient;
use App\Services\WideWebBlogApi\Clients\SeoClient;
use App\Services\WideWebBlogApi\Clients\TagClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiValidationException;
use App\Support\Auth\AdminSessionManager;
use App\Support\Media\MediaUrl;
use App\Support\Seo\SeoInsightsPresenter;
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

    public ?int $editingPostId = null;

    public bool $aiReviewMode = false;

    public bool $isAiGenerated = false;

    public ?int $sourceContentTopicId = null;

    public ?int $generatedByAiJobId = null;

    public ?string $generatedBy = null;

    public string $title = '';

    public string $slug = '';

    public string $shortDescription = '';

    public string $description = '';

    public string $fullArticleHtml = '';

    public string $fullArticleDelta = '';

    public string $categoryId = '';

    public string $featuredMediaId = '';

    public string $status = 'draft';

    public string $visibility = 'public';

    public string $publishedAt = '';

    public array $tagIds = [];

    public array $faq = [];

    public string $metaJson = '';

    public string $metaTitle = '';

    public string $metaDescription = '';

    public string $canonicalUrl = '';

    public bool $robotsIndex = true;

    public bool $robotsFollow = true;

    public string $ogTitle = '';

    public string $ogDescription = '';

    public string $ogImageMediaId = '';

    public string $focusKeyword = '';

    public array $categoryOptions = [];

    public array $tagOptions = [];

    public array $mediaOptions = [];

    public ?string $pageError = null;

    public ?string $formError = null;

    public ?string $seoLoadError = null;

    public ?string $seoFormError = null;

    public array $seoScore = [];

    public array $seoSchema = [];

    public ?string $seoScoreLoadError = null;

    public ?string $seoSchemaLoadError = null;

    public bool $actionDialogOpen = false;

    public string $actionMode = 'publish';

    public ?string $actionError = null;

    public function mount(
        AdminSessionManager $session,
        CategoryClient $categories,
        TagClient $tags,
        MediaClient $media,
        PostClient $posts,
        SeoClient $seo,
        mixed $post = null,
    ): mixed {
        $this->aiReviewMode = request()->routeIs('draft-review.*');
        $this->faq = [$this->blankFaqItem()];

        $lookupResult = $this->loadLookups($session, $categories, $tags, $media);

        if ($lookupResult !== null) {
            return $lookupResult;
        }

        if (is_numeric($post)) {
            $postId = (int) $post;
            $loadPostResult = $this->loadPost($postId, $posts, $session);

            if ($loadPostResult !== null || $this->pageError !== null) {
                return $loadPostResult;
            }

            $seoMetadataResult = $this->loadSeoMetadata($this->seoableType(), $postId, $seo, $session);

            if ($seoMetadataResult !== null) {
                return $seoMetadataResult;
            }

            return $this->loadSeoInsights($this->seoableType(), $postId, $seo, $session);
        }

        return null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:190'],
            'shortDescription' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'fullArticleHtml' => ['required', 'string'],
            'fullArticleDelta' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                $this->validateJsonPayload($attribute, $value, $fail);
            }],
            'categoryId' => ['required', 'integer'],
            'featuredMediaId' => ['nullable', 'integer'],
            'status' => ['required', Rule::in(self::POST_STATUSES)],
            'visibility' => ['required', Rule::in(self::POST_VISIBILITIES)],
            'publishedAt' => ['nullable', 'date'],
            'tagIds' => ['nullable', 'array'],
            'tagIds.*' => ['integer'],
            'faq' => ['nullable', 'array'],
            'faq.*.question' => ['nullable', 'string'],
            'faq.*.answer' => ['nullable', 'string'],
            'metaJson' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                $this->validateMetaJsonPayload($attribute, $value, $fail);
            }],
        ];
    }

    public function updated(string $property): void
    {
        if (
            in_array($property, [
                'title', 'slug', 'shortDescription', 'description', 'fullArticleHtml', 'fullArticleDelta',
                'categoryId', 'featuredMediaId', 'status', 'visibility', 'publishedAt', 'metaJson',
            ], true)
            || str_starts_with($property, 'faq.')
            || str_starts_with($property, 'tagIds.')
        ) {
            $this->validateOnly($property);
        }
    }

    public function addFaqItem(): void
    {
        $this->faq[] = $this->blankFaqItem();
    }

    public function removeFaqItem(int $index): void
    {
        if (! array_key_exists($index, $this->faq)) {
            return;
        }

        unset($this->faq[$index]);
        $this->faq = array_values($this->faq);

        if ($this->faq === []) {
            $this->faq = [$this->blankFaqItem()];
        }
    }

    public function saveSeo(SeoClient $seo, AdminSessionManager $session): mixed
    {
        if (! $this->editingPostId) {
            return null;
        }

        $validated = $this->validate($this->seoRules());
        $this->seoFormError = null;

        try {
            $response = $seo->update(
                $this->token($session),
                $session->tokenType(),
                $this->seoableType(),
                $this->editingPostId,
                $this->seoPayload($validated),
            );

            $this->fillFromSeoMetadata(Arr::get($response, 'data', []));
            session()->flash('status', 'SEO metadata updated.');

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->seoFormError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->seoFormError = $exception->getMessage() ?: 'SEO metadata could not be updated.';

            return null;
        }
    }

    public function save(PostClient $posts, AdminSessionManager $session): mixed
    {
        $this->faq = $this->normalizedFaq();
        $validated = $this->validate();
        $this->formError = null;

        try {
            $response = $this->editingPostId
                ? $posts->update($this->token($session), $session->tokenType(), $this->editingPostId, $this->postPayload($validated))
                : $posts->store($this->token($session), $session->tokenType(), $this->postPayload($validated));

            $this->fillFromPost(Arr::get($response, 'data', []));
            session()->flash('status', $this->editingPostId ? 'Post updated.' : 'Post created.');

            if ($this->editingPostId) {
                return null;
            }

            return $this->redirectRoute(
                $this->aiReviewMode ? 'draft-review.show' : 'posts.edit',
                ['post' => $this->editingPostId],
                navigate: true,
            );
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

    public function openActionDialog(string $mode): void
    {
        if (! $this->editingPostId || ! in_array($mode, ['publish', 'unpublish', 'delete'], true)) {
            return;
        }

        $this->actionDialogOpen = true;
        $this->actionMode = $mode;
        $this->actionError = null;
    }

    public function closeActionDialog(): void
    {
        $this->actionDialogOpen = false;
        $this->actionMode = 'publish';
        $this->actionError = null;
    }

    public function executeAction(PostClient $posts, AdminSessionManager $session): mixed
    {
        if (! $this->editingPostId) {
            return null;
        }

        $this->actionError = null;

        try {
            $response = match ($this->actionMode) {
                'publish' => $posts->publish($this->token($session), $session->tokenType(), $this->editingPostId),
                'unpublish' => $posts->unpublish($this->token($session), $session->tokenType(), $this->editingPostId),
                'delete' => tap(null, fn () => $posts->delete($this->token($session), $session->tokenType(), $this->editingPostId)),
                default => null,
            };

            if ($this->actionMode === 'delete') {
                session()->flash('status', 'Post deleted.');

                return $this->redirectRoute($this->aiReviewMode ? 'draft-review.index' : 'posts.index', navigate: true);
            }

            if (is_array($response)) {
                $this->fillFromPost(Arr::get($response, 'data', []));
            }

            session()->flash('status', match ($this->actionMode) {
                'publish' => 'Post published.',
                'unpublish' => 'Post unpublished.',
                default => 'Post updated.',
            });

            $this->closeActionDialog();

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->actionError = $exception->getMessage() ?: 'The post action could not be completed.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.posts.editor', [
            'selectedFeaturedMedia' => $this->selectedMedia($this->featuredMediaId),
            'selectedOgImageMedia' => $this->selectedMedia($this->ogImageMediaId),
            'seoScoreValue' => $this->seoPresenter()->scoreValue($this->seoScore),
            'seoScoreGrade' => $this->seoPresenter()->scoreGrade($this->seoScore),
            'seoSubscores' => $this->seoPresenter()->scoreSubscores($this->seoScore),
            'seoRecommendations' => $this->seoPresenter()->recommendations($this->seoScore),
            'schemaSummary' => $this->seoPresenter()->schemaSummary($this->seoSchema),
            'prettySchema' => $this->seoPresenter()->prettySchema($this->seoSchema),
            'articleEditorInitialHtml' => $this->articleEditorInitialHtml(),
            'actionConfig' => $this->actionConfig(),
        ])->layout('layouts.admin', [
            'title' => $this->aiReviewMode ? 'Review Draft' : ($this->editingPostId ? 'Edit Post' : 'Create Post'),
        ]);
    }

    protected function loadLookups(
        AdminSessionManager $session,
        CategoryClient $categories,
        TagClient $tags,
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

            $this->mediaOptions = collect(Arr::get($media->index($token, $tokenType), 'data', []))
                ->map(fn (array $asset): array => [
                    'id' => (int) Arr::get($asset, 'id'),
                    'name' => (string) (Arr::get($asset, 'original_filename') ?? Arr::get($asset, 'filename') ?? 'Media asset'),
                    'original_filename' => (string) (Arr::get($asset, 'original_filename') ?? Arr::get($asset, 'filename') ?? 'Media asset'),
                    'mime_type' => (string) Arr::get($asset, 'mime_type', ''),
                    'alt_text' => (string) (Arr::get($asset, 'alt_text') ?? ''),
                    'caption' => (string) (Arr::get($asset, 'caption') ?? ''),
                    'url' => $this->mediaUrl()->resolve(Arr::get($asset, 'url')),
                    'is_image' => str_starts_with((string) Arr::get($asset, 'mime_type', ''), 'image/'),
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
            $this->mediaOptions = [];

            return null;
        }
    }

    protected function loadPost(int $postId, PostClient $posts, AdminSessionManager $session): mixed
    {
        try {
            $response = $posts->show($this->token($session), $session->tokenType(), $postId);
            $this->fillFromPost(Arr::get($response, 'data', []));

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

    protected function loadSeoMetadata(string $seoableType, int $seoableId, SeoClient $seo, AdminSessionManager $session): mixed
    {
        $this->seoLoadError = null;
        $this->seoFormError = null;

        try {
            $response = $seo->show($this->token($session), $session->tokenType(), $seoableType, $seoableId);
            $this->fillFromSeoMetadata(Arr::get($response, 'data', []));

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->seoLoadError = $exception->getMessage() ?: 'SEO metadata could not be loaded.';

            return null;
        }
    }

    protected function loadSeoInsights(string $seoableType, int $seoableId, SeoClient $seo, AdminSessionManager $session): mixed
    {
        $this->seoScoreLoadError = null;
        $this->seoSchemaLoadError = null;
        $this->seoScore = [];
        $this->seoSchema = [];

        try {
            $scoreResponse = $seo->score($this->token($session), $session->tokenType(), $seoableType, $seoableId);
            $this->seoScore = Arr::get($scoreResponse, 'data', []);
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->seoScoreLoadError = $exception->getMessage() ?: 'SEO score could not be loaded.';
        }

        try {
            $schemaResponse = $seo->schema($this->token($session), $session->tokenType(), $seoableType, $seoableId);
            $this->seoSchema = Arr::get($schemaResponse, 'data', []);
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->seoSchemaLoadError = $exception->getMessage() ?: 'Schema output could not be loaded.';
        }

        return null;
    }

    protected function fillFromPost(array $post): void
    {
        $this->resetValidation();
        $this->editingPostId = Arr::get($post, 'id');
        $this->title = (string) Arr::get($post, 'title', '');
        $this->slug = (string) Arr::get($post, 'slug', '');
        $this->shortDescription = (string) Arr::get($post, 'short_description', '');
        $this->description = (string) Arr::get($post, 'description', '');
        $this->fullArticleHtml = (string) Arr::get($post, 'full_article_html', '');
        $this->fullArticleDelta = $this->prettyJson(Arr::get($post, 'full_article_delta'));
        $this->categoryId = Arr::get($post, 'category.id') ? (string) Arr::get($post, 'category.id') : '';
        $this->featuredMediaId = Arr::get($post, 'featured_media.id') ? (string) Arr::get($post, 'featured_media.id') : '';
        $this->status = (string) Arr::get($post, 'status', 'draft');
        $this->visibility = (string) Arr::get($post, 'visibility', 'public');
        $this->publishedAt = $this->formatDateInput(Arr::get($post, 'published_at'));
        $this->tagIds = collect(Arr::get($post, 'tags', []))
            ->map(fn (array $tag): string => (string) Arr::get($tag, 'id'))
            ->values()
            ->all();
        $this->faq = collect(Arr::get($post, 'faq', []))
            ->map(fn (array $item): array => [
                'question' => (string) Arr::get($item, 'question', ''),
                'answer' => (string) Arr::get($item, 'answer', ''),
            ])
            ->values()
            ->all();
        $this->faq = $this->faq !== [] ? $this->faq : [$this->blankFaqItem()];
        $this->metaJson = $this->prettyJson(Arr::get($post, 'meta'));
        $this->isAiGenerated = filter_var(Arr::get($post, 'is_ai_generated', false), FILTER_VALIDATE_BOOL);
        $this->sourceContentTopicId = Arr::get($post, 'source_content_topic_id');
        $this->generatedByAiJobId = Arr::get($post, 'generated_by_ai_job_id');
        $this->generatedBy = Arr::get($post, 'generated_by');
    }

    protected function fillFromSeoMetadata(array $seo): void
    {
        $this->metaTitle = (string) Arr::get($seo, 'meta_title', '');
        $this->metaDescription = (string) Arr::get($seo, 'meta_description', '');
        $this->canonicalUrl = (string) Arr::get($seo, 'canonical_url', '');
        $this->robotsIndex = (bool) Arr::get($seo, 'robots_index', true);
        $this->robotsFollow = (bool) Arr::get($seo, 'robots_follow', true);
        $this->ogTitle = (string) Arr::get($seo, 'og_title', '');
        $this->ogDescription = (string) Arr::get($seo, 'og_description', '');
        $this->ogImageMediaId = Arr::get($seo, 'og_image_media.id')
            ? (string) Arr::get($seo, 'og_image_media.id')
            : '';
        $this->focusKeyword = (string) Arr::get($seo, 'focus_keyword', '');
    }

    protected function postPayload(array $validated): array
    {
        return [
            'title' => trim($validated['title']),
            'slug' => filled($validated['slug']) ? trim($validated['slug']) : null,
            'short_description' => filled($validated['shortDescription']) ? trim($validated['shortDescription']) : null,
            'description' => filled($validated['description']) ? trim($validated['description']) : null,
            'full_article_html' => trim($validated['fullArticleHtml']),
            'full_article_delta' => $this->decodedJsonPayload($this->fullArticleDelta),
            'category_id' => (int) $validated['categoryId'],
            'featured_media_id' => filled($validated['featuredMediaId']) ? (int) $validated['featuredMediaId'] : null,
            'status' => $validated['status'],
            'visibility' => $validated['visibility'],
            'published_at' => filled($validated['publishedAt']) ? Carbon::parse($validated['publishedAt'])->toISOString() : null,
            'meta' => $this->metaPayload(),
            'tag_ids' => collect($validated['tagIds'] ?? [])
                ->filter(fn (mixed $id): bool => $id !== null && $id !== '')
                ->map(fn (mixed $id): int => (int) $id)
                ->values()
                ->all(),
            'faq' => $this->normalizedFaq(),
        ];
    }

    protected function metaPayload(): ?array
    {
        if (trim($this->metaJson) === '') {
            return null;
        }

        $decoded = json_decode($this->metaJson, true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function normalizedFaq(): array
    {
        return collect($this->faq)
            ->map(fn (array $item): array => [
                'question' => trim((string) ($item['question'] ?? '')),
                'answer' => trim((string) ($item['answer'] ?? '')),
            ])
            ->filter(fn (array $item): bool => $item['question'] !== '' || $item['answer'] !== '')
            ->values()
            ->all();
    }

    protected function blankFaqItem(): array
    {
        return [
            'question' => '',
            'answer' => '',
        ];
    }

    protected function articleEditorInitialHtml(): string
    {
        return $this->fullArticleHtml;
    }

    protected function selectedMedia(string $mediaId): ?array
    {
        if ($mediaId === '') {
            return null;
        }

        $match = collect($this->mediaOptions)->firstWhere('id', (int) $mediaId);

        return is_array($match) ? $match : null;
    }

    protected function formatDateInput(mixed $value): string
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

    protected function prettyJson(mixed $value): string
    {
        if (! is_array($value) || $value === []) {
            return '';
        }

        $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return is_string($encoded) ? $encoded : '';
    }

    protected function seoRules(): array
    {
        return [
            'metaTitle' => ['nullable', 'string', 'max:255'],
            'metaDescription' => ['nullable', 'string', 'max:320'],
            'canonicalUrl' => ['nullable', 'string', 'max:500', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || trim($value) === '') {
                    return;
                }

                if (filter_var($value, FILTER_VALIDATE_URL) === false) {
                    $fail('The canonical url field must be a valid URL.');
                }
            }],
            'robotsIndex' => ['boolean'],
            'robotsFollow' => ['boolean'],
            'ogTitle' => ['nullable', 'string', 'max:255'],
            'ogDescription' => ['nullable', 'string', 'max:320'],
            'ogImageMediaId' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === null || $value === '') {
                    return;
                }

                if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $fail('The open graph image field must be an integer.');
                }
            }],
            'focusKeyword' => ['nullable', 'string', 'max:190'],
        ];
    }

    protected function seoPayload(array $validated): array
    {
        return [
            'meta_title' => filled($validated['metaTitle']) ? trim($validated['metaTitle']) : null,
            'meta_description' => filled($validated['metaDescription']) ? trim($validated['metaDescription']) : null,
            'canonical_url' => filled($validated['canonicalUrl']) ? trim($validated['canonicalUrl']) : null,
            'robots_index' => (bool) $validated['robotsIndex'],
            'robots_follow' => (bool) $validated['robotsFollow'],
            'og_title' => filled($validated['ogTitle']) ? trim($validated['ogTitle']) : null,
            'og_description' => filled($validated['ogDescription']) ? trim($validated['ogDescription']) : null,
            'og_image_media_id' => filled($validated['ogImageMediaId']) ? (int) $validated['ogImageMediaId'] : null,
            'focus_keyword' => filled($validated['focusKeyword']) ? trim($validated['focusKeyword']) : null,
        ];
    }

    protected function validateMetaJsonPayload(string $attribute, mixed $value, \Closure $fail): void
    {
        $this->validateJsonPayload($attribute, $value, $fail, true);
    }

    protected function validateJsonPayload(string $attribute, mixed $value, \Closure $fail, bool $mustDecodeToArray = false): void
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

        if ($mustDecodeToArray && ! is_array($decoded)) {
            $fail('The meta field must decode to a JSON object or array.');
        }
    }

    protected function decodedJsonPayload(string $value): mixed
    {
        if (trim($value) === '') {
            return null;
        }

        try {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }
    }

    protected function normalizeApiErrors(array $errors): array
    {
        return collect($errors)
            ->mapWithKeys(fn (array $messages, string $key): array => [
                match ($key) {
                    'short_description' => 'shortDescription',
                    'full_article_html' => 'fullArticleHtml',
                    'full_article_delta' => 'fullArticleDelta',
                    'category_id' => 'categoryId',
                    'featured_media_id' => 'featuredMediaId',
                    'tag_ids' => 'tagIds',
                    default => str($key)->replace('.', '.')->camel()->toString(),
                } => $messages,
            ])
            ->all();
    }

    protected function actionConfig(): array
    {
        return match ($this->actionMode) {
            'unpublish' => [
                'title' => 'Unpublish Post',
                'description' => 'Take this article out of the live state without deleting it.',
                'confirm' => 'Unpublish',
                'destructive' => false,
            ],
            'delete' => [
                'title' => 'Delete Post',
                'description' => 'Delete this article only when it is no longer needed.',
                'confirm' => 'Delete',
                'destructive' => true,
            ],
            default => [
                'title' => 'Publish Post',
                'description' => 'Publish this reviewed article manually.',
                'confirm' => 'Publish',
                'destructive' => false,
            ],
        };
    }

    protected function seoableType(): string
    {
        return 'post';
    }

    protected function seoPresenter(): SeoInsightsPresenter
    {
        return app(SeoInsightsPresenter::class);
    }

    protected function mediaUrl(): MediaUrl
    {
        return app(MediaUrl::class);
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
