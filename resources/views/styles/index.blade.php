<x-app-layout>
    <x-slot name="title">Khám phá Styles - {{ App\Models\Setting::get('site_name', 'ZDream') }}</x-slot>

    <livewire:styles-browser />
</x-app-layout>
