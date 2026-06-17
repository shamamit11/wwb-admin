<?php

namespace App\Livewire\Admin\KnowledgeBase;

use App\Services\WideWebBlogApi\Clients\KnowledgeBaseClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    private const ENTRY_TYPES = [
        'note',
        'research',
        'experience',
        'architecture',
        'code',
        'reference',
        'idea',
    ];

    private const ENTRY_STATUSES = [
        'draft',
        'active',
        'archived',
    ];

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'type', except: 'all')]
    public string $typeFilter = 'all';

    #[Url(as: 'sort', except: 'updated_at')]
    public string $sortColumn = 'updated_at';

    #[Url(as: 'dir', except: 'desc')]
    public string $sortDirection = 'desc';

    public array $entries = [];

    public bool $deleteDialogOpen = false;

    public ?int $deleteEntryId = null;

    public string $deleteEntryTitle = '';

    public ?string $pageError = null;

    public ?string $deleteError = null;

    public function mount(AdminSessionManager $session, KnowledgeBaseClient $knowledgeBase): mixed
    {
        return $this->loadEntries($knowledgeBase, $session);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'statusFilter', 'typeFilter'], true)) {
            $this->refreshEntries();
        }
    }

    public function sortBy(string $column): void
    {
        if (! in_array($column, ['title', 'created_at', 'updated_at'], true)) {
            return;
        }

        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = $column === 'title' ? 'asc' : 'desc';
        }

        $this->refreshEntries();
    }

    public function confirmDelete(int $entryId): void
    {
        $entry = collect($this->entries)->firstWhere('id', $entryId);

        if (! is_array($entry)) {
            return;
        }

        $this->deleteDialogOpen = true;
        $this->deleteEntryId = $entryId;
        $this->deleteEntryTitle = (string) ($entry['title'] ?? 'this entry');
        $this->deleteError = null;
    }

    public function closeDeleteDialog(): void
    {
        $this->deleteDialogOpen = false;
        $this->deleteEntryId = null;
        $this->deleteEntryTitle = '';
        $this->deleteError = null;
    }

    public function delete(KnowledgeBaseClient $knowledgeBase, AdminSessionManager $session): mixed
    {
        if (! $this->deleteEntryId) {
            return null;
        }

        $this->deleteError = null;

        try {
            $knowledgeBase->delete($this->token($session), $session->tokenType(), $this->deleteEntryId);
            session()->flash('status', 'Knowledge entry deleted.');
            $this->closeDeleteDialog();

            return $this->loadEntries($knowledgeBase, $session);
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->deleteError = $exception->getMessage() ?: 'Knowledge entry could not be deleted.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.knowledge-base.index', [
            'entries' => $this->entries,
            'entryTypes' => self::ENTRY_TYPES,
            'entryStatuses' => self::ENTRY_STATUSES,
        ])->layout('layouts.admin', [
            'title' => 'Knowledge Base',
        ]);
    }

    protected function loadEntries(KnowledgeBaseClient $knowledgeBase, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $knowledgeBase->index($this->token($session), $session->tokenType(), $this->filters());

            $this->entries = collect(Arr::get($response, 'data', []))
                ->map(fn (array $entry): array => $this->mapEntry($entry))
                ->values()
                ->all();

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->entries = [];
            $this->pageError = $exception->getMessage() ?: 'Knowledge base entries could not be loaded.';

            return null;
        }
    }

    protected function refreshEntries(): void
    {
        $this->loadEntries(app(KnowledgeBaseClient::class), app(AdminSessionManager::class));
    }

    protected function filters(): array
    {
        return [
            'search' => trim($this->search) !== '' ? trim($this->search) : null,
            'status' => $this->statusFilter !== 'all' ? $this->statusFilter : null,
            'entry_type' => $this->typeFilter !== 'all' ? $this->typeFilter : null,
            'sort' => $this->apiSort(),
        ];
    }

    protected function apiSort(): string
    {
        return $this->sortDirection === 'desc'
            ? '-'.$this->sortColumn
            : $this->sortColumn;
    }

    protected function mapEntry(array $entry): array
    {
        return [
            'id' => (int) Arr::get($entry, 'id'),
            'title' => (string) Arr::get($entry, 'title', 'Untitled entry'),
            'slug' => (string) Arr::get($entry, 'slug', ''),
            'entry_type' => (string) Arr::get($entry, 'entry_type', 'note'),
            'status' => (string) Arr::get($entry, 'status', 'draft'),
            'summary' => Arr::get($entry, 'summary'),
            'source_url' => Arr::get($entry, 'source_url'),
            'updated_at' => $this->formatTimestamp(Arr::get($entry, 'updated_at')),
            'created_at' => $this->formatTimestamp(Arr::get($entry, 'created_at')),
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
