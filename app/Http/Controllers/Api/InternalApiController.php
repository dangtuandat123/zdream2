<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Internal API Controller
 * 
 * API nội bộ để cộng/trừ credits (dùng cho webhook VietQR, Telegram bot, etc.)
 * Bảo vệ bằng API Secret Key
 */
class InternalApiController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Điều chỉnh credits của user
     * 
     * POST /api/internal/wallet/adjust
     * Headers: X-API-Secret: {secret}
     * Body: { user_id, amount, reason, source? }
     */
    public function adjustWallet(Request $request): JsonResponse
    {
        // Validate API Secret (fail-close: reject if secret not configured)
        $secret = config('services_custom.internal_api_secret');
        $apiSecret = $request->header('X-API-Secret');
        
        if (empty($secret) || !hash_equals($secret, (string) $apiSecret)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|not_in:0',
            'reason' => 'required|string|max:255',
            'source' => 'nullable|string|max:100',
            'reference_id' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $amount = abs($validated['amount']);
        $source = $validated['source'] ?? 'admin_adjust';
        $referenceId = $validated['reference_id'] ?? null;

        // [BUG FIX] Idempotency check: nếu có reference_id, kiểm tra đã xử lý chưa
        if ($referenceId) {
            $existing = WalletTransaction::where('source', $source)
                ->where('reference_id', $referenceId)
                ->first();
            
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction already processed',
                    'transaction_id' => $existing->id,
                    'new_balance' => $user->fresh()->credits,
                ]);
            }
        }

        try {
            if ($validated['amount'] > 0) {
                // Cộng tiền
                $transaction = $this->walletService->addCredits(
                    $user,
                    $amount,
                    $validated['reason'],
                    $source,
                    $referenceId
                );
            } else {
                // Trừ tiền
                $transaction = $this->walletService->deductCredits(
                    $user,
                    $amount,
                    $validated['reason'],
                    $source,
                    $referenceId
                );
            }

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->id,
                'new_balance' => $user->fresh()->credits,
            ]);

        } catch (\App\Exceptions\InsufficientCreditsException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Insufficient credits',
                'current_balance' => $user->credits,
            ], 422);
        }
    }

    /**
     * Callback từ VietQR (khi user nạp tiền thành công)
     * 
     * POST /api/internal/payment/callback
     * 
     * HIGH-03 FIX: Sử dụng DB transaction với lockForUpdate để đảm bảo idempotent
     */
    public function paymentCallback(Request $request): JsonResponse
    {
        // Validate API Secret (fail-close: reject if secret not configured)
        $secret = config('services_custom.internal_api_secret');
        $apiSecret = $request->header('X-API-Secret');
        
        if (empty($secret) || !hash_equals($secret, (string) $apiSecret)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'transaction_ref' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($validated['user_id']);

        // HIGH-03 FIX: Atomic idempotent check với DB transaction
        // Sử dụng lockForUpdate để tránh race condition
        try {
            $result = \DB::transaction(function () use ($user, $validated) {
                // Lock row nếu đã tồn tại, hoặc insert mới
                $existing = WalletTransaction::where('source', 'vietqr')
                    ->where('reference_id', $validated['transaction_ref'])
                    ->lockForUpdate()
                    ->first();
                
                if ($existing) {
                    // Transaction đã được xử lý trước đó
                    return [
                        'already_processed' => true,
                        'transaction' => $existing,
                    ];
                }
                
                // Chưa có, tạo mới
                // [FIX API-01] Chuyển đổi VND → Xu theo settings
                $exchangeRate = (int) (Setting::get('credit_exchange_rate', 1000) ?: 1000);
                $exchangeRate = $exchangeRate > 0 ? $exchangeRate : 1000;
                $creditsToAdd = $validated['amount'] / $exchangeRate;
                
                $transaction = $this->walletService->addCredits(
                    $user,
                    $creditsToAdd,
                    'Nạp tiền qua VietQR (' . number_format($validated['amount']) . ' VND)',
                    'vietqr',
                    $validated['transaction_ref']
                );
                
                return [
                    'already_processed' => false,
                    'transaction' => $transaction,
                ];
            });
            
            if ($result['already_processed']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction already processed',
                    'new_balance' => $user->fresh()->credits,
                ]);
            }
            
            return response()->json([
                'success' => true,
                'transaction_id' => $result['transaction']->id,
                'new_balance' => $user->fresh()->credits,
            ]);
            
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle unique constraint violation (backup safety)
            if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'UNIQUE')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction already processed (duplicate)',
                    'new_balance' => $user->fresh()->credits,
                ]);
            }
            throw $e;
        }
    }
}
