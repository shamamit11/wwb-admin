<?php

use App\Http\Controllers\Admin\Auth\LogoutController;
use App\Livewire\Admin\AiPrompts\Index as AiPromptIndex;
use App\Livewire\Admin\AiPrompts\Show as AiPromptShow;
use App\Livewire\Admin\AboutPage\Index as AboutPageIndex;
use App\Livewire\Admin\ContactPage\Index as ContactPageIndex;
use App\Livewire\Admin\ContactSubmissions\Index as ContactSubmissionsIndex;
use App\Livewire\Admin\ContactSubmissions\Show as ContactSubmissionsShow;
use App\Livewire\Admin\Auth\Login as LoginScreen;
use App\Livewire\Admin\AiJobs\Index as AiJobIndex;
use App\Livewire\Admin\AiJobs\Show as AiJobShow;
use App\Livewire\Admin\Password\Index as PasswordIndex;
use App\Livewire\Admin\Categories\Index as CategoryIndex;
use App\Livewire\Admin\ContentBriefs\Index as ContentBriefIndex;
use App\Livewire\Admin\ContentBriefs\Show as ContentBriefShow;
use App\Livewire\Admin\Dashboard\Index as DashboardIndex;
use App\Livewire\Admin\Homepage\Index as HomepageIndex;
use App\Livewire\Admin\KnowledgeBase\Editor as KnowledgeBaseEditor;
use App\Livewire\Admin\KnowledgeBase\Index as KnowledgeBaseIndex;
use App\Livewire\Admin\Media\Index as MediaIndex;
use App\Livewire\Admin\Pages\Editor as PageEditor;
use App\Livewire\Admin\Pages\Index as PageIndex;
use App\Livewire\Admin\Posts\Editor as PostEditor;
use App\Livewire\Admin\Posts\Index as PostIndex;
use App\Livewire\Admin\Seo\Index as SeoIndex;
use App\Livewire\Admin\Settings\Index as SettingsIndex;
use App\Livewire\Admin\Tags\Index as TagIndex;
use App\Livewire\Admin\Templates\Index as TemplateIndex;
use App\Livewire\Admin\TopicQueue\Index as TopicQueueIndex;
use App\Livewire\Admin\TopicQueue\Show as TopicQueueShow;
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

    Route::get('/about-page', AboutPageIndex::class)->name('about-page.index');

    Route::get('/contact-page', ContactPageIndex::class)->name('contact-page.index');

    Route::get('/contact-submissions', ContactSubmissionsIndex::class)->name('contact-submissions.index');

    Route::get('/contact-submissions/{contactSubmission}', ContactSubmissionsShow::class)->name('contact-submissions.show');

    Route::get('/categories', CategoryIndex::class)->name('categories.index');

    Route::get('/tags', TagIndex::class)->name('tags.index');

    Route::get('/media', MediaIndex::class)->name('media.index');

    Route::get('/templates', TemplateIndex::class)->name('templates.index');

    Route::get('/knowledge-base', KnowledgeBaseIndex::class)->name('knowledge-base.index');

    Route::get('/knowledge-base/create', KnowledgeBaseEditor::class)->name('knowledge-base.create');

    Route::get('/knowledge-base/{knowledgeBaseEntry}/edit', KnowledgeBaseEditor::class)->name('knowledge-base.edit');

    Route::get('/seo', SeoIndex::class)->name('seo.index');

    Route::get('/settings', SettingsIndex::class)->name('settings.index');

    Route::get('/password', PasswordIndex::class)->name('password.index');

    Route::get('/topic-queue', TopicQueueIndex::class)->name('topic-queue.index');

    Route::get('/topic-queue/{topic}', TopicQueueShow::class)->name('topic-queue.show');

    Route::get('/content-briefs', ContentBriefIndex::class)->name('content-briefs.index');

    Route::get('/content-briefs/{contentBrief}', ContentBriefShow::class)->name('content-briefs.show');

    Route::get('/draft-review', PostIndex::class)->name('draft-review.index');

    Route::get('/draft-review/{post}', PostEditor::class)->name('draft-review.show');

    Route::get('/ai-prompts', AiPromptIndex::class)->name('ai-prompts.index');

    Route::get('/ai-prompts/create', AiPromptShow::class)->name('ai-prompts.create');

    Route::get('/ai-prompts/{aiPrompt}', AiPromptShow::class)->name('ai-prompts.show');

    Route::get('/ai-jobs', AiJobIndex::class)->name('ai-jobs.index');

    Route::get('/ai-jobs/{aiJob}', AiJobShow::class)->name('ai-jobs.show');
});
