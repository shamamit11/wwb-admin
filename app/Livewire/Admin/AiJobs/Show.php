<?php

namespace App\Livewire\Admin\AiJobs;

use App\Services\WideWebBlogApi\Clients\AiJobClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
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

    public function refreshJob(AiJobClient $jobs, AdminSessionManager $session): mixed
    {
        $this->actionError = null;

        return $this->loadJob($jobs, $session);
    }

    public function render()
    {
        return view('livewire.admin.ai-jobs.show', [
            'lifecycleItems' => $this->lifecycleItems(),
            'summaryItems' => $this->summaryItems(),
            'costItems' => $this->costItems(),
            'payloadCards' => $this->payloadCards(),
            'stepCards' => $this->stepCards(),
            'entityLink' => $this->entityLink(),
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
            'started_at_raw' => Arr::get($job, 'started_at'),
            'completed_at_raw' => Arr::get($job, 'completed_at'),
            'failed_at_raw' => Arr::get($job, 'failed_at'),
            'created_at_raw' => Arr::get($job, 'created_at'),
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
                    'started_at_raw' => Arr::get($step, 'started_at'),
                    'completed_at_raw' => Arr::get($step, 'completed_at'),
                    'failed_at_raw' => Arr::get($step, 'failed_at'),
                ])
                ->values()
                ->all(),
            'costs' => Arr::get($job, 'costs', []),
        ];
    }

    protected function lifecycleItems(): array
    {
        $items = [[
            'label' => 'Queued',
            'timestamp' => $this->job['created_at'] ?? null,
            'state' => 'completed',
        ]];

        if ($this->job['started_at'] ?? null) {
            $items[] = [
                'label' => 'Started',
                'timestamp' => $this->job['started_at'],
                'state' => 'completed',
            ];
        }

        if ($this->job['completed_at'] ?? null) {
            $items[] = [
                'label' => 'Completed',
                'timestamp' => $this->job['completed_at'],
                'state' => 'success',
            ];
        } elseif ($this->job['failed_at'] ?? null) {
            $items[] = [
                'label' => 'Failed',
                'timestamp' => $this->job['failed_at'],
                'state' => 'danger',
            ];
        } elseif (($this->job['status'] ?? '') === 'queued') {
            $items[] = [
                'label' => 'Waiting',
                'timestamp' => null,
                'state' => 'current',
            ];
        }

        return $items;
    }

    protected function summaryItems(): array
    {
        return [
            ['label' => 'Provider', 'value' => $this->job['provider'] ?? 'Unknown'],
            ['label' => 'Model', 'value' => $this->job['model'] ?? 'Unknown'],
            ['label' => 'Attempts', 'value' => (string) ($this->job['attempts'] ?? 0)],
            ['label' => 'Generation Steps', 'value' => (string) ($this->job['steps_count'] ?? count($this->job['steps'] ?? []))],
            ['label' => 'Started', 'value' => $this->job['started_at'] ?? 'Not started'],
            ['label' => 'Completed', 'value' => $this->job['completed_at'] ?? 'Not completed'],
            ['label' => 'Failed', 'value' => $this->job['failed_at'] ?? 'Not failed'],
        ];
    }

    protected function costItems(): array
    {
        return [
            ['label' => 'Input Tokens', 'value' => $this->formatMetric(data_get($this->job, 'cost_summary.input_tokens'))],
            ['label' => 'Output Tokens', 'value' => $this->formatMetric(data_get($this->job, 'cost_summary.output_tokens'))],
            ['label' => 'Total Tokens', 'value' => $this->formatMetric(data_get($this->job, 'cost_summary.total_tokens'))],
            ['label' => 'Estimated Cost', 'value' => $this->formatMetric(data_get($this->job, 'cost_summary.estimated_cost'), data_get($this->job, 'cost_summary.currency'))],
            ['label' => 'Actual Cost', 'value' => $this->formatMetric(data_get($this->job, 'cost_summary.actual_cost'), data_get($this->job, 'cost_summary.currency'))],
        ];
    }

    protected function payloadCards(): array
    {
        return [
            $this->payloadCard('job-input-payload', 'Input Payload', $this->job['input_payload'] ?? null),
            $this->payloadCard('job-output-payload', 'Output Payload', $this->job['output_payload'] ?? null),
            $this->payloadCard('job-usage-payload', 'Usage Payload', $this->job['usage_payload'] ?? null),
        ];
    }

    protected function stepCards(): array
    {
        return collect($this->job['steps'] ?? [])
            ->map(function (array $step): array {
                return [
                    'id' => $step['id'] ?? null,
                    'agent_name' => $step['agent_name'] ?? 'Unknown agent',
                    'status' => $step['status'] ?? 'pending',
                    'started_at' => $step['started_at'] ?? null,
                    'completed_at' => $step['completed_at'] ?? null,
                    'failed_at' => $step['failed_at'] ?? null,
                    'duration' => $this->durationLabel($step['started_at_raw'] ?? null, $step['completed_at_raw'] ?? $step['failed_at_raw'] ?? null),
                    'input_summary' => $this->payloadSummary($step['input_payload'] ?? null),
                    'output_summary' => $this->payloadSummary($step['output_payload'] ?? null),
                    'usage_summary' => $this->payloadSummary($step['usage_payload'] ?? null),
                    'error_message' => $step['error_message'] ?? null,
                    'input_payload_card' => $this->payloadCard('step-'.$step['id'].'-input', 'Input Payload', $step['input_payload'] ?? null),
                    'output_payload_card' => $this->payloadCard('step-'.$step['id'].'-output', 'Output Payload', $step['output_payload'] ?? null),
                ];
            })
            ->values()
            ->all();
    }

    protected function payloadCard(string $id, string $title, mixed $payload): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'summary' => $this->payloadSummary($payload),
            'json' => $this->prettyJson($payload),
            'copy' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: 'No payload available.',
            'has_payload' => $payload !== null,
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

    protected function payloadSummary(mixed $payload): string
    {
        if ($payload === null) {
            return 'No payload available.';
        }

        if (! is_array($payload)) {
            $value = trim((string) $payload);

            return $value !== '' ? Str::limit($value, 120) : 'Payload value is empty.';
        }

        $pairs = collect($payload)
            ->take(4)
            ->map(function (mixed $value, string|int $key): string {
                if (is_array($value)) {
                    return Str::headline((string) $key).': '.count($value).' '.Str::plural('item', count($value));
                }

                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                } elseif ($value === null) {
                    $value = 'null';
                }

                return Str::headline((string) $key).': '.Str::limit((string) $value, 42);
            })
            ->values()
            ->all();

        return $pairs !== [] ? implode(' · ', $pairs) : 'Payload is present but has no summary-friendly fields.';
    }

    protected function durationLabel(mixed $startedAt, mixed $endedAt): ?string
    {
        if (! is_string($startedAt) || ! is_string($endedAt) || $startedAt === '' || $endedAt === '') {
            return null;
        }

        try {
            $seconds = Carbon::parse($startedAt)->diffInSeconds(Carbon::parse($endedAt));

            if ($seconds < 60) {
                return $seconds.'s';
            }

            $minutes = intdiv($seconds, 60);
            $remainingSeconds = $seconds % 60;

            return $remainingSeconds > 0 ? $minutes.'m '.$remainingSeconds.'s' : $minutes.'m';
        } catch (\Throwable) {
            return null;
        }
    }

    protected function formatMetric(mixed $value, mixed $currency = null): string
    {
        if ($value === null || $value === '') {
            return 'Unavailable';
        }

        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            return number_format((int) $value);
        }

        if (is_numeric($value)) {
            $formatted = number_format((float) $value, 8, '.', '');

            return is_string($currency) && $currency !== '' ? $formatted.' '.$currency : $formatted;
        }

        return (string) $value;
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
