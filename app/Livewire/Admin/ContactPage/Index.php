<?php

namespace App\Livewire\Admin\ContactPage;

use App\Services\WideWebBlogApi\Clients\ContactPageClient;
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
    public array $hero = [];

    public array $contact_form = [];

    public array $contact_reasons = [];

    public array $seo = [];

    public ?string $updated_at = null;

    public array $updated_by = [];

    public ?string $pageError = null;

    public ?string $formError = null;

    public function mount(ContactPageClient $contactPage, AdminSessionManager $session): mixed
    {
        $this->fillFromResource([]);

        return $this->loadContactPage($contactPage, $session);
    }

    public function rules(): array
    {
        return [
            'hero.eyebrow' => ['required', 'string'],
            'hero.title' => ['required', 'string'],
            'hero.description' => ['required', 'string'],

            'contact_form.eyebrow' => ['required', 'string'],
            'contact_form.title' => ['required', 'string'],
            'contact_form.description' => ['required', 'string'],
            'contact_form.submit_label' => ['required', 'string'],
            'contact_form.success_message' => ['required', 'string'],

            'contact_reasons.items' => ['required', 'array'],
            'contact_reasons.items.*.title' => ['required', 'string'],
            'contact_reasons.items.*.description' => ['required', 'string'],

            'seo.meta_title' => ['required', 'string'],
            'seo.meta_description' => ['required', 'string'],
        ];
    }

    public function updated(string $property): void
    {
        if (
            str_starts_with($property, 'hero.')
            || str_starts_with($property, 'contact_form.')
            || str_starts_with($property, 'contact_reasons.')
            || str_starts_with($property, 'seo.')
        ) {
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

        $items[] = ['title' => '', 'description' => ''];
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

    public function save(ContactPageClient $contactPage, AdminSessionManager $session): mixed
    {
        $this->sanitizeSectionState();

        $validated = $this->validate();
        $this->formError = null;

        try {
            $response = $contactPage->update($this->token($session), $session->tokenType(), $this->payload($validated));
            $this->fillFromResource(Arr::get($response, 'data', []));
            session()->flash('status', 'Contact page updated.');

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->formError = $exception->getMessage();

            throw ValidationException::withMessages($exception->errors());
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->formError = $exception->getMessage() ?: 'Contact page changes could not be saved.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.contact-page.index', [
            'sectionSummary' => $this->sectionSummary(),
        ])->layout('layouts.admin', [
            'title' => 'Contact Page',
        ]);
    }

    protected function loadContactPage(ContactPageClient $contactPage, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $contactPage->show($this->token($session), $session->tokenType());
            $this->fillFromResource(Arr::get($response, 'data', []));

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'Contact page data could not be loaded.';

            return null;
        }
    }

    protected function fillFromResource(array $data): void
    {
        $this->hero = array_replace($this->defaultHero(), $this->ensureMap(Arr::get($data, 'hero')));
        $this->contact_form = array_replace($this->defaultContactForm(), $this->ensureMap(Arr::get($data, 'contact_form')));
        $this->contact_reasons = array_replace($this->defaultContactReasons(), $this->ensureMap(Arr::get($data, 'contact_reasons')));
        $this->seo = array_replace($this->defaultSeo(), $this->ensureMap(Arr::get($data, 'seo')));
        $this->updated_at = $this->formatTimestamp(Arr::get($data, 'updated_at'));
        $this->updated_by = is_array(Arr::get($data, 'updated_by')) ? Arr::get($data, 'updated_by') : [];

        $this->hero = $this->normalizeSimpleSection($this->hero, ['eyebrow', 'title', 'description']);
        $this->contact_form = $this->normalizeSimpleSection($this->contact_form, ['eyebrow', 'title', 'description', 'submit_label', 'success_message']);
        $this->contact_reasons = [
            'items' => is_array($this->contact_reasons['items'] ?? null) ? array_values($this->contact_reasons['items']) : [],
        ];
        $this->seo = $this->normalizeSimpleSection($this->seo, ['meta_title', 'meta_description']);
    }

    protected function defaultHero(): array
    {
        return [
            'eyebrow' => '',
            'title' => '',
            'description' => '',
        ];
    }

    protected function defaultContactForm(): array
    {
        return [
            'eyebrow' => '',
            'title' => '',
            'description' => '',
            'submit_label' => '',
            'success_message' => '',
        ];
    }

    protected function defaultContactReasons(): array
    {
        return [
            'items' => [],
        ];
    }

    protected function defaultSeo(): array
    {
        return [
            'meta_title' => '',
            'meta_description' => '',
        ];
    }

    protected function normalizeSimpleSection(array $section, array $keys): array
    {
        $normalized = [];

        foreach ($keys as $key) {
            $normalized[$key] = (string) ($section[$key] ?? '');
        }

        return $normalized;
    }

    protected function ensureMap(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    protected function sanitizeSectionState(): void
    {
        $this->contact_reasons['items'] = collect(Arr::get($this->contact_reasons, 'items', []))
            ->map(fn (mixed $item): array => [
                'title' => is_array($item) ? trim((string) ($item['title'] ?? '')) : '',
                'description' => is_array($item) ? trim((string) ($item['description'] ?? '')) : '',
            ])
            ->values()
            ->all();
    }

    protected function payload(array $validated): array
    {
        return [
            'hero' => $this->requiredStrings($validated['hero']),
            'contact_form' => $this->requiredStrings($validated['contact_form']),
            'contact_reasons' => [
                'items' => collect(Arr::get($validated, 'contact_reasons.items', []))
                    ->map(fn (array $item): array => [
                        'title' => trim((string) ($item['title'] ?? '')),
                        'description' => trim((string) ($item['description'] ?? '')),
                    ])
                    ->values()
                    ->all(),
            ],
            'seo' => $this->requiredStrings($validated['seo']),
        ];
    }

    protected function requiredStrings(array $items): array
    {
        return collect($items)
            ->map(fn (mixed $value): string => trim((string) $value))
            ->all();
    }

    protected function sectionSummary(): array
    {
        return [
            ['label' => 'Hero', 'detail' => $this->hero['title'] ?: 'No hero title yet'],
            ['label' => 'Contact Form', 'detail' => $this->contact_form['title'] ?: 'No form title yet'],
            ['label' => 'Contact Reasons', 'detail' => count($this->contact_reasons['items']).' reason items'],
            ['label' => 'SEO', 'detail' => $this->seo['meta_title'] ?: 'No meta title yet'],
        ];
    }

    protected function allowsListField(string $section, string $field): bool
    {
        return "{$section}.{$field}" === 'contact_reasons.items';
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
