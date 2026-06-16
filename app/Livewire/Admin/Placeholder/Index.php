<?php

namespace App\Livewire\Admin\Placeholder;

use Livewire\Component;

class Index extends Component
{
    public string $eyebrow = 'Module';

    public string $pageTitle = 'Module placeholder';

    public string $pageDescription = 'This area is scaffolded and ready for the next implementation phase.';

    public string $moduleLabel = 'Module';

    public string $moduleDescription = 'This admin area has been added to the route and navigation skeleton.';

    public string $primaryActionLabel = 'Implementation Pending';

    public string $primaryActionHint = 'Service-backed workflows and data loading will be wired in a later task.';

    public array $nextSteps = [];

    public bool $roadmap = false;

    public function mount(): void
    {
        $defaults = request()->route()?->defaults ?? [];

        $this->eyebrow = (string) ($defaults['eyebrow'] ?? $this->eyebrow);
        $this->pageTitle = (string) ($defaults['pageTitle'] ?? $this->pageTitle);
        $this->pageDescription = (string) ($defaults['pageDescription'] ?? $this->pageDescription);
        $this->moduleLabel = (string) ($defaults['moduleLabel'] ?? $this->moduleLabel);
        $this->moduleDescription = (string) ($defaults['moduleDescription'] ?? $this->moduleDescription);
        $this->primaryActionLabel = (string) ($defaults['primaryActionLabel'] ?? $this->primaryActionLabel);
        $this->primaryActionHint = (string) ($defaults['primaryActionHint'] ?? $this->primaryActionHint);
        $this->nextSteps = array_values($defaults['nextSteps'] ?? $this->nextSteps);
        $this->roadmap = (bool) ($defaults['roadmap'] ?? $this->roadmap);
    }

    public function render()
    {
        return view('livewire.admin.placeholder.index')
            ->layout('layouts.admin', [
                'title' => $this->moduleLabel,
                'pageTitle' => $this->pageTitle,
                'pageDescription' => $this->pageDescription,
            ]);
    }
}
