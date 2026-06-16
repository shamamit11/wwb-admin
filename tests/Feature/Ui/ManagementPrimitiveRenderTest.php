<?php

namespace Tests\Feature\Ui;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ManagementPrimitiveRenderTest extends TestCase
{
    public function test_table_and_empty_state_render_management_markup(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.table caption="Posts">
                <x-ui.table-head>
                    <tr>
                        <x-ui.table-heading sortable href="/posts?sort=title">Title</x-ui.table-heading>
                        <x-ui.table-heading align="right">Actions</x-ui.table-heading>
                    </tr>
                </x-ui.table-head>
                <x-ui.table-body>
                    <x-ui.table-empty colspan="2" title="No posts found" message="Adjust filters or create a new post." />
                </x-ui.table-body>
            </x-ui.table>
        BLADE);

        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('No posts found', $html);
        $this->assertStringContainsString('href="/posts?sort=title"', $html);
    }

    public function test_pagination_dialog_and_drawer_render_shared_structure(): void
    {
        $paginator = new LengthAwarePaginator(
            items: Collection::make([['id' => 1], ['id' => 2]]),
            total: 25,
            perPage: 2,
            currentPage: 2,
            options: ['path' => '/admin/posts']
        );

        $html = Blade::render(<<<'BLADE'
            <div>
                <x-ui.pagination :paginator="$paginator" />
                <x-ui.dialog open title="Delete post" description="This cannot be undone.">
                    Review impact.
                    <x-slot:actions>
                        <x-ui.button variant="secondary">Cancel</x-ui.button>
                    </x-slot:actions>
                </x-ui.dialog>
                <x-ui.drawer open title="Edit category" description="Keep list context visible.">
                    Drawer content
                </x-ui.drawer>
            </div>
        BLADE, ['paginator' => $paginator]);

        $this->assertStringContainsString('Page 2 of 13', $html);
        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('Delete post', $html);
        $this->assertStringContainsString('Drawer content', $html);
    }

    public function test_tabs_dropdown_filter_bar_and_domain_badges_render_consistently(): void
    {
        $html = Blade::render(<<<'BLADE'
            <div>
                <x-ui.tabs>
                    <x-ui.tabs-list>
                        <x-ui.tabs-trigger active href="/posts">Posts</x-ui.tabs-trigger>
                        <x-ui.tabs-trigger href="/templates">Templates</x-ui.tabs-trigger>
                    </x-ui.tabs-list>
                    <x-ui.tabs-panel>Panel</x-ui.tabs-panel>
                </x-ui.tabs>

                <x-admin.filter-bar>
                    <x-slot:search>
                        <x-admin.topbar-search placeholder="Search posts" />
                    </x-slot:search>
                    <x-slot:filters>
                        <x-ui.select size="sm">
                            <option>All statuses</option>
                        </x-ui.select>
                    </x-slot:filters>
                    <x-slot:actions>
                        <x-ui.button>Create post</x-ui.button>
                    </x-slot:actions>
                </x-admin.filter-bar>

                <x-admin.row-actions>
                    <x-ui.dropdown-item href="/posts/1/edit">Edit</x-ui.dropdown-item>
                    <x-ui.dropdown-item destructive>Delete</x-ui.dropdown-item>
                </x-admin.row-actions>

                <x-admin.status-badge status="published" />
                <x-admin.seo-score-badge :score="91" />
                <x-ui.skeleton lines="3" />
            </div>
        BLADE);

        $this->assertStringContainsString('role="tablist"', $html);
        $this->assertStringContainsString('Search posts', $html);
        $this->assertStringContainsString('Actions', $html);
        $this->assertStringContainsString('Published', $html);
        $this->assertStringContainsString('SEO 91 · Excellent', $html);
        $this->assertStringContainsString('animate-pulse', $html);
    }
}
