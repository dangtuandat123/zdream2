<?php

namespace App\Http\Controllers\Admin;

use App\Models\WalletTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Admin Transaction Controller
 * 
 * Xem lịch sử giao dịch toàn hệ thống
 */
class TransactionController extends Controller
{
    /**
     * Display a listing of all transactions.
     */
    public function index(Request $request)
    {
        $query = WalletTransaction::with('user')->latest();

        // Filter by type
        if ($type = $request->get('type')) {
            if ($type === 'credit') {
                $query->credits();
            } elseif ($type === 'debit') {
                $query->debits();
            }
        }

        // Filter by source
        if ($source = $request->get('source')) {
            $query->fromSource($source);
        }

        // Search by user
        if ($search = $request->get('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $transactions = $query->paginate(30)->withQueryString();

        // Stats
        $stats = [
            'total_credits' => WalletTransaction::credits()->sum('amount'),
            'total_debits' => WalletTransaction::debits()->sum('amount'),
            'today_credits' => WalletTransaction::credits()->whereDate('created_at', today())->sum('amount'),
            'today_debits' => WalletTransaction::debits()->whereDate('created_at', today())->sum('amount'),
        ];

        $sources = [
            WalletTransaction::SOURCE_GENERATION => 'Generation',
            WalletTransaction::SOURCE_VIETQR => 'VietQR',
            WalletTransaction::SOURCE_ADMIN => 'Admin',
            WalletTransaction::SOURCE_REFUND => 'Refund',
        ];

        return view('admin.transactions.index', compact('transactions', 'stats', 'sources'));
    }
}
