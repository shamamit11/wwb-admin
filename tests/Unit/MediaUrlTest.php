<?php

namespace Tests\Unit;

use App\Support\Media\MediaUrl;
use Tests\TestCase;

class MediaUrlTest extends TestCase
{
    public function test_it_prefixes_relative_media_paths_with_configured_base_url(): void
    {
        config()->set('widewebblog.media.base_url', 'https://media.widewebblog.com');

        $resolved = app(MediaUrl::class)->resolve('/2026/06/asset.webp');

        $this->assertSame('https://media.widewebblog.com/2026/06/asset.webp', $resolved);
    }

    public function test_it_leaves_absolute_media_urls_untouched(): void
    {
        config()->set('widewebblog.media.base_url', 'https://media.widewebblog.com');

        $resolved = app(MediaUrl::class)->resolve('https://cdn.example.com/asset.webp');

        $this->assertSame('https://cdn.example.com/asset.webp', $resolved);
    }
}
