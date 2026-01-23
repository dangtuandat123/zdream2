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
        
        // Lấy lịch sử giao dịch gần đây
        $transactions = $user->walletTransactions()
            ->latest()
            ->limit(20)
            ->get();

        // Tạo VietQR URL
        $vietqrUrl = $user->getVietQRUrl();

        return view('wallet.index', compact('user', 'transactions', 'vietqrUrl'));
    }
}
