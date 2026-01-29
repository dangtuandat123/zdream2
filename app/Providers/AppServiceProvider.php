<?php

namespace App\Providers;

use App\Support\Livewire\ComponentRegistryStub;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (!class_exists(\Livewire\Mechanisms\ComponentRegistry::class)) {
            class_alias(ComponentRegistryStub::class, \Livewire\Mechanisms\ComponentRegistry::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.zdream');
        Paginator::defaultSimpleView('vendor.pagination.zdream-simple');
    }
}
