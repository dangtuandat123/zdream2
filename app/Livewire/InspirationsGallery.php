<?php

namespace App\Livewire;

use App\Models\Inspiration;
use Livewire\Component;

class InspirationsGallery extends Component
{
    public $inspirations = [];
    public $page = 1;
    public $perPage = 20;
    public $hasMore = true;
    public $loadedIds = [];

    public function mount()
    {
        $this->loadMore();
    }

    public function loadMore()
    {
        if (!$this->hasMore) {
            return;
        }

        $newInspirations = Inspiration::where('is_active', true)
            ->whereNotIn('id', $this->loadedIds)
            ->inRandomOrder()
            ->take($this->perPage)
            ->get();

        if ($newInspirations->isEmpty()) {
            $this->hasMore = false;
            return;
        }

        foreach ($newInspirations as $inspiration) {
            $this->loadedIds[] = $inspiration->id;
            $this->inspirations[] = [
                'id' => $inspiration->id,
                'image_url' => $inspiration->image_url,
                'prompt' => $inspiration->prompt,
                'ref_images' => $inspiration->ref_images,
            ];
        }

        $this->page++;

        // Check if there are more to load
        $remainingCount = Inspiration::where('is_active', true)
            ->whereNotIn('id', $this->loadedIds)
            ->count();

        $this->hasMore = $remainingCount > 0;
    }

    public function render()
    {
        return view('livewire.inspirations-gallery');
    }
}
