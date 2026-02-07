<div x-show="showImagePicker" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-[#0a0a0c]/80 backdrop-blur-md" @click="showImagePicker = false"></div>

    <div x-show="showImagePicker" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        class="relative w-full max-w-xl bg-[#1b1c21] border border-white/10 rounded-3xl shadow-3xl overflow-hidden">

        {{-- Header --}}
        <div class="p-6 border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center">
                    <i class="fa-solid fa-images text-purple-400"></i>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg">Thêm ảnh tham chiếu</h3>
                    <p class="text-white/40 text-xs">Sử dụng ảnh để hướng dẫn AI</p>
                </div>
            </div>
            <button @click="showImagePicker = false" class="text-white/40 hover:text-white transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-white/5 bg-black/20">
            <button @click="activeTab = 'upload'" class="flex-1 py-3 text-sm font-medium transition-all"
                :class="activeTab === 'upload' ? 'text-purple-400 border-b-2 border-purple-500' : 'text-white/40 hover:text-white/60'">
                Tải ảnh lên
            </button>
            <button @click="activeTab = 'recent'; loadRecentImages()"
                class="flex-1 py-3 text-sm font-medium transition-all"
                :class="activeTab === 'recent' ? 'text-purple-400 border-b-2 border-purple-500' : 'text-white/40 hover:text-white/60'">
                Ảnh gần đây
            </button>
        </div>

        {{-- Content Area --}}
        <div class="p-6 h-[400px] overflow-y-auto">
            {{-- Upload Tab --}}
            <div x-show="activeTab === 'upload'" class="h-full flex flex-col">
                <label
                    class="flex-1 flex flex-col items-center justify-center border-2 border-dashed border-white/5 rounded-2xl cursor-pointer hover:border-purple-500/30 hover:bg-purple-500/5 transition-all group">
                    <input type="file" class="hidden" multiple @change="handleFileSelect" accept="image/*">
                    <div
                        class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-cloud-arrow-up text-2xl text-white/20 group-hover:text-purple-400"></i>
                    </div>
                    <p class="text-white/60 font-medium">Kéo thả hoặc nhấn để tải ảnh</p>
                    <p class="text-white/30 text-xs mt-2">Dưới 10MB • Tối đa 4 ảnh</p>
                </label>
            </div>

            {{-- Recent Tab --}}
            <div x-show="activeTab === 'recent'" class="h-full">
                <div x-show="isLoadingPicker" class="h-full flex items-center justify-center">
                    <i class="fa-solid fa-spinner fa-spin text-purple-400 text-2xl"></i>
                </div>

                <div x-show="!isLoadingPicker && recentImages.length > 0" class="grid grid-cols-3 gap-3">
                    <template x-for="img in recentImages" :key="img.id">
                        <button @click="selectFromRecent(img.image_url)"
                            class="aspect-square rounded-xl overflow-hidden border-2 transition-all relative group"
                            :class="isSelected(img.image_url) ? 'border-purple-500 ring-4 ring-purple-500/20' : 'border-transparent hover:border-white/20'">
                            <img :src="img.image_url" class="w-full h-full object-cover">
                            <div x-show="isSelected(img.image_url)"
                                class="absolute inset-0 bg-purple-500/20 flex items-center justify-center">
                                <div
                                    class="w-6 h-6 rounded-full bg-purple-500 flex items-center justify-center shadow-lg">
                                    <i class="fa-solid fa-check text-white text-[10px]"></i>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>

                <div x-show="!isLoadingPicker && recentImages.length === 0"
                    class="h-full flex flex-col items-center justify-center text-center">
                    <i class="fa-regular fa-image text-4xl text-white/10 mb-4"></i>
                    <p class="text-white/40">Chưa có ảnh nào gần đây</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="p-6 border-t border-white/5 bg-black/20 flex items-center justify-between">
            <div class="text-sm text-white/40">
                <span x-text="selectedImages.length"></span>/4 ảnh đã chọn
            </div>
            <div class="flex gap-3">
                <button @click="showImagePicker = false"
                    class="px-6 py-2 rounded-xl text-white/60 hover:text-white hover:bg-white/5 transition-all text-sm font-medium">
                    Hủy
                </button>
                <button @click="showImagePicker = false"
                    class="px-8 py-2 rounded-xl bg-purple-500 text-white font-bold hover:bg-purple-400 transition-all text-sm shadow-lg shadow-purple-500/25 disabled:opacity-50"
                    :disabled="selectedImages.length === 0">
                    Xác nhận
                </button>
            </div>
        </div>
    </div>
</div>