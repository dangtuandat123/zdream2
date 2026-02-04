<?php

namespace App\Livewire;

use App\Models\Inspiration;
use Livewire\Component;

class InspirationsGallery extends Component
{
    // We only need to maintain state for pagination
    public $page = 1;
    public $perPage = 30;
    public $hasMore = true;
    public $loadedIds = [];

    // Initial data to pass to Alpine
    public $initialInspirations = [];

    public function mount()
    {
        $this->loadMore(true);
    }

    public function loadMore($initial = false)
    {
        if (!$this->hasMore && !$initial) {
            return [];
        }

        $newInspirations = Inspiration::where('is_active', true)
            ->whereNotIn('id', $this->loadedIds)
            ->inRandomOrder()
            ->take($this->perPage)
            ->get();

        if ($newInspirations->isEmpty()) {
            $this->hasMore = false;
            return [];
        }

        $items = [];
        foreach ($newInspirations as $inspiration) {
            $this->loadedIds[] = $inspiration->id;
            $items[] = [
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

        if ($initial) {
            $this->initialInspirations = $items;
        }

        return $items;
    }

    public function render()
    {
        return view('livewire.inspirations-gallery');
    }
}
