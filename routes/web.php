<?php

use App\Http\Controllers\Admin\Auth\LogoutController;
use App\Livewire\Admin\Auth\Login as LoginScreen;
use App\Livewire\Admin\Categories\Index as CategoryIndex;
use App\Livewire\Admin\Dashboard\Index as DashboardIndex;
use App\Livewire\Admin\Media\Index as MediaIndex;
use App\Livewire\Admin\Placeholder\Index as PlaceholderIndex;
use App\Livewire\Admin\Posts\Editor as PostEditor;
use App\Livewire\Admin\Posts\Index as PostIndex;
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

    Route::get('/categories', CategoryIndex::class)->name('categories.index');

    Route::get('/tags', TagIndex::class)->name('tags.index');

    Route::get('/media', MediaIndex::class)->name('media.index');

    Route::get('/templates', TemplateIndex::class)->name('templates.index');

    Route::get('/knowledge-base', PlaceholderIndex::class)
        ->defaults('eyebrow', 'Editorial Context')
        ->defaults('pageTitle', 'Knowledge base routing is scaffolded for searchable editorial context management.')
        ->defaults('pageDescription', 'This route makes the knowledge base a first-class admin section before its list and markdown editing workflows are implemented.')
        ->defaults('moduleLabel', 'Knowledge Base')
        ->defaults('moduleDescription', 'This module will support searchable entries, metadata editing, and post/topic linking once integrated.')
        ->defaults('primaryActionLabel', 'Create Knowledge Entry')
        ->defaults('primaryActionHint', 'Later work should keep the list searchable and the editing flow content-first.')
        ->defaults('nextSteps', [
            'Add the knowledge base API client and response mapping.',
            'Build the index screen using the shared search, filters, and row action patterns.',
            'Add the editing flow with markdown content and related-link support.',
        ])
        ->name('knowledge-base.index');

    Route::get('/seo', PlaceholderIndex::class)
        ->defaults('eyebrow', 'Search Optimization')
        ->defaults('pageTitle', 'SEO routing is ready for metadata and score management work.')
        ->defaults('pageDescription', 'The SEO section is scaffolded so per-entity metadata, score visibility, schema, and feed inspection can be layered in without moving routes again.')
        ->defaults('moduleLabel', 'SEO')
        ->defaults('moduleDescription', 'This module will cover metadata editing, score surfaces, schema inspection, sitemap review, and feed visibility.')
        ->defaults('primaryActionLabel', 'Review SEO Metadata')
        ->defaults('primaryActionHint', 'SEO work should stay operational and low-noise rather than overly technical.')
        ->defaults('nextSteps', [
            'Add the SEO API client for metadata, score, schema, sitemap, and RSS endpoints.',
            'Build operational SEO views with low-score surfacing and retry-safe error handling.',
            'Decide which SEO screens belong as list views versus entity-linked side panels.',
        ])
        ->name('seo.index');

    Route::get('/settings', PlaceholderIndex::class)
        ->defaults('eyebrow', 'Operations')
        ->defaults('pageTitle', 'Settings are scaffolded as a clear placeholder module.')
        ->defaults('pageDescription', 'Settings are intentionally present in the MVP route map even though most service-backed configuration work remains future scope.')
        ->defaults('moduleLabel', 'Settings')
        ->defaults('moduleDescription', 'This module will begin as a scoped operational placeholder and can expand as service-backed settings become concrete.')
        ->defaults('primaryActionLabel', 'Settings Placeholder')
        ->defaults('primaryActionHint', 'Avoid exposing unsupported sensitive configuration until the service contract explicitly allows it.')
        ->defaults('nextSteps', [
            'Decide which settings tabs are genuinely available in MVP versus roadmap-only.',
            'Add read-only operational summaries where service-backed writes do not exist yet.',
            'Expand into service-backed settings flows only after backend support is confirmed.',
        ])
        ->name('settings.index');

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
