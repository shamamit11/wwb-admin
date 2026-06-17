<?php

use App\Http\Controllers\Admin\Auth\LogoutController;
use App\Livewire\Admin\Auth\Login as LoginScreen;
use App\Livewire\Admin\Categories\Index as CategoryIndex;
use App\Livewire\Admin\Dashboard\Index as DashboardIndex;
use App\Livewire\Admin\Homepage\Index as HomepageIndex;
use App\Livewire\Admin\KnowledgeBase\Editor as KnowledgeBaseEditor;
use App\Livewire\Admin\KnowledgeBase\Index as KnowledgeBaseIndex;
use App\Livewire\Admin\Media\Index as MediaIndex;
use App\Livewire\Admin\Placeholder\Index as PlaceholderIndex;
use App\Livewire\Admin\Pages\Editor as PageEditor;
use App\Livewire\Admin\Pages\Index as PageIndex;
use App\Livewire\Admin\Posts\Editor as PostEditor;
use App\Livewire\Admin\Posts\Index as PostIndex;
use App\Livewire\Admin\Seo\Index as SeoIndex;
use App\Livewire\Admin\Settings\Index as SettingsIndex;
use App\Livewire\Admin\Tags\Index as TagIndex;
use App\Livewire\Admin\Templates\Index as TemplateIndex;
use Illuminate\Support\Facades\Route;

Route::middleware('admin.guest')->group(function (): void {
    Route::get(config('widewebblog.auth.login_path', '/login'), LoginScreen::class)->name('login');
});

Route::view('/forbidden', 'auth.forbidden')->name('auth.forbidden');

Route::middleware('admin.auth')->group(function (): void {
    Route::get(config('widewebblog.auth.home_path', '/'), DashboardIndex::class)->name('dashboard');
    Route::post(config('widewebblog.auth.logout_path', '/logout'), LogoutController::class)->name('logout');

    Route::get('/posts', PostIndex::class)->name('posts.index');

    Route::get('/posts/create', PostEditor::class)->name('posts.create');

    Route::get('/posts/{post}/edit', PostEditor::class)->name('posts.edit');

    Route::get('/pages', PageIndex::class)->name('pages.index');

    Route::get('/pages/create', PageEditor::class)->name('pages.create');

    Route::get('/pages/{page}/edit', PageEditor::class)->name('pages.edit');

    Route::get('/homepage', HomepageIndex::class)->name('homepage.index');

    Route::get('/categories', CategoryIndex::class)->name('categories.index');

    Route::get('/tags', TagIndex::class)->name('tags.index');

    Route::get('/media', MediaIndex::class)->name('media.index');

    Route::get('/templates', TemplateIndex::class)->name('templates.index');

    Route::get('/knowledge-base', KnowledgeBaseIndex::class)->name('knowledge-base.index');

    Route::get('/knowledge-base/create', KnowledgeBaseEditor::class)->name('knowledge-base.create');

    Route::get('/knowledge-base/{knowledgeBaseEntry}/edit', KnowledgeBaseEditor::class)->name('knowledge-base.edit');

    Route::get('/seo', SeoIndex::class)->name('seo.index');

    Route::get('/settings', SettingsIndex::class)->name('settings.index');

    Route::get('/topic-queue', PlaceholderIndex::class)
        ->defaults('eyebrow', 'Roadmap')
        ->defaults('pageTitle', 'Topic queue is visible as a roadmap placeholder.')
        ->defaults('pageDescription', 'This navigation target is intentionally explicit so future editorial workflow work has a reserved place in the admin shell.')
        ->defaults('moduleLabel', 'Topic Queue')
        ->defaults('moduleDescription', 'No service API endpoints exist for topic queue management in the current phase, so this module remains planning-only.')
        ->defaults('primaryActionLabel', 'Workflow Placeholder')
        ->defaults('primaryActionHint', 'Do not imply approval flows or list data until the service contract exists.')
        ->defaults('nextSteps', [
            'Confirm future topic queue API coverage in the service contract.',
            'Choose a list/detail/action pattern consistent with lightweight editorial review.',
            'Add real routes and interactions only once backend support is available.',
        ])
        ->defaults('roadmap', true)
        ->name('topic-queue.index');

    Route::get('/ai-jobs', PlaceholderIndex::class)
        ->defaults('eyebrow', 'Roadmap')
        ->defaults('pageTitle', 'AI jobs are visible as a roadmap placeholder.')
        ->defaults('pageDescription', 'The route exists now only to reserve navigation structure for later monitoring and review workflows.')
        ->defaults('moduleLabel', 'AI Jobs')
        ->defaults('moduleDescription', 'No AI job endpoints exist in the current service API phase, so this area remains intentionally non-operational.')
        ->defaults('primaryActionLabel', 'Monitoring Placeholder')
        ->defaults('primaryActionHint', 'Keep the distinction between completed, failed, and review-needed work for later implementation.')
        ->defaults('nextSteps', [
            'Confirm future AI job endpoints and list/detail requirements.',
            'Revisit whether a table plus detail drawer pattern still fits once APIs exist.',
            'Only surface job actions after contract-backed behavior is available.',
        ])
        ->defaults('roadmap', true)
        ->name('ai-jobs.index');
});
