<?php

namespace App\Livewire\Admin\Posts;

use App\Data\Posts\PostBlockData;
use App\Data\Posts\PostEditorData;
use App\Services\WideWebBlogApi\Clients\CategoryClient;
use App\Services\WideWebBlogApi\Clients\MediaClient;
use App\Services\WideWebBlogApi\Clients\PostClient;
use App\Services\WideWebBlogApi\Clients\SeoClient;
use App\Services\WideWebBlogApi\Clients\TagClient;
use App\Services\WideWebBlogApi\Clients\TemplateClient;
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

    public bool $aiReviewMode = false;

    public bool $isAiGenerated = false;

    public ?int $sourceContentBriefId = null;

    public ?int $sourceContentTopicId = null;

    public ?int $generatedByAiJobId = null;

    public ?string $generatedBy = null;

    public array $faqSuggestions = [];

    public array $suggestedTags = [];

    public array $imagePlacementNotes = [];

    public array $altTextSuggestions = [];

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

    public array $templateOptions = [];

    public array $mediaOptions = [];

    public ?string $pageError = null;

    public ?string $formError = null;

    public ?string $seoLoadError = null;

    public ?string $seoFormError = null;

    public array $seoScore = [];

    public array $seoSchema = [];

    public ?string $seoScoreLoadError = null;

    public ?string $seoSchemaLoadError = null;

    public bool $mediaPickerOpen = false;

    public string $mediaSearch = '';

    public bool $actionDialogOpen = false;

    public string $actionMode = 'publish';

    public ?int $actionPostId = null;

    public string $actionPostTitle = '';

    public ?string $actionError = null;

    public bool $rewriteDialogOpen = false;

    public string $rewriteScope = 'full_draft';

    public array $rewriteTargetBlockIds = [];

    public string $rewriteInstructions = '';

    public string $rewritePromptTemplateKey = '';

    public ?string $rewriteError = null;

    public function mount(
        AdminSessionManager $session,
        CategoryClient $categories,
        TagClient $tags,
        TemplateClient $templates,
        MediaClient $media,
        PostClient $posts,
        SeoClient $seo,
        mixed $post = null,
    ): mixed {
        $this->aiReviewMode = request()->routeIs('draft-review.*');
        $this->fillFromEditorData(PostEditorData::blank());

        $lookupResult = $this->loadLookups($session, $categories, $tags, $templates, $media);

        if ($lookupResult !== null) {
            return $lookupResult;
        }

        if (is_numeric($post)) {
            $loadPostResult = $this->loadPost((int) $post, $posts, $session);

            if ($loadPostResult !== null || $this->pageError !== null) {
                return $loadPostResult;
            }

            $seoMetadataResult = $this->loadSeoMetadata($this->seoableType(), (int) $post, $seo, $session);

            if ($seoMetadataResult !== null) {
                return $seoMetadataResult;
            }

            return $this->loadSeoInsights($this->seoableType(), (int) $post, $seo, $session);
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
        if (in_array($property, [
            'metaTitle',
            'metaDescription',
            'canonicalUrl',
            'robotsIndex',
            'robotsFollow',
            'ogTitle',
            'ogDescription',
            'ogImageMediaId',
            'focusKeyword',
        ], true)) {
            $this->validateOnly($property, $this->seoRules());

            return;
        }

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

            return;
        }

        if (
            in_array($property, ['rewriteScope', 'rewriteInstructions', 'rewritePromptTemplateKey'], true)
            || str_starts_with($property, 'rewriteTargetBlockIds')
        ) {
            $this->validateOnly($property, $this->rewriteRules());
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

    public function insertBlockSnippet(int $index, string $snippet): void
    {
        if (! isset($this->blocks[$index])) {
            return;
        }

        $blockType = (string) ($this->blocks[$index]['blockType'] ?? 'paragraph');
        $snippets = $this->toolbarSnippets($blockType);

        if (! array_key_exists($snippet, $snippets)) {
            return;
        }

        $current = rtrim((string) ($this->blocks[$index]['contentText'] ?? ''));
        $this->blocks[$index]['contentText'] = $current === ''
            ? $snippets[$snippet]
            : $current."\n".$snippets[$snippet];

        $this->validateOnly('blocks.'.$index.'.contentText');
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

    public function openMediaPicker(): void
    {
        $this->mediaPickerOpen = true;
    }

    public function closeMediaPicker(): void
    {
        $this->mediaPickerOpen = false;
        $this->mediaSearch = '';
    }

    public function selectFeaturedMedia(int $mediaId): void
    {
        $match = collect($this->mediaOptions)
            ->contains(fn (array $asset): bool => (int) $asset['id'] === $mediaId);

        if (! $match) {
            return;
        }

        $this->featuredMediaId = (string) $mediaId;
        $this->closeMediaPicker();
        $this->validateOnly('featuredMediaId');
    }

    public function clearFeaturedMedia(): void
    {
        $this->featuredMediaId = '';
        $this->validateOnly('featuredMediaId');
    }

    public function openActionDialog(string $mode): void
    {
        if (! $this->editingPostId || ! in_array($mode, ['publish', 'schedule', 'unpublish', 'delete'], true)) {
            return;
        }

        $this->resetValidation();
        $this->actionDialogOpen = true;
        $this->actionMode = $mode;
        $this->actionPostId = $this->editingPostId;
        $this->actionPostTitle = $this->title !== '' ? $this->title : 'this post';
        $this->actionError = null;

        if ($mode === 'schedule' && $this->scheduledFor === '') {
            $this->scheduledFor = now()->addDay()->startOfHour()->format('Y-m-d\TH:i');
        }
    }

    public function closeActionDialog(): void
    {
        $this->resetValidation();
        $this->actionDialogOpen = false;
        $this->actionMode = 'publish';
        $this->actionPostId = null;
        $this->actionPostTitle = '';
        $this->actionError = null;
    }

    public function openRewriteDialog(string $scope = 'full_draft'): void
    {
        if (! $this->canQueueRewrite()) {
            return;
        }

        $allowedScopes = ['full_draft', 'section', 'paragraph'];

        $this->resetValidation();
        $this->rewriteDialogOpen = true;
        $this->rewriteError = null;
        $this->rewriteScope = in_array($scope, $allowedScopes, true) ? $scope : 'full_draft';
        $this->rewriteTargetBlockIds = [];

        if ($this->rewriteScope === 'paragraph') {
            $firstParagraphId = collect($this->rewriteableBlockOptions())
                ->first(fn (array $block): bool => $block['block_type'] === 'paragraph')['id'] ?? null;

            if (is_int($firstParagraphId)) {
                $this->rewriteTargetBlockIds = [$firstParagraphId];
            }
        }
    }

    public function closeRewriteDialog(): void
    {
        $this->resetValidation();
        $this->rewriteDialogOpen = false;
        $this->rewriteScope = 'full_draft';
        $this->rewriteTargetBlockIds = [];
        $this->rewriteInstructions = '';
        $this->rewritePromptTemplateKey = '';
        $this->rewriteError = null;
    }

    public function queueRewrite(PostClient $posts, AdminSessionManager $session): mixed
    {
        if (! $this->editingPostId) {
            return null;
        }

        $validated = $this->validate($this->rewriteRules());
        $this->rewriteError = null;

        try {
            $response = $posts->rewrite(
                $this->token($session),
                $session->tokenType(),
                $this->editingPostId,
                $this->rewritePayload($validated),
            );

            $jobId = Arr::get($response, 'data.id');

            $this->closeRewriteDialog();
            $this->flashJobAlert('Draft rewrite job queued.', $jobId);

            if (is_int($jobId) || ctype_digit((string) $jobId)) {
                return $this->redirectRoute('ai-jobs.show', ['aiJob' => (int) $jobId], navigate: true);
            }

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->rewriteError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->rewriteError = $exception->getMessage() ?: 'Draft rewrite could not be queued.';

            return null;
        }
    }

    public function executeAction(PostClient $posts, AdminSessionManager $session): mixed
    {
        if (! $this->actionPostId) {
            return null;
        }

        $this->actionError = null;

        if ($this->actionMode === 'schedule') {
            $this->validate($this->scheduleActionRules());
        }

        try {
            $response = match ($this->actionMode) {
                'publish' => $posts->publish($this->token($session), $session->tokenType(), $this->actionPostId),
                'schedule' => $posts->schedule($this->token($session), $session->tokenType(), $this->actionPostId, [
                    'scheduled_for' => Carbon::parse($this->scheduledFor)->toISOString(),
                ]),
                'unpublish' => $posts->unpublish($this->token($session), $session->tokenType(), $this->actionPostId),
                'delete' => null,
                default => null,
            };

            if ($this->actionMode === 'delete') {
                $posts->delete($this->token($session), $session->tokenType(), $this->actionPostId);
                session()->flash('status', 'Post deleted.');

                return $this->redirectRoute($this->aiReviewMode ? 'draft-review.index' : 'posts.index', navigate: true);
            }

            if (is_array($response)) {
                $this->fillFromEditorData(PostEditorData::fromApi(Arr::get($response, 'data', [])));
            }

            session()->flash('status', $this->actionSuccessMessage());
            $this->closeActionDialog();

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->actionError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
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
            'postStatuses' => self::POST_STATUSES,
            'postVisibilities' => self::POST_VISIBILITIES,
            'blockTypes' => self::BLOCK_TYPES,
            'blockUi' => collect($this->blocks)
                ->map(fn (array $block): array => $this->blockUi($block))
                ->all(),
            'actionConfig' => $this->actionConfig(),
            'selectedFeaturedMedia' => $this->selectedFeaturedMedia(),
            'visibleMediaOptions' => $this->visibleMediaOptions(),
            'seoScoreValue' => $this->seoPresenter()->scoreValue($this->seoScore),
            'seoScoreGrade' => $this->seoPresenter()->scoreGrade($this->seoScore),
            'seoScoreSubscores' => $this->seoPresenter()->scoreSubscores($this->seoScore),
            'seoRecommendations' => $this->seoPresenter()->recommendations($this->seoScore),
            'seoSchemaSummary' => $this->seoPresenter()->schemaSummary($this->seoSchema),
            'seoSchemaJson' => $this->seoPresenter()->prettySchema($this->seoSchema),
            'aiReviewMode' => $this->aiReviewMode,
            'sourceContentBriefLink' => $this->sourceContentBriefId ? route('content-briefs.show', ['contentBrief' => $this->sourceContentBriefId]) : null,
            'sourceContentTopicLink' => $this->sourceContentTopicId ? route('topic-queue.show', ['topic' => $this->sourceContentTopicId]) : null,
            'generatedByAiJobLink' => $this->generatedByAiJobId ? route('ai-jobs.show', ['aiJob' => $this->generatedByAiJobId]) : null,
            'aiSuggestionSections' => $this->aiSuggestionSections(),
            'canQueueRewrite' => $this->canQueueRewrite(),
            'hasRewriteableBlocks' => $this->rewriteableBlockOptions() !== [],
            'rewriteableBlocks' => $this->rewriteableBlockOptions(),
            'rewriteParagraphBlocks' => $this->rewriteableParagraphBlockOptions(),
        ])->layout('layouts.admin', [
            'title' => $this->aiReviewMode ? 'Draft Review' : ($this->editingPostId ? 'Edit Post' : 'Create Post'),
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
                    'url' => $this->mediaUrl()->resolve(Arr::get($asset, 'url')),
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

    protected function mediaUrl(): MediaUrl
    {
        return app(MediaUrl::class);
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
        $this->isAiGenerated = $data->isAiGenerated;
        $this->sourceContentBriefId = $data->sourceContentBriefId;
        $this->sourceContentTopicId = $data->sourceContentTopicId;
        $this->generatedByAiJobId = $data->generatedByAiJobId;
        $this->generatedBy = $data->generatedBy;
        $this->faqSuggestions = $this->suggestionItems($data->meta['faq_suggestions'] ?? []);
        $this->suggestedTags = $this->suggestionItems($data->meta['suggested_tags'] ?? []);
        $this->imagePlacementNotes = $this->suggestionItems($data->meta['image_placement_notes'] ?? []);
        $this->altTextSuggestions = $this->suggestionItems($data->meta['alt_text_suggestions'] ?? []);
        $this->tagIds = $data->tagIds;
        $this->blocks = $data->blocks;
        $this->canonicalUrl = $data->canonicalUrl ?? '';
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

    protected function rewriteRules(): array
    {
        return [
            'rewriteScope' => ['required', Rule::in(['full_draft', 'section', 'paragraph'])],
            'rewriteTargetBlockIds' => ['array', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! in_array($this->rewriteScope, ['section', 'paragraph'], true)) {
                    return;
                }

                if (! is_array($value) || $value === []) {
                    $fail('Select at least one block to regenerate.');

                    return;
                }

                $selectedIds = collect($value)
                    ->filter(fn (mixed $id): bool => $id !== null && $id !== '')
                    ->map(fn (mixed $id): int => (int) $id)
                    ->values();

                $availableBlocks = collect($this->rewriteableBlockOptions());
                $availableIds = $availableBlocks->pluck('id')->values()->all();

                if ($selectedIds->contains(fn (int $id): bool => ! in_array($id, $availableIds, true))) {
                    $fail('Selected blocks are no longer available for rewrite.');

                    return;
                }

                if ($this->rewriteScope === 'paragraph') {
                    if ($selectedIds->count() !== 1) {
                        $fail('Select exactly one paragraph block for paragraph regeneration.');

                        return;
                    }

                    $selectedBlock = $availableBlocks->first(fn (array $block): bool => $block['id'] === $selectedIds->first());

                    if (($selectedBlock['block_type'] ?? null) !== 'paragraph') {
                        $fail('Paragraph regeneration requires a paragraph block.');
                    }

                    return;
                }

                $positions = $selectedIds
                    ->map(fn (int $id): ?int => array_search($id, $availableIds, true))
                    ->filter(fn (mixed $position): bool => is_int($position))
                    ->sort()
                    ->values();

                if ($positions->count() !== $selectedIds->count()) {
                    $fail('Selected blocks are no longer available for rewrite.');

                    return;
                }

                $expected = range($positions->first(), $positions->last());

                if ($positions->all() !== $expected) {
                    $fail('Section regeneration requires a contiguous block selection.');
                }
            }],
            'rewriteTargetBlockIds.*' => ['integer'],
            'rewriteInstructions' => ['nullable', 'string'],
            'rewritePromptTemplateKey' => ['nullable', 'string', 'max:190'],
        ];
    }

    protected function rewritePayload(array $validated): array
    {
        $targetBlockIds = collect($validated['rewriteTargetBlockIds'] ?? [])
            ->filter(fn (mixed $id): bool => $id !== null && $id !== '')
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        $payload = [
            'scope' => $validated['rewriteScope'],
            'instructions' => filled($validated['rewriteInstructions']) ? trim($validated['rewriteInstructions']) : null,
            'prompt_template_key' => filled($validated['rewritePromptTemplateKey']) ? trim($validated['rewritePromptTemplateKey']) : null,
        ];

        if (in_array($validated['rewriteScope'], ['section', 'paragraph'], true)) {
            $payload['target_block_ids'] = $targetBlockIds;
        }

        return $payload;
    }

    protected function aiSuggestionSections(): array
    {
        if (! $this->aiReviewMode || ! $this->isAiGenerated) {
            return [];
        }

        return array_values(array_filter([
            [
                'title' => 'Suggested Tags',
                'items' => $this->suggestedTags,
            ],
            [
                'title' => 'FAQ Suggestions',
                'items' => $this->faqSuggestions,
            ],
            [
                'title' => 'Image Placement Notes',
                'items' => $this->imagePlacementNotes,
            ],
            [
                'title' => 'Alt Text Suggestions',
                'items' => $this->altTextSuggestions,
            ],
        ], fn (array $section): bool => $section['items'] !== []));
    }

    protected function suggestionItems(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(function (mixed $item): ?string {
                if (is_string($item)) {
                    $trimmed = trim($item);

                    return $trimmed !== '' ? $trimmed : null;
                }

                if (! is_array($item) || $item === []) {
                    return null;
                }

                $parts = collect($item)
                    ->filter(fn (mixed $part): bool => $part !== null && $part !== '')
                    ->map(function (mixed $part, string|int $key): string {
                        $value = is_scalar($part)
                            ? (string) $part
                            : (json_encode($part, JSON_UNESCAPED_SLASHES) ?: '');

                        return (string) str((string) $key)->headline()->append(': ')->append($value);
                    })
                    ->filter(fn (string $part): bool => trim($part) !== '')
                    ->values()
                    ->all();

                return $parts !== [] ? implode(' | ', $parts) : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function canQueueRewrite(): bool
    {
        return $this->aiReviewMode
            && $this->editingPostId !== null
            && $this->status === 'draft'
            && $this->isAiGenerated
            && ($this->sourceContentBriefId !== null || $this->sourceContentTopicId !== null);
    }

    protected function rewriteableBlockOptions(): array
    {
        return collect($this->blocks)
            ->values()
            ->map(function (array $block, int $index): ?array {
                $id = $block['id'] ?? null;

                if (! is_int($id) && ! ctype_digit((string) $id)) {
                    return null;
                }

                $blockType = (string) ($block['blockType'] ?? 'paragraph');
                $sortOrder = (int) ($block['sortOrder'] ?? $index + 1);
                $content = trim((string) ($block['contentText'] ?? ''));
                $preview = str($content)->squish()->limit(96)->value();

                return [
                    'id' => (int) $id,
                    'block_type' => $blockType,
                    'sort_order' => $sortOrder,
                    'label' => 'Block '.$sortOrder.' • '.str($blockType)->headline(),
                    'preview' => $preview !== '' ? $preview : 'No content preview available.',
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function rewriteableParagraphBlockOptions(): array
    {
        return array_values(array_filter(
            $this->rewriteableBlockOptions(),
            fn (array $block): bool => $block['block_type'] === 'paragraph',
        ));
    }

    protected function flashJobAlert(string $message, mixed $jobId): void
    {
        if (is_int($jobId) || ctype_digit((string) $jobId)) {
            session()->flash('status', [
                'message' => $message,
                'link_href' => route('ai-jobs.show', ['aiJob' => (int) $jobId]),
                'link_label' => 'Open AI Job',
            ]);

            return;
        }

        session()->flash('status', $message);
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
                'rewriteScope', 'scope' => 'rewriteScope',
                'rewriteTargetBlockIds', 'target_block_ids' => 'rewriteTargetBlockIds',
                'rewriteInstructions', 'instructions' => 'rewriteInstructions',
                'rewritePromptTemplateKey', 'prompt_template_key' => 'rewritePromptTemplateKey',
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

    protected function selectedFeaturedMedia(): ?array
    {
        if (! filled($this->featuredMediaId)) {
            return null;
        }

        return collect($this->mediaOptions)
            ->first(fn (array $asset): bool => (string) $asset['id'] === (string) $this->featuredMediaId);
    }

    protected function visibleMediaOptions(): array
    {
        $term = str($this->mediaSearch)->lower()->trim()->value();

        return collect($this->mediaOptions)
            ->filter(function (array $asset) use ($term): bool {
                if ($term === '') {
                    return true;
                }

                return str_contains(strtolower((string) $asset['name']), $term)
                    || str_contains(strtolower((string) $asset['alt_text']), $term);
            })
            ->values()
            ->all();
    }

    protected function blockUi(array $block): array
    {
        $blockType = (string) ($block['blockType'] ?? 'paragraph');
        $sourceTemplateBlockId = (string) ($block['sourceTemplateBlockId'] ?? '');

        return [
            'contentLabel' => $this->blockContentLabel($blockType),
            'contentHint' => $this->blockContentHint($blockType),
            'placeholder' => $this->blockPlaceholder($blockType),
            'toolbar' => $this->toolbarActions($blockType),
            'showsToolbar' => $this->supportsMarkdownToolbar($blockType),
            'sourceTemplateBlockId' => $sourceTemplateBlockId,
            'sourceTemplateHint' => $sourceTemplateBlockId !== ''
                ? 'This block keeps its linkage to the template block it was seeded from.'
                : null,
        ];
    }

    protected function blockContentLabel(string $blockType): string
    {
        return match ($blockType) {
            'heading' => 'Heading Content',
            'image' => 'Image Content',
            'quote' => 'Quote Content',
            'list' => 'List Content',
            'code' => 'Code Content',
            'faq' => 'FAQ Content',
            'callout' => 'Callout Content',
            default => 'Content',
        };
    }

    protected function blockContentHint(string $blockType): string
    {
        return match ($blockType) {
            'heading' => 'Keep headings concise. Each non-empty line becomes one ordered content item.',
            'image' => 'The API stores image blocks as an ordered string array. Put the main media reference on line 1 and any supporting text on later lines.',
            'quote' => 'Use the first line for the quote itself. Add attribution or context on later lines when needed.',
            'list' => 'Use one list item per line so the payload stays easy to reason about.',
            'code' => 'Paste code as plain text. The editor keeps it lightweight and preserves the line order in the payload.',
            'faq' => 'Keep each FAQ item grouped clearly. One line becomes one ordered content item in the saved payload.',
            'callout' => 'Use brief high-signal copy. Additional lines can hold short supporting notes.',
            default => 'Write markdown-friendly editorial copy. Non-empty lines become ordered content items on save.',
        };
    }

    protected function blockPlaceholder(string $blockType): string
    {
        return match ($blockType) {
            'heading' => "Section heading\nOptional deck line",
            'image' => "https://example.com/image.webp\nOptional caption\nOptional credit",
            'quote' => "\"A useful editorial quote\"\nAttribution",
            'list' => "First list item\nSecond list item\nThird list item",
            'code' => "function example() {\n    return true;\n}",
            'faq' => "Question: What is this block for?\nAnswer: Short editorial answer.",
            'callout' => "Key takeaway\nOptional supporting note",
            default => 'Write the block content here',
        };
    }

    protected function supportsMarkdownToolbar(string $blockType): bool
    {
        return $blockType !== 'image';
    }

    protected function toolbarActions(string $blockType): array
    {
        if (! $this->supportsMarkdownToolbar($blockType)) {
            return [];
        }

        return [
            ['action' => 'bold', 'label' => 'Bold'],
            ['action' => 'italic', 'label' => 'Italic'],
            ['action' => 'link', 'label' => 'Link'],
            ['action' => 'list', 'label' => 'List'],
            ['action' => 'quote', 'label' => 'Quote'],
            ['action' => 'code', 'label' => 'Code'],
        ];
    }

    protected function toolbarSnippets(string $blockType): array
    {
        $codeSnippet = $blockType === 'code'
            ? "```php\n// code sample\n```"
            : '`inline code`';

        return [
            'bold' => '**Bold text**',
            'italic' => '*Italic text*',
            'link' => '[Link text](https://example.com)',
            'list' => "- First item\n- Second item",
            'quote' => '> Pull quote',
            'code' => $codeSnippet,
        ];
    }

    protected function actionSuccessMessage(): string
    {
        return match ($this->actionMode) {
            'publish' => 'Post published.',
            'schedule' => 'Post scheduled.',
            'unpublish' => 'Post unpublished.',
            'delete' => 'Post deleted.',
            default => 'Post updated.',
        };
    }

    protected function actionConfig(): array
    {
        return match ($this->actionMode) {
            'schedule' => [
                'title' => 'Schedule post',
                'description' => 'Choose when this post should go live.',
                'body' => 'Set a publish time for',
                'confirm' => 'Schedule post',
                'tone' => 'default',
            ],
            'unpublish' => [
                'title' => 'Unpublish post',
                'description' => 'Take this post out of the live state without deleting it.',
                'body' => 'Move',
                'confirm' => 'Unpublish post',
                'tone' => 'default',
            ],
            'delete' => [
                'title' => 'Delete post',
                'description' => 'Delete the post only when it is no longer needed.',
                'body' => 'Delete',
                'confirm' => 'Delete post',
                'tone' => 'destructive',
            ],
            default => [
                'title' => 'Publish post',
                'description' => 'Make the current version live immediately.',
                'body' => 'Publish',
                'confirm' => 'Publish post',
                'tone' => 'default',
            ],
        };
    }

    protected function scheduleActionRules(): array
    {
        return [
            'scheduledFor' => ['required', 'date', 'after:now'],
        ];
    }

    protected function seoableType(): string
    {
        return 'post';
    }

    protected function seoPresenter(): SeoInsightsPresenter
    {
        return app(SeoInsightsPresenter::class);
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
