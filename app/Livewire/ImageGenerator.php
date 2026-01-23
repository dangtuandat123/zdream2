<?php

namespace App\Livewire;

use App\Exceptions\InsufficientCreditsException;
use App\Models\GeneratedImage;
use App\Models\Style;
use App\Services\OpenRouterService;
use App\Services\StorageService;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Livewire Component: ImageGenerator
 * 
 * Component chính để tạo ảnh AI.
 * Xử lý: chọn options -> trừ credits -> gọi API -> lưu ảnh -> hiển thị
 */
class ImageGenerator extends Component
{
    // Style đang sử dụng
    public Style $style;
    
    // Selected option IDs (grouped by group_name)
    public array $selectedOptions = [];
    
    // User custom input (nếu được phép)
    public string $customInput = '';
    
    // Aspect Ratio đã chọn
    public string $selectedAspectRatio = '1:1';
    
    // Các aspect ratios hỗ trợ
    public array $aspectRatios = [
        '1:1' => 'Vuông (1:1)',
        '16:9' => 'Ngang (16:9)',
        '9:16' => 'Dọc (9:16)',
        '4:3' => 'Chuẩn (4:3)',
        '3:4' => 'Portrait (3:4)',
    ];
    
    // State
    public bool $isGenerating = false;
    public ?string $generatedImageUrl = null;
    public ?string $errorMessage = null;
    
    // Last generated image
    public ?int $lastImageId = null;

    /**
     * Mount component với Style
     */
    public function mount(Style $style): void
    {
        $this->style = $style;
        
        // Set default aspect ratio từ style config
        $this->selectedAspectRatio = $style->aspect_ratio;
        
        // Pre-select default options
        foreach ($style->options as $option) {
            if ($option->is_default) {
                $this->selectedOptions[$option->group_name] = $option->id;
            }
        }
    }

    /**
     * Toggle chọn option (dạng single select per group)
     */
    public function selectOption(string $groupName, int $optionId): void
    {
        // Nếu đã chọn rồi thì bỏ chọn
        if (isset($this->selectedOptions[$groupName]) && $this->selectedOptions[$groupName] === $optionId) {
            unset($this->selectedOptions[$groupName]);
        } else {
            $this->selectedOptions[$groupName] = $optionId;
        }
    }

    /**
     * Generate image
     */
    public function generate(): void
    {
        $this->resetState();

        $user = Auth::user();
        
        if (!$user) {
            $this->errorMessage = 'Vui lòng đăng nhập để tạo ảnh.';
            return;
        }

        // Kiểm tra đủ credits
        if (!$user->hasEnoughCredits($this->style->price)) {
            $this->errorMessage = "Bạn không đủ credits. Cần: {$this->style->price}, Hiện có: {$user->credits}";
            return;
        }

        $this->isGenerating = true;

        try {
            // Lấy danh sách option IDs đã chọn
            $selectedOptionIds = array_values($this->selectedOptions);

            // Tạo record GeneratedImage trước (trạng thái pending)
            $generatedImage = GeneratedImage::create([
                'user_id' => $user->id,
                'style_id' => $this->style->id,
                'final_prompt' => '', // Sẽ cập nhật sau
                'selected_options' => $selectedOptionIds,
                'user_custom_input' => $this->customInput ?: null,
                'status' => GeneratedImage::STATUS_PROCESSING,
                'credits_used' => $this->style->price,
            ]);

            // Trừ credits
            $walletService = app(WalletService::class);
            $walletService->deductCredits(
                $user,
                $this->style->price,
                "Tạo ảnh Style: {$this->style->name}",
                'generation',
                (string) $generatedImage->id
            );

            // Gọi OpenRouter API
            $openRouterService = app(OpenRouterService::class);
            $result = $openRouterService->generateImage(
                $this->style,
                $selectedOptionIds,
                $this->customInput ?: null,
                $this->selectedAspectRatio
            );

            if (!$result['success']) {
                // Hoàn tiền nếu API thất bại
                $walletService->refundCredits(
                    $user,
                    $this->style->price,
                    "API error: {$result['error']}",
                    (string) $generatedImage->id
                );
                
                $generatedImage->markAsFailed($result['error']);
                $this->errorMessage = 'Có lỗi khi tạo ảnh. Credits đã được hoàn lại.';
                $this->isGenerating = false;
                return;
            }

            // Cập nhật final prompt
            $generatedImage->update(['final_prompt' => $result['final_prompt']]);

            // Lưu ảnh vào MinIO
            $storageService = app(StorageService::class);
            $storageResult = $storageService->saveBase64Image(
                $result['image_base64'],
                $user->id
            );

            if (!$storageResult['success']) {
                $generatedImage->markAsFailed('Storage error: ' . $storageResult['error']);
                $this->errorMessage = 'Có lỗi khi lưu ảnh.';
                $this->isGenerating = false;
                return;
            }

            // Đánh dấu hoàn thành
            $generatedImage->markAsCompleted(
                $storageResult['path'],
                $result['openrouter_id']
            );

            $this->generatedImageUrl = $storageResult['url'];
            $this->lastImageId = $generatedImage->id;

        } catch (InsufficientCreditsException $e) {
            $this->errorMessage = 'Bạn không đủ credits để tạo ảnh.';
        } catch (\Exception $e) {
            $this->errorMessage = 'Có lỗi xảy ra: ' . $e->getMessage();
        }

        $this->isGenerating = false;
    }

    /**
     * Reset state
     */
    protected function resetState(): void
    {
        $this->errorMessage = null;
        $this->generatedImageUrl = null;
    }

    /**
     * Reset form để tạo ảnh mới
     */
    public function resetForm(): void
    {
        $this->resetState();
        $this->customInput = '';
        $this->selectedOptions = [];
        
        // Re-select defaults
        foreach ($this->style->options as $option) {
            if ($option->is_default) {
                $this->selectedOptions[$option->group_name] = $option->id;
            }
        }
    }

    /**
     * Render component
     */
    public function render()
    {
        return view('livewire.image-generator', [
            'optionGroups' => $this->style->options->groupBy('group_name'),
            'user' => Auth::user(),
        ]);
    }
}
