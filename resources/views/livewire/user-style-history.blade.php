<div>
    @if($userImages->isNotEmpty())
        @php
            $imageData = $userImages->map(fn($img) => [
                'url' => $img->image_url,
                'id' => $img->id,
                'download' => route('history.download', $img),
                'delete' => route('history.destroy', $img),
            ])->toArray();
        @endphp
        <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl overflow-hidden" wire:poll.5s>
            <div class="flex items-center justify-between p-4 border-b border-white/[0.05]">
                <div class="flex items-center gap-2 text-white/60">
                    <i class="fa-solid fa-clock-rotate-left" style="font-size: 14px;"></i>
                    <span class="font-medium text-sm">Ảnh đã tạo</span>
                </div>
                <a href="{{ route('history.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">
                    Xem tất cả
                </a>
            </div>
            <div class="p-3">
                <div class="grid grid-cols-3 gap-2">
                    @foreach($userImages as $index => $img)
                        <button 
                            onclick="openLightboxWithActions({{ $index }}, {{ json_encode($imageData) }})"
                            class="group relative aspect-square rounded-lg overflow-hidden bg-white/[0.05] cursor-pointer focus:outline-none"
                        >
                            <img src="{{ $img->image_url }}" alt="Generated" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" decoding="async" fetchpriority="low" onerror="this.src='/images/placeholder.svg'">
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <i class="fa-solid fa-expand text-white"></i>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
