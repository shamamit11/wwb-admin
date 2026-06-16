<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class UiPrimitiveRenderTest extends TestCase
{
    public function test_button_variants_render_expected_states(): void
    {
        $button = Blade::render('<x-ui.button variant="secondary" size="sm">Save</x-ui.button>');
        $link = Blade::render('<x-ui.button as="a" href="/posts" disabled>Posts</x-ui.button>');

        $this->assertStringContainsString('bg-[var(--color-panel)]', $button);
        $this->assertStringContainsString('h-9 px-3 text-sm', $button);
        $this->assertStringContainsString('aria-disabled="true"', $link);
        $this->assertStringNotContainsString('href="/posts"', $link);
    }

    public function test_form_primitives_render_invalid_and_disabled_states(): void
    {
        $input = Blade::render('<x-ui.input invalid disabled placeholder="Email" />');
        $textarea = Blade::render('<x-ui.textarea invalid size="sm">Draft</x-ui.textarea>');
        $select = Blade::render('<x-ui.select invalid><option>Draft</option></x-ui.select>');

        $this->assertStringContainsString('aria-invalid="true"', $input);
        $this->assertStringContainsString('disabled:bg-[color-mix(in_srgb,var(--color-panel)_88%,var(--color-page))]', $input);
        $this->assertStringContainsString('min-h-24', $textarea);
        $this->assertStringContainsString('aria-invalid="true"', $select);
    }

    public function test_field_card_and_badge_render_configured_variants(): void
    {
        $field = Blade::render('<x-ui.field label="Email" for="email" hint="Use your work address" optional>Email</x-ui.field>');
        $card = Blade::render('<x-ui.card as="section" padding="lg">Body</x-ui.card>');
        $badge = Blade::render('<x-ui.badge tone="muted">Queued</x-ui.badge>');

        $this->assertStringContainsString('for="email"', $field);
        $this->assertStringContainsString('Optional', $field);
        $this->assertStringContainsString('<section', $card);
        $this->assertStringContainsString('p-8', $card);
        $this->assertStringContainsString('text-[var(--color-muted)]', $badge);
    }
}
