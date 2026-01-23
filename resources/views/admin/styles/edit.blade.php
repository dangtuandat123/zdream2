<x-app-layout>
    <x-slot name="title">Sửa {{ $style->name }} - Admin | EZShot AI</x-slot>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center gap-4 mb-8">
                <a href="{{ route('admin.styles.index') }}" class="text-white/50 hover:text-white transition-colors">
                    ← Quay lại
                </a>
                <h1 class="text-2xl font-bold text-white/90">Sửa: {{ $style->name }}</h1>
            </div>

            @include('admin.styles._form', ['style' => $style])
        </div>
    </div>
</x-app-layout>
