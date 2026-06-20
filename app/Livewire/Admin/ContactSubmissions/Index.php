<?php

namespace App\Livewire\Admin\ContactSubmissions;

use App\Services\WideWebBlogApi\Clients\ContactSubmissionClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Index extends Component
{
    public array $submissions = [];

    public ?string $pageError = null;

    public function mount(ContactSubmissionClient $submissions, AdminSessionManager $session): mixed
    {
        return $this->loadSubmissions($submissions, $session);
    }

    public function render()
    {
        return view('livewire.admin.contact-submissions.index', [
            'submissions' => $this->submissions,
        ])->layout('layouts.admin', [
            'title' => 'Contact Submissions',
        ]);
    }

    protected function loadSubmissions(ContactSubmissionClient $submissions, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $submissions->index($this->token($session), $session->tokenType());
            $this->submissions = collect(Arr::get($response, 'data', []))
                ->map(fn (array $submission): array => $this->mapSubmission($submission))
                ->values()
                ->all();

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->submissions = [];
            $this->pageError = $exception->getMessage() ?: 'Contact submissions could not be loaded.';

            return null;
        }
    }

    protected function mapSubmission(array $submission): array
    {
        return [
            'id' => (string) Arr::get($submission, 'id', ''),
            'name' => (string) Arr::get($submission, 'name', 'Unknown'),
            'email' => (string) Arr::get($submission, 'email', ''),
            'topic' => (string) Arr::get($submission, 'topic', ''),
            'status' => (string) Arr::get($submission, 'status', 'new'),
            'submitted_at' => $this->formatTimestamp(Arr::get($submission, 'submitted_at')),
        ];
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
