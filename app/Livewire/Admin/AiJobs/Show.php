<?php

namespace App\Livewire\Admin\AiJobs;

use App\Services\WideWebBlogApi\Clients\AiJobClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Show extends Component
{
    public int $jobId;

    public array $job = [];

    public ?string $pageError = null;

    public ?string $actionError = null;

    public bool $notFound = false;

    public function mount(int $aiJob, AdminSessionManager $session, AiJobClient $jobs): mixed
    {
        $this->jobId = $aiJob;

        return $this->loadJob($jobs, $session);
    }

    public function retry(AiJobClient $jobs, AdminSessionManager $session): mixed
    {
        $this->actionError = null;

        try {
            $response = $jobs->retry($this->token($session), $session->tokenType(), $this->jobId);
            $this->job = $this->mapJob(Arr::get($response, 'data', []));

            session()->flash('status', 'AI job retry requested.');

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->actionError = $exception->getMessage() ?: 'The AI job could not be retried.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.ai-jobs.show', [
            'entityLink' => $this->entityLink(),
            'inputPayloadJson' => $this->prettyJson($this->job['input_payload'] ?? null),
            'outputPayloadJson' => $this->prettyJson($this->job['output_payload'] ?? null),
            'usagePayloadJson' => $this->prettyJson($this->job['usage_payload'] ?? null),
        ])->layout('layouts.admin', [
            'title' => 'AI Job Detail',
        ]);
    }

    protected function loadJob(AiJobClient $jobs, AdminSessionManager $session): mixed
    {
        $this->pageError = null;
        $this->notFound = false;

        try {
            $response = $jobs->show($this->token($session), $session->tokenType(), $this->jobId);
            $this->job = $this->mapJob(Arr::get($response, 'data', []));

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            if ($exception->status() === 404) {
                $this->notFound = true;
                $this->pageError = 'This AI job could not be found in the service API.';

                return null;
            }

            $this->pageError = $exception->getMessage() ?: 'AI job details could not be loaded.';

            return null;
        }
    }

    protected function mapJob(array $job): array
    {
        return [
            'id' => Arr::get($job, 'id'),
            'type' => (string) Arr::get($job, 'type', ''),
            'status' => (string) Arr::get($job, 'status', 'pending'),
            'entity_type' => Arr::get($job, 'entity_type'),
            'entity_id' => Arr::get($job, 'entity_id'),
            'provider' => Arr::get($job, 'provider'),
            'model' => Arr::get($job, 'model'),
            'input_payload' => Arr::get($job, 'input_payload'),
            'output_payload' => Arr::get($job, 'output_payload'),
            'usage_payload' => Arr::get($job, 'usage_payload'),
            'error_message' => Arr::get($job, 'error_message'),
            'attempts' => (int) Arr::get($job, 'attempts', 0),
            'retry_of_ai_job_id' => Arr::get($job, 'retry_of_ai_job_id'),
            'can_retry' => (bool) Arr::get($job, 'can_retry', false),
            'steps_count' => (int) Arr::get($job, 'steps_count', 0),
            'cost_summary' => Arr::get($job, 'cost_summary'),
            'started_at' => $this->formatTimestamp(Arr::get($job, 'started_at')),
            'completed_at' => $this->formatTimestamp(Arr::get($job, 'completed_at')),
            'failed_at' => $this->formatTimestamp(Arr::get($job, 'failed_at')),
            'created_at' => $this->formatTimestamp(Arr::get($job, 'created_at')),
            'updated_at' => $this->formatTimestamp(Arr::get($job, 'updated_at')),
            'retry_of' => Arr::get($job, 'retry_of'),
            'retries' => collect(Arr::get($job, 'retries', []))
                ->map(fn (array $retry): array => [
                    'id' => Arr::get($retry, 'id'),
                    'status' => Arr::get($retry, 'status'),
                    'type' => Arr::get($retry, 'type'),
                ])
                ->values()
                ->all(),
            'steps' => collect(Arr::get($job, 'steps', []))
                ->map(fn (array $step): array => [
                    'id' => Arr::get($step, 'id'),
                    'agent_name' => Arr::get($step, 'agent_name', 'Unknown agent'),
                    'status' => Arr::get($step, 'status', 'pending'),
                    'input_payload' => Arr::get($step, 'input_payload'),
                    'output_payload' => Arr::get($step, 'output_payload'),
                    'usage_payload' => Arr::get($step, 'usage_payload'),
                    'error_message' => Arr::get($step, 'error_message'),
                    'costs' => Arr::get($step, 'costs', []),
                    'started_at' => $this->formatTimestamp(Arr::get($step, 'started_at')),
                    'completed_at' => $this->formatTimestamp(Arr::get($step, 'completed_at')),
                    'failed_at' => $this->formatTimestamp(Arr::get($step, 'failed_at')),
                ])
                ->values()
                ->all(),
            'costs' => Arr::get($job, 'costs', []),
        ];
    }

    protected function entityLink(): ?array
    {
        $entityType = (string) ($this->job['entity_type'] ?? '');
        $entityId = $this->job['entity_id'] ?? null;

        if (! $entityId) {
            return null;
        }

        if (str_contains($entityType, 'ContentTopic') || str_contains(strtolower($entityType), 'content_topic')) {
            return [
                'label' => 'Open topic review',
                'href' => route('topic-queue.show', ['topic' => $entityId]),
            ];
        }

        return null;
    }

    protected function prettyJson(mixed $payload): string
    {
        if ($payload === null) {
            return 'No payload available.';
        }

        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return $encoded !== false ? $encoded : 'Payload could not be rendered.';
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
