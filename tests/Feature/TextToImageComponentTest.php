<?php

namespace Tests\Feature;

use App\Livewire\TextToImage;
use App\Models\GeneratedImage;
use App\Models\Style;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TextToImageComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_load_more_updates_per_page_and_dispatches_history_updated(): void
    {
        $user = User::factory()->create();
        $style = $this->createSystemStyle();

        foreach (range(1, 10) as $idx) {
            GeneratedImage::create([
                'user_id' => $user->id,
                'style_id' => $style->id,
                'final_prompt' => 'Prompt ' . $idx,
                'status' => GeneratedImage::STATUS_COMPLETED,
                'storage_path' => 'generated-images/test-' . $idx . '.png',
                'generation_params' => [
                    'model_id' => 'flux-pro-1.1-ultra',
                    'aspect_ratio' => '1:1',
                ],
                'credits_used' => 1,
            ]);
        }

        Livewire::actingAs($user)
            ->test(TextToImage::class)
            ->assertSet('perPage', 6)
            ->call('loadMore', 2)
            ->assertSet('perPage', 8)
            ->assertDispatched('historyUpdated', fn($event, $params) => ($params['hasMore'] ?? null) === true)
            ->call('loadMore', 20)
            ->assertSet('perPage', 10)
            ->assertDispatched('historyUpdated', fn($event, $params) => ($params['hasMore'] ?? null) === false);
    }

    public function test_load_more_dispatches_history_updated_false_when_no_history(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(TextToImage::class)
            ->assertSet('perPage', 6)
            ->call('loadMore', 4)
            ->assertSet('perPage', 6)
            ->assertDispatched('historyUpdated', fn($event, $params) => ($params['hasMore'] ?? null) === false);
    }

    public function test_cancel_generation_marks_pending_failed_and_refunds_once(): void
    {
        $user = User::factory()->create(['credits' => 95]);
        $style = $this->createSystemStyle();

        $pending = GeneratedImage::create([
            'user_id' => $user->id,
            'style_id' => $style->id,
            'final_prompt' => 'Pending image',
            'status' => GeneratedImage::STATUS_PENDING,
            'credits_used' => 5,
        ]);

        $completed = GeneratedImage::create([
            'user_id' => $user->id,
            'style_id' => $style->id,
            'final_prompt' => 'Completed image',
            'status' => GeneratedImage::STATUS_COMPLETED,
            'storage_path' => 'generated-images/completed.png',
            'credits_used' => 5,
        ]);

        Livewire::actingAs($user)
            ->test(TextToImage::class)
            ->set('isGenerating', true)
            ->set('generatingImageIds', [$pending->id, $completed->id])
            ->call('cancelGeneration')
            ->assertSet('isGenerating', false)
            ->assertSet('generatingImageIds', []);

        $pending->refresh();
        $completed->refresh();
        $user->refresh();

        $this->assertSame(GeneratedImage::STATUS_FAILED, $pending->status);
        $this->assertSame(GeneratedImage::STATUS_COMPLETED, $completed->status);
        $this->assertSame('100.00', (string) $user->credits);

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'source' => WalletTransaction::SOURCE_REFUND,
            'reference_id' => (string) $pending->id,
        ]);

        $this->assertDatabaseMissing('wallet_transactions', [
            'user_id' => $user->id,
            'source' => WalletTransaction::SOURCE_REFUND,
            'reference_id' => (string) $completed->id,
        ]);
    }

    public function test_set_reference_images_respects_model_limits(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)->test(TextToImage::class);

        $component
            ->set('modelId', 'flux-pro')
            ->call('setReferenceImages', [
                ['url' => 'https://example.com/a.png'],
                ['url' => 'https://example.com/b.png'],
            ])
            ->assertSet('referenceImages', []);

        $component
            ->set('modelId', 'flux-pro-1.1-ultra')
            ->call('setReferenceImages', [
                ['url' => 'https://example.com/1.png'],
                ['url' => 'https://example.com/2.png'],
                ['url' => 'https://example.com/3.png'],
            ])
            ->assertSet('referenceImages', [
                ['url' => 'https://example.com/1.png'],
            ]);
    }

    private function createSystemStyle(): Style
    {
        return Style::create([
            'name' => 'Text to Image',
            'slug' => Style::SYSTEM_T2I_SLUG,
            'description' => 'System style',
            'price' => 1,
            'openrouter_model_id' => 'flux-pro-1.1-ultra',
            'bfl_model_id' => 'flux-pro-1.1-ultra',
            'base_prompt' => '',
            'is_active' => true,
            'is_system' => true,
            'allow_user_custom_prompt' => true,
        ]);
    }
}
