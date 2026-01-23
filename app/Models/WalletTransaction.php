<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: WalletTransaction
 * 
 * Lưu lịch sử giao dịch cộng/trừ credits.
 * 
 * @property int $id
 * @property int $user_id
 * @property string $type - 'credit' hoặc 'debit'
 * @property float $amount
 * @property float $balance_before
 * @property float $balance_after
 * @property string $reason
 * @property string|null $source
 * @property string|null $reference_id
 */
class WalletTransaction extends Model
{
    use HasFactory;

    const TYPE_CREDIT = 'credit';  // Cộng tiền
    const TYPE_DEBIT = 'debit';    // Trừ tiền

    const SOURCE_GENERATION = 'generation';   // Trừ tiền khi tạo ảnh
    const SOURCE_VIETQR = 'vietqr';           // Nạp tiền qua VietQR
    const SOURCE_ADMIN = 'admin_adjust';      // Admin điều chỉnh
    const SOURCE_REFUND = 'refund';           // Hoàn tiền

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reason',
        'source',
        'reference_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    // =========================================
    // RELATIONSHIPS
    // =========================================

    /**
     * Lấy user sở hữu giao dịch này
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // =========================================
    // SCOPES
    // =========================================

    /**
     * Scope: Giao dịch cộng tiền
     */
    public function scopeCredits($query)
    {
        return $query->where('type', self::TYPE_CREDIT);
    }

    /**
     * Scope: Giao dịch trừ tiền
     */
    public function scopeDebits($query)
    {
        return $query->where('type', self::TYPE_DEBIT);
    }

    /**
     * Scope: Theo nguồn
     */
    public function scopeFromSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope: Sắp xếp mới nhất
     */
    public function scopeLatest($query)
    {
        return $query->orderByDesc('created_at');
    }

    // =========================================
    // ACCESSORS
    // =========================================

    /**
     * Lấy amount có dấu (+/-)
     */
    public function getSignedAmountAttribute(): string
    {
        $sign = $this->type === self::TYPE_CREDIT ? '+' : '-';
        return $sign . number_format($this->amount, 2);
    }

    /**
     * Kiểm tra là giao dịch cộng tiền
     */
    public function getIsCreditAttribute(): bool
    {
        return $this->type === self::TYPE_CREDIT;
    }

    /**
     * Kiểm tra là giao dịch trừ tiền
     */
    public function getIsDebitAttribute(): bool
    {
        return $this->type === self::TYPE_DEBIT;
    }
}
