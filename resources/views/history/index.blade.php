<x-app-layout>
    <x-slot name="title">Lịch sử ảnh - {{ App\Models\Setting::get('site_name', 'ZDream') }}</x-slot>

    <livewire:history-browser />
</x-app-layout>