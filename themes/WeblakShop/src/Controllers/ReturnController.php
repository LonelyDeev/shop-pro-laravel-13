<?php

namespace Themes\WeblakShop\src\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnReason;
use App\Models\ReturnRequest;
use App\Models\ReturnImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReturnController extends Controller
{
    /**
     * لیست درخواست‌های مرجوعی کاربر
     */
    public function index()
    {
        $returns = ReturnRequest::where('user_id', auth()->id())
            ->with(['order', 'orderItem.product', 'reason', 'images'])
            ->latest()
            ->paginate(15);

        return view('front::user.returns.index', compact('returns'));
    }

    /**
     * نمایش فرم درخواست مرجوعی
     */
    public function create(Order $order, OrderItem $orderItem)
    {
        if ($order->user_id !== auth()->id()) abort(404);
        if ($orderItem->order_id !== $order->id) abort(404);

        // بررسی پرداخت شده
        if ($order->status !== 'paid') {
            return redirect()->back()->with('error', 'فقط سفارشات پرداخت‌شده قابل مرجوعی هستند.');
        }

        // بررسی تحویل شده
        if ($orderItem->shipping_status !== 'delivered') {
            return redirect()->back()->with('error', 'فقط محصولاتی که تحویل داده شده‌اند قابل مرجوعی هستند.');
        }

        // بررسی مهلت مرجوعی
        if (!ReturnRequest::isWithinReturnPeriod($orderItem->id)) {
            return redirect()->back()->with('error', 'مهلت مرجوعی این محصول به پایان رسیده است.');
        }

        // بررسی قبلاً درخواست ثبت نشده
        $existingReturn = ReturnRequest::where('order_item_id', $orderItem->id)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->exists();

        if ($existingReturn) {
            return redirect()->back()->with('error', 'برای این محصول قبلاً درخواست مرجوعی ثبت شده است.');
        }

        $reasons = ReturnReason::active()->get();

        return view('front::user.returns.create', compact('order', 'orderItem', 'reasons'));
    }

    /**
     * ثبت درخواست مرجوعی
     */
    public function store(Request $request, Order $order, OrderItem $orderItem)
    {
        if ($order->user_id !== auth()->id()) abort(404);
        if ($orderItem->order_id !== $order->id) abort(404);

        $validated = $request->validate([
            'return_reason_id' => 'required|exists:return_reasons,id',
            'description'       => 'required|string|max:1000',
            'images.*'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'images'            => 'nullable|array|max:5',
        ], [
            'return_reason_id.required' => 'لطفاً دلیل مرجوعی را انتخاب کنید.',
            'description.required'       => 'لطفاً توضیحات محصول را وارد کنید.',
            'images.*.image'             => 'فقط فایل تصویری مجاز است.',
            'images.*.max'               => 'حداکثر حجم هر تصویر ۵ مگابایت.',
            'images.max'                  => 'حداکثر ۵ تصویر می‌توانید آپلود کنید.',
        ]);

        // بررسی مهلت مرجوعی
        if (!ReturnRequest::isWithinReturnPeriod($orderItem->id)) {
            return redirect()->back()->with('error', 'مهلت مرجوعی این محصول به پایان رسیده است.')->withInput();
        }

        $returnRequest = ReturnRequest::create([
            'order_id'          => $order->id,
            'order_item_id'     => $orderItem->id,
            'user_id'           => auth()->id(),
            'product_id'        => $orderItem->product_id,
            'seller_id'         => $orderItem->seller_id,
            'return_reason_id'  => $validated['return_reason_id'],
            'description'       => $validated['description'],
            'status'            => ReturnRequest::STATUS_PENDING,
            'refund_amount'     => (int) ($orderItem->price * $orderItem->quantity),
        ]);

        // آپلود تصاویر
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('returns/' . $returnRequest->id, 'public');
                ReturnImage::create([
                    'return_request_id' => $returnRequest->id,
                    'path'              => $path,
                    'original_name'     => $image->getClientOriginalName(),
                ]);
            }
        }

        // به‌روزرسانی وضعیت order_item
        $orderItem->update(['return_status' => 'pending']);

        return redirect()->route('front.returns.show', $returnRequest)
            ->with('success', 'درخواست مرجوعی شما با موفقیت ثبت شد. در حال بررسی است.');
    }

    /**
     * نمایش جزئیات درخواست مرجوعی
     */
    public function show(ReturnRequest $returnRequest)
    {
        if ($returnRequest->user_id !== auth()->id()) abort(404);

        $returnRequest->load(['order', 'orderItem.product', 'reason', 'images']);

        return view('front::user.returns.show', compact('returnRequest'));
    }

    /**
     * لغو درخواست مرجوعی توسط کاربر
     */
    public function cancel(ReturnRequest $returnRequest)
    {
        if ($returnRequest->user_id !== auth()->id()) abort(404);

        if (!$returnRequest->canBeCancelled()) {
            return redirect()->back()->with('error', 'این درخواست قابل لغو نیست.');
        }

        $returnRequest->update([
            'status'       => ReturnRequest::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        // بازگرداندن وضعیت order_item
        $returnRequest->orderItem->update(['return_status' => 'none']);

        return redirect()->route('front.returns.show', $returnRequest)
            ->with('success', 'درخواست مرجوعی لغو شد.');
    }
}
