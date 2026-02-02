<x-app-layout>
    <x-slot name="title">Quản lý Options - {{ $style->name }} | Admin</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.styles.index') }}" class="w-10 h-10 rounded-xl bg-white/[0.05] border border-white/[0.1] inline-flex items-center justify-center text-white/60 hover:text-[#d3d6db] hover:bg-white/[0.1] transition-all">
                <i class="fa-solid fa-arrow-left" style="font-size: 14px;"></i>
            </a>
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-[#d3d6db]">Quản lý Options</h1>
                <p class="text-white/50 text-sm">Style: <span class="text-purple-400">{{ $style->name }}</span></p>
            </div>
            <a href="{{ route('admin.styles.options.create', $style) }}" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-[#d3d6db] font-medium inline-flex items-center justify-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
                <i class="fa-solid fa-plus" style="font-size: 14px;"></i>
                <span>Thêm Option</span>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 inline-flex items-center gap-2">
                <i class="fa-solid fa-check-circle" style="font-size: 16px;"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 inline-flex items-center gap-2">
                <i class="fa-solid fa-exclamation-circle" style="font-size: 16px;"></i>
                {{ session('error') }}
            </div>
        @endif

        @if($options->isEmpty())
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl text-center py-16">
                <i class="fa-solid fa-sliders text-4xl text-white/20 mb-4"></i>
                <p class="text-white/50 text-lg mb-2">Chưa có Option nào</p>
                <p class="text-white/30 text-sm mb-4">Thêm các tùy chọn để người dùng có thể customize prompt</p>
                <a href="{{ route('admin.styles.options.create', $style) }}" class="inline-flex items-center gap-2 text-purple-400 hover:text-purple-300 transition-colors">
                    <i class="fa-solid fa-plus" style="font-size: 12px;"></i> Thêm option đầu tiên
                </a>
            </div>
        @else
            <div class="space-y-6">
                @foreach($options as $groupName => $groupOptions)
                    <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl overflow-hidden">
                        <div class="px-4 py-3 bg-white/[0.02] border-b border-white/[0.05] flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-[#d3d6db] inline-flex items-center gap-2">
                                <i class="fa-solid fa-folder text-purple-400" style="font-size: 14px;"></i>
                                {{ $groupName }}
                            </h3>
                            <span class="text-white/40 text-sm">{{ count($groupOptions) }} options</span>
                        </div>
                        <div class="divide-y divide-white/[0.05]">
                            @foreach($groupOptions as $option)
                                <div class="p-4 flex items-center gap-4 hover:bg-white/[0.02] transition-colors">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-[#d3d6db] font-medium">{{ $option->label }}</span>
                                            @if($option->is_default)
                                                <span class="px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-400 text-xs font-medium">Default</span>
                                            @endif
                                        </div>
                                        <p class="text-white/40 text-sm font-mono line-clamp-1">{{ $option->prompt_fragment }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.styles.options.edit', [$style, $option]) }}" class="w-9 h-9 rounded-lg bg-white/[0.05] border border-white/[0.1] inline-flex items-center justify-center text-white/60 hover:text-[#d3d6db] hover:bg-white/[0.1] transition-all">
                                            <i class="fa-solid fa-pen" style="font-size: 12px;"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.styles.options.destroy', [$style, $option]) }}" onsubmit="return confirm('Xác nhận xóa option này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-9 h-9 rounded-lg bg-red-500/10 border border-red-500/30 inline-flex items-center justify-center text-red-400 hover:bg-red-500/20 transition-all">
                                                <i class="fa-solid fa-trash" style="font-size: 12px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Tips -->
        <div class="mt-8 p-4 rounded-xl bg-purple-500/10 border border-purple-500/20">
            <h4 class="text-[#d3d6db] font-medium mb-2 inline-flex items-center gap-2">
                <i class="fa-solid fa-lightbulb text-yellow-400" style="font-size: 14px;"></i>
                Hướng dẫn
            </h4>
            <ul class="text-white/60 text-sm space-y-1">
                <li>• <strong>Group Name:</strong> Nhóm các options liên quan (VD: "Làn da", "Ánh sáng", "Background")</li>
                <li>• <strong>Prompt Fragment:</strong> Đoạn text sẽ được nối vào base prompt (VD: ", smooth soft skin")</li>
                <li>• <strong>Default:</strong> Option sẽ được chọn sẵn khi user vào trang</li>
            </ul>
        </div>
    </div>
</x-app-layout>
