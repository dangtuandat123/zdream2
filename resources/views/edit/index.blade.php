<x-app-layout>
    <x-slot name="title">Image Edit Studio - {{ App\Models\Setting::get('site_name', 'ZDream') }}</x-slot>

    <livewire:image-edit-studio />
</x-app-layout>