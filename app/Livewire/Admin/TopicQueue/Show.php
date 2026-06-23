<?php

namespace App\Livewire\Admin\TopicQueue;

use App\Services\WideWebBlogApi\Clients\ContentTopicClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
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

    public function mount(ContentTopicClient $topics, AdminSessionManager $session, int $topic): mixed
    {
        $this->topicId = $topic;

        return $this->loadTopic($topics, $session);
    }

    public function render()
    {
        return view('livewire.admin.topic-queue.show', [
            'automationTone' => $this->automationTone($this->topicRecord['priority_score'] ?? null),
        ])->layout('layouts.admin', [
            'title' => 'Topic Details',
        ]);
    }

    public function generateDraft(ContentTopicClient $topics, AdminSessionManager $session): mixed
    {
        $this->actionError = null;

        try {
            $response = $topics->generateDraft($this->token($session), $session->tokenType(), $this->topicId);
            $jobId = Arr::get($response, 'data.id');

            session()->flash('status', 'Draft generation queued for this topic category.');

            if (is_int($jobId) || ctype_digit((string) $jobId)) {
                return $this->redirectRoute('ai-jobs.show', ['aiJob' => (int) $jobId], navigate: true);
            }

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->actionError = $exception->getMessage() ?: 'Draft generation could not be started for this topic.';

            return null;
        }
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
            'notes' => (string) Arr::get($topic, 'notes', ''),
            'can_generate_draft' => (bool) Arr::get($topic, 'can_generate_draft', false),
            'approved_at' => $this->formatTimestamp(Arr::get($topic, 'approved_at')),
            'rejected_at' => $this->formatTimestamp(Arr::get($topic, 'rejected_at')),
            'used_at' => $this->formatTimestamp(Arr::get($topic, 'used_at')),
            'created_at' => $this->formatTimestamp(Arr::get($topic, 'created_at')),
            'updated_at' => $this->formatTimestamp(Arr::get($topic, 'updated_at')),
            'automation_state' => $this->automationState($score),
        ];
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
}
