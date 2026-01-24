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
        
        // Lấy lịch sử giao dịch với pagination
        $transactions = $user->walletTransactions()
            ->latest()
            ->paginate(20);

        // Tạo VietQR URL
        $vietqrUrl = $user->getVietQRUrl();

        return view('wallet.index', compact('user', 'transactions', 'vietqrUrl'));
    }
}
