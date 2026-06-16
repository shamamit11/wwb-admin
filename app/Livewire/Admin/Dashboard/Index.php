<?php

namespace App\Livewire\Admin\Dashboard;

use App\Support\Auth\AdminSessionManager;
use Livewire\Component;

class Index extends Component
{
    public array $currentAdmin = [];

    public function mount(AdminSessionManager $session): void
    {
        $this->currentAdmin = $session->user() ?? [];
    }

    public function render()
    {
        return view('livewire.admin.dashboard.index')
            ->layout('layouts.admin', [
                'title' => 'Dashboard',
                'pageTitle' => 'Laravel 13 is live. The admin shell is ready for API-driven editorial workflows.',
                'pageDescription' => 'This bootstrap replaces the stock Laravel welcome screen with an admin-oriented shell, installs Livewire, and establishes a Blade-native shadcn-inspired component system.',
            ]);
    }
}
