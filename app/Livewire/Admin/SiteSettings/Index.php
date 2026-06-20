<?php

namespace App\Livewire\Admin\SiteSettings;

use App\Services\WideWebBlogApi\Clients\SiteSettingsClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiValidationException;
use App\Support\Auth\AdminSessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Index extends Component
{
    public array $footer = [];

    public ?string $updated_at = null;

    public array $updated_by = [];

    public ?string $pageError = null;

    public ?string $formError = null;

    public function mount(SiteSettingsClient $siteSettings, AdminSessionManager $session): mixed
    {
        $this->fillFromResource([]);

        return $this->loadSiteSettings($siteSettings, $session);
    }

    public function rules(): array
    {
        return [
            'footer.brand_name' => ['nullable', 'string', 'max:120'],
            'footer.description' => ['nullable', 'string', 'max:2000'],

            'footer.social_links' => ['required', 'array'],
            'footer.social_links.*.label' => ['required', 'string', 'max:80'],
            'footer.social_links.*.url' => ['required', 'string', 'max:500'],
            'footer.social_links.*.icon' => ['nullable', 'string', 'max:80'],

            'footer.legal_links' => ['required', 'array'],
            'footer.legal_links.*.label' => ['required', 'string', 'max:120'],
            'footer.legal_links.*.slug' => ['nullable', 'string', 'max:190'],
            'footer.legal_links.*.url' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'footer.')) {
            $this->validateOnly($property);
        }
    }

    public function addListItem(string $section, string $field): void
    {
        if (! $this->allowsListField($section, $field)) {
            return;
        }

        $items = data_get($this, "{$section}.{$field}", []);

        if (! is_array($items)) {
            $items = [];
        }

        $items[] = $this->defaultListItem($section, $field);

        data_set($this, "{$section}.{$field}", array_values($items));
    }

    public function removeListItem(string $section, string $field, int $index): void
    {
        if (! $this->allowsListField($section, $field)) {
            return;
        }

        $items = data_get($this, "{$section}.{$field}", []);

        if (! is_array($items) || ! array_key_exists($index, $items)) {
            return;
        }

        unset($items[$index]);
        data_set($this, "{$section}.{$field}", array_values($items));
    }

    public function moveListItem(string $section, string $field, int $index, string $direction): void
    {
        if (! $this->allowsListField($section, $field)) {
            return;
        }

        $items = data_get($this, "{$section}.{$field}", []);

        if (! is_array($items)) {
            return;
        }

        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if (! array_key_exists($index, $items) || ! array_key_exists($swapIndex, $items)) {
            return;
        }

        [$items[$index], $items[$swapIndex]] = [$items[$swapIndex], $items[$index]];
        data_set($this, "{$section}.{$field}", array_values($items));
    }

    public function save(SiteSettingsClient $siteSettings, AdminSessionManager $session): mixed
    {
        $this->sanitizeSectionState();

        $validated = $this->validate();
        $this->formError = null;

        try {
            $response = $siteSettings->update($this->token($session), $session->tokenType(), $this->payload($validated));
            $this->fillFromResource(Arr::get($response, 'data', []));
            session()->flash('status', 'Site settings updated.');

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->formError = $exception->getMessage();

            throw ValidationException::withMessages($exception->errors());
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->formError = $exception->getMessage() ?: 'Site settings changes could not be saved.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.site-settings.index', [
            'sectionSummary' => $this->sectionSummary(),
        ])->layout('layouts.admin', [
            'title' => 'Site Settings',
        ]);
    }

    protected function loadSiteSettings(SiteSettingsClient $siteSettings, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $siteSettings->show($this->token($session), $session->tokenType());
            $this->fillFromResource(Arr::get($response, 'data', []));

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'Site settings could not be loaded.';

            return null;
        }
    }

    protected function fillFromResource(array $data): void
    {
        $this->footer = $this->normalizeFooter(array_replace($this->defaultFooter(), $this->ensureMap(Arr::get($data, 'footer'))));
        $this->updated_at = $this->formatTimestamp(Arr::get($data, 'updated_at'));
        $this->updated_by = is_array(Arr::get($data, 'updated_by')) ? Arr::get($data, 'updated_by') : [];
    }

    protected function defaultFooter(): array
    {
        return [
            'brand_name' => '',
            'description' => '',
            'social_links' => [],
            'legal_links' => [],
        ];
    }

    protected function normalizeFooter(array $footer): array
    {
        return [
            'brand_name' => (string) ($footer['brand_name'] ?? ''),
            'description' => (string) ($footer['description'] ?? ''),
            'social_links' => collect(Arr::get($footer, 'social_links', []))
                ->map(fn (mixed $item): array => [
                    'label' => is_array($item) ? (string) ($item['label'] ?? '') : '',
                    'url' => is_array($item) ? (string) ($item['url'] ?? '') : '',
                    'icon' => is_array($item) ? (string) ($item['icon'] ?? '') : '',
                ])
                ->values()
                ->all(),
            'legal_links' => collect(Arr::get($footer, 'legal_links', []))
                ->map(fn (mixed $item): array => [
                    'label' => is_array($item) ? (string) ($item['label'] ?? '') : '',
                    'slug' => is_array($item) ? (string) ($item['slug'] ?? '') : '',
                    'url' => is_array($item) ? (string) ($item['url'] ?? '') : '',
                ])
                ->values()
                ->all(),
        ];
    }

    protected function ensureMap(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    protected function sanitizeSectionState(): void
    {
        $this->footer['social_links'] = collect(Arr::get($this->footer, 'social_links', []))
            ->map(fn (mixed $item): array => [
                'label' => is_array($item) ? trim((string) ($item['label'] ?? '')) : '',
                'url' => is_array($item) ? trim((string) ($item['url'] ?? '')) : '',
                'icon' => is_array($item) ? trim((string) ($item['icon'] ?? '')) : '',
            ])
            ->values()
            ->all();

        $this->footer['legal_links'] = collect(Arr::get($this->footer, 'legal_links', []))
            ->map(fn (mixed $item): array => [
                'label' => is_array($item) ? trim((string) ($item['label'] ?? '')) : '',
                'slug' => is_array($item) ? trim((string) ($item['slug'] ?? '')) : '',
                'url' => is_array($item) ? trim((string) ($item['url'] ?? '')) : '',
            ])
            ->values()
            ->all();
    }

    protected function payload(array $validated): array
    {
        return [
            'footer' => [
                'brand_name' => $this->nullableString(Arr::get($validated, 'footer.brand_name')),
                'description' => $this->nullableString(Arr::get($validated, 'footer.description')),
                'social_links' => collect(Arr::get($validated, 'footer.social_links', []))
                    ->map(fn (array $item): array => [
                        'label' => trim((string) ($item['label'] ?? '')),
                        'url' => trim((string) ($item['url'] ?? '')),
                        'icon' => $this->nullableString($item['icon'] ?? null),
                    ])
                    ->values()
                    ->all(),
                'legal_links' => collect(Arr::get($validated, 'footer.legal_links', []))
                    ->map(fn (array $item): array => [
                        'label' => trim((string) ($item['label'] ?? '')),
                        'slug' => $this->nullableString($item['slug'] ?? null),
                        'url' => $this->nullableString($item['url'] ?? null),
                    ])
                    ->values()
                    ->all(),
            ],
        ];
    }

    protected function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    protected function sectionSummary(): array
    {
        return [
            ['label' => 'Brand', 'detail' => $this->footer['brand_name'] ?: 'No footer brand name yet'],
            ['label' => 'Description', 'detail' => $this->footer['description'] ?: 'No footer description yet'],
            ['label' => 'Social Links', 'detail' => count($this->footer['social_links']).' social links'],
            ['label' => 'Legal Links', 'detail' => count($this->footer['legal_links']).' legal links'],
        ];
    }

    protected function allowsListField(string $section, string $field): bool
    {
        return in_array("{$section}.{$field}", [
            'footer.social_links',
            'footer.legal_links',
        ], true);
    }

    protected function defaultListItem(string $section, string $field): array
    {
        return match ("{$section}.{$field}") {
            'footer.social_links' => ['label' => '', 'url' => '', 'icon' => ''],
            'footer.legal_links' => ['label' => '', 'slug' => '', 'url' => ''],
            default => [],
        };
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
