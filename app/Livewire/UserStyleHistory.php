<?php

namespace App\Livewire;

use App\Models\GeneratedImage;
use App\Models\Style;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class UserStyleHistory extends Component
{
    public Style $style;

    public function mount(Style $style): void
    {
        $this->style = $style;
    }

    /**
     * Listen for image generated event to refresh
     */
    #[On('imageGenerated')]
    public function refreshHistory(): void
    {
        // Just re-render the component
    }

    public function render()
    {
        $userImages = Auth::check()
            ? GeneratedImage::where('user_id', Auth::id())
                ->where('style_id', $this->style->id)
                ->completed()
                ->latest()
                ->limit(6)
                ->get()
            : collect();

        return view('livewire.user-style-history', [
            'userImages' => $userImages,
        ]);
    }
}
