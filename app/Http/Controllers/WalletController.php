<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * WalletController
 * 
 * Trang ví - xem số dư và nạp tiền
 */
class WalletController extends Controller
{
    /**
     * Hiển thị trang ví
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Get amount from query param (for dynamic QR)
        $amount = max(0, (float) $request->input('amount', 0));
        
        // Lấy lịch sử giao dịch với pagination
        $transactions = $user->walletTransactions()
            ->latest()
            ->paginate(20);

        // Tạo VietQR URL với amount nếu có
        $vietqrUrl = $user->getVietQRUrl($amount);
        
        // Bank info for copy functionality
        $bankInfo = [
            'bank_name' => 'MB Bank',
            'account_number' => config('services_custom.vietqr.account_number'),
            'account_name' => config('services_custom.vietqr.account_name'),
            'transfer_content' => "EZSHOT {$user->id} NAP",
        ];

        return view('wallet.index', compact('user', 'transactions', 'vietqrUrl', 'amount', 'bankInfo'));
    }
}
