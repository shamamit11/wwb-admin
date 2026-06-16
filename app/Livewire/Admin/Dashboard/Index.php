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
            ]);
    }
}
