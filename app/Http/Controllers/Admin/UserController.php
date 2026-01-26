<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Admin User Controller
 * 
 * Quản lý users: list, view, edit credits, toggle ban status
 */
class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query()->withCount('generatedImages');

        // Search by name or email
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'banned') {
                $query->where('is_active', false);
            } elseif ($status === 'admin') {
                $query->where('is_admin', true);
            }
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['name', 'email', 'credits', 'created_at', 'generated_images_count'];
        
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $users = $query->paginate(20)->withQueryString();

        // Stats for header
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'banned' => User::where('is_active', false)->count(),
            'admins' => User::where('is_admin', true)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show user details with transactions and images.
     */
    public function show(User $user)
    {
        $user->loadCount('generatedImages');
        
        $transactions = $user->walletTransactions()
            ->latest()
            ->take(20)
            ->get();

        $recentImages = $user->generatedImages()
            ->latest()
            ->take(12)
            ->get();

        return view('admin.users.show', compact('user', 'transactions', 'recentImages'));
    }

    /**
     * Show form to edit user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user details.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'is_admin' => 'boolean',
        ]);

        // [FIX loi.md #4] Guard self-demotion
        $newIsAdmin = $request->boolean('is_admin');
        if ($user->id === auth()->id() && $user->is_admin && !$newIsAdmin) {
            return back()->with('error', 'Không thể tự bỏ quyền admin của chính mình!');
        }

        // [FIX loi.md #4] Ensure at least one admin remains
        if ($user->is_admin && !$newIsAdmin) {
            $otherAdminCount = User::where('is_admin', true)->where('id', '!=', $user->id)->count();
            if ($otherAdminCount === 0) {
                return back()->with('error', 'Phải có ít nhất 1 admin trong hệ thống!');
            }
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_admin' => $newIsAdmin,
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Đã cập nhật thông tin user.');
    }

    /**
     * Toggle user active/banned status.
     */
    public function toggleStatus(User $user)
    {
        // Prevent self-ban
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Không thể tự ban chính mình!');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'kích hoạt' : 'vô hiệu hóa';
        
        Log::info('Admin toggled user status', [
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
            'new_status' => $user->is_active,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', "Đã {$status} tài khoản {$user->name}.");
    }

    /**
     * Adjust user credits (add or subtract).
     */
    public function adjustCredits(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:-10000|max:10000',
            'reason' => 'required|string|max:500',
        ]);

        $amount = (float) $validated['amount'];
        $reason = $validated['reason'];

        if ($amount == 0) {
            return back()->with('error', 'Số tiền phải khác 0.');
        }

        try {
            $walletService = app(WalletService::class);

            if ($amount > 0) {
                // Cộng credits
                $walletService->addCredits(
                    $user,
                    $amount,
                    $reason,
                    WalletTransaction::SOURCE_ADMIN
                );
                $message = "Đã cộng {$amount} Xu cho {$user->name}.";
            } else {
                // Trừ credits
                $absAmount = abs($amount);
                
                if (bccomp((string)$user->credits, (string)$absAmount, 2) < 0) {
                    return back()->with('error', "User chỉ có {$user->credits} Xu, không thể trừ {$absAmount} Xu.");
                }

                $walletService->deductCredits(
                    $user,
                    $absAmount,
                    $reason,
                    WalletTransaction::SOURCE_ADMIN
                );
                $message = "Đã trừ {$absAmount} Xu của {$user->name}.";
            }

            Log::info('Admin adjusted user credits', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'amount' => $amount,
                'reason' => $reason,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Failed to adjust credits', [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }
}
