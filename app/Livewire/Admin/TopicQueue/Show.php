<?php

namespace App\Livewire\Admin\TopicQueue;

use App\Services\WideWebBlogApi\Clients\ContentTopicClient;
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
    private const TOPIC_CLUSTERS = [
        'ai_tools',
        'ai_for_blogging',
        'seo',
        'content_marketing',
        'productivity_automation',
        'developer_ai',
    ];

    private const TOPIC_SOURCES = [
        'manual',
        'ai_suggested',
    ];

    public int $topicId;

    public string $title = '';

    public string $slug = '';

    public string $cluster = 'ai_tools';

    public string $primaryKeyword = '';

    public string $secondaryKeywords = '';

    public string $searchIntent = '';

    public string $priorityScore = '';

    public string $difficultyNote = '';

    public string $source = 'manual';

    public string $notes = '';

    public string $status = 'suggested';

    public ?string $approvedAt = null;

    public ?string $rejectedAt = null;

    public ?string $usedAt = null;

    public ?string $createdAt = null;

    public ?string $updatedAt = null;

    public bool $canGenerateContentBrief = false;

    public bool $transitionDialogOpen = false;

    public string $transitionAction = 'approve';

    public string $transitionNotes = '';

    public bool $briefDialogOpen = false;

    public ?string $pageError = null;

    public ?string $formError = null;

    public ?string $actionError = null;

    public ?string $briefError = null;

    public bool $notFound = false;

    public function mount(int $topic, AdminSessionManager $session, ContentTopicClient $topics): mixed
    {
        $this->topicId = $topic;

        return $this->loadTopic($topics, $session);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:160'],
            'cluster' => ['required', 'in:'.implode(',', self::TOPIC_CLUSTERS)],
            'primaryKeyword' => ['nullable', 'string', 'max:255'],
            'secondaryKeywords' => ['nullable', 'string'],
            'searchIntent' => ['nullable', 'string', 'max:255'],
            'priorityScore' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'difficultyNote' => ['nullable', 'string'],
            'source' => ['required', 'in:'.implode(',', self::TOPIC_SOURCES)],
            'notes' => ['nullable', 'string'],
            'transitionNotes' => ['nullable', 'string'],
        ];
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['title', 'slug', 'cluster', 'primaryKeyword', 'secondaryKeywords', 'searchIntent', 'priorityScore', 'difficultyNote', 'source', 'notes', 'transitionNotes'], true)) {
            $this->validateOnly($property);
        }
    }

    public function save(ContentTopicClient $topics, AdminSessionManager $session): mixed
    {
        $validated = $this->validate();
        $this->formError = null;

        try {
            $response = $topics->update(
                $this->token($session),
                $session->tokenType(),
                $this->topicId,
                $this->topicPayload($validated),
            );

            $this->fillTopic(Arr::get($response, 'data', []));

            session()->flash('status', 'Topic updated.');

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->formError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->formError = $exception->getMessage() ?: 'Topic changes could not be saved.';

            return null;
        }
    }

    public function openTransitionDialog(string $action): void
    {
        if (! in_array($action, ['approve', 'reject', 'mark-used'], true)) {
            return;
        }

        $this->resetValidation('transitionNotes');
        $this->transitionDialogOpen = true;
        $this->transitionAction = $action;
        $this->transitionNotes = '';
        $this->actionError = null;
    }

    public function closeTransitionDialog(): void
    {
        $this->resetValidation('transitionNotes');
        $this->transitionDialogOpen = false;
        $this->transitionAction = 'approve';
        $this->transitionNotes = '';
        $this->actionError = null;
    }

    public function openBriefDialog(): void
    {
        $this->briefDialogOpen = true;
        $this->briefError = null;
    }

    public function closeBriefDialog(): void
    {
        $this->briefDialogOpen = false;
        $this->briefError = null;
    }

    public function executeTransition(ContentTopicClient $topics, AdminSessionManager $session): mixed
    {
        $validated = $this->validateOnly('transitionNotes');
        $this->actionError = null;

        try {
            $response = match ($this->transitionAction) {
                'approve' => $topics->approve($this->token($session), $session->tokenType(), $this->topicId, $this->transitionPayload($validated)),
                'reject' => $topics->reject($this->token($session), $session->tokenType(), $this->topicId, $this->transitionPayload($validated)),
                'mark-used' => $topics->markUsed($this->token($session), $session->tokenType(), $this->topicId, $this->transitionPayload($validated)),
            };

            $this->fillTopic(Arr::get($response, 'data', []));
            $this->closeTransitionDialog();

            session()->flash('status', match ($this->transitionAction) {
                'approve' => 'Topic approved.',
                'reject' => 'Topic rejected.',
                'mark-used' => 'Topic marked as used.',
            });

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->actionError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->actionError = $exception->getMessage() ?: 'The topic status change failed.';

            return null;
        }
    }

    public function generateBrief(ContentTopicClient $topics, AdminSessionManager $session): mixed
    {
        $this->briefError = null;

        try {
            $response = $topics->generateBrief($this->token($session), $session->tokenType(), $this->topicId);
            $briefId = Arr::get($response, 'data.id');
            $this->closeBriefDialog();
            session()->flash('status', 'Content brief generated.');

            if (is_int($briefId) || ctype_digit((string) $briefId)) {
                return $this->redirectRoute('content-briefs.show', ['contentBrief' => (int) $briefId], navigate: true);
            }

            return $this->redirectRoute('content-briefs.index', navigate: true);
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->briefError = $exception->getMessage() ?: 'Content brief generation failed.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.topic-queue.show', [
            'clusterOptions' => self::TOPIC_CLUSTERS,
            'sourceOptions' => self::TOPIC_SOURCES,
            'canApprove' => in_array($this->status, ['suggested', 'rejected'], true),
            'canReject' => in_array($this->status, ['suggested', 'approved'], true),
            'canMarkUsed' => $this->status === 'approved',
        ])->layout('layouts.admin', [
            'title' => 'Topic Review',
        ]);
    }

    protected function loadTopic(ContentTopicClient $topics, AdminSessionManager $session): mixed
    {
        $this->pageError = null;
        $this->notFound = false;

        try {
            $response = $topics->show($this->token($session), $session->tokenType(), $this->topicId);
            $this->fillTopic(Arr::get($response, 'data', []));

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            if ($exception->status() === 404) {
                $this->notFound = true;
                $this->pageError = 'This topic could not be found in the service API.';

                return null;
            }

            $this->pageError = $exception->getMessage() ?: 'Topic details could not be loaded.';

            return null;
        }
    }

    protected function fillTopic(array $topic): void
    {
        $this->title = (string) Arr::get($topic, 'title', '');
        $this->slug = (string) Arr::get($topic, 'slug', '');
        $this->cluster = (string) Arr::get($topic, 'cluster', 'ai_tools');
        $this->primaryKeyword = (string) Arr::get($topic, 'primary_keyword', '');
        $this->secondaryKeywords = implode(', ', Arr::get($topic, 'secondary_keywords', []));
        $this->searchIntent = (string) Arr::get($topic, 'search_intent', '');
        $this->priorityScore = Arr::get($topic, 'priority_score') !== null ? (string) Arr::get($topic, 'priority_score') : '';
        $this->difficultyNote = (string) Arr::get($topic, 'difficulty_note', '');
        $this->source = (string) Arr::get($topic, 'source', 'manual');
        $this->notes = (string) Arr::get($topic, 'notes', '');
        $this->status = (string) Arr::get($topic, 'status', 'suggested');
        $this->canGenerateContentBrief = (bool) Arr::get($topic, 'can_generate_content_brief', false);
        $this->approvedAt = $this->formatTimestamp(Arr::get($topic, 'approved_at'));
        $this->rejectedAt = $this->formatTimestamp(Arr::get($topic, 'rejected_at'));
        $this->usedAt = $this->formatTimestamp(Arr::get($topic, 'used_at'));
        $this->createdAt = $this->formatTimestamp(Arr::get($topic, 'created_at'));
        $this->updatedAt = $this->formatTimestamp(Arr::get($topic, 'updated_at'));
    }

    protected function topicPayload(array $validated): array
    {
        return [
            'title' => trim($validated['title']),
            'slug' => filled($validated['slug']) ? trim($validated['slug']) : null,
            'cluster' => $validated['cluster'],
            'primary_keyword' => filled($validated['primaryKeyword']) ? trim($validated['primaryKeyword']) : null,
            'secondary_keywords' => $this->secondaryKeywordList($validated['secondaryKeywords'] ?? ''),
            'search_intent' => filled($validated['searchIntent']) ? trim($validated['searchIntent']) : null,
            'priority_score' => filled($validated['priorityScore']) ? (float) $validated['priorityScore'] : null,
            'difficulty_note' => filled($validated['difficultyNote']) ? trim($validated['difficultyNote']) : null,
            'source' => $validated['source'],
            'notes' => filled($validated['notes']) ? trim($validated['notes']) : null,
        ];
    }

    protected function transitionPayload(mixed $validated = null): array
    {
        return [
            'notes' => filled($this->transitionNotes) ? trim($this->transitionNotes) : null,
        ];
    }

    protected function secondaryKeywordList(string $keywords): array
    {
        return collect(explode(',', $keywords))
            ->map(fn (string $keyword): string => trim($keyword))
            ->filter()
            ->values()
            ->all();
    }

    protected function normalizeApiErrors(array $errors): array
    {
        $mapped = [];

        foreach ($errors as $field => $messages) {
            $property = match ($field) {
                'primary_keyword' => 'primaryKeyword',
                'secondary_keywords' => 'secondaryKeywords',
                'search_intent' => 'searchIntent',
                'priority_score' => 'priorityScore',
                'difficulty_note' => 'difficultyNote',
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
