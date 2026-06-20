<?php

namespace App\Livewire\Admin\AboutPage;

use App\Services\WideWebBlogApi\Clients\AboutPageClient;
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

    public array $mission_section = [];

    public array $stats_section = [];

    public array $values_section = [];

    public array $team_section = [];

    public array $seo = [];

    public ?string $updated_at = null;

    public array $updated_by = [];

    public ?string $pageError = null;

    public ?string $formError = null;

    public function mount(AboutPageClient $aboutPage, AdminSessionManager $session): mixed
    {
        $this->fillFromResource([]);

        return $this->loadAboutPage($aboutPage, $session);
    }

    public function rules(): array
    {
        return [
            'hero.eyebrow' => ['nullable', 'string', 'max:120'],
            'hero.title' => ['nullable', 'string', 'max:255'],
            'hero.description' => ['nullable', 'string', 'max:2000'],
            'hero.media_url' => ['nullable', 'url', 'max:500'],
            'hero.media_alt' => ['nullable', 'string', 'max:255'],

            'mission_section.title' => ['nullable', 'string', 'max:255'],
            'mission_section.description' => ['nullable', 'string', 'max:3000'],
            'mission_section.quote' => ['nullable', 'string', 'max:1000'],

            'stats_section.items' => ['required', 'array'],
            'stats_section.items.*.label' => ['required', 'string', 'max:120'],
            'stats_section.items.*.value' => ['required', 'string', 'max:120'],

            'values_section.title' => ['nullable', 'string', 'max:255'],
            'values_section.items' => ['required', 'array'],
            'values_section.items.*.icon' => ['nullable', 'string', 'max:80'],
            'values_section.items.*.title' => ['required', 'string', 'max:120'],
            'values_section.items.*.description' => ['required', 'string', 'max:1000'],

            'team_section.title' => ['nullable', 'string', 'max:255'],
            'team_section.description' => ['nullable', 'string', 'max:2000'],
            'team_section.primary_cta_label' => ['nullable', 'string', 'max:120'],
            'team_section.primary_cta_url' => ['nullable', 'url', 'max:500'],
            'team_section.members' => ['required', 'array'],
            'team_section.members.*.name' => ['required', 'string', 'max:120'],
            'team_section.members.*.role' => ['required', 'string', 'max:160'],
            'team_section.members.*.image_url' => ['nullable', 'url', 'max:500'],
            'team_section.members.*.image_alt' => ['nullable', 'string', 'max:255'],

            'seo.meta_title' => ['nullable', 'string', 'max:255'],
            'seo.meta_description' => ['nullable', 'string', 'max:320'],
        ];
    }

    public function updated(string $property): void
    {
        if (
            str_starts_with($property, 'hero.')
            || str_starts_with($property, 'mission_section.')
            || str_starts_with($property, 'stats_section.')
            || str_starts_with($property, 'values_section.')
            || str_starts_with($property, 'team_section.')
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

    public function save(AboutPageClient $aboutPage, AdminSessionManager $session): mixed
    {
        $this->sanitizeSectionState();

        $validated = $this->validate();
        $this->formError = null;

        try {
            $response = $aboutPage->update($this->token($session), $session->tokenType(), $this->payload($validated));
            $this->fillFromResource(Arr::get($response, 'data', []));
            session()->flash('status', 'About page updated.');

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->formError = $exception->getMessage();

            throw ValidationException::withMessages($exception->errors());
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->formError = $exception->getMessage() ?: 'About page changes could not be saved.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.about-page.index', [
            'sectionSummary' => $this->sectionSummary(),
        ])->layout('layouts.admin', [
            'title' => 'About Us',
        ]);
    }

    protected function loadAboutPage(AboutPageClient $aboutPage, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $aboutPage->show($this->token($session), $session->tokenType());
            $this->fillFromResource(Arr::get($response, 'data', []));

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'About page data could not be loaded.';

            return null;
        }
    }

    protected function fillFromResource(array $data): void
    {
        $this->hero = array_replace($this->defaultHero(), $this->ensureMap(Arr::get($data, 'hero')));
        $this->mission_section = array_replace($this->defaultMissionSection(), $this->ensureMap(Arr::get($data, 'mission_section')));
        $this->stats_section = array_replace($this->defaultStatsSection(), $this->ensureMap(Arr::get($data, 'stats_section')));
        $this->values_section = array_replace($this->defaultValuesSection(), $this->ensureMap(Arr::get($data, 'values_section')));
        $this->team_section = array_replace($this->defaultTeamSection(), $this->ensureMap(Arr::get($data, 'team_section')));
        $this->seo = array_replace($this->defaultSeo(), $this->ensureMap(Arr::get($data, 'seo')));
        $this->updated_at = $this->formatTimestamp(Arr::get($data, 'updated_at'));
        $this->updated_by = is_array(Arr::get($data, 'updated_by')) ? Arr::get($data, 'updated_by') : [];

        $this->hero = $this->normalizeHero($this->hero);
        $this->mission_section = $this->normalizeMissionSection($this->mission_section);
        $this->stats_section = $this->normalizeStatsSection($this->stats_section);
        $this->values_section = $this->normalizeValuesSection($this->values_section);
        $this->team_section = $this->normalizeTeamSection($this->team_section);
        $this->seo = $this->normalizeSeo($this->seo);
    }

    protected function defaultHero(): array
    {
        return [
            'eyebrow' => '',
            'title' => '',
            'description' => '',
            'media_url' => '',
            'media_alt' => '',
        ];
    }

    protected function defaultMissionSection(): array
    {
        return [
            'title' => '',
            'description' => '',
            'quote' => '',
        ];
    }

    protected function defaultStatsSection(): array
    {
        return [
            'items' => [],
        ];
    }

    protected function defaultValuesSection(): array
    {
        return [
            'title' => '',
            'items' => [],
        ];
    }

    protected function defaultTeamSection(): array
    {
        return [
            'title' => '',
            'description' => '',
            'primary_cta_label' => '',
            'primary_cta_url' => '',
            'members' => [],
        ];
    }

    protected function defaultSeo(): array
    {
        return [
            'meta_title' => '',
            'meta_description' => '',
        ];
    }

    protected function normalizeHero(array $section): array
    {
        return [
            'eyebrow' => (string) ($section['eyebrow'] ?? ''),
            'title' => (string) ($section['title'] ?? ''),
            'description' => (string) ($section['description'] ?? ''),
            'media_url' => (string) ($section['media_url'] ?? ''),
            'media_alt' => (string) ($section['media_alt'] ?? ''),
        ];
    }

    protected function normalizeMissionSection(array $section): array
    {
        return [
            'title' => (string) ($section['title'] ?? ''),
            'description' => (string) ($section['description'] ?? ''),
            'quote' => (string) ($section['quote'] ?? ''),
        ];
    }

    protected function normalizeStatsSection(array $section): array
    {
        return [
            'items' => is_array($section['items'] ?? null) ? array_values($section['items']) : [],
        ];
    }

    protected function normalizeValuesSection(array $section): array
    {
        return [
            'title' => (string) ($section['title'] ?? ''),
            'items' => is_array($section['items'] ?? null) ? array_values($section['items']) : [],
        ];
    }

    protected function normalizeTeamSection(array $section): array
    {
        return [
            'title' => (string) ($section['title'] ?? ''),
            'description' => (string) ($section['description'] ?? ''),
            'primary_cta_label' => (string) ($section['primary_cta_label'] ?? ''),
            'primary_cta_url' => (string) ($section['primary_cta_url'] ?? ''),
            'members' => is_array($section['members'] ?? null) ? array_values($section['members']) : [],
        ];
    }

    protected function normalizeSeo(array $section): array
    {
        return [
            'meta_title' => (string) ($section['meta_title'] ?? ''),
            'meta_description' => (string) ($section['meta_description'] ?? ''),
        ];
    }

    protected function ensureMap(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    protected function sanitizeSectionState(): void
    {
        $this->stats_section['items'] = $this->sanitizeStatsItems(Arr::get($this->stats_section, 'items', []));
        $this->values_section['items'] = $this->sanitizeValuesItems(Arr::get($this->values_section, 'items', []));
        $this->team_section['members'] = $this->sanitizeTeamMembers(Arr::get($this->team_section, 'members', []));
    }

    protected function payload(array $validated): array
    {
        return [
            'hero' => $this->nullableStrings($validated['hero']),
            'mission_section' => $this->nullableStrings($validated['mission_section']),
            'stats_section' => [
                'items' => collect(Arr::get($validated, 'stats_section.items', []))
                    ->map(fn (array $item): array => [
                        'label' => trim((string) ($item['label'] ?? '')),
                        'value' => trim((string) ($item['value'] ?? '')),
                    ])
                    ->values()
                    ->all(),
            ],
            'values_section' => [
                'title' => $this->nullableString(Arr::get($validated, 'values_section.title')),
                'items' => collect(Arr::get($validated, 'values_section.items', []))
                    ->map(fn (array $item): array => [
                        'icon' => $this->nullableString($item['icon'] ?? null),
                        'title' => trim((string) ($item['title'] ?? '')),
                        'description' => trim((string) ($item['description'] ?? '')),
                    ])
                    ->values()
                    ->all(),
            ],
            'team_section' => [
                'title' => $this->nullableString(Arr::get($validated, 'team_section.title')),
                'description' => $this->nullableString(Arr::get($validated, 'team_section.description')),
                'primary_cta_label' => $this->nullableString(Arr::get($validated, 'team_section.primary_cta_label')),
                'primary_cta_url' => $this->nullableString(Arr::get($validated, 'team_section.primary_cta_url')),
                'members' => collect(Arr::get($validated, 'team_section.members', []))
                    ->map(fn (array $item): array => [
                        'name' => trim((string) ($item['name'] ?? '')),
                        'role' => trim((string) ($item['role'] ?? '')),
                        'image_url' => $this->nullableString($item['image_url'] ?? null),
                        'image_alt' => $this->nullableString($item['image_alt'] ?? null),
                    ])
                    ->values()
                    ->all(),
            ],
            'seo' => $this->nullableStrings($validated['seo']),
        ];
    }

    protected function nullableStrings(array $items): array
    {
        return collect($items)
            ->map(fn (mixed $value): mixed => is_string($value) ? $this->nullableString($value) : $value)
            ->all();
    }

    protected function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    protected function sanitizeStatsItems(array $items): array
    {
        return collect($items)
            ->map(fn (mixed $item): array => [
                'label' => is_array($item) ? trim((string) ($item['label'] ?? '')) : '',
                'value' => is_array($item) ? trim((string) ($item['value'] ?? '')) : '',
            ])
            ->values()
            ->all();
    }

    protected function sanitizeValuesItems(array $items): array
    {
        return collect($items)
            ->map(fn (mixed $item): array => [
                'icon' => is_array($item) ? trim((string) ($item['icon'] ?? '')) : '',
                'title' => is_array($item) ? trim((string) ($item['title'] ?? '')) : '',
                'description' => is_array($item) ? trim((string) ($item['description'] ?? '')) : '',
            ])
            ->values()
            ->all();
    }

    protected function sanitizeTeamMembers(array $items): array
    {
        return collect($items)
            ->map(fn (mixed $item): array => [
                'name' => is_array($item) ? trim((string) ($item['name'] ?? '')) : '',
                'role' => is_array($item) ? trim((string) ($item['role'] ?? '')) : '',
                'image_url' => is_array($item) ? trim((string) ($item['image_url'] ?? '')) : '',
                'image_alt' => is_array($item) ? trim((string) ($item['image_alt'] ?? '')) : '',
            ])
            ->values()
            ->all();
    }

    protected function sectionSummary(): array
    {
        return [
            ['label' => 'Hero', 'detail' => $this->hero['title'] ?: 'No hero title yet'],
            ['label' => 'Mission', 'detail' => $this->mission_section['title'] ?: 'No mission title yet'],
            ['label' => 'Stats', 'detail' => count($this->stats_section['items']).' stat items'],
            ['label' => 'Values', 'detail' => count($this->values_section['items']).' value items'],
            ['label' => 'Team', 'detail' => count($this->team_section['members']).' team members'],
            ['label' => 'SEO', 'detail' => $this->seo['meta_title'] ?: 'No meta title yet'],
        ];
    }

    protected function allowsListField(string $section, string $field): bool
    {
        return in_array("{$section}.{$field}", [
            'stats_section.items',
            'values_section.items',
            'team_section.members',
        ], true);
    }

    protected function defaultListItem(string $section, string $field): array
    {
        return match ("{$section}.{$field}") {
            'stats_section.items' => ['label' => '', 'value' => ''],
            'values_section.items' => ['icon' => '', 'title' => '', 'description' => ''],
            'team_section.members' => ['name' => '', 'role' => '', 'image_url' => '', 'image_alt' => ''],
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
