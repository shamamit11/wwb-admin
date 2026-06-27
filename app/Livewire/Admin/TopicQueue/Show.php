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
    private const REVIEW_THRESHOLD = 70.0;

    private const AUTO_DRAFT_THRESHOLD = 85.0;

    public int $topicId;

    public array $topicRecord = [];

    public ?string $pageError = null;

    public ?string $actionError = null;

    public bool $notFound = false;

    public bool $actionDialogOpen = false;

    public string $actionMode = 'reject';

    public string $actionNotes = '';

    public function mount(ContentTopicClient $topics, AdminSessionManager $session, int $topic): mixed
    {
        $this->topicId = $topic;

        return $this->loadTopic($topics, $session);
    }

    public function render()
    {
        $isAiToolsTopic = ($this->topicRecord['category_slug'] ?? null) === 'ai-tools';

        return view('livewire.admin.topic-queue.show', [
            'automationTone' => $this->automationTone($this->topicRecord['priority_score'] ?? null),
            'actionState' => $this->actionState(),
            'actionConfig' => $this->actionConfig(),
            'isAiToolsTopic' => $isAiToolsTopic,
            'aiToolsGuidance' => $this->aiToolsGuidance($isAiToolsTopic),
            'aiToolsFit' => $this->aiToolsFit(
                $isAiToolsTopic,
                (string) ($this->topicRecord['title'] ?? ''),
                (string) ($this->topicRecord['primary_keyword'] ?? ''),
            ),
        ])->layout('layouts.admin', [
            'title' => 'Topic Details',
        ]);
    }

    public function generateDraft(ContentTopicClient $topics, AdminSessionManager $session): mixed
    {
        if (! $this->canQueueDraft()) {
            return null;
        }

        $this->actionError = null;

        try {
            $topics->generateDraft($this->token($session), $session->tokenType(), $this->topicId);
            session()->flash('status', 'Draft generation queued for this topic.');

            return $this->loadTopic($topics, $session);
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiValidationException $exception) {
            $this->actionError = $exception->getMessage() ?: 'Draft generation could not be queued for this topic.';

            return null;
        } catch (WideWebBlogApiException $exception) {
            return $this->handleActionException($exception, $topics, $session, 'Draft generation could not be queued for this topic.');
        }
    }

    public function approveTopic(ContentTopicClient $topics, AdminSessionManager $session): mixed
    {
        if (! $this->canApprove()) {
            return null;
        }

        return $this->runTopicAction($topics, $session, 'approve', 'Topic approved.');
    }

    public function openActionDialog(string $mode): void
    {
        if (! in_array($mode, ['reject', 'mark-used'], true)) {
            return;
        }

        if ($mode === 'reject' && ! $this->canReject()) {
            return;
        }

        if ($mode === 'mark-used' && ! $this->canMarkUsed()) {
            return;
        }

        $this->resetValidation();
        $this->actionDialogOpen = true;
        $this->actionMode = $mode;
        $this->actionNotes = '';
        $this->actionError = null;
    }

    public function closeActionDialog(): void
    {
        $this->resetValidation();
        $this->actionDialogOpen = false;
        $this->actionMode = 'reject';
        $this->actionNotes = '';
        $this->actionError = null;
    }

    public function executeAction(ContentTopicClient $topics, AdminSessionManager $session): mixed
    {
        if ($this->actionMode === 'reject' && ! $this->canReject()) {
            return null;
        }

        if ($this->actionMode === 'mark-used' && ! $this->canMarkUsed()) {
            return null;
        }

        return $this->runTopicAction(
            $topics,
            $session,
            $this->actionMode,
            $this->actionMode === 'mark-used' ? 'Topic marked as used.' : 'Topic rejected.',
            $this->actionPayload(),
        );
    }

    protected function runTopicAction(
        ContentTopicClient $topics,
        AdminSessionManager $session,
        string $action,
        string $successMessage,
        array $payload = [],
    ): mixed {
        $this->actionError = null;

        try {
            match ($action) {
                'approve' => $topics->approve($this->token($session), $session->tokenType(), $this->topicId, $payload),
                'reject' => $topics->reject($this->token($session), $session->tokenType(), $this->topicId, $payload),
                'mark-used' => $topics->markUsed($this->token($session), $session->tokenType(), $this->topicId, $payload),
                default => null,
            };

            session()->flash('status', $successMessage);
            $this->closeActionDialog();

            return $this->loadTopic($topics, $session);
        } catch (WideWebBlogApiValidationException $exception) {
            $this->actionError = $exception->getMessage() ?: 'The topic action could not be completed.';

            throw ValidationException::withMessages($this->normalizeActionApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            return $this->handleActionException($exception, $topics, $session, 'The topic action could not be completed.');
        }
    }

    protected function handleActionException(
        WideWebBlogApiException $exception,
        ContentTopicClient $topics,
        AdminSessionManager $session,
        string $fallbackMessage,
    ): mixed {
        if ($exception->status() === 404) {
            $this->topicRecord = [];
            $this->notFound = true;
            $this->pageError = 'This topic could not be found in the service API.';
            $this->actionDialogOpen = false;

            return null;
        }

        if ($exception->status() === 409) {
            $message = $exception->getMessage() ?: 'This editorial action is no longer valid for the current topic state.';

            $this->loadTopic($topics, $session);
            $this->actionError = $message;

            return null;
        }

        $this->actionError = $exception->getMessage() ?: $fallbackMessage;

        return null;
    }

    protected function loadTopic(ContentTopicClient $topics, AdminSessionManager $session): mixed
    {
        $this->pageError = null;
        $this->notFound = false;

        try {
            $response = $topics->show($this->token($session), $session->tokenType(), $this->topicId);
            $this->topicRecord = $this->mapTopic(Arr::get($response, 'data', []));

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

    protected function mapTopic(array $topic): array
    {
        $score = is_numeric(Arr::get($topic, 'priority_score')) ? (float) Arr::get($topic, 'priority_score') : null;
        $scoreBreakdown = Arr::get($topic, 'score_breakdown');

        return [
            'id' => (int) Arr::get($topic, 'id'),
            'category_id' => Arr::get($topic, 'category_id'),
            'category_name' => (string) Arr::get($topic, 'category.name', 'Unassigned'),
            'category_slug' => (string) Arr::get($topic, 'category.slug', ''),
            'title' => (string) Arr::get($topic, 'title', 'Untitled topic'),
            'slug' => (string) Arr::get($topic, 'slug', ''),
            'cluster' => (string) Arr::get($topic, 'cluster', ''),
            'primary_keyword' => (string) Arr::get($topic, 'primary_keyword', ''),
            'secondary_keywords' => Arr::get($topic, 'secondary_keywords', []),
            'search_intent' => (string) Arr::get($topic, 'search_intent', ''),
            'priority_score' => $score,
            'priority_score_label' => $score !== null ? number_format($score, $score === floor($score) ? 0 : 2) : 'Not scored',
            'score_breakdown' => is_array($scoreBreakdown)
                ? $scoreBreakdown
                : (is_string($scoreBreakdown) && $scoreBreakdown !== '' ? json_decode($scoreBreakdown, true) ?? [] : []),
            'difficulty_note' => (string) Arr::get($topic, 'difficulty_note', ''),
            'source' => (string) Arr::get($topic, 'source', 'manual'),
            'status' => (string) Arr::get($topic, 'status', 'suggested'),
            'editorial_recommendation' => (string) Arr::get($topic, 'editorial_recommendation', 'unscored'),
            'notes' => (string) Arr::get($topic, 'notes', ''),
            'can_generate_draft' => (bool) Arr::get($topic, 'can_generate_draft', false),
            'has_draft_generation_job' => (bool) Arr::get($topic, 'has_draft_generation_job', false),
            'approved_at' => $this->formatTimestamp(Arr::get($topic, 'approved_at')),
            'rejected_at' => $this->formatTimestamp(Arr::get($topic, 'rejected_at')),
            'used_at' => $this->formatTimestamp(Arr::get($topic, 'used_at')),
            'created_at' => $this->formatTimestamp(Arr::get($topic, 'created_at')),
            'updated_at' => $this->formatTimestamp(Arr::get($topic, 'updated_at')),
            'is_duplicate' => (bool) Arr::get($topic, 'is_duplicate', false),
            'duplicate_matches' => Arr::get($topic, 'duplicate_matches', []),
            'automation_state' => $this->automationState($score),
        ];
    }

    protected function actionPayload(): array
    {
        return $this->actionNotes !== '' ? ['notes' => $this->actionNotes] : [];
    }

    protected function normalizeActionApiErrors(array $errors): array
    {
        return collect($errors)
            ->mapWithKeys(fn (mixed $messages, string $key): array => [
                match ($key) {
                    'notes' => 'actionNotes',
                    default => $key,
                } => is_array($messages) ? $messages : [$messages],
            ])
            ->all();
    }

    protected function canApprove(): bool
    {
        return in_array($this->topicRecord['status'] ?? null, ['suggested', 'rejected'], true);
    }

    protected function canReject(): bool
    {
        return in_array($this->topicRecord['status'] ?? null, ['suggested', 'approved'], true);
    }

    protected function canMarkUsed(): bool
    {
        return ($this->topicRecord['status'] ?? null) === 'approved';
    }

    protected function canQueueDraft(): bool
    {
        return ($this->topicRecord['status'] ?? null) === 'approved'
            && (bool) ($this->topicRecord['can_generate_draft'] ?? false)
            && ! (bool) ($this->topicRecord['has_draft_generation_job'] ?? false);
    }

    protected function actionState(): array
    {
        $status = (string) ($this->topicRecord['status'] ?? '');
        $hasDraftJob = (bool) ($this->topicRecord['has_draft_generation_job'] ?? false);
        $canGenerateDraft = (bool) ($this->topicRecord['can_generate_draft'] ?? false);

        return [
            'show_approve' => in_array($status, ['suggested', 'rejected'], true),
            'show_reject' => in_array($status, ['suggested', 'approved'], true),
            'show_queue_draft' => $status === 'approved',
            'queue_draft_disabled' => $status !== 'approved' || ! $canGenerateDraft || $hasDraftJob,
            'queue_draft_label' => $hasDraftJob ? 'Draft Queued' : 'Queue Draft',
            'show_mark_used' => $status === 'approved',
        ];
    }

    protected function actionConfig(): array
    {
        return match ($this->actionMode) {
            'mark-used' => [
                'title' => 'Mark Topic Used',
                'description' => 'Confirm that this approved topic has now been used in the editorial workflow.',
                'confirm' => 'Mark Used',
                'destructive' => false,
            ],
            default => [
                'title' => 'Reject Topic',
                'description' => 'Reject this topic and optionally record editorial notes for the decision.',
                'confirm' => 'Reject Topic',
                'destructive' => true,
            ],
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
        session()->flash('auth.error', 'You no longer have access to the admin.');

        return $this->redirectRoute('auth.forbidden', navigate: true);
    }

    protected function automationTone(?float $score): string
    {
        if ($score === null) {
            return 'muted';
        }

        if ($score >= self::AUTO_DRAFT_THRESHOLD) {
            return 'success';
        }

        if ($score >= self::REVIEW_THRESHOLD) {
            return 'warning';
        }

        return 'muted';
    }

    protected function automationState(?float $score): string
    {
        if ($score === null) {
            return 'Score pending';
        }

        if ($score >= self::AUTO_DRAFT_THRESHOLD) {
            return 'The backend should auto-queue draft generation using this topic’s saved category.';
        }

        if ($score >= self::REVIEW_THRESHOLD) {
            return 'The backend should keep this topic in Topic Queue for editorial review.';
        }

        return 'The backend should auto-delete this topic below the 70 threshold.';
    }

    protected function aiToolsGuidance(bool $isAiToolsTopic): array
    {
        if (! $isAiToolsTopic) {
            return [];
        }

        return [
            'context' => 'AI Tools topics should stay practical, tool-specific, and commercially relevant without reading like ad copy.',
            'good_fit' => [
                'Named tool review',
                'Tool comparison or alternatives page',
                'Best tool for a specific use case',
                'Pricing, trade-off, workflow, or integration coverage',
            ],
            'weak_fit' => [
                'Generic AI future or opinion angles',
                'Abstract AI theory',
                'Broad non-tool educational topics',
            ],
        ];
    }

    protected function aiToolsFit(bool $isAiToolsTopic, string $title, string $primaryKeyword): array
    {
        if (! $isAiToolsTopic) {
            return [
                'label' => null,
                'tone' => 'muted',
                'note' => null,
            ];
        }

        $text = strtolower(trim($title.' '.$primaryKeyword));
        $hasCommercialToolCue = preg_match('/\b(review|reviews|compare|comparison|versus|vs|alternative|alternatives|best|pricing|price|cost|feature|features|workflow|integration|tool|tools|software|app|platform)\b/i', $text) === 1;
        $hasNamedToolCue = preg_match('/\b(claude|codex|gemini|kiro|chatgpt|cursor|copilot|midjourney|perplexity|notebooklm|runway|suno|elevenlabs)\b/i', $text) === 1;
        $hasBroadAiCue = preg_match('/\b(future|ethics|history|theory|basics|explained|overview|guide to ai|what is ai|artificial intelligence|society|impact|agi)\b/i', $text) === 1;

        if ($hasCommercialToolCue || $hasNamedToolCue) {
            return [
                'label' => 'Tool-Specific',
                'tone' => 'success',
                'note' => 'Strong category fit for evaluation, selection, or workflow coverage.',
            ];
        }

        if ($hasBroadAiCue) {
            return [
                'label' => 'Weak Fit',
                'tone' => 'warning',
                'note' => 'Broad AI framing; confirm a real tool or buyer-use-case angle before moving forward.',
            ];
        }

        return [
            'label' => 'Needs Tool Angle',
            'tone' => 'muted',
            'note' => 'Add a named tool, practical use case, or clearer evaluation angle.',
        ];
    }
}
