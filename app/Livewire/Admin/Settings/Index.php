<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'tab', except: 'general')]
    public string $activeTab = 'general';

    public function mount(): void
    {
        if (! in_array($this->activeTab, $this->tabs(), true)) {
            $this->activeTab = 'general';
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.index', [
            'tabs' => $this->tabDefinitions(),
            'generalSummary' => $this->generalSummary(),
            'publishingSummary' => $this->publishingSummary(),
            'storageSummary' => $this->storageSummary(),
            'integrationSummary' => $this->integrationSummary(),
        ])->layout('layouts.admin', [
            'title' => 'Settings',
        ]);
    }

    protected function tabs(): array
    {
        return ['general', 'publishing', 'storage', 'ai', 'integrations'];
    }

    protected function tabDefinitions(): array
    {
        return [
            ['key' => 'general', 'label' => 'General'],
            ['key' => 'publishing', 'label' => 'Publishing'],
            ['key' => 'storage', 'label' => 'Storage'],
            ['key' => 'ai', 'label' => 'AI'],
            ['key' => 'integrations', 'label' => 'Integrations'],
        ];
    }

    protected function generalSummary(): array
    {
        return [
            ['label' => 'Application Name', 'value' => (string) config('app.name')],
            ['label' => 'Environment', 'value' => (string) config('app.env')],
            ['label' => 'Base URL', 'value' => (string) config('app.url')],
            ['label' => 'Locale', 'value' => (string) config('app.locale')],
            ['label' => 'Timezone', 'value' => (string) config('app.timezone')],
            ['label' => 'Home Path', 'value' => (string) config('widewebblog.auth.home_path')],
            ['label' => 'Login Path', 'value' => (string) config('widewebblog.auth.login_path')],
            ['label' => 'Logout Path', 'value' => (string) config('widewebblog.auth.logout_path')],
        ];
    }

    protected function publishingSummary(): array
    {
        return [
            'operational' => [
                'Post create, edit, publish, schedule, unpublish, and delete flows are service-backed.',
                'Per-entity SEO editing, score review, schema inspection, RSS, and sitemap utilities are available.',
                'Templates, categories, tags, media, and knowledge base management are available in dedicated modules.',
            ],
            'unsupported' => [
                'No sitewide publishing-defaults endpoint is currently defined in the service contract.',
                'No global editorial workflow or autosave settings are available yet.',
                'No secret-bearing notification or deployment settings should be edited from this screen.',
            ],
        ];
    }

    protected function storageSummary(): array
    {
        return [
            'default_disk' => (string) config('filesystems.default'),
            'media_base_url' => (string) config('widewebblog.media.base_url'),
            'public_url' => (string) config('filesystems.disks.public.url'),
            'disks' => collect((array) config('filesystems.disks'))
                ->map(fn (array $disk, string $name): array => [
                    'name' => $name,
                    'driver' => (string) ($disk['driver'] ?? 'unknown'),
                    'visibility' => isset($disk['visibility']) ? (string) $disk['visibility'] : 'internal',
                ])
                ->values()
                ->all(),
        ];
    }

    protected function integrationSummary(): array
    {
        return [
            'service_api' => [
                ['label' => 'Base URL', 'value' => (string) config('widewebblog.api.base_url')],
                ['label' => 'Timeout', 'value' => (string) config('widewebblog.api.timeout').' seconds'],
                ['label' => 'Connect Timeout', 'value' => (string) config('widewebblog.api.connect_timeout').' seconds'],
                ['label' => 'Retry Attempts', 'value' => (string) config('widewebblog.api.retry_times')],
                ['label' => 'Retry Sleep', 'value' => (string) config('widewebblog.api.retry_sleep_ms').' ms'],
            ],
            'session_bridge' => [
                ['label' => 'Auth Device Name', 'value' => (string) config('widewebblog.auth.device_name')],
                ['label' => 'Token Session Key', 'value' => (string) config('widewebblog.session.token_key')],
                ['label' => 'Token Type Key', 'value' => (string) config('widewebblog.session.token_type_key')],
                ['label' => 'Abilities Key', 'value' => (string) config('widewebblog.session.abilities_key')],
                ['label' => 'User Key', 'value' => (string) config('widewebblog.session.user_key')],
            ],
        ];
    }
}
