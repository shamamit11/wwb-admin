<?php

namespace App\Livewire\Admin\Media;

use App\Services\WideWebBlogApi\Clients\MediaClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiValidationException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'source', except: 'all')]
    public string $sourceTypeFilter = 'all';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'used', except: 'all')]
    public string $usageFilter = 'all';

    #[Url(as: 'image', except: 'all')]
    public string $imageFilter = 'all';

    #[Url(as: 'sort', except: 'created_at')]
    public string $sortColumn = 'created_at';

    #[Url(as: 'dir', except: 'desc')]
    public string $sortDirection = 'desc';

    public array $media = [];

    public bool $uploadDrawerOpen = false;

    public string $uploadMode = 'single';

    public mixed $singleFile = null;

    public array $batchFiles = [];

    public string $uploadAltText = '';

    public string $uploadCaption = '';

    public string $uploadSourceType = 'uploaded';

    public string $uploadSourceUrl = '';

    public string $uploadAttributionText = '';

    public bool $drawerOpen = false;

    public ?int $selectedMediaId = null;

    public array $selectedMedia = [];

    public string $altText = '';

    public string $caption = '';

    public string $sourceType = 'uploaded';

    public string $sourceUrl = '';

    public string $attributionText = '';

    public bool $deleteDialogOpen = false;

    public ?int $deleteMediaId = null;

    public string $deleteMediaName = '';

    public int $deleteUsageCount = 0;

    public array $deleteUsage = [];

    public bool $deleteBlocked = false;

    public ?string $pageError = null;

    public ?string $formError = null;

    public ?string $uploadError = null;

    public function mount(AdminSessionManager $session, MediaClient $media): mixed
    {
        return $this->loadMedia($media, $session);
    }

    public function rules(): array
    {
        return [
            'altText' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string'],
            'sourceType' => ['required', 'in:uploaded,ai_generated,stock'],
            'sourceUrl' => ['nullable', 'url', 'max:500'],
            'attributionText' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function singleUploadRules(): array
    {
        return [
            'singleFile' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,gif,svg,pdf'],
            'uploadAltText' => ['nullable', 'string', 'max:255'],
            'uploadCaption' => ['nullable', 'string'],
            'uploadSourceType' => ['required', 'in:uploaded,ai_generated,stock'],
            'uploadSourceUrl' => ['nullable', 'url', 'max:500'],
            'uploadAttributionText' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function batchUploadRules(): array
    {
        return [
            'batchFiles' => ['required', 'array', 'min:1'],
            'batchFiles.*' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,gif,svg,pdf'],
            'uploadSourceType' => ['required', 'in:uploaded,ai_generated,stock'],
            'uploadSourceUrl' => ['nullable', 'url', 'max:500'],
            'uploadAttributionText' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['altText', 'caption', 'sourceType', 'sourceUrl', 'attributionText'], true)) {
            $this->validateOnly($property);

            return;
        }

        if (in_array($property, ['singleFile', 'uploadAltText', 'uploadCaption', 'uploadSourceType', 'uploadSourceUrl', 'uploadAttributionText'], true)) {
            $this->validateOnly($property, $this->singleUploadRules());

            return;
        }

        if ($property === 'batchFiles' || str_starts_with($property, 'batchFiles.')) {
            $this->validateOnly($property, $this->batchUploadRules());

            return;
        }

        if (in_array($property, ['search', 'sourceTypeFilter', 'statusFilter', 'usageFilter', 'imageFilter'], true)) {
            $this->loadMedia(app(MediaClient::class), app(AdminSessionManager::class));
        }
    }

    public function setUploadMode(string $mode): void
    {
        if (! in_array($mode, ['single', 'batch'], true)) {
            return;
        }

        $this->uploadMode = $mode;
        $this->uploadError = null;
        $this->resetValidation();
    }

    public function openUploadDrawer(): void
    {
        $this->resetUploadForm();
        $this->uploadDrawerOpen = true;
    }

    public function closeUploadDrawer(): void
    {
        $this->uploadDrawerOpen = false;
        $this->resetUploadForm();
    }

    public function sortBy(string $column): void
    {
        if (! in_array($column, ['original_filename', 'source_type', 'status', 'usage_count', 'created_at'], true)) {
            return;
        }

        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortColumn = $column;
        $this->sortDirection = $column === 'created_at' ? 'desc' : 'asc';
    }

    public function openDetailDrawer(int $mediaId, MediaClient $media, AdminSessionManager $session): mixed
    {
        $this->resetValidation();
        $this->formError = null;

        try {
            $response = $media->show($this->token($session), $session->tokenType(), $mediaId);

            $this->fillSelectedMedia($this->mapMedia(Arr::get($response, 'data', [])));
            $this->drawerOpen = true;

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'Media details could not be loaded.';

            return null;
        }
    }

    public function closeDrawer(): void
    {
        $this->resetDetail();
    }

    public function uploadSingle(MediaClient $media, AdminSessionManager $session): mixed
    {
        /** @var array{singleFile:UploadedFile,uploadAltText?:string|null,uploadCaption?:string|null,uploadSourceType:string,uploadSourceUrl?:string|null,uploadAttributionText?:string|null} $validated */
        $validated = $this->validate($this->singleUploadRules());
        $this->uploadError = null;

        try {
            $media->store(
                $this->token($session),
                $validated['singleFile'],
                $this->singleUploadPayload($validated),
                $session->tokenType(),
            );

            session()->flash('status', 'Media uploaded.');

            $this->closeUploadDrawer();
            $this->loadMedia($media, $session);

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->uploadError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeUploadApiErrors($exception->errors(), 'single'));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->uploadError = $exception->getMessage() ?: 'Media upload failed.';

            return null;
        }
    }

    public function uploadBatch(MediaClient $media, AdminSessionManager $session): mixed
    {
        /** @var array{batchFiles:array<int,UploadedFile>,uploadSourceType:string,uploadSourceUrl?:string|null,uploadAttributionText?:string|null} $validated */
        $validated = $this->validate($this->batchUploadRules());
        $this->uploadError = null;

        try {
            $media->batchStore(
                $this->token($session),
                $validated['batchFiles'],
                $this->batchUploadPayload($validated),
                $session->tokenType(),
            );

            session()->flash('status', count($validated['batchFiles']).' media files uploaded.');

            $this->closeUploadDrawer();
            $this->loadMedia($media, $session);

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->uploadError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeUploadApiErrors($exception->errors(), 'batch'));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->uploadError = $exception->getMessage() ?: 'Batch upload failed.';

            return null;
        }
    }

    public function saveMetadata(MediaClient $media, AdminSessionManager $session): mixed
    {
        if (! $this->selectedMediaId) {
            return null;
        }

        $validated = $this->validate();
        $payload = $this->metadataPayload($validated);
        $this->formError = null;

        try {
            $response = $media->update($this->token($session), $session->tokenType(), $this->selectedMediaId, $payload);
            $this->fillSelectedMedia($this->mapMedia(Arr::get($response, 'data', [])));
            $this->loadMedia($media, $session);

            session()->flash('status', 'Media metadata updated.');

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->formError = $exception->getMessage();

            throw ValidationException::withMessages($this->normalizeApiErrors($exception->errors()));
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->formError = $exception->getMessage() ?: 'Media metadata could not be saved.';

            return null;
        }
    }

    public function confirmDelete(int $mediaId): void
    {
        $media = $this->mediaRecord($mediaId);

        $this->deleteMediaId = $mediaId;
        $this->deleteMediaName = (string) ($media['original_filename'] ?? 'this asset');
        $this->deleteUsageCount = (int) ($media['usage_count'] ?? 0);
        $this->deleteUsage = $media['usage'] ?? [];
        $this->deleteBlocked = $this->deleteUsageCount > 0;
        $this->deleteDialogOpen = true;
        $this->pageError = null;
    }

    public function cancelDelete(): void
    {
        $this->deleteDialogOpen = false;
        $this->deleteMediaId = null;
        $this->deleteMediaName = '';
        $this->deleteUsageCount = 0;
        $this->deleteUsage = [];
        $this->deleteBlocked = false;
    }

    public function delete(MediaClient $media, AdminSessionManager $session): mixed
    {
        if (! $this->deleteMediaId) {
            return null;
        }

        if ($this->deleteBlocked) {
            $this->pageError = 'This asset is still in use and cannot be deleted.';

            return null;
        }

        try {
            $deletedMediaId = $this->deleteMediaId;

            $media->delete($this->token($session), $session->tokenType(), $deletedMediaId);

            session()->flash('status', 'Media asset deleted.');

            $this->cancelDelete();
            $this->loadMedia($media, $session);

            if ($this->selectedMediaId === $deletedMediaId) {
                $this->resetDetail();
            }

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            if ($exception->status() === 409) {
                $this->deleteUsageCount = (int) Arr::get($exception->meta(), 'usage_count', $this->deleteUsageCount);
                $this->deleteUsage = Arr::get($exception->errors(), 'usage', $this->deleteUsage);
                $this->deleteBlocked = true;
            }

            $this->pageError = $exception->getMessage() ?: 'Media deletion failed.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.media.index', [
            'mediaItems' => $this->visibleMedia(),
        ])->layout('layouts.admin', [
            'title' => 'Media Library',
        ]);
    }

    protected function loadMedia(MediaClient $media, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $media->index($this->token($session), $session->tokenType(), $this->mediaFilters());
            $this->media = collect(Arr::get($response, 'data', []))
                ->map(fn (array $item): array => $this->mapMedia($item))
                ->values()
                ->all();

            if ($this->selectedMediaId) {
                $freshSelected = collect($this->media)->firstWhere('id', $this->selectedMediaId);

                if ($freshSelected) {
                    $this->selectedMedia = array_replace($this->selectedMedia, $freshSelected);
                }
            }

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->media = [];
            $this->pageError = $exception->getMessage() ?: 'Media could not be loaded from the service API.';

            return null;
        }
    }

    protected function visibleMedia(): array
    {
        return collect($this->media)
            ->sortBy(
                fn (array $item): mixed => $this->sortValue($item),
                SORT_NATURAL,
                $this->sortDirection === 'desc'
            )
            ->values()
            ->all();
    }

    protected function mediaFilters(): array
    {
        return [
            'search' => trim($this->search) !== '' ? trim($this->search) : null,
            'source_type' => $this->sourceTypeFilter !== 'all' ? $this->sourceTypeFilter : null,
            'status' => $this->statusFilter !== 'all' ? $this->statusFilter : null,
            'used' => match ($this->usageFilter) {
                'used' => 1,
                'unused' => 0,
                default => null,
            },
            'is_image' => match ($this->imageFilter) {
                'images' => 1,
                'non-images' => 0,
                default => null,
            },
        ];
    }

    protected function mapMedia(array $media): array
    {
        $filename = (string) Arr::get($media, 'original_filename', 'Untitled asset');
        $mimeType = (string) Arr::get($media, 'mime_type', '');
        $updatedAtRaw = Arr::get($media, 'updated_at');
        $createdAtRaw = Arr::get($media, 'created_at');

        return [
            'id' => Arr::get($media, 'id'),
            'original_filename' => $filename,
            'source_type' => Arr::get($media, 'source_type', 'uploaded'),
            'source_url' => Arr::get($media, 'source_url'),
            'attribution_text' => Arr::get($media, 'attribution_text'),
            'mime_type' => $mimeType,
            'file_size_bytes' => (int) Arr::get($media, 'file_size_bytes', 0),
            'width' => Arr::get($media, 'width'),
            'height' => Arr::get($media, 'height'),
            'alt_text' => Arr::get($media, 'alt_text'),
            'caption' => Arr::get($media, 'caption'),
            'url' => Arr::get($media, 'url'),
            'status' => Arr::get($media, 'status', 'ready'),
            'usage_count' => (int) Arr::get($media, 'usage_count', 0),
            'usage' => Arr::get($media, 'usage', []),
            'is_image' => str_starts_with($mimeType, 'image/'),
            'extension' => strtolower((string) pathinfo($filename, PATHINFO_EXTENSION)),
            'created_at' => $this->formatTimestamp($createdAtRaw),
            'created_at_raw' => $createdAtRaw,
            'updated_at' => $this->formatTimestamp($updatedAtRaw),
            'updated_at_raw' => $updatedAtRaw,
            'file_size_label' => $this->formatBytes((int) Arr::get($media, 'file_size_bytes', 0)),
        ];
    }

    protected function fillSelectedMedia(array $media): void
    {
        $this->resetValidation();
        $this->selectedMediaId = $media['id'];
        $this->selectedMedia = $media;
        $this->altText = (string) ($media['alt_text'] ?? '');
        $this->caption = (string) ($media['caption'] ?? '');
        $this->sourceType = (string) ($media['source_type'] ?? 'uploaded');
        $this->sourceUrl = (string) ($media['source_url'] ?? '');
        $this->attributionText = (string) ($media['attribution_text'] ?? '');
    }

    protected function resetDetail(): void
    {
        $this->resetValidation();
        $this->drawerOpen = false;
        $this->selectedMediaId = null;
        $this->selectedMedia = [];
        $this->altText = '';
        $this->caption = '';
        $this->sourceType = 'uploaded';
        $this->sourceUrl = '';
        $this->attributionText = '';
        $this->formError = null;
    }

    protected function resetUploadForm(): void
    {
        $this->resetValidation();
        $this->singleFile = null;
        $this->batchFiles = [];
        $this->uploadAltText = '';
        $this->uploadCaption = '';
        $this->uploadSourceType = 'uploaded';
        $this->uploadSourceUrl = '';
        $this->uploadAttributionText = '';
        $this->uploadError = null;
        $this->uploadMode = 'single';
    }

    protected function metadataPayload(array $validated): array
    {
        return [
            'alt_text' => filled($validated['altText']) ? trim($validated['altText']) : null,
            'caption' => filled($validated['caption']) ? trim($validated['caption']) : null,
            'source_type' => $validated['sourceType'],
            'source_url' => filled($validated['sourceUrl']) ? trim($validated['sourceUrl']) : null,
            'attribution_text' => filled($validated['attributionText']) ? trim($validated['attributionText']) : null,
        ];
    }

    protected function singleUploadPayload(array $validated): array
    {
        return [
            'alt_text' => filled($validated['uploadAltText'] ?? null) ? trim((string) $validated['uploadAltText']) : null,
            'caption' => filled($validated['uploadCaption'] ?? null) ? trim((string) $validated['uploadCaption']) : null,
            'source_type' => $validated['uploadSourceType'],
            'source_url' => filled($validated['uploadSourceUrl'] ?? null) ? trim((string) $validated['uploadSourceUrl']) : null,
            'attribution_text' => filled($validated['uploadAttributionText'] ?? null) ? trim((string) $validated['uploadAttributionText']) : null,
        ];
    }

    protected function batchUploadPayload(array $validated): array
    {
        return [
            'source_type' => $validated['uploadSourceType'],
            'source_url' => filled($validated['uploadSourceUrl'] ?? null) ? trim((string) $validated['uploadSourceUrl']) : null,
            'attribution_text' => filled($validated['uploadAttributionText'] ?? null) ? trim((string) $validated['uploadAttributionText']) : null,
        ];
    }

    protected function normalizeApiErrors(array $errors): array
    {
        $mapped = [];

        foreach ($errors as $field => $messages) {
            $property = match ($field) {
                'alt_text' => 'altText',
                'source_type' => 'sourceType',
                'source_url' => 'sourceUrl',
                'attribution_text' => 'attributionText',
                default => $field,
            };

            $mapped[$property] = $messages;
        }

        return $mapped;
    }

    protected function normalizeUploadApiErrors(array $errors, string $mode): array
    {
        $mapped = [];

        foreach ($errors as $field => $messages) {
            $property = match (true) {
                $field === 'file' => 'singleFile',
                $field === 'files' => 'batchFiles',
                str_starts_with($field, 'files.') => preg_replace('/^files/', 'batchFiles', $field) ?: 'batchFiles',
                $field === 'alt_text' => 'uploadAltText',
                $field === 'caption' => 'uploadCaption',
                $field === 'source_type' => 'uploadSourceType',
                $field === 'source_url' => 'uploadSourceUrl',
                $field === 'attribution_text' => 'uploadAttributionText',
                default => $field,
            };

            if ($mode === 'batch' && in_array($property, ['uploadAltText', 'uploadCaption'], true)) {
                continue;
            }

            $mapped[$property] = $messages;
        }

        return $mapped;
    }

    protected function mediaRecord(int $mediaId): array
    {
        if ($this->selectedMediaId === $mediaId && $this->selectedMedia !== []) {
            return $this->selectedMedia;
        }

        return collect($this->media)->firstWhere('id', $mediaId) ?? [];
    }

    protected function sortValue(array $item): mixed
    {
        return match ($this->sortColumn) {
            'original_filename' => mb_strtolower($item['original_filename']),
            'source_type' => mb_strtolower($item['source_type']),
            'status' => mb_strtolower($item['status']),
            'usage_count' => $item['usage_count'],
            default => $item['created_at_raw'] ?? '',
        };
    }

    protected function formatTimestamp(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('M j, Y');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        $units = ['KB', 'MB', 'GB'];
        $value = $bytes / 1024;

        foreach ($units as $unit) {
            if ($value < 1024 || $unit === 'GB') {
                return round($value, 1).' '.$unit;
            }

            $value /= 1024;
        }

        return $bytes.' B';
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
