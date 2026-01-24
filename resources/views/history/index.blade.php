<x-app-layout>
    <x-slot name="title">Lịch sử ảnh - {{ App\Models\Setting::get('site_name', 'ZDream') }}</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Lịch sử ảnh</h1>
                <p class="text-white/50 text-sm">Các ảnh bạn đã tạo</p>
            </div>
            <a href="{{ route('home') }}" class="px-4 py-2 rounded-xl bg-purple-500/20 border border-purple-500/30 text-purple-400 text-sm hover:bg-purple-500/30 transition-colors inline-flex items-center gap-2">
                <i class="fa-solid fa-plus" style="font-size: 12px;"></i>
                <span>Tạo ảnh mới</span>
            </a>
        </div>

        @if($images->isEmpty())
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-20">
                <div class="w-20 h-20 rounded-full bg-white/[0.03] border border-white/[0.08] flex items-center justify-center mb-4">
                    <i class="fa-solid fa-images text-3xl text-white/20"></i>
                </div>
                <h3 class="text-lg font-medium text-white/80 mb-2">Chưa có ảnh nào</h3>
                <p class="text-sm text-white/40 mb-6 text-center">Bạn chưa tạo ảnh nào. Hãy chọn style và bắt đầu!</p>
                <a href="{{ route('home') }}" class="px-6 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all inline-flex items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>Khám phá Styles</span>
                </a>
            </div>
        @else
            <!-- Image Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($images as $image)
                    <div class="group relative bg-white/[0.03] border border-white/[0.08] rounded-xl overflow-hidden hover:border-purple-500/30 transition-all">
                        <!-- Image -->
                        <div class="aspect-[3/4] relative">
                            @if($image->status === 'completed' && $image->storage_path)
                                <img src="{{ $image->full_url }}" alt="Generated Image" 
                                     class="w-full h-full object-cover" loading="lazy">
                            @elseif($image->status === 'processing')
                                <div class="w-full h-full flex items-center justify-center bg-black/20">
                                    <div class="text-center">
                                        <i class="fa-solid fa-spinner fa-spin text-2xl text-purple-400 mb-2"></i>
                                        <p class="text-xs text-white/40">Đang xử lý...</p>
                                    </div>
                                </div>
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-black/20">
                                    <div class="text-center">
                                        <i class="fa-solid fa-exclamation-triangle text-2xl text-red-400 mb-2"></i>
                                        <p class="text-xs text-white/40">Thất bại</p>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Overlay on hover -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                                <div class="absolute bottom-3 left-3 right-3">
                                    @if($image->status === 'completed' && $image->full_url)
                                        <a href="{{ $image->full_url }}" target="_blank" 
                                           class="w-full py-2 rounded-lg bg-white/20 backdrop-blur-sm text-white text-xs font-medium text-center block hover:bg-white/30 transition-colors">
                                            <i class="fa-solid fa-download mr-1"></i> Tải xuống
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Info -->
                        <div class="p-3">
                            <p class="text-sm font-medium text-white/80 truncate">{{ $image->style->name ?? 'Unknown Style' }}</p>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-white/40">{{ $image->created_at->format('d/m/Y H:i') }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full 
                                    {{ $image->status === 'completed' ? 'bg-green-500/20 text-green-400' : '' }}
                                    {{ $image->status === 'processing' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                                    {{ $image->status === 'failed' ? 'bg-red-500/20 text-red-400' : '' }}
                                ">
                                    {{ $image->status === 'completed' ? 'Hoàn thành' : ($image->status === 'processing' ? 'Đang xử lý' : 'Thất bại') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $images->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
