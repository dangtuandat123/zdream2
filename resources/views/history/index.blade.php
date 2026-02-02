<x-app-layout>
    <x-slot name="title">Lịch sử ảnh - {{ App\Models\Setting::get('site_name', 'ZDream') }}</x-slot>

    <style>
        .image-card-hover .hover-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        .image-card-hover:hover .hover-overlay {
            opacity: 1;
        }
        .image-card-hover:hover img {
            transform: scale(1.05);
        }
        .history-filters .filter-select {
            width: 100%;
            height: 44px;
            padding: 0 36px 0 12px;
            border-radius: 0.75rem;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.9);
            font-size: 0.875rem;
            line-height: 44px;
            appearance: none;
            background-image: linear-gradient(45deg, transparent 50%, rgba(255,255,255,0.6) 50%), linear-gradient(135deg, rgba(255,255,255,0.6) 50%, transparent 50%);
            background-position: calc(100% - 18px) 18px, calc(100% - 12px) 18px;
            background-size: 6px 6px, 6px 6px;
            background-repeat: no-repeat;
        }
        .history-filters .filter-select:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(168,85,247,0.35);
            border-color: rgba(168,85,247,0.6);
        }
        .history-filters .filter-reset {
            height: 44px;
            padding: 0 14px;
            border-radius: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .history-filters {
            position: relative;
            z-index: 20;
        }
        .history-filters select {
            position: relative;
            z-index: 21;
        }
    </style>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8 overflow-x-hidden">
        
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-[#d3d6db]">Lịch sử ảnh</h1>
                <p class="text-white/50 text-sm">Các ảnh bạn đã tạo</p>
            </div>
            <a href="{{ route('home') }}" class="px-4 py-2 rounded-xl bg-purple-500/20 border border-purple-500/30 text-purple-400 text-sm hover:bg-purple-500/30 transition-colors inline-flex items-center gap-2">
                <i class="fa-solid fa-plus" style="font-size: 12px;"></i>
                <span>Tạo ảnh mới</span>
            </a>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-3 mb-6 history-filters">
            <form method="GET" action="{{ route('history.index') }}" id="filter-form" class="w-full sm:w-auto flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3">
                <div class="w-full sm:w-52">
                    <select name="status" class="filter-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Thất bại</option>
                    </select>
                </div>
                @if(isset($styles) && $styles->isNotEmpty())
                    <div class="w-full sm:w-52">
                        <select name="style_id" class="filter-select">
                            <option value="">Tất cả styles</option>
                            @foreach($styles as $style)
                                <option value="{{ $style->id }}" {{ request('style_id') == $style->id ? 'selected' : '' }}>{{ $style->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if(request('status') || request('style_id'))
                    <a href="{{ route('history.index') }}" class="filter-reset bg-red-500/10 border border-red-500/30 text-red-400 text-sm hover:bg-red-500/20 transition-colors">
                        <i class="fa-solid fa-times mr-1"></i> Xóa lọc
                    </a>
                @endif
            </form>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 flex items-center gap-2">
                <i class="fa-solid fa-check-circle w-5 h-5"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 flex items-center gap-2">
                <i class="fa-solid fa-exclamation-circle w-5 h-5"></i>
                {{ session('error') }}
            </div>
        @endif

        @if($images->isEmpty())
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-20">
                <div class="w-20 h-20 rounded-full bg-[#1b1c21] border border-[#2a2b30] flex items-center justify-center mb-4">
                    <i class="fa-solid fa-images text-3xl text-white/20"></i>
                </div>
                <h3 class="text-lg font-medium text-white/80 mb-2">Chưa có ảnh nào</h3>
                <p class="text-sm text-white/40 mb-6 text-center">Bạn chưa tạo ảnh nào. Hãy chọn style và bắt đầu!</p>
                <a href="{{ route('home') }}" class="px-6 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-[#d3d6db] font-semibold hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all inline-flex items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>Khám phá Styles</span>
                </a>
            </div>
        @else
            <!-- Image Grid -->
            @php 
                $completedImages = $images->where('status', 'completed')->whereNotNull('storage_path')->values();
                $imageData = $completedImages->map(fn($img) => [
                    'url' => $img->image_url,
                    'id' => $img->id,
                    'download' => route('history.download', $img),
                    'delete' => route('history.destroy', $img),
                ])->toArray();
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @php $galleryIndex = 0; @endphp
                @foreach($images as $image)
                    <div class="relative bg-[#1b1c21] border border-[#2a2b30] rounded-xl overflow-hidden hover:border-purple-500/30 transition-all">
                        <!-- Image -->
                        <div class="aspect-[3/4] relative overflow-hidden image-card-hover">
                            @if($image->status === 'completed' && $image->storage_path)
                                <button 
                                    onclick="openLightboxWithActions({{ $galleryIndex }}, {{ json_encode($imageData) }})"
                                    class="w-full h-full cursor-pointer block"
                                >
                                    <img src="{{ $image->image_url }}" alt="Generated Image" 
                                         class="w-full h-full object-cover transition-transform duration-300" loading="lazy" decoding="async" fetchpriority="low">
                                </button>
                                <!-- Hover overlay với icon mắt -->
                                <div class="hover-overlay">
                                    <i class="fa-solid fa-eye text-[#d3d6db] text-3xl"></i>
                                </div>
                                @php $galleryIndex++; @endphp
                            @elseif($image->status === 'processing')
                                <div class="w-full h-full flex items-center justify-center bg-black/20">
                                    <div class="text-center">
                                        <i class="fa-solid fa-spinner fa-spin text-2xl text-purple-400 mb-2"></i>
                                        <p class="text-xs text-white/40">Đang xử lý...</p>
                                    </div>
                                </div>
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-black/20">
                                    <div class="text-center px-2">
                                        <i class="fa-solid fa-exclamation-triangle text-2xl text-red-400 mb-2"></i>
                                        <p class="text-xs text-white/40">Thất bại</p>
                                        @if($image->error_message)
                                            <p class="text-[10px] text-red-400/70 mt-1 line-clamp-2" title="{{ $image->error_message }}">{{ Str::limit($image->error_message, 50) }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Info & Actions -->
                        <div class="p-2 sm:p-3">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-xs sm:text-sm font-medium text-white/80 truncate flex-1">{{ $image->style?->name ?? 'Style đã xóa' }}</p>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full whitespace-nowrap flex-shrink-0
                                    {{ $image->status === 'completed' ? 'bg-green-500/20 text-green-400' : '' }}
                                    {{ $image->status === 'processing' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                                    {{ $image->status === 'failed' ? 'bg-red-500/20 text-red-400' : '' }}
                                ">
                                    {{ $image->status === 'completed' ? '✓' : ($image->status === 'processing' ? '⏳' : '✗') }}
                                </span>
                            </div>
                            <span class="text-[10px] text-white/40">{{ $image->created_at->format('d/m H:i') }}</span>
                            
                            @if($image->status === 'completed' && $image->storage_path)
                                <div class="flex gap-2 mt-2">
                                    <a href="{{ route('history.download', $image) }}"
                                       class="flex-1 py-1.5 sm:py-2 rounded-lg bg-purple-500/20 text-purple-300 text-[10px] sm:text-xs font-medium text-center hover:bg-purple-500/30 transition-colors inline-flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-download"></i> <span class="hidden sm:inline">Tải</span>
                                    </a>
                                    <form method="POST" action="{{ route('history.destroy', $image) }}" 
                                          onsubmit="return confirm('Bạn có chắc muốn xóa ảnh này?')"
                                          class="flex-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full py-1.5 sm:py-2 rounded-lg bg-red-500/20 text-red-300 text-[10px] sm:text-xs font-medium hover:bg-red-500/30 transition-colors inline-flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-trash"></i> <span class="hidden sm:inline">Xóa</span>
                                        </button>
                                    </form>
                                </div>
                            @elseif($image->status === 'failed' && $image->style)
                                <!-- Retry button for failed images -->
                                <div class="flex gap-2 mt-2">
                                    <a href="{{ route('studio.show', $image->style->slug) }}"
                                       class="flex-1 py-1.5 sm:py-2 rounded-lg bg-purple-500/20 text-purple-300 text-[10px] sm:text-xs font-medium text-center hover:bg-purple-500/30 transition-colors inline-flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-redo"></i> <span class="hidden sm:inline">Tạo lại</span>
                                    </a>
                                    <form method="POST" action="{{ route('history.destroy', $image) }}" 
                                          onsubmit="return confirm('Bạn có chắc muốn xóa ảnh này?')"
                                          class="flex-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full py-1.5 sm:py-2 rounded-lg bg-red-500/20 text-red-300 text-[10px] sm:text-xs font-medium hover:bg-red-500/30 transition-colors inline-flex items-center justify-center gap-1">
                                            <i class="fa-solid fa-trash"></i> <span class="hidden sm:inline">Xóa</span>
                                        </button>
                                    </form>
                                </div>
                            @endif
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
