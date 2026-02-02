<x-app-layout>
    <x-slot name="title">Chi tiết ảnh #{{ $image->id }} - Admin | ZDream</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.images.index') }}" class="w-10 h-10 rounded-xl bg-white/[0.05] border border-white/[0.1] flex items-center justify-center text-white/60 hover:text-[#d3d6db] hover:bg-white/[0.1] transition-all">
                <i class="fa-solid fa-arrow-left" style="font-size: 14px;"></i>
            </a>
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-[#d3d6db]">Chi tiết ảnh #{{ $image->id }}</h1>
                <p class="text-white/50 text-sm">Tạo lúc {{ $image->created_at->format('d/m/Y H:i:s') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.images.destroy', $image) }}" 
                  onsubmit="return confirm('Xác nhận xóa ảnh này?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-xl bg-red-500/20 border border-red-500/30 text-red-400 hover:bg-red-500/30 transition-all inline-flex items-center gap-2">
                    <i class="fa-solid fa-trash" style="font-size: 12px;"></i>
                    Xóa ảnh
                </button>
            </form>
        </div>

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 flex items-center gap-2">
                <i class="fa-solid fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Image Preview -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl overflow-hidden">
                <div class="aspect-[3/4] relative">
                    @if($image->status === 'completed' && $image->image_url)
                        <img src="{{ $image->image_url }}" alt="" class="w-full h-full object-contain bg-black/20">
                    @elseif($image->status === 'failed')
                        <div class="w-full h-full flex flex-col items-center justify-center bg-red-500/10">
                            <i class="fa-solid fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                            <p class="text-red-400">Tạo ảnh thất bại</p>
                            @if($image->error_message)
                                <p class="text-red-400/60 text-sm mt-2 max-w-sm text-center">{{ $image->error_message }}</p>
                            @endif
                        </div>
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-white/[0.02]">
                            <i class="fa-solid fa-spinner fa-spin text-3xl text-white/30"></i>
                        </div>
                    @endif
                </div>
                @if($image->status === 'completed' && $image->image_url)
                    <div class="p-4 border-t border-white/[0.05]">
                        <a href="{{ $image->image_url }}" target="_blank" class="w-full py-2 rounded-lg bg-purple-500/20 border border-purple-500/30 text-purple-400 hover:bg-purple-500/30 transition-colors inline-flex items-center justify-center gap-2">
                            <i class="fa-solid fa-external-link"></i>
                            Mở ảnh gốc
                        </a>
                    </div>
                @endif
            </div>

            <!-- Image Details -->
            <div class="space-y-6">
                <!-- Status & Meta -->
                <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-[#d3d6db] mb-4">Thông tin</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-white/50">Status</span>
                            <span class="px-2 py-0.5 rounded text-sm {{ $image->status === 'completed' ? 'bg-green-500/20 text-green-400' : ($image->status === 'failed' ? 'bg-red-500/20 text-red-400' : 'bg-yellow-500/20 text-yellow-400') }}">
                                {{ ucfirst($image->status) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-white/50">Credits used</span>
                            <span class="text-[#d3d6db] font-semibold">{{ number_format($image->credits_used, 0) }} Xu</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-white/50">User</span>
                            @if($image->user)
                                <a href="{{ route('admin.users.show', $image->user) }}" class="text-purple-400 hover:text-purple-300">
                                    {{ $image->user->name }}
                                </a>
                            @else
                                <span class="text-white/30">Đã xóa</span>
                            @endif
                        </div>
                        <div class="flex justify-between">
                            <span class="text-white/50">Style</span>
                            <span class="text-[#d3d6db]">{{ $image->style->name ?? 'Đã xóa' }}</span>
                        </div>
                        @if($image->bfl_task_id || $image->openrouter_id)
                            <div class="flex justify-between">
                                <span class="text-white/50">BFL Task ID</span>
                                <span class="text-white/60 text-xs font-mono">{{ $image->bfl_task_id ?? $image->openrouter_id }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Prompt -->
                <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-[#d3d6db] mb-4">Final Prompt</h3>
                    <div class="p-3 bg-black/20 rounded-lg">
                        <p class="text-white/70 text-sm font-mono whitespace-pre-wrap break-words">{{ $image->final_prompt }}</p>
                    </div>
                </div>

                <!-- Generation Params -->
                @if($image->generation_params && count($image->generation_params) > 0)
                    <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-[#d3d6db] mb-4">Generation Params</h3>
                        <div class="space-y-2">
                            @foreach($image->generation_params as $key => $value)
                                @php
                                    $displayValue = is_bool($value)
                                        ? ($value ? 'true' : 'false')
                                        : (is_array($value) ? json_encode($value) : $value);
                                @endphp
                                <div class="flex items-start justify-between gap-4">
                                    <span class="text-white/50 text-sm">{{ $key }}</span>
                                    <span class="text-white/70 text-sm font-mono break-all text-right">{{ $displayValue }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- User Custom Input -->
                @if($image->user_custom_input)
                    <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-[#d3d6db] mb-4">User Custom Input</h3>
                        <div class="p-3 bg-black/20 rounded-lg">
                            <p class="text-white/70 text-sm">{{ $image->user_custom_input }}</p>
                        </div>
                    </div>
                @endif

                <!-- Selected Options -->
                @if($image->selected_options && count($image->selected_options) > 0)
                    <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-[#d3d6db] mb-4">Selected Options</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($image->selected_options as $group => $optionId)
                                <span class="px-2 py-1 rounded bg-white/[0.05] text-white/60 text-sm">
                                    {{ $group }}: #{{ $optionId }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
