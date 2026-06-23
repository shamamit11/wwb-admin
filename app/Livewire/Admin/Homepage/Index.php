<?php

namespace App\Livewire\Admin\Homepage;

use App\Services\WideWebBlogApi\Clients\HomepageClient;
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

    public array $featured_editorial = [];

    public array $guide_section = [];

    public array $topic_section = [];

    public array $promo_section = [];

    public array $newsletter_section = [];

    public array $seo = [];

    public ?string $updated_at = null;

    public array $updated_by = [];

    public ?string $pageError = null;

    public ?string $formError = null;

    public function mount(HomepageClient $homepage, AdminSessionManager $session): mixed
    {
        $this->fillFromResource([]);

        return $this->loadHomepage($homepage, $session);
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

        $items[] = '';
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

    public function addStat(): void
    {
        $stats = Arr::get($this->promo_section, 'stats', []);
        $stats[] = ['label' => '', 'value' => ''];
        $this->promo_section['stats'] = array_values($stats);
    }

    public function removeStat(int $index): void
    {
        $stats = Arr::get($this->promo_section, 'stats', []);

        if (! array_key_exists($index, $stats)) {
            return;
        }

        unset($stats[$index]);
        $this->promo_section['stats'] = array_values($stats);
    }

    public function moveStat(int $index, string $direction): void
    {
        $stats = Arr::get($this->promo_section, 'stats', []);
        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if (! array_key_exists($index, $stats) || ! array_key_exists($swapIndex, $stats)) {
            return;
        }

        [$stats[$index], $stats[$swapIndex]] = [$stats[$swapIndex], $stats[$index]];
        $this->promo_section['stats'] = array_values($stats);
    }

    public function save(HomepageClient $homepage, AdminSessionManager $session): mixed
    {
        $this->sanitizeSectionState();
        $this->formError = null;
        $this->resetValidation();

        try {
            $response = $homepage->update($this->token($session), $session->tokenType(), $this->payload());
            $this->fillFromResource(Arr::get($response, 'data', []));
            session()->flash('status', 'Homepage updated.');

            return null;
        } catch (WideWebBlogApiValidationException $exception) {
            $this->formError = $exception->getMessage();

            throw ValidationException::withMessages($exception->errors());
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->formError = $exception->getMessage() ?: 'Homepage changes could not be saved.';

            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.homepage.index', [
            'sectionSummary' => $this->sectionSummary(),
        ])->layout('layouts.admin', [
            'title' => 'Homepage',
        ]);
    }

    protected function loadHomepage(HomepageClient $homepage, AdminSessionManager $session): mixed
    {
        $this->pageError = null;

        try {
            $response = $homepage->show($this->token($session), $session->tokenType());
            $this->fillFromResource(Arr::get($response, 'data', []));

            return null;
        } catch (WideWebBlogApiAuthenticationException) {
            return $this->expireSession($session);
        } catch (WideWebBlogApiAuthorizationException) {
            return $this->forbidden($session);
        } catch (WideWebBlogApiException $exception) {
            $this->pageError = $exception->getMessage() ?: 'Homepage data could not be loaded.';

            return null;
        }
    }

    protected function fillFromResource(array $data): void
    {
        $this->hero = array_replace($this->defaultHero(), $this->ensureMap(Arr::get($data, 'hero')));
        $this->featured_editorial = array_replace($this->defaultAutomaticSection(3), $this->ensureMap(Arr::get($data, 'featured_editorial')));
        $this->guide_section = array_replace($this->defaultAutomaticSection(4), $this->ensureMap(Arr::get($data, 'guide_section')));
        $this->topic_section = array_replace($this->defaultTopicSection(), $this->ensureMap(Arr::get($data, 'topic_section')));
        $this->promo_section = array_replace($this->defaultPromoSection(), $this->ensureMap(Arr::get($data, 'promo_section')));
        $this->newsletter_section = array_replace($this->defaultNewsletterSection(), $this->ensureMap(Arr::get($data, 'newsletter_section')));
        $this->seo = array_replace($this->defaultSeo(), $this->ensureMap(Arr::get($data, 'seo')));
        $this->updated_at = $this->formatTimestamp(Arr::get($data, 'updated_at'));
        $this->updated_by = is_array(Arr::get($data, 'updated_by')) ? Arr::get($data, 'updated_by') : [];

        $this->hero['primary_cta_url'] = (string) ($this->hero['primary_cta_url'] ?? '');
        $this->hero['secondary_cta_url'] = (string) ($this->hero['secondary_cta_url'] ?? '');
        $this->hero['media_url'] = (string) ($this->hero['media_url'] ?? '');
        $this->featured_editorial = $this->normalizeAutomaticSection($this->featured_editorial, 3);
        $this->guide_section = $this->normalizeAutomaticSection($this->guide_section, 4);
        $this->topic_section = $this->normalizeTopicSection($this->topic_section);
        $this->promo_section = $this->normalizePromoSection($this->promo_section);
        $this->newsletter_section = $this->normalizeNewsletterSection($this->newsletter_section);
        $this->promo_section['primary_cta_url'] = (string) ($this->promo_section['primary_cta_url'] ?? '');
    }

    protected function defaultHero(): array
    {
        return [
            'eyebrow' => '',
            'title' => '',
            'description' => '',
            'primary_cta_label' => '',
            'primary_cta_url' => '',
            'secondary_cta_label' => '',
            'secondary_cta_url' => '',
            'media_url' => '',
            'media_alt' => '',
        ];
    }

    protected function defaultAutomaticSection(int $limit): array
    {
        return [
            'title' => '',
            'description' => '',
            'limit' => $limit,
        ];
    }

    protected function defaultTopicSection(): array
    {
        return [
            'title' => '',
            'description' => '',
        ];
    }

    protected function defaultPromoSection(): array
    {
        return [
            'enabled' => true,
            'eyebrow' => '',
            'title' => '',
            'description' => '',
            'bullet_points' => [''],
            'primary_cta_label' => '',
            'primary_cta_url' => '',
            'stats' => [
                ['label' => '', 'value' => ''],
            ],
        ];
    }

    protected function defaultNewsletterSection(): array
    {
        return [
            'enabled' => true,
            'title' => '',
            'description' => '',
        ];
    }

    protected function defaultSeo(): array
    {
        return [
            'meta_title' => '',
            'meta_description' => '',
        ];
    }

    protected function normalizeAutomaticSection(array $section, int $defaultLimit): array
    {
        return [
            'title' => (string) ($section['title'] ?? ''),
            'description' => (string) ($section['description'] ?? ''),
            'limit' => is_numeric($section['limit'] ?? null) ? (int) $section['limit'] : $defaultLimit,
        ];
    }

    protected function normalizeTopicSection(array $section): array
    {
        return [
            'title' => (string) ($section['title'] ?? ''),
            'description' => (string) ($section['description'] ?? ''),
        ];
    }

    protected function normalizePromoSection(array $section): array
    {
        $stats = is_array($section['stats'] ?? null) ? array_values($section['stats']) : [];

        if ($stats === []) {
            $stats = [['label' => '', 'value' => '']];
        }

        return [
            'enabled' => (bool) ($section['enabled'] ?? true),
            'eyebrow' => (string) ($section['eyebrow'] ?? ''),
            'title' => (string) ($section['title'] ?? ''),
            'description' => (string) ($section['description'] ?? ''),
            'bullet_points' => is_array($section['bullet_points'] ?? null) ? array_values($section['bullet_points']) : [''],
            'primary_cta_label' => (string) ($section['primary_cta_label'] ?? ''),
            'primary_cta_url' => (string) ($section['primary_cta_url'] ?? ''),
            'stats' => $stats,
        ];
    }

    protected function normalizeNewsletterSection(array $section): array
    {
        return [
            'enabled' => (bool) ($section['enabled'] ?? true),
            'title' => (string) ($section['title'] ?? ''),
            'description' => (string) ($section['description'] ?? ''),
        ];
    }

    protected function ensureMap(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    protected function sanitizeSectionState(): void
    {
        $this->promo_section['bullet_points'] = $this->sanitizeStringList(Arr::get($this->promo_section, 'bullet_points', []));
        $this->promo_section['stats'] = $this->sanitizeStats(Arr::get($this->promo_section, 'stats', []));
    }

    protected function payload(): array
    {
        $hero = $this->nullableStrings($this->hero);
        $hero['media_url'] = $this->normalizeAbsoluteHttpUrl($hero['media_url'] ?? null);

        return [
            'hero' => $hero,
            'featured_editorial' => [
                'title' => $this->nullableString(Arr::get($this->featured_editorial, 'title')),
                'description' => $this->nullableString(Arr::get($this->featured_editorial, 'description')),
                'limit' => (int) Arr::get($this->featured_editorial, 'limit', 3),
            ],
            'guide_section' => [
                'title' => $this->nullableString(Arr::get($this->guide_section, 'title')),
                'description' => $this->nullableString(Arr::get($this->guide_section, 'description')),
                'limit' => (int) Arr::get($this->guide_section, 'limit', 4),
            ],
            'topic_section' => [
                'title' => $this->nullableString(Arr::get($this->topic_section, 'title')),
                'description' => $this->nullableString(Arr::get($this->topic_section, 'description')),
            ],
            'promo_section' => [
                'enabled' => (bool) Arr::get($this->promo_section, 'enabled', false),
                'eyebrow' => $this->nullableString(Arr::get($this->promo_section, 'eyebrow')),
                'title' => $this->nullableString(Arr::get($this->promo_section, 'title')),
                'description' => $this->nullableString(Arr::get($this->promo_section, 'description')),
                'bullet_points' => Arr::get($this->promo_section, 'bullet_points', []),
                'primary_cta_label' => $this->nullableString(Arr::get($this->promo_section, 'primary_cta_label')),
                'primary_cta_url' => $this->nullableString(Arr::get($this->promo_section, 'primary_cta_url')),
                'stats' => Arr::get($this->promo_section, 'stats', []),
            ],
            'newsletter_section' => [
                'enabled' => (bool) Arr::get($this->newsletter_section, 'enabled', false),
                'title' => $this->nullableString(Arr::get($this->newsletter_section, 'title')),
                'description' => $this->nullableString(Arr::get($this->newsletter_section, 'description')),
            ],
            'seo' => $this->nullableStrings($this->seo),
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

    protected function normalizeAbsoluteHttpUrl(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '' || preg_match('/^https?:\/\//i', $trimmed) !== 1) {
            return $trimmed === '' ? null : $trimmed;
        }

        if (! preg_match('~^(https?://)([^/?#]+)([^?#]*)(\?[^#]*)?(#.*)?$~i', $trimmed, $matches)) {
            return $trimmed;
        }

        $path = $this->encodeUrlPath($matches[3] ?? '');

        return ($matches[1] ?? '')
            .($matches[2] ?? '')
            .$path
            .($matches[4] ?? '')
            .($matches[5] ?? '');
    }

    protected function encodeUrlPath(string $path): string
    {
        return implode('/', array_map(
            fn (string $segment): string => rawurlencode(rawurldecode($segment)),
            explode('/', $path)
        ));
    }

    protected function sanitizeStringList(array $items): array
    {
        return collect($items)
            ->map(fn (mixed $item): string => is_string($item) ? trim($item) : '')
            ->filter(fn (string $item): bool => $item !== '')
            ->values()
            ->all();
    }

    protected function sanitizeStats(array $items): array
    {
        return collect($items)
            ->map(function (mixed $item): array {
                return [
                    'label' => is_array($item) ? trim((string) ($item['label'] ?? '')) : '',
                    'value' => is_array($item) ? trim((string) ($item['value'] ?? '')) : '',
                ];
            })
            ->filter(fn (array $item): bool => $item['label'] !== '' || $item['value'] !== '')
            ->values()
            ->all();
    }

    protected function sectionSummary(): array
    {
        return [
            ['label' => 'Hero', 'detail' => $this->hero['title'] ?: 'No hero title yet'],
            ['label' => 'Featured Editorial', 'detail' => 'Automatic from featured published posts · '.$this->featured_editorial['limit'].' item limit'],
            ['label' => 'Recent Articles', 'detail' => 'Automatic from recent published posts · '.$this->guide_section['limit'].' item limit'],
            ['label' => 'Explore Core Topics', 'detail' => 'Automatic from all active categories'],
            ['label' => 'Promo Block', 'detail' => ($this->promo_section['enabled'] ? 'Enabled' : 'Disabled').' · '.count($this->promo_section['bullet_points']).' bullets'],
            ['label' => 'Newsletter', 'detail' => $this->newsletter_section['enabled'] ? 'Enabled' : 'Disabled'],
            ['label' => 'Homepage SEO', 'detail' => $this->seo['meta_title'] ?: 'No meta title yet'],
        ];
    }

    protected function allowsListField(string $section, string $field): bool
    {
        return in_array("{$section}.{$field}", [
            'promo_section.bullet_points',
        ], true);
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
