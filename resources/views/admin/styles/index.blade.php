<x-app-layout>
    <x-slot name="title">Quản lý Styles - Admin | ZDream</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <i class="fa-solid fa-palette w-6 h-6 text-purple-400"></i>
                    Quản lý Styles
                </h1>
                <p class="text-white/50 text-sm mt-1">{{ $styles->count() }} styles</p>
            </div>
            <a href="{{ route('admin.styles.create') }}" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-medium flex items-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
                <i class="fa-solid fa-plus w-4 h-4"></i> Tạo Style
            </a>
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

        @if($styles->isEmpty())
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl text-center py-16">
                <i class="fa-solid fa-palette text-4xl text-white/20 mb-4"></i>
                <p class="text-white/50 text-lg mb-2">Chưa có Style nào</p>
                <a href="{{ route('admin.styles.create') }}" class="inline-flex items-center gap-2 text-purple-400 hover:text-purple-300 transition-colors">
                    <i class="fa-solid fa-plus w-4 h-4"></i> Tạo style đầu tiên
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($styles as $style)
                    <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl overflow-hidden group hover:border-purple-500/30 transition-all">
                        <div class="aspect-video relative">
                            <img src="{{ $style->thumbnail }}" alt="{{ $style->name }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                            <div class="absolute bottom-3 left-3 right-3">
                                <h3 class="text-white font-semibold truncate">{{ $style->name }}</h3>
                            </div>
                            <div class="absolute top-3 right-3 flex items-center gap-2">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $style->is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                    {{ $style->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2 text-cyan-400">
                                    <i class="fa-solid fa-gem w-4 h-4"></i>
                                    <span class="font-semibold">{{ number_format($style->price, 0) }} Xu</span>
                                </div>
                                <span class="text-xs text-white/40">{{ $style->slug }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.styles.edit', $style) }}" class="flex-1 py-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/80 text-sm text-center hover:bg-white/[0.1] transition-colors inline-flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-pen" style="font-size: 10px;"></i>
                                    <span>Sửa</span>
                                </a>
                                <a href="{{ route('admin.styles.options.index', $style) }}" class="flex-1 py-2 rounded-lg bg-purple-500/10 border border-purple-500/30 text-purple-400 text-sm text-center hover:bg-purple-500/20 transition-colors inline-flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-sliders" style="font-size: 10px;"></i>
                                    <span>Options ({{ $style->options_count ?? 0 }})</span>
                                </a>
                                <form method="POST" action="{{ route('admin.styles.destroy', $style) }}" onsubmit="return confirm('Xác nhận xóa?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-9 h-9 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 hover:bg-red-500/20 transition-colors inline-flex items-center justify-center">
                                        <i class="fa-solid fa-trash" style="font-size: 12px;"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $styles->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
