<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'credits',
        'is_admin',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'credits' => 'decimal:2',
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
    ];

    // =========================================
    // RELATIONSHIPS
    // =========================================

    /**
     * Lấy tất cả ảnh đã tạo của user
     */
    public function generatedImages(): HasMany
    {
        return $this->hasMany(GeneratedImage::class);
    }

    /**
     * Lấy lịch sử giao dịch ví
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    // =========================================
    // SCOPES
    // =========================================

    /**
     * Scope: Chỉ lấy user đang active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Chỉ lấy admin
     */
    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    // =========================================
    // HELPER METHODS
    // =========================================

    /**
     * Kiểm tra user có đủ credits không
     */
    public function hasEnoughCredits(float $amount): bool
    {
        return $this->credits >= $amount;
    }

    /**
     * Lấy VietQR URL để nạp tiền
     */
    public function getVietQRUrl(float $amount = 0): string
    {
        $bankId = config('services_custom.vietqr.bank_id');
        $accountNumber = config('services_custom.vietqr.account_number');
        $template = config('services_custom.vietqr.template');
        $accountName = urlencode(config('services_custom.vietqr.account_name'));
        $addInfo = urlencode("EZSHOT {$this->id} NAP");

        $url = "https://api.vietqr.io/image/{$bankId}-{$accountNumber}-{$template}.jpg";
        $url .= "?accountName={$accountName}&addInfo={$addInfo}";
        
        if ($amount > 0) {
            $url .= "&amount=" . (int)$amount;
        }

        return $url;
    }
}
