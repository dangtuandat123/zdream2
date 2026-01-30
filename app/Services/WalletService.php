<?php

namespace App\Services;

use App\Exceptions\InsufficientCreditsException;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * WalletService
 * 
 * Xử lý logic cộng/trừ credits với transaction logging.
 * Đảm bảo ACID compliance cho các giao dịch.
 */
class WalletService
{
    /**
     * Trừ credits từ ví user
     * 
     * @param User $user User cần trừ tiền
     * @param float $amount Số tiền cần trừ (dương)
     * @param string $reason Lý do trừ tiền
     * @param string $source Nguồn (generation, admin, etc.)
     * @param string|null $referenceId ID tham chiếu (generated_image_id, etc.)
     * @return WalletTransaction
     * @throws InsufficientCreditsException
     */
    public function deductCredits(
        User $user,
        float $amount,
        string $reason,
        string $source = WalletTransaction::SOURCE_GENERATION,
        ?string $referenceId = null
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }

        return DB::transaction(function () use ($user, $amount, $reason, $source, $referenceId) {
            // Lock user row để tránh race condition
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            // Kiểm tra đủ tiền
            if (!$user->hasEnoughCredits($amount)) {
                throw new InsufficientCreditsException(
                    "Insufficient credits. Required: {$amount}, Available: {$user->credits}"
                );
            }

            $balanceBefore = $user->credits;
            $balanceAfter = $balanceBefore - $amount;

            // Trừ tiền
            $user->credits = $balanceAfter;
            $user->save();

            // Log transaction
            $transaction = WalletTransaction::create([
                'user_id' => $user->id,
                'type' => WalletTransaction::TYPE_DEBIT,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => $reason,
                'source' => $source,
                'reference_id' => $referenceId,
            ]);

            Log::info('Credits deducted', [
                'user_id' => $user->id,
                'amount' => $amount,
                'balance_after' => $balanceAfter,
                'transaction_id' => $transaction->id,
            ]);

            return $transaction;
        });
    }

    /**
     * Cộng credits vào ví user
     * 
     * @param User $user User cần cộng tiền
     * @param float $amount Số tiền cần cộng (dương)
     * @param string $reason Lý do cộng tiền
     * @param string $source Nguồn (vietqr, admin, refund, etc.)
     * @param string|null $referenceId ID tham chiếu
     * @return WalletTransaction
     */
    public function addCredits(
        User $user,
        float $amount,
        string $reason,
        string $source = WalletTransaction::SOURCE_ADMIN,
        ?string $referenceId = null
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }

        return DB::transaction(function () use ($user, $amount, $reason, $source, $referenceId) {
            // Lock user row
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            $balanceBefore = $user->credits;
            $balanceAfter = $balanceBefore + $amount;

            // Cộng tiền
            $user->credits = $balanceAfter;
            $user->save();

            // Log transaction
            $transaction = WalletTransaction::create([
                'user_id' => $user->id,
                'type' => WalletTransaction::TYPE_CREDIT,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => $reason,
                'source' => $source,
                'reference_id' => $referenceId,
            ]);

            Log::info('Credits added', [
                'user_id' => $user->id,
                'amount' => $amount,
                'balance_after' => $balanceAfter,
                'transaction_id' => $transaction->id,
            ]);

            return $transaction;
        });
    }

    /**
     * Hoàn tiền cho user (khi tạo ảnh thất bại)
     */
    public function refundCredits(
        User $user,
        float $amount,
        string $reason,
        ?string $referenceId = null
    ): WalletTransaction {
        if ($referenceId) {
            $existing = WalletTransaction::where('user_id', $user->id)
                ->where('source', WalletTransaction::SOURCE_REFUND)
                ->where('reference_id', $referenceId)
                ->first();

            if ($existing) {
                Log::info('Skip duplicate refund (already refunded)', [
                    'user_id' => $user->id,
                    'reference_id' => $referenceId,
                    'transaction_id' => $existing->id,
                ]);
                return $existing;
            }
        }

        return $this->addCredits(
            $user,
            $amount,
            'Refund: ' . $reason,
            WalletTransaction::SOURCE_REFUND,
            $referenceId
        );
    }

    /**
     * Lấy lịch sử giao dịch của user
     */
    public function getTransactionHistory(User $user, int $limit = 20)
    {
        return $user->walletTransactions()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy tổng số tiền đã nạp
     */
    public function getTotalCredits(User $user): float
    {
        return (float) $user->walletTransactions()
            ->credits()
            ->sum('amount');
    }

    /**
     * Lấy tổng số tiền đã tiêu
     */
    public function getTotalDebits(User $user): float
    {
        return (float) $user->walletTransactions()
            ->debits()
            ->sum('amount');
    }
}
