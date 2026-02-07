<x-app-layout>
    <x-slot name="title">Tạo ảnh AI | {{ App\Models\Setting::get('site_name', 'ZDream') }}</x-slot>

    <div class="fixed inset-0 pt-16 sm:pt-20">
        @livewire('text-to-image', ['initialPrompt' => $initialPrompt ?? ''])
    </div>
</x-app-layout>