<?php

namespace Martinoak\StatamicLinkChecker\Livewire;

use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class RunButton extends Component
{
    public bool $isRunning = false;

    public function run()
    {
        $this->isRunning = true;

        // Instead of running the command directly, redirect to the run route
        // This will show the loading state during the redirect
        return redirect()->route('link-checker.run');
    }

    public function render()
    {
        return view('link-checker::livewire.run-button');
    }
}
