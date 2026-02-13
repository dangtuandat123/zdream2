<x-app-layout>
    <x-slot name="title">AI Studio - Tạo ảnh AI | {{ App\Models\Setting::get('site_name', 'ZDream') }}</x-slot>

    @push('meta')
        <meta name="description"
            content="Tạo ảnh AI chất lượng cao với {{ App\Models\Setting::get('site_name', 'ZDream') }}. Nhập mô tả, chọn model AI và tỉ lệ khung hình để tạo ảnh độc đáo trong vài giây.">
        <meta property="og:title" content="AI Studio - Tạo ảnh AI | {{ App\Models\Setting::get('site_name', 'ZDream') }}">
        <meta property="og:description"
            content="Tạo ảnh AI chất lượng cao từ mô tả văn bản. Hỗ trợ nhiều model AI và tỉ lệ khung hình.">
        <meta property="og:type" content="website">
    @endpush

    @livewire('text-to-image', ['initialPrompt' => $initialPrompt ?? ''])
</x-app-layout>