<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class HeaderCredits extends Component
{
    public int $credits = 0;

    public function mount(): void
    {
        $this->refreshCredits();
    }

    #[On('imageGenerated')]
    #[On('creditsUpdated')]
    public function refreshCredits(): void
    {
        $this->credits = Auth::check() ? (int) Auth::user()->refresh()->credits : 0;
    }

    public function render()
    {
        return view('livewire.header-credits');
    }
}
