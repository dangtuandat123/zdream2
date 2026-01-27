<?php

namespace App\Support\Livewire;

use Livewire\LivewireManager;

class ComponentRegistryStub
{
    public function getClass(string $alias): ?string
    {
        try {
            return app(LivewireManager::class)->getClass($alias);
        } catch (\Throwable) {
            return null;
        }
    }
}
