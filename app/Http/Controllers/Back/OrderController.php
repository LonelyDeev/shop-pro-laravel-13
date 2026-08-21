<?php

namespace App\Http\Controllers\Back;

use App\Events\OrderCreated;
use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Back\Order\OrderStoreRequest;
use App\Http\Resources\Api\V1\Product\ProductResource;
use App\Http\Resources\Datatable\Order\OrderCollection;
use App\Models\Carrier;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Price;
use App\Models\Product;
use App\Models\Province;
use App\Models\Seller;
use App\Models\SizeType;
use App\Models\User;
use App\Models\WalletHistory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Order::class, 'order');
    }

    public function index()
    {
        $sizeTypes = SizeType::latest()->get();

        return view('back.orders.index' , compact('sizeTypes'));
    }

    public function apiIndex(Request $request)
    {
        $this->authorize('orders.index');

        $orders = Order::filter($request);

        $orders = datatable($request, $orders);

        return new OrderCollection($orders);
    }


    public function create()
    {
        $provinces = Province::detectLang()->orderBy('ordering')->get();
        $carriers  = Carrier::active()->get();

        return view('back.orders.create', compact('provinces', 'carriers'));
    }

    public function store(OrderStoreRequest $request)
    {
        $user = User::firstOrCreate(
            [
                'username' => $request->username
            ],
            [
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name
            ]
        );

        $order_price = 0;

        foreach ($request->products as $requestProduct) {
            $product = Product::find($requestProduct['id']);
            $price   = $product->prices()->find($requestProduct['price_id']);

            $orderItems[] = [
                'product_id'      => $product->id,
                'title'           => $product->title,
                'price'           => $price->discountPrice(),
                'real_price'      => $price->tomanPrice(),
                'quantity'        => $requestProduct['quantity'],
                'discount'        => $price->discount,
                'price_id'        => $price->id,
            ];

            $order_price += $price->discountPrice() * $requestProduct['quantity'];
        }

        $order_price += $request->shipping_cost;
        $order_price -= $request->discount_amount;

        $order = Order::create([
            'user_id'           => $user->id,
            'name'              => $request->first_name . ' ' . $request->last_name,
            'mobile'            => $request->username,
            'province_id'       => $request->province_id,
            'city_id'           => $request->city_id,
            'postal_code'       => $request->postal_code,
            'carrier_id'        => $request->carrier_id,
            'address'           => $request->address,
            'description'       => $request->description,
            'shipping_cost'     => $request->shipping_cost ?: 0,
            'status'            => 'paid',
            'shipping_status'   => $request->shipping_status,
            'discount_amount'   => $request->discount_amount,
            'price'             => $order_price
        ]);

        $order->items()->createMany($orderItems);

        event(new OrderCreated($order));

        return response('success');
    }

    public function show(Order $order)
    {
        $this->authorize('orders.view');

        // روش صحیح برای بارگذاری روابط
        $orderItems = $order->items()
            ->with(['product', 'seller', 'carrier'])
            ->latest()
            ->get();

        // طرح اقساطی در صورت وجود ماژول
        $installmentPlan = null;
        if (function_exists('module_is_active') && module_is_active('InstallmentPayment')) {
            $installmentPlan = \Modules\InstallmentPayment\Models\InstallmentPlan::where('order_id', $order->id)->first();
        }

        return view('back.orders.show', compact('order', 'orderItems', 'installmentPlan'));
    }

    public function destroy(Order $order)
    {
        $order->items()->delete();
        $order->transactions()->delete();

        $order->delete();
        session()->put('toast-success','سفارش با موفقیت حذف شد.');
        return redirect()->route('admin.orders.index');
    }

    public function multipleDestroy(Request $request)
    {
        $this->authorize('orders.delete');

        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:orders,id',
        ]);

        foreach ($request->ids as $id) {
            $order = Order::find($id);
            $this->destroy($order);
        }

        return response('success');
    }

    public function printAllShippingForms(Request $request)
    {
        $this->authorize('orders.view');

        $orders = Order::paid()->whereIn('id', $request->ids)->get();
        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';
        $orderIds = implode('، ', $request->ids);

        activity()
            ->causedBy(auth('adminPanel')->user())
            ->event('export')
            ->withProperties([
                'action' => 'print_shipping_forms',
                'order_ids' => $request->ids,
                'orders_count' => $orders->count(),
                'format' => 'shipping_forms',
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} فرم‌های حمل و نقل سفارش‌های {$orderIds} را چاپ کرد");

        return view('back.orders.print-all-shipping-forms', compact('orders'));
    }

    public function printAllShippingFormsMin(Request $request)
    {
        $this->authorize('orders.view');

        $orders = Order::paid()->whereIn('id', $request->ids)->get();
        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';
        $orderIds = implode('، ', $request->ids);

        activity()
            ->causedBy(auth('adminPanel')->user())
            ->event('export')
            ->withProperties([
                'action' => 'print_shipping_forms_min',
                'order_ids' => $request->ids,
                'orders_count' => $orders->count(),
                'format' => 'shipping_forms_min',
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} فرم‌های خلاصه حمل و نقل سفارش‌های {$orderIds} را چاپ کرد");

        return view('back.orders.print-all-shipping-form-min', compact('orders'));
    }

    public function printAll(Request $request)
    {
        $this->authorize('orders.view');

        $orders = Order::paid()->whereIn('id', $request->ids)->get();
        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';
        $orderIds = implode('، ', $request->ids);

        activity()
            ->causedBy(auth('adminPanel')->user())
            ->event('export')
            ->withProperties([
                'action' => 'print_orders',
                'order_ids' => $request->ids,
                'orders_count' => $orders->count(),
                'format' => 'print_all',
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} سفارش‌های {$orderIds} را چاپ کرد");

        return view('back.orders.print-all', compact('orders'));
    }

    public function print(Request $request, Order $order)
    {
        $this->authorize('orders.view');
        $id_seller = $request->seller_id;
        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';

        activity()
            ->performedOn($order)
            ->causedBy(auth('adminPanel')->user())
            ->event('export')
            ->withProperties([
                'action' => 'print_order',
                'order_id' => $order->id,
                'seller_id' => $id_seller,
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} سفارش شماره {$order->id} را چاپ کرد");

        return view('back.orders.print', compact('order', 'id_seller'));
    }

    public function shipping_status(Order $order, Request $request)
    {
        $this->authorize('orders.update');

        $this->validate($request, [
            'status' => 'required',
        ]);

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';
        $oldStatus = $order->shipping_status;
        $newStatus = $request->status;

        $order->update([
            'shipping_status' => $request->status
        ]);

        $order->reservedOrders()->update([
            'shipping_status' => $request->status
        ]);

        // ثبت لاگ تغییر وضعیت ارسال
        activity()
            ->performedOn($order)
            ->causedBy(auth('adminPanel')->user())
            ->event('updated')
            ->withProperties([
                'action' => 'update_shipping_status',
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'back_money' => $request->back_money_val ?? 'no',
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} وضعیت ارسال سفارش شماره {$order->id} را از {$oldStatus} به {$newStatus} تغییر داد");

        if ($request->back_money_val == 'yes' && $request->back_money_val) {
            $user = User::find($order->user_id);
            $walletHistory = WalletHistory::where(['wallet_id' => $user->wallet->id, 'order_id' => $order->id, 'orderCanceled' => 1])->first();

            foreach ($order->items as $item) {
                if ($item->seller_id) {
                    $seller = Seller::find($item->seller_id);
                    $walletHistorySellerAmount = WalletHistory::where(['wallet_id' => $seller->wallet->id, 'order_id' => $order->id])->first();
                    if ($walletHistorySellerAmount) {
                        $walletHistorySeller = WalletHistory::where(['wallet_id' => $seller->wallet->id, 'order_id' => $order->id, 'orderCanceled' => 1])->first();
                        if (!$walletHistorySeller) {
                            $request->merge([
                                'backMoney' => 'on',
                                'amount' => $walletHistorySellerAmount->amount,
                                'type' => 'withdraw',
                                'order_id' => $order->id,
                                'description' => 'لغو سفارش با شماره: ' . $order->id,
                            ]);

                            (new WalletController)->store($seller->wallet, $request);
                            sleep(1);
                        }
                    }
                }
            }

            if (!$walletHistory) {
                $request->merge([
                    'backMoney' => 'on',
                    'amount' => $order->price,
                    'type' => 'deposit',
                    'order_id' => $order->id,
                    'description' => 'لغو سفارش با شماره: ' . $order->id,
                ]);

                (new WalletController)->store($user->wallet, $request);
            }
        }

        return response('success');
    }

    public function shippingsStatus(Request $request)
    {
        $this->authorize('orders.update');

        $request->validate([
            'status' => 'required',
        ]);

        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';
        $orders = Order::whereIn('id', $request->ids)->get();
        $oldStatuses = [];

        foreach ($orders as $order) {
            $oldStatuses[$order->id] = $order->shipping_status;

            if (!$order->isPaid()) {
                throw ValidationException::withMessages(['id' => 'سفارش شماره ' . $order->id . ' پرداخت نشده است']);
            }

            if ($order->reserved()) {
                throw ValidationException::withMessages(['id' => 'سفارش شماره ' . $order->id . ' رزرو شده است']);
            }

            if ($request->status == 'canceled' && $request->backMoney) {
                $user = User::find($order->user_id);
                $walletHistory = WalletHistory::where(['wallet_id' => $user->wallet->id, 'order_id' => $order->id, 'orderCanceled' => 1])->first();
                if (!$walletHistory) {
                    $request->merge([
                        'amount' => $order->price,
                        'type' => 'deposit',
                        'order_id' => $order->id,
                        'description' => 'لغو سفارش با شماره: ' . $order->id,
                    ]);

                    (new WalletController)->store($user->wallet, $request);
                }
            }
        }

        foreach ($orders as $order) {
            $order->update([
                'shipping_status' => $request->status
            ]);

            $order->reservedOrders()->update([
                'shipping_status' => $request->status
            ]);
        }

        // ثبت لاگ تغییر گروهی وضعیت ارسال
        $orderIds = implode('، ', $request->ids);
        $newStatus = $request->status;
        $backMoneyText = $request->backMoney ? 'بازگشت وجه انجام شد' : 'بدون بازگشت وجه';

        activity()
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties([
                'action' => 'bulk_update_shipping_status',
                'order_ids' => $request->ids,
                'old_statuses' => $oldStatuses,
                'new_status' => $newStatus,
                'back_money' => $request->backMoney ?? 'no',
                'back_money_text' => $backMoneyText,
                'orders_count' => $orders->count(),
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} وضعیت ارسال {$orders->count()} سفارش (شماره‌های {$orderIds}) را به {$newStatus} تغییر داد");

        return response('success');
    }

    public function export(Request $request)
    {
        $this->authorize('orders.export');

        $orders = Order::filter($request)->get();
        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';
        $ordersCount = $orders->count();
        $format = $request->export_type ?? 'print';

        activity()
            ->causedBy(auth()->user())
            ->event('export')
            ->withProperties([
                'action' => 'export_orders',
                'export_type' => $format,
                'orders_count' => $ordersCount,
                'filters' => $request->except('export_type'),
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} خروجی {$ordersCount} سفارش را با فرمت {$format} دریافت کرد");

        switch ($request->export_type) {
            case 'excel':
                return $this->exportExcel($orders, $request);
                break;
            default:
                return $this->exportPrint($orders, $request);
        }
    }

    private function exportExcel($orders)
    {
        return Excel::download(new OrdersExport($orders), 'orders.xlsx');
    }


    public function notCompleted()
    {
        $this->authorize('orders.index');

        $prices = Price::whereHas('orderItems', function ($q) {
            $q->whereHas('order', function ($q2) {
                $q2->notCompleted();
            })->whereHas('product', function ($q3) {
                $q3->physical();
            });
        })->paginate(20);

        return view('back.orders.not-completed', compact('prices'));
    }

    public function shippingForm(Order $order)
    {
        $this->authorize('orders.view');

        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';

        activity()
            ->performedOn($order)
            ->causedBy(auth()->user())
            ->event('export')
            ->withProperties([
                'action' => 'view_shipping_form',
                'order_id' => $order->id,
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} فرم حمل و نقل سفارش شماره {$order->id} را مشاهده کرد");

        return view('back.orders.shipping-form', compact('order'));
    }

    public function shippingFormMin(Order $order)
    {
        $this->authorize('orders.view');

        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';

        activity()
            ->performedOn($order)
            ->causedBy(auth()->user())
            ->event('export')
            ->withProperties([
                'action' => 'view_shipping_form_min',
                'order_id' => $order->id,
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} فرم خلاصه حمل و نقل سفارش شماره {$order->id} را مشاهده کرد");

        return view('back.orders.shipping-form-min', compact('order'));
    }

    public function set_tracking_code(Request $request, Order $order)
    {
        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';
        $oldTrackingCode = $order->tracking_code;
        $newTrackingCode = $request->code;

        $order->tracking_code = $request->code;
        $order->save();

        activity()
            ->performedOn($order)
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties([
                'action' => 'set_tracking_code',
                'order_id' => $order->id,
                'old_tracking_code' => $oldTrackingCode,
                'new_tracking_code' => $newTrackingCode,
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} کد رهگیری سفارش شماره {$order->id} را از «{$oldTrackingCode}» به «{$newTrackingCode}» تغییر داد");
    }


    public function showItem(OrderItem $orderItem)
    {
        // لود کردن روابط لازم
        $orderItem->load(['order', 'product', 'seller.seller_info']);

        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';

        activity()
            ->performedOn($orderItem->order)
            ->causedBy(auth()->user())
            ->event('view')
            ->withProperties([
                'action' => 'view_order_item',
                'order_id' => $orderItem->order->id,
                'order_item_id' => $orderItem->id,
                'product_title' => $orderItem->product->title ?? 'نامشخص',
                'seller_id' => $orderItem->seller_id,
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} جزئیات آیتم سفارش شماره {$orderItem->order->id} را مشاهده کرد");

        // اطلاعات فروشنده
        $sellerName = $orderItem->seller
            ? ($orderItem->seller->seller_info->business_name ?? $orderItem->seller->name ?? 'فروشنده')
            : 'فروشگاه اصلی';

        // گرفتن تمام محصولات این فروشنده در این سفارش
        $sellerItems = $orderItem->order->items->where('seller_id', $orderItem->seller_id);

        // بررسی لغو شدن سفارش
        $orderCanceled = null;
        if ($orderItem->order && $orderItem->order->user) {
            $orderCanceled = WalletHistory::where([
                'order_id' => $orderItem->order->id,
                'order_item_id' => $orderItem->id,
                'orderCanceled' => 1
            ])->first();
        }

        return view('back.orders.order-item', compact(
            'orderItem',
            'sellerName',
            'sellerItems',
            'orderCanceled'
        ));
    }

    public function itemShippingStatus(Request $request, OrderItem $orderItem)
    {
        $this->authorize('orders.update');

        $request->validate([
            'shipping_status' => 'required|in:w-pending,pending,processing,waiting,sent,post-sent,delivered,canceled,refunded',
            'cancel_reason' => 'nullable|string|max:500',
            'canceled_refund_amount' => 'nullable|boolean'
        ]);

        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';
        $order = $orderItem->order()->first();
        $sellerId = $orderItem->seller_id;
        $oldStatus = $orderItem->shipping_status;
        $newStatus = $request->shipping_status;

        // بررسی پرداخت شده بودن سفارش
        if (!$order->isPaid()) {
            throw ValidationException::withMessages([
                'message' => 'سفارش پرداخت نشده است'
            ]);
        }

        if ($orderItem->refunded) {
            throw ValidationException::withMessages([
                'message' => 'این آیتم سفارش قبلاً لغو شده و وجه آن برگشت داده شده است. امکان تغییر وضعیت وجود ندارد.'
            ]);
        }

        if ($request->shipping_status=="delivered"){
            $order->items()->where('seller_id', $sellerId)->update([
                'delivery_date' =>now()
            ]);
        }

        // به‌روزرسانی وضعیت ارسال برای همه آیتم‌های同一 فروشنده
        $order->items()->where('seller_id', $sellerId)->update([
            'shipping_status' => $request->shipping_status
        ]);

        // ذخیره دلیل لغو (در صورت وجود)
        $cancelReason = null;
        if ($request->filled('cancel_reason')) {
            $cancelReason = $request->cancel_reason;
            $order->items()->where('seller_id', $sellerId)->update([
                'cancel_reason' => $request->cancel_reason,
                'canceled_at' => now()
            ]);
        }

        // ثبت لاگ تغییر وضعیت ارسال آیتم سفارش
        $logMessage = "مدیر {$adminName} وضعیت ارسال آیتم سفارش شماره {$order->id} (محصول: {$orderItem->product->title}) را از {$oldStatus} به {$newStatus} تغییر داد";
        if ($cancelReason) {
            $logMessage .= " (دلیل لغو: {$cancelReason})";
        }

        activity()
            ->performedOn($order)
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties([
                'action' => 'update_item_shipping_status',
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'product_title' => $orderItem->product->title ?? 'نامشخص',
                'seller_id' => $sellerId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'cancel_reason' => $cancelReason,
                'refund_amount' => $request->boolean('canceled_refund_amount'),
                'ip' => request()->ip()
            ])
            ->log($logMessage);

        // در صورت لغو سفارش و بازگشت وجه
        if ($request->shipping_status == 'canceled') {
            $this->refundOrderItems($order, $orderItem, $sellerId);
        }

        return response()->json([
            'success' => true,
            'message' => 'وضعیت با موفقیت تغییر کرد'
        ]);
    }

    private function createWalletHistory($wallet, $type, $amount, $orderId, $orderItemId, $description, $source = 'admin')
    {
        return $wallet->histories()->create([
            'type' => $type,  // deposit (واریز) یا withdraw (برداشت)
            'source' => $source, // admin, seller, user
            'status' => 'success',
            'amount' => $amount,
            'order_id' => $orderId,
            'order_item_id' => $orderItemId,
            'description' => $description,
            'orderCanceled' => 1
        ]);
    }

    public function itemTrackingStatus(Request $request, OrderItem $orderItem)
    {
        $this->authorize('orders.update');

        $request->validate([
            'tracking_code' => 'required',
        ]);

        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';
        $order = $orderItem->order()->first();
        $sellerId = $orderItem->seller_id;

        // بررسی پرداخت شده بودن سفارش
        if (!$order->isPaid()) {
            throw ValidationException::withMessages([
                'message' => 'سفارش پرداخت نشده است'
            ]);
        }

        if ($orderItem->refunded) {
            throw ValidationException::withMessages([
                'message' => 'این آیتم سفارش قبلاً لغو شده و وجه آن برگشت داده شده است. امکان ثبت کد رهگیری وجود ندارد.'
            ]);
        }

        $oldTrackingCode = $orderItem->tracking_code;
        $newTrackingCode = $request->tracking_code;

        $order->items()->where('seller_id', $sellerId)->update([
            'tracking_code' => $request->tracking_code,
        ]);

        activity()
            ->performedOn($order)
            ->causedBy(auth()->user())
            ->event('updated')
            ->withProperties([
                'action' => 'set_item_tracking_code',
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'product_title' => $orderItem->product->title ?? 'نامشخص',
                'seller_id' => $sellerId,
                'old_tracking_code' => $oldTrackingCode,
                'new_tracking_code' => $newTrackingCode,
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} کد رهگیری آیتم سفارش شماره {$order->id} (محصول: {$orderItem->product->title}) را از «{$oldTrackingCode}» به «{$newTrackingCode}» تغییر داد");

        return response('success');
    }
    public function userInfo(Request $request)
    {
        $this->authorize('orders.create');

        $request->validate([
            'input' => 'required|in:username',
        ]);

        if (!$request->term) {
            return;
        }

        $input = $request->input('input');
        $term  = $request->input('term');

        switch ($input) {
            case "username": {
                    $users = User::with('address')
                        ->where('username', 'like', "%$term%")
                        ->latest()->take(10)
                        ->get();
                    break;
                }
        }

        return response()->json($users);
    }

    public function productsList(Request $request)
    {
        $this->authorize('orders.create');

        $term = $request->term;

        if (!$term) {
            return;
        }

        $products = Product::with('getPrices')
            ->available()
            ->where(function ($query) use ($term) {
                $query->where('title', 'like', "%$term%")->orWhere('title_en', 'like', "%$term%");
            })
            ->orderByStock()
            ->latest()
            ->take(10)
            ->get();

        return ProductResource::collection($products);
    }




    /**
     * بازگشت وجه آیتم‌های سفارش
     */
    private function refundOrderItems(Order $order, OrderItem $orderItem, $sellerId)
    {
        $user = $order->user;
        $orderItems = $order->items()->where('seller_id', $sellerId)->get();
        // محاسبه مبلغ کل برای بازگشت
        $totalAmount = $orderItems->sum('price') + $orderItems->first()?->shipping_cost;

        $commission=$orderItem->commission;
        $commissionPrice=$totalAmount*$commission/100;
        $totalAmountSeller=$totalAmount-$commissionPrice;

        // ========== بررسی موجودی کیف پول فروشنده ==========
        if ($orderItem->seller_id) {
            $seller = $orderItem->seller;

            if (!$seller || !$seller->wallet) {
                throw ValidationException::withMessages([
                    'message' => 'کیف پول فروشنده یافت نشد'
                ]);
            }

            // بررسی موجودی کافی فروشنده
            if ($seller->getWallet()->balance() < $totalAmountSeller) {
                throw ValidationException::withMessages([
                    'message' => "موجودی کیف پول فروشنده ({$seller->getWallet()->balance()}) برای برگشت وجه کافی نیست. موجودی فعلی: " . number_format($seller->getWallet()->balance()) . " تومان"
                ]);
            }
        }

        // ========== 1. کم کردن از حساب فروشنده (برداشت از کیف پول فروشنده) ==========
        if ($orderItem->seller_id) {
            $seller = $orderItem->seller;

            // کم کردن از موجودی فروشنده
            $seller->wallet->decrement('balance', $totalAmountSeller);

            // ثبت در تاریخچه کیف پول فروشنده (برداشت)
            $this->createWalletHistory(
                $seller->wallet,
                'withdraw',
                $totalAmountSeller,
                $order->id,
                $orderItem->id,
                "برداشت بابت لغو سفارش شماره {$order->id} - فروشنده: {$seller->getWallet()->balance()}",
                'seller'
            );
        }

        // ========== 2. اضافه کردن به حساب کاربر (واریز به کیف پول کاربر) ==========
        $user = $order->user;

        if (!$user || !$user->wallet) {
            throw ValidationException::withMessages([
                'message' => 'کیف پول کاربر یافت نشد'
            ]);
        }

        // بررسی اینکه قبلاً برای این سفارش برگشت وجه انجام نشده باشد
        $existingHistory = WalletHistory::where([
            'wallet_id' => $user->wallet->id,
            'order_id' => $order->id,
            'orderCanceled' => 1
        ])->first();

        if (!$existingHistory) {
            // اضافه کردن به موجودی کاربر
            $user->wallet->increment('balance', $totalAmount);

            // ثبت در تاریخچه کیف پول کاربر (واریز)
            $this->createWalletHistory(
                $user->wallet,
                'deposit',
                $totalAmount,
                $order->id,
                $orderItem->id,
                "بازگشت وجه سفارش لغو شده شماره {$order->id}",
                'user'
            );
        }

        // ========== 3. به‌روزرسانی وضعیت refund در order_items ==========
        $order->items()->where('seller_id', $sellerId)->update([
            'refunded' => true,
            'refunded_at' => now(),
            'refunded_amount' => $totalAmount
        ]);


        $order->items()->where('seller_id', $sellerId)->get()->each(function ($item) {
            $item->get_price()->first()->increment('stock', $item->quantity);
        });
    }

    /**
     * ایجاد تاریخچه کیف پول
     */

}
