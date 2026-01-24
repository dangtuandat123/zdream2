<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        try {
            if ($validated['amount'] > 0) {
                // Cộng tiền
                $transaction = $this->walletService->addCredits(
                    $user,
                    $amount,
                    $validated['reason'],
                    $source,
                    $validated['reference_id'] ?? null
                );
            } else {
                // Trừ tiền
                $transaction = $this->walletService->deductCredits(
                    $user,
                    $amount,
                    $validated['reason'],
                    $source,
                    $validated['reference_id'] ?? null
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

        // Idempotent check: ngăn cộng tiền nhiều lần với cùng transaction_ref
        $existing = WalletTransaction::where('source', 'vietqr')
            ->where('reference_id', $validated['transaction_ref'])
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction already processed',
                'new_balance' => $user->credits,
            ]);
        }

        $transaction = $this->walletService->addCredits(
            $user,
            $validated['amount'],
            'Nạp tiền qua VietQR',
            'vietqr',
            $validated['transaction_ref']
        );

        return response()->json([
            'success' => true,
            'transaction_id' => $transaction->id,
            'new_balance' => $user->fresh()->credits,
        ]);
    }
}
