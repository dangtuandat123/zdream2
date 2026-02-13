{{-- ========== IMAGE PICKER - DESKTOP ========== --}}
<div x-show="showImagePicker" x-cloak x-init="$watch('showImagePicker', v => {
            if (v) { document.documentElement.style.setProperty('overflow','hidden','important'); document.body.style.setProperty('overflow','hidden','important'); }
            else { document.documentElement.style.removeProperty('overflow'); document.body.style.removeProperty('overflow'); }
        })" class="hidden sm:flex fixed inset-0 z-[100] items-center justify-center bg-black/60 backdrop-blur-sm"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click.self="showImagePicker = false">

    <div x-show="showImagePicker" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0 translate-y-8 scale-95"
        class="w-full max-w-4xl max-h-[90vh] mx-4 rounded-2xl bg-[#15161A] border border-white/10 shadow-2xl overflow-hidden flex flex-col"
        @click.stop>

        {{-- Header --}}
        <div class="flex items-center justify-between p-5 border-b border-white/5 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-500/20 flex items-center justify-center">
                    <i class="fa-solid fa-images text-purple-400"></i>
                </div>
                <div>
                    <h3 class="text-white font-semibold text-lg">Chọn ảnh mẫu</h3>
                    <p class="text-white/50 text-sm">Tối đa <span x-text="maxImages"></span> ảnh tham chiếu</p>
                </div>
            </div>
            <button type="button" @click="showImagePicker = false"
                class="w-10 h-10 flex items-center justify-center rounded-full bg-white/5 text-white/60 hover:bg-white/10 hover:text-white transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-white/5 px-5 shrink-0">
            <button type="button" @click="activeTab = 'upload'"
                class="py-3 px-4 text-sm font-medium transition-all relative rounded-t-lg"
                :class="activeTab === 'upload' ? 'text-purple-400 bg-purple-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5'">
                <i class="fa-solid fa-upload mr-2"></i>Upload
                <div x-show="activeTab === 'upload'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-500">
                </div>
            </button>
            <button type="button" @click="activeTab = 'url'"
                class="py-3 px-4 text-sm font-medium transition-all relative rounded-t-lg"
                :class="activeTab === 'url' ? 'text-purple-400 bg-purple-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5'">
                <i class="fa-solid fa-link mr-2"></i>Dán URL
                <div x-show="activeTab === 'url'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-500">
                </div>
            </button>
            <button type="button" @click="activeTab = 'recent'; loadRecentImages()"
                class="py-3 px-4 text-sm font-medium transition-all relative rounded-t-lg"
                :class="activeTab === 'recent' ? 'text-purple-400 bg-purple-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5'">
                <i class="fa-solid fa-clock-rotate-left mr-2"></i>Thư viện
                <div x-show="activeTab === 'recent'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-500">
                </div>
            </button>
        </div>

        {{-- Content --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 p-4 overflow-y-auto">
                {{-- Upload Tab --}}
                <div x-show="activeTab === 'upload'" class="h-full flex flex-col">
                    <label
                        class="shrink-0 flex items-center gap-4 p-4 rounded-xl border border-dashed cursor-pointer transition-all group"
                        :class="isDragging ? 'border-purple-500 bg-purple-500/10' : 'border-white/20 hover:border-purple-500/50 bg-white/[0.02]'"
                        @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop($event)">
                        <input type="file" accept="image/*" multiple class="hidden" @change="handleFileSelect($event)">
                        <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-cloud-arrow-up text-xl text-purple-400"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-white font-medium text-sm">Kéo thả hoặc <span class="text-purple-400">chọn
                                    ảnh</span></p>
                            <p class="text-white/40 text-xs">PNG, JPG, WebP • Tối đa 10MB • Chọn tối đa <span
                                    x-text="maxImages"></span> ảnh</p>
                        </div>
                    </label>
                    <div x-show="selectedImages.length > 0" class="mt-4 flex-1">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-white/60 text-sm"><i
                                    class="fa-solid fa-images text-purple-400 mr-1.5"></i>Đã chọn <span
                                    class="text-white font-medium" x-text="selectedImages.length"></span>/<span
                                    x-text="maxImages"></span></span>
                            <button type="button" @click="clearAll()"
                                class="text-red-400/60 text-xs hover:text-red-400 transition-colors">Xóa tất
                                cả</button>
                        </div>
                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="(img, index) in selectedImages" :key="img.id">
                                <div
                                    class="relative group rounded-xl overflow-hidden bg-black/40 border border-white/10 aspect-square">
                                    <img :src="img.url" class="w-full h-full object-contain">
                                    <div
                                        class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <button type="button" @click="removeImage(img.id)"
                                            class="w-9 h-9 rounded-full bg-red-500/80 hover:bg-red-500 text-white flex items-center justify-center"><i
                                                class="fa-solid fa-trash-can text-sm"></i></button>
                                    </div>
                                    <div class="absolute top-2 left-2 w-5 h-5 rounded-full bg-purple-500 text-white text-[10px] font-bold flex items-center justify-center"
                                        x-text="index + 1"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- URL Tab --}}
                <div x-show="activeTab === 'url'" class="h-full flex flex-col">
                    <div class="flex gap-2">
                        <input type="text" x-model="urlInput" placeholder="Dán URL ảnh vào đây..."
                            class="flex-1 px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/30 focus:outline-none focus:border-purple-500/50">
                        <button type="button" @click="addFromUrl()"
                            class="px-5 py-3 rounded-xl bg-purple-500 hover:bg-purple-600 text-white font-medium transition-colors"><i
                                class="fa-solid fa-plus"></i></button>
                    </div>
                    <div x-show="selectedImages.length > 0" class="mt-4 flex-1">
                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="(img, index) in selectedImages" :key="img.id">
                                <div
                                    class="relative group rounded-xl overflow-hidden bg-black/40 border border-white/10 aspect-square">
                                    <img :src="img.url" class="w-full h-full object-contain">
                                    <button type="button" @click="removeImage(img.id)"
                                        class="absolute top-1 right-1 w-6 h-6 rounded-full bg-red-500 text-white text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"><i
                                            class="fa-solid fa-xmark"></i></button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Recent Tab --}}
                <div x-show="activeTab === 'recent'" class="h-full">
                    <template x-if="isLoadingPicker">
                        <div class="flex justify-center py-10"><i
                                class="fa-solid fa-spinner fa-spin text-purple-400 text-2xl"></i></div>
                    </template>
                    <template x-if="!isLoadingPicker && recentImages.length > 0">
                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="img in recentImages" :key="img.id">
                                <button type="button" @click="selectFromRecent(img.url)"
                                    class="aspect-square rounded-xl overflow-hidden border-2 transition-all relative"
                                    :class="isSelected(img.url) ? 'border-purple-500' : 'border-transparent hover:border-white/20'">
                                    <img :src="img.url" class="w-full h-full object-cover">
                                    <div x-show="isSelected(img.url)"
                                        class="absolute inset-0 bg-purple-500/40 flex items-center justify-center">
                                        <i class="fa-solid fa-check text-white text-xl"></i>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </template>
                    <template x-if="!isLoadingPicker && recentImages.length === 0">
                        <div class="text-center py-10"><i class="fa-regular fa-image text-4xl text-white/10 mb-3"></i>
                            <p class="text-white/40">Chưa có ảnh nào</p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Footer --}}
            <div class="p-4 border-t border-white/5 bg-[#15161A] flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2 text-white/50 text-sm">
                    <template x-if="selectedImages.length > 0"><span class="flex items-center gap-1.5"><span
                                class="w-2 h-2 rounded-full bg-green-400"></span><span
                                x-text="selectedImages.length + ' ảnh đã chọn'"></span></span></template>
                    <template x-if="selectedImages.length === 0"><span>Chưa chọn ảnh nào</span></template>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="showImagePicker = false"
                        class="px-4 py-2 rounded-lg text-white/60 font-medium hover:bg-white/5 transition-colors text-sm">Hủy</button>
                    <button type="button" @click="confirmSelection()"
                        class="px-5 py-2 rounded-lg bg-purple-500 hover:bg-purple-600 text-white font-medium transition-colors text-sm disabled:opacity-40"
                        :disabled="selectedImages.length === 0">
                        <i class="fa-solid fa-check mr-1.5"></i>Xác nhận
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========== IMAGE PICKER - MOBILE BOTTOM SHEET ========== --}}
<div x-show="showImagePicker" x-cloak
    class="sm:hidden fixed inset-0 z-[100] flex items-end justify-center bg-black/60 backdrop-blur-sm"
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click.self="showImagePicker = false">

    <div x-show="showImagePicker" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="w-full max-w-lg bg-[#1a1b20] border-t border-white/10 rounded-t-3xl flex flex-col max-h-[85vh]"
        @click.stop>

        {{-- Handle --}}
        <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 rounded-full bg-white/20"></div>
        </div>

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 pb-3 border-b border-white/5 shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center"><i
                        class="fa-solid fa-images text-purple-400 text-sm"></i></div>
                <div>
                    <span class="text-white font-semibold text-base">Chọn ảnh mẫu</span>
                    <span class="text-white/40 text-xs ml-1"
                        x-text="'(' + selectedImages.length + '/' + maxImages + ')'"></span>
                </div>
            </div>
            <button type="button" @click="showImagePicker = false"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 text-white/60 active:scale-95"><i
                    class="fa-solid fa-xmark"></i></button>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-white/5 shrink-0">
            <button type="button" @click="activeTab = 'upload'"
                class="flex-1 py-3 text-sm font-medium transition-all flex items-center justify-center gap-1.5"
                :class="activeTab === 'upload' ? 'text-purple-400 bg-purple-500/10 border-b-2 border-purple-500' : 'text-white/50 active:bg-white/5'">
                <i class="fa-solid fa-upload text-xs"></i> Upload
            </button>
            <button type="button" @click="activeTab = 'url'"
                class="flex-1 py-3 text-sm font-medium transition-all flex items-center justify-center gap-1.5"
                :class="activeTab === 'url' ? 'text-purple-400 bg-purple-500/10 border-b-2 border-purple-500' : 'text-white/50 active:bg-white/5'">
                <i class="fa-solid fa-link text-xs"></i> URL
            </button>
            <button type="button" @click="activeTab = 'recent'; loadRecentImages()"
                class="flex-1 py-3 text-sm font-medium transition-all flex items-center justify-center gap-1.5"
                :class="activeTab === 'recent' ? 'text-purple-400 bg-purple-500/10 border-b-2 border-purple-500' : 'text-white/50 active:bg-white/5'">
                <i class="fa-solid fa-clock-rotate-left text-xs"></i> Gần đây
            </button>
        </div>

        {{-- Content --}}
        <div class="p-4 overflow-y-auto flex-1">
            {{-- Upload --}}
            <div x-show="activeTab === 'upload'" class="grid grid-cols-2 gap-3">
                <label
                    class="flex flex-col items-center gap-2 p-6 rounded-xl bg-white/5 border border-white/10 cursor-pointer active:scale-95 transition-transform">
                    <input type="file" accept="image/*" multiple class="hidden" @change="handleFileSelect($event)">
                    <i class="fa-solid fa-images text-3xl text-purple-400"></i>
                    <span class="text-white/70 text-sm font-medium">Thư viện</span>
                </label>
                <label
                    class="flex flex-col items-center gap-2 p-6 rounded-xl bg-white/5 border border-white/10 cursor-pointer active:scale-95 transition-transform">
                    <input type="file" accept="image/*" capture="environment" class="hidden"
                        @change="handleFileSelect($event)">
                    <i class="fa-solid fa-camera text-3xl text-pink-400"></i>
                    <span class="text-white/70 text-sm font-medium">Camera</span>
                </label>
            </div>
            {{-- URL --}}
            <div x-show="activeTab === 'url'">
                <div class="flex gap-2">
                    <input type="text" x-model="urlInput" placeholder="Dán URL ảnh..."
                        class="flex-1 px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/30 focus:outline-none focus:border-purple-500/50">
                    <button type="button" @click="addFromUrl()"
                        class="px-5 py-3 rounded-xl bg-purple-500 text-white font-medium active:scale-95 transition-transform"><i
                            class="fa-solid fa-plus"></i></button>
                </div>
            </div>
            {{-- Recent --}}
            <div x-show="activeTab === 'recent'">
                <template x-if="isLoadingPicker">
                    <div class="flex flex-col items-center py-10 gap-3"><i
                            class="fa-solid fa-spinner fa-spin text-purple-400 text-2xl"></i><span
                            class="text-white/40 text-sm">Đang tải...</span></div>
                </template>
                <template x-if="!isLoadingPicker && recentImages.length > 0">
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="img in recentImages" :key="img.id">
                            <button type="button" @click="selectFromRecent(img.url)"
                                class="aspect-square rounded-xl overflow-hidden border-2 transition-all relative active:scale-95"
                                :class="isSelected(img.url) ? 'border-purple-500 ring-2 ring-purple-500/30' : 'border-transparent'">
                                <img :src="img.url" class="w-full h-full object-cover">
                                <div x-show="isSelected(img.url)"
                                    class="absolute inset-0 bg-purple-500/40 flex items-center justify-center"><i
                                        class="fa-solid fa-check text-white text-xl"></i></div>
                            </button>
                        </template>
                    </div>
                </template>
                <template x-if="!isLoadingPicker && recentImages.length === 0">
                    <div class="text-center py-10 text-white/40"><i class="fa-regular fa-image text-4xl mb-3 block"></i>
                        <p class="text-sm">Chưa có ảnh nào</p>
                    </div>
                </template>
            </div>

            {{-- Selected Preview --}}
            <template x-if="selectedImages.length > 0">
                <div class="mt-4 pt-4 border-t border-white/5">
                    <div class="text-white/50 text-xs font-medium mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-check-circle text-green-400"></i>
                        Đã chọn <span class="text-white" x-text="selectedImages.length"></span>/<span
                            x-text="maxImages"></span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(img, idx) in selectedImages" :key="img.id">
                            <div class="relative group">
                                <img :src="img.url"
                                    class="w-16 h-16 rounded-xl object-cover border-2 border-purple-500/30">
                                <div class="absolute top-1 left-1 w-5 h-5 rounded-full bg-purple-500 text-white text-[10px] font-bold flex items-center justify-center"
                                    x-text="idx + 1"></div>
                                <button type="button" @click="removeImage(img.id)"
                                    class="absolute -top-1.5 -right-1.5 w-6 h-6 rounded-full bg-red-500 text-white text-xs flex items-center justify-center shadow-lg active:scale-90"><i
                                        class="fa-solid fa-xmark"></i></button>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <div class="p-4 border-t border-white/5 bg-[#1a1b20] safe-area-bottom shrink-0">
            <button type="button" @click="confirmSelection()"
                class="w-full py-3.5 rounded-xl text-white font-bold text-center active:scale-[0.98] transition-all"
                :disabled="selectedImages.length === 0"
                :class="selectedImages.length === 0 ? 'bg-white/10 text-white/40 cursor-not-allowed' : 'bg-gradient-to-r from-purple-600 to-pink-600 shadow-lg shadow-purple-500/20'">
                <template x-if="selectedImages.length === 0"><span class="flex items-center justify-center gap-2"><i
                            class="fa-solid fa-image"></i> Chọn ít nhất 1 ảnh</span></template>
                <template x-if="selectedImages.length > 0"><span class="flex items-center justify-center gap-2"><i
                            class="fa-solid fa-check"></i> Xác nhận (<span x-text="selectedImages.length"></span>
                        ảnh)</span></template>
            </button>
        </div>
    </div>
</div>