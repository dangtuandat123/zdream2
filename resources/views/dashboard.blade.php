<x-app-layout>
    <x-slot name="title">Dashboard - {{ App\Models\Setting::get('site_name', 'ZDream') }}</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        
        <!-- Welcome Header -->
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-white">
                Xin chào, <span class="bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">{{ auth()->user()->name }}</span>! 👋
            </h1>
            <p class="text-white/50 mt-1">Chào mừng bạn quay lại ZDream</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Credit Balance -->
            <div class="bg-gradient-to-br from-purple-500/10 to-pink-500/10 border border-purple-500/20 rounded-xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-gem text-cyan-400"></i>
                    <span class="text-white/50 text-sm">Số dư</span>
                </div>
                <p class="text-2xl font-bold text-white">{{ number_format(auth()->user()->credits, 0) }}</p>
                <p class="text-xs text-white/40">Xu</p>
            </div>

            <!-- Total Images -->
            @php
                $totalImages = auth()->user()->generatedImages()->count();
                $completedImages = auth()->user()->generatedImages()->where('status', 'completed')->count();
            @endphp
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-images text-purple-400"></i>
                    <span class="text-white/50 text-sm">Ảnh đã tạo</span>
                </div>
                <p class="text-2xl font-bold text-white">{{ number_format($completedImages) }}</p>
                <p class="text-xs text-white/40">ảnh thành công</p>
            </div>

            <!-- Processing -->
            @php
                $processingImages = auth()->user()->generatedImages()->where('status', 'processing')->count();
            @endphp
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-spinner text-yellow-400"></i>
                    <span class="text-white/50 text-sm">Đang xử lý</span>
                </div>
                <p class="text-2xl font-bold text-white">{{ number_format($processingImages) }}</p>
                <p class="text-xs text-white/40">ảnh</p>
            </div>

            <!-- Member Since -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-calendar text-green-400"></i>
                    <span class="text-white/50 text-sm">Thành viên</span>
                </div>
                <p class="text-2xl font-bold text-white">{{ auth()->user()->created_at->diffInDays(now()) }}</p>
                <p class="text-xs text-white/40">ngày</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <a href="{{ route('styles.index') }}" class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 border border-purple-500/30 rounded-xl p-5 hover:border-purple-500/50 hover:shadow-[0_8px_30px_rgba(168,85,247,0.2)] transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-wand-magic-sparkles text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white">Tạo ảnh mới</h3>
                        <p class="text-sm text-white/50">Khám phá các styles</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('history.index') }}" class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-5 hover:border-white/[0.15] hover:bg-white/[0.05] transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/[0.1] flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-clock-rotate-left text-white/70 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white">Lịch sử ảnh</h3>
                        <p class="text-sm text-white/50">Xem ảnh đã tạo</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('wallet.index') }}" class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-5 hover:border-white/[0.15] hover:bg-white/[0.05] transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/[0.1] flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-coins text-yellow-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white">Nạp Xu</h3>
                        <p class="text-sm text-white/50">Nạp thêm Xu để tạo ảnh</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Recent Images -->
        @php
            $recentImages = auth()->user()->generatedImages()
                ->where('status', 'completed')
                ->whereNotNull('storage_path')
                ->latest()
                ->take(4)
                ->get();
        @endphp
        
        @if($recentImages->isNotEmpty())
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-white/[0.05]">
                    <h2 class="font-semibold text-white flex items-center gap-2">
                        <i class="fa-solid fa-images text-purple-400"></i>
                        Ảnh gần đây
                    </h2>
                    <a href="{{ route('history.index') }}" class="text-sm text-purple-400 hover:text-purple-300 transition-colors">
                        Xem tất cả →
                    </a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4">
                    @foreach($recentImages as $image)
                        <a href="{{ route('history.index') }}" class="aspect-square rounded-xl overflow-hidden bg-white/[0.05] hover:ring-2 hover:ring-purple-500/50 transition-all">
                            <img src="{{ $image->image_url }}" alt="Generated Image" class="w-full h-full object-cover" loading="lazy" decoding="async" fetchpriority="low">
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-8 text-center">
                <i class="fa-solid fa-images text-4xl text-white/20 mb-4"></i>
                <h3 class="font-semibold text-white mb-2">Chưa có ảnh nào</h3>
                <p class="text-sm text-white/50 mb-4">Bắt đầu tạo ảnh AI đầu tiên của bạn!</p>
                <a href="{{ route('styles.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    Khám phá Styles
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
