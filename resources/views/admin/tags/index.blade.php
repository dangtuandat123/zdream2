<x-app-layout>
    <x-slot name="title">Quản lý Tags - Admin | ZDream</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-[#d3d6db]">Quản lý Tags</h1>
                <p class="text-white/50 text-sm">Tags gắn lên styles (HOT, MỚI, SALE...)</p>
            </div>
            <a href="{{ route('admin.tags.create') }}" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-[#d3d6db] font-medium flex items-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.4)] transition-all">
                <i class="fa-solid fa-plus w-4 h-4"></i> Tạo Tag
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 flex items-center gap-2">
                <i class="fa-solid fa-check-circle" style="font-size: 16px;"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($tags->isEmpty())
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-12 text-center">
                <i class="fa-solid fa-tags text-4xl text-white/20 mb-4"></i>
                <p class="text-white/50 mb-4">Chưa có tag nào</p>
                <a href="{{ route('admin.tags.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-500/20 text-purple-300 hover:bg-purple-500/30 transition-colors">
                    <i class="fa-solid fa-plus w-3 h-3"></i> Tạo tag đầu tiên
                </a>
            </div>
        @else
            <div class="space-y-3">
                @foreach($tags as $tag)
                    <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4 flex items-center justify-between hover:border-white/[0.15] transition-colors">
                        <div class="flex items-center gap-4">
                            <!-- Preview Tag -->
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gradient-to-r from-{{ $tag->color_from }} to-{{ $tag->color_to }} text-[#d3d6db] text-xs font-bold shadow-lg">
                                <i class="fa-solid {{ $tag->icon }} w-3 h-3"></i>
                                {{ $tag->name }}
                            </span>
                            
                            <div>
                                <p class="text-white/60 text-sm">
                                    <span class="text-white/40">Icon:</span> {{ $tag->icon }}
                                    <span class="mx-2 text-white/20">|</span>
                                    <span class="text-white/40">Colors:</span> {{ $tag->color_from }} → {{ $tag->color_to }}
                                </p>
                                <p class="text-white/40 text-xs mt-0.5">
                                    {{ $tag->styles_count }} style(s) đang sử dụng
                                    @if(!$tag->is_active)
                                        <span class="ml-2 px-1.5 py-0.5 rounded bg-red-500/20 text-red-400 text-[10px]">Ẩn</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.tags.edit', $tag) }}" class="w-9 h-9 rounded-lg bg-white/[0.05] border border-white/[0.1] flex items-center justify-center text-white/60 hover:text-[#d3d6db] hover:bg-white/[0.1] transition-all">
                                <i class="fa-solid fa-pen w-3.5 h-3.5"></i>
                            </a>
                            <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST" onsubmit="return confirm('Xóa tag này? Các style đang dùng sẽ không còn tag.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-9 h-9 rounded-lg bg-red-500/10 border border-red-500/30 flex items-center justify-center text-red-400 hover:bg-red-500/20 transition-all">
                                    <i class="fa-solid fa-trash w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
