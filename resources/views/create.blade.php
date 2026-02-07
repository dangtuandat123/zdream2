<x-app-layout>
    <x-slot name="title">AI Studio - Tạo ảnh AI | {{ App\Models\Setting::get('site_name', 'ZDream') }}</x-slot>

    @livewire('text-to-image', ['initialPrompt' => $initialPrompt ?? ''])
</x-app-layout>