<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Models\ReturnReason;
use App\Models\WalletHistory;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ReturnRequest::class, 'returnRequest');
    }

    /**
     * لیست درخواست‌های مرجوعی
     */
    public function index(Request $request)
    {
        $query = ReturnRequest::with(['order', 'orderItem.product', 'user', 'reason', 'images']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('order', function ($q) use ($search) {
                $q->where('id', $search);
            })->orWhereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $returns = $query->latest()->paginate(20);

        $stats = [
            'total'     => ReturnRequest::count(),
            'pending'   => ReturnRequest::where('status', 'pending')->count(),
            'approved'  => ReturnRequest::where('status', 'approved')->count(),
            'received'  => ReturnRequest::where('status', 'received')->count(),
            'completed' => ReturnRequest::where('status', 'completed')->count(),
            'rejected'  => ReturnRequest::where('status', 'rejected')->count(),
        ];

        return view('back.returns.index', compact('returns', 'stats'));
    }

    /**
     * نمایش جزئیات درخواست مرجوعی
     */
    public function show(ReturnRequest $returnRequest)
    {
        $returnRequest->load(['order', 'orderItem.product', 'user', 'reason', 'images']);

        return view('back.returns.show', compact('returnRequest'));
    }

    /**
     * تایید اولیه درخواست (منتظر دریافت محصول)
     */
    public function approve(Request $request, ReturnRequest $returnRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if (!$returnRequest->isPending()) {
            return redirect()->back()->with('error', 'این درخواست قابل تایید نیست.');
        }

        $returnRequest->update([
            'status'       => ReturnRequest::STATUS_APPROVED,
            'admin_id'     => auth()->id(),
            'admin_notes'  => $request->admin_notes,
            'approved_at'  => now(),
        ]);

        $returnRequest->orderItem->update(['return_status' => 'approved']);

        return redirect()->back()->with('success', 'درخواست تایید اولیه شد. منتظر دریافت محصول هستیم.');
    }

    /**
     * محصول دریافت شد
     */
    public function markReceived(Request $request, ReturnRequest $returnRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if (!$returnRequest->isApproved()) {
            return redirect()->back()->with('error', 'این درخواست قابل بررسی نیست.');
        }

        $returnRequest->update([
            'status'       => ReturnRequest::STATUS_RECEIVED,
            'admin_notes'  => $request->admin_notes,
            'received_at'  => now(),
        ]);

        $returnRequest->orderItem->update(['return_status' => 'received']);

        return redirect()->back()->with('success', 'محصول دریافت شد. در حال بررسی نهایی.');
    }

    /**
     * تایید نهایی و بازگشت وجه به کیف پول
     */
    public function complete(Request $request, ReturnRequest $returnRequest)
    {
        $request->validate([
            'admin_notes'      => 'nullable|string|max:500',
            'refund_to_wallet' => 'nullable|boolean',
        ]);

        if (!$returnRequest->isReceived()) {
            return redirect()->back()->with('error', 'این درخواست قابل تکمیل نیست.');
        }

        $refundAmount = $returnRequest->calculateRefundAmount();
        $refundToWallet = $request->boolean('refund_to_wallet', true);

        if ($refundToWallet && $refundAmount > 0) {
            $user = $returnRequest->user;
            $wallet = $user->getWallet();

            if ($wallet) {
                $wallet->increment('balance', $refundAmount);

                WalletHistory::create([
                    'wallet_id'   => $wallet->id,
                    'type'        => 'deposit',
                    'amount'      => $refundAmount,
                    'source'      => 'admin',
                    'status'      => 'success',
                    'order_id'    => $returnRequest->order_id,
                    'description' => "بازگشت وجه مرجوعی - سفارش #{$returnRequest->order_id} - محصول: {$returnRequest->orderItem->title}",
                ]);
            }
        }

        $returnRequest->update([
            'status'           => ReturnRequest::STATUS_COMPLETED,
            'admin_id'         => auth()->id(),
            'admin_notes'      => $request->admin_notes,
            'completed_at'     => now(),
            'refund_to_wallet' => $refundToWallet,
            'refund_amount'    => $refundAmount,
        ]);

        $returnRequest->orderItem->update([
            'return_status' => 'completed',
            'refunded'      => true,
            'refunded_at'   => now(),
            'refunded_amount' => $refundAmount,
        ]);

        return redirect()->back()->with('success', 'مرجوعی تایید نهایی شد و وجه به کیف پول کاربر برگشت داده شد.');
    }

    /**
     * رد درخواست مرجوعی
     */
    public function reject(Request $request, ReturnRequest $returnRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if (in_array($returnRequest->status, ['completed', 'cancelled'])) {
            return redirect()->back()->with('error', 'این درخواست قابل رد نیست.');
        }

        $returnRequest->update([
            'status'           => ReturnRequest::STATUS_REJECTED,
            'admin_id'         => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
            'rejected_at'      => now(),
        ]);

        $returnRequest->orderItem->update(['return_status' => 'rejected']);

        return redirect()->back()->with('success', 'درخواست مرجوعی رد شد.');
    }

    /**
     * مدیریت دلایل مرجوعی
     */
    public function reasonsIndex()
    {
        $reasons = ReturnReason::latest()->paginate(20);
        return view('back.returns.reasons', compact('reasons'));
    }

    public function reasonsStore(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
        ]);

        $reason = ReturnReason::create([
            'title'       => $request->title,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
            'ordering'    => ReturnReason::max('ordering') + 1,
        ]);

        if ($request->ajax()) {
            $html = view('back.returns.partials.reasons_rows', ['reasons' => collect([$reason])])->render();
            return response()->json([
                'success' => true,
                'message' => 'دلیل مرجوعی با موفقیت ایجاد شد.',
                'html' => $html
            ]);
        }

        return redirect()->back()->with('success', 'دلیل مرجوعی ایجاد شد.');
    }

    public function reasonsDestroy(ReturnReason $reason)
    {
        $reason->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'دلیل مرجوعی با موفقیت حذف شد.'
            ]);
        }

        return redirect()->back()->with('success', 'دلیل مرجوعی حذف شد.');
    }

    public function reasonsToggle(ReturnReason $reason)
    {
        $reason->update(['is_active' => !$reason->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'وضعیت دلیل مرجوعی با موفقیت تغییر یافت.',
            'is_active' => $reason->is_active
        ]);
    }
}
