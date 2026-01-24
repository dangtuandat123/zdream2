<x-app-layout>
    <x-slot name="title">Quản lý ảnh - Admin | ZDream</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Quản lý ảnh đã tạo</h1>
                <p class="text-white/50 text-sm">{{ $stats['total'] }} ảnh | {{ $stats['completed'] }} hoàn thành | {{ $stats['failed'] }} thất bại</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 flex items-center gap-2">
                <i class="fa-solid fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 flex items-center gap-2">
                <i class="fa-solid fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
                <div class="text-white/50 text-sm mb-1">Tổng ảnh</div>
                <div class="text-2xl font-bold text-white">{{ number_format($stats['total']) }}</div>
            </div>
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
                <div class="text-white/50 text-sm mb-1">Hoàn thành</div>
                <div class="text-2xl font-bold text-green-400">{{ number_format($stats['completed']) }}</div>
            </div>
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
                <div class="text-white/50 text-sm mb-1">Thất bại</div>
                <div class="text-2xl font-bold text-red-400">{{ number_format($stats['failed']) }}</div>
            </div>
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
                <div class="text-white/50 text-sm mb-1">Hôm nay</div>
                <div class="text-2xl font-bold text-cyan-400">{{ number_format($stats['today']) }}</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4 mb-6">
            <form method="GET" action="{{ route('admin.images.index') }}" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Tìm theo user..."
                           class="w-full px-4 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                </div>
                <select name="status" class="px-4 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                    <option value="">Tất cả status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Thất bại</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                </select>
                <select name="style_id" class="px-4 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                    <option value="">Tất cả styles</option>
                    @foreach($styles as $style)
                        <option value="{{ $style->id }}" {{ request('style_id') == $style->id ? 'selected' : '' }}>{{ $style->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ request('from') }}" 
                       class="px-4 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                <button type="submit" class="px-4 py-2 rounded-lg bg-purple-500/20 border border-purple-500/30 text-purple-400 hover:bg-purple-500/30 transition-colors">
                    <i class="fa-solid fa-filter mr-1"></i> Lọc
                </button>
                @if(request()->hasAny(['search', 'status', 'style_id', 'from']))
                    <a href="{{ route('admin.images.index') }}" class="px-4 py-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/60 hover:text-white transition-colors">
                        Xóa filter
                    </a>
                @endif
            </form>
        </div>

        <!-- Image Grid -->
        @if($images->isEmpty())
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl text-center py-16">
                <i class="fa-solid fa-images text-4xl text-white/20 mb-4"></i>
                <p class="text-white/50">Không có ảnh nào</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($images as $image)
                    <div class="group relative bg-white/[0.03] border border-white/[0.08] rounded-xl overflow-hidden hover:border-purple-500/30 transition-all">
                        <div class="aspect-square relative">
                            @if($image->status === 'completed' && $image->image_url)
                                <img src="{{ $image->image_url }}" alt="" class="w-full h-full object-cover" loading="lazy">
                            @elseif($image->status === 'failed')
                                <div class="w-full h-full flex items-center justify-center bg-red-500/10">
                                    <i class="fa-solid fa-exclamation-triangle text-2xl text-red-400"></i>
                                </div>
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-white/[0.02]">
                                    <i class="fa-solid fa-spinner fa-spin text-xl text-white/30"></i>
                                </div>
                            @endif

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                <a href="{{ route('admin.images.show', $image) }}" class="p-2 rounded-lg bg-white/20 text-white hover:bg-white/30 transition-colors">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.images.destroy', $image) }}" 
                                      onsubmit="return confirm('Xác nhận xóa ảnh này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg bg-red-500/50 text-white hover:bg-red-500/70 transition-colors">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="p-2">
                            <div class="text-white/70 text-xs truncate">{{ $image->user->name ?? 'Unknown' }}</div>
                            <div class="flex items-center justify-between text-xs text-white/40">
                                <span>{{ $image->created_at->format('d/m') }}</span>
                                <span class="px-1.5 py-0.5 rounded {{ $image->status === 'completed' ? 'bg-green-500/20 text-green-400' : ($image->status === 'failed' ? 'bg-red-500/20 text-red-400' : 'bg-yellow-500/20 text-yellow-400') }}">
                                    {{ $image->status }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $images->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
