<?php

namespace App\Livewire\Admin\ContactSubmissions;

use App\Services\WideWebBlogApi\Clients\ContactSubmissionClient;
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
    private const STATUSES = [
        'new',
        'read',
        'archived',
    ];

    public string $submissionId = '';

    public string $name = '';

    public string $email = '';

    public string $topic = '';

    public string $message = '';

    public string $status = 'new';

    public string $admin_notes = '';

    public array $metadata = [];

    public ?string $submitted_at = null;

    public ?string $reviewed_at = null;

    public array $reviewed_by = [];

    public ?string $created_at = null;

    public ?string $updated_at = null;

    public ?string $pageError = null;

    public ?string $formError = null;

    public function mount(string $contactSubmission, AdminSessionManager $session, ContactSubmissionClient $submissions): mixed
    {
        $this->submissionId = $contactSubmission;

        return $this->loadSubmission($submissions, $session);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:'.implode(',', self::STATUSES)],
            'admin_notes' => ['nullable', 'string'],
        ];
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['status', 'admin_notes'], true)) {
            $this->validateOnly($property);
        }
    }

    public function save(ContactSubmissionClient $submissions, AdminSessionManager $session): mixed
    {
        $validated = $this->validate();
        $this->formError = null;

        try {
            $response = $submissions->update(
                $this->token($session),
                $session->tokenType(),
                $this->submissionId,
                [
                    'status' => $validated['status'],
                    'admin_notes' => $this->nullableString($validated['admin_notes'] ?? null),
                    'metadata' => $this->metadata,
                ],
            );

            $this->fillSubmission(Arr::get($response, 'data', []));
            session()->flash('status', 'Contact submission updated.');

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->formError = $exception->getMessage();

            throw ValidationException::withMessages($exception->errors());
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->formError = $exception->getMessage() ?: 'Contact submission changes could not be saved.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.contact-submissions.show', [
            'statusOptions' => self::STATUSES,
            'metadataJson' => $this->metadataJson(),
        ])->layout('layouts.admin', [
            'title' => 'Contact Submission',
        ]);
    }

    protected function loadSubmission(ContactSubmissionClient $submissions, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $submissions->show($this->token($session), $session->tokenType(), $this->submissionId);
            $this->fillSubmission(Arr::get($response, 'data', []));

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'Contact submission could not be loaded.';

            return null;
        }
    }

    protected function fillSubmission(array $submission): void
    {
        $this->name = (string) Arr::get($submission, 'name', '');
        $this->email = (string) Arr::get($submission, 'email', '');
        $this->topic = (string) Arr::get($submission, 'topic', '');
        $this->message = (string) Arr::get($submission, 'message', '');
        $this->status = (string) Arr::get($submission, 'status', 'new');
        $this->admin_notes = (string) (Arr::get($submission, 'admin_notes') ?? '');
        $this->metadata = is_array(Arr::get($submission, 'metadata')) ? Arr::get($submission, 'metadata') : [];
        $this->submitted_at = $this->formatTimestamp(Arr::get($submission, 'submitted_at'));
        $this->reviewed_at = $this->formatTimestamp(Arr::get($submission, 'reviewed_at'));
        $this->reviewed_by = is_array(Arr::get($submission, 'reviewed_by')) ? Arr::get($submission, 'reviewed_by') : [];
        $this->created_at = $this->formatTimestamp(Arr::get($submission, 'created_at'));
        $this->updated_at = $this->formatTimestamp(Arr::get($submission, 'updated_at'));
    }

    protected function metadataJson(): string
    {
        $json = json_encode($this->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : '{}';
    }

    protected function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
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
