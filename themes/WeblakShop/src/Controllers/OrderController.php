<?php

namespace Themes\WeblakShop\src\Controllers;

use App\Events\OrderCreated;
use App\Events\OrderPaid as OrderPaidEvent;
use App\Http\Controllers\Controller;
use App\Jobs\CancelOrder;
use App\Models\Address;
use App\Models\Carrier;
use App\Models\City;
use App\Models\Gateway;
use App\Models\Order;
use App\Models\Price;
use App\Models\Seller;
use App\Models\SellerVariant;
use App\Models\Sms;
use App\Models\Tariff;
use App\Models\Transaction;
use App\Models\WalletHistory;
use App\Notifications\Order\OrderCancelled as OrderCancelledNotification;
use App\Notifications\Sms\OrderCancelledSms;
use App\Services\HolidayService;
use App\Services\Sms\SmsService;
use App\Services\StockMovementService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Shetabit\Payment\Facade\Payment;
use Shetabit\Multipay\Invoice;
use Themes\WeblakShop\src\Requests\StoreOrderRequest;

class OrderController extends Controller
{
    protected $holidayService;
    protected StockMovementService $stockService;

    public function __construct(HolidayService $holidayService,StockMovementService $stockService)
    {
        $this->holidayService = $holidayService;
        $this->stockService = $stockService;
    }
    public function index()
    {
        $orders = auth()->user()->orders()->latest()->paginate(10);
        $active="orders";
        return view('front::user.orders.index', compact('orders','active'));
    }

    public function show(Order $order)
    {
        if ($order->user_id != auth()->user()->id) {
            abort(404);
        }

        $active = "orders";
        $gateways = Gateway::active()->get();
        $wallet = auth()->user()->getWallet();

        // بارگذاری روابط با Eager Loading
        $order->load([
            'items.product',
            'items.carrier',
            'items.get_price',
            'items.get_price.attributes',
            'items.get_price.attributes.group',
            'transactions',
            'user'
        ]);

        return view('front::user.orders.show', compact(
            'order',
            'gateways',
            'wallet',
            'active'
        ));
    }

    public function store(StoreOrderRequest $request)
    {
        $user = auth()->user();
        $cart = $user->cart;
        $address = Address::find($request->address);

        if (!$cart || !$cart->products->count() || !check_cart_quantity()) {
            return redirect()->route('front.cart');
        }

        if (!check_cart_discount()['status']) {
            return redirect()->route('front.checkout');
        }

        // ========== دسته‌بندی محصولات بر اساس فروشنده ==========
        $sellerGroups = [];

        foreach ($cart->products as $product) {
            $price = Price::with(['seller'])->find($product->pivot->price_id);
            if (!$price) continue;

            $sellerId = $price->seller_id;
            $groupId = $sellerId ? 'seller_' . $sellerId : 'store';

            if (!isset($sellerGroups[$groupId])) {
                if ($sellerId) {
                    $seller = Seller::find($sellerId);
                    $sellerInfo = $seller ? [
                        'id' => $seller->id,
                        'name' => $seller->seller_info->business_name ?? $seller->name,
                    ] : null;
                } else {
                    $sellerInfo = null;
                }

                // روش ارسال انتخاب شده برای این گروه
                $selectedCarrierId = $request->input("carrier_id_{$groupId}");
                $selectedDeliveryDate = $request->input("carrier_date_{$groupId}");

                $sellerGroups[$groupId] = [
                    'seller_id' => $sellerId,
                    'seller_info' => $sellerInfo,
                    'name' => $sellerInfo ? $sellerInfo['name'] : option('info_site_title'),
                    'groupId' => $groupId,
                    'products' => [],
                    'total_weight' => 0,
                    'price' => 0,
                    'total_price' => 0,
                    'carrier_id' => $selectedCarrierId,
                    'delivery_date' => $selectedDeliveryDate,
                    'shipping_cost' => 0
                ];
            }

            $sellerGroups[$groupId]['products'][] = [
                'product' => $product,
                'price' => $price,
                'quantity' => $product->pivot->quantity,
                'weight' => ($product->weight ?? 0) * $product->pivot->quantity,
                'final_price' => $price->discount_price ?? $price->price
            ];
            $sellerGroups[$groupId]['total_weight'] += ($product->weight ?? 0) * $product->pivot->quantity;
            $sellerGroups[$groupId]['price'] += ($price->price) * $product->pivot->quantity;
            $sellerGroups[$groupId]['total_price'] += ($price->discount_price ?? $price->price) * $product->pivot->quantity;
        }

        // ========== اعتبارسنجی و محاسبه هزینه ارسال برای هر گروه ==========
        $totalShippingCost = 0;
        $subtotal = 0;
        $errors = [];

        foreach ($sellerGroups as $groupId => &$group) {
            $subtotal += $group['price'];

            // 1. بررسی وجود روش ارسال
            if (!$group['carrier_id']) {
                $errors[] = "روش ارسال برای مرسوله {$group['name']} انتخاب نشده است";
                continue;
            }

            $carrier = Carrier::find($group['carrier_id']);
            if (!$carrier) {
                $errors[] = "روش ارسال نامعتبر برای مرسوله {$group['name']}";
                continue;
            }

            // 2. بررسی امکان استفاده از روش ارسال
            $carrierResult = $cart->canUseCarrier($carrier->id, $address->city_id);
            if (!$carrierResult['status']) {
                $errors[] = "روش ارسال {$carrier->title} برای مرسوله {$group['name']} قابل استفاده نیست: {$carrierResult['message']}";
                continue;
            }

            // 3. محاسبه هزینه ارسال
            $shippingCost = $this->calculateCarrierCost($carrier, $group['total_weight'], $address->city_id);
            if ($shippingCost === false) {
                $errors[] = "محاسبه هزینه ارسال برای مرسوله {$group['name']} امکان پذیر نیست";
                continue;
            }

            $group['shipping_cost'] = $shippingCost;
            $totalShippingCost += $shippingCost;

            // 4. اعتبارسنجی تاریخ تحویل برای روش‌های user_select
            if ($carrier->delivery_time_type == 'user_select') {
                if (!$group['delivery_date']) {
                    $errors[] = "لطفاً تاریخ تحویل برای مرسوله {$group['name']} را انتخاب کنید";
                    continue;
                }

                $deliveryDates = $this->getDeliveryDates($carrier);
                $isValidDate = false;
                foreach ($deliveryDates as $date) {
                    if ($date['date'] == $group['delivery_date'] && $date['is_selectable']) {
                        $isValidDate = true;
                        break;
                    }
                }

                if (!$isValidDate) {
                    $errors[] = "تاریخ تحویل انتخاب شده برای مرسوله {$group['name']} معتبر نیست";
                    continue;
                }
            }
        }

        if (!empty($errors)) {
            return redirect()->back()->withInput()->withErrors($errors);
        }

        // ========== ایجاد سفارش اصلی ==========
        $gateway = Gateway::where('key', $request->gateway)->first();
        $data = $request->validated();

        $discountAmount = $cart->totalDiscount();
        $finalPrice = $subtotal + $totalShippingCost - $discountAmount;

        $data['shipping_cost'] = $totalShippingCost;
        $data['price'] = $finalPrice;
        $data['status'] = 'unpaid';
        $data['discount_amount'] = $discountAmount;
        $data['discount_id'] = $cart->discount_id;

        if ($cart->discount) {
            if ($cart->discount->type == "percent") {
                $data['discount_percent'] = $cart->discount->amount;
            } else {
                $data['discount_price'] = $cart->discount->amount;
            }
        }

        $data['user_id'] = $user->id;
        $data['name'] = $address->fullname;
        $data['mobile'] = $address->mobile;
        $data['province_id'] = $address->province_id;
        $data['city_id'] = $address->city_id;
        $data['postal_code'] = $address->postal_code;
        $data['address'] = $address->address;
        $data['location'] = $address->lat.','.$address->lng;

        if ($gateway) {
            $data['gateway_id'] = $gateway->id;
        }

        $order = Order::create($data);

        // ========== ایجاد آیتم‌های سفارش (فقط با حلقه $cart->products) ==========
        $sellerIds = [];

        foreach ($cart->products as $product) {
            $price = $price = Price::with(['seller'])->find($product->pivot->price_id);

            if (!$price) continue;

            // پیدا کردن گروه مربوط به این محصول برای دریافت carrier_id و delivery_date
            $sellerId = $price->seller_id;
            $groupId = $sellerId ? 'seller_' . $sellerId : 'store';
            $group = $sellerGroups[$groupId] ?? null;


            if ($request->has('carrier_id_store') and $request->input('carrier_id_store') !== '') {
                $carrier=Carrier::find($request->input('carrier_id_store'));
                $carrier_id=$carrier->id;
                $delivery_date=$carrier->default_delivery_range;
                if ($carrier->delivery_time_type == 'user_select') {
                    $delivery_date=$request->input('carrier_date_store');
                }

                $shipping_cost= $this->calculateCarrierCost($carrier,$product->weight,$address->city_id);
            }

            if ($request->has('carrier_id_seller_'.$price->seller_id) and $request->input('carrier_id_seller_'.$price->seller_id) !== '') {
                $carrier=Carrier::find($request->input('carrier_id_seller_'.$price->seller_id));
                $carrier_id=$carrier->id;
                $delivery_date=$carrier->default_delivery_range;
                if ($carrier->delivery_time_type == 'user_select') {
                    $delivery_date=$request->input('carrier_date_seller_'.$price->seller_id);
                }
                $shipping_cost= $this->calculateCarrierCost($carrier,$product->weight,$address->city_id);
            }

            $allAttributes = [];
            $get_attributes = $price->get_attributes()->with('group')->get();

            foreach ($get_attributes as $attribute) {
                $attribute_groups_name = $attribute->group->name;

                // اگر گروه قبلاً وجود دارد، به آرایه اضافه کن
                if (!isset($allAttributes[$attribute_groups_name])) {
                    $allAttributes[$attribute_groups_name] = [];
                }

                $allAttributes[$attribute_groups_name][] = [
                    'name' => $attribute->name,
                    'value' => $attribute->value,
                ];
            }

            $allAttributes = json_encode($allAttributes);


            if ($price) {
                $order->items()->create([
                    'product_id' => $product->id,
                    'seller_id' => $price->seller_id,
                    'title' => $product->title,
                    'price' => $price->discount_price ?? $price->price,
                    'real_price' => $price->price,
                    'quantity' => $product->pivot->quantity,
                    'discount' => $price->discount ?? 0,
                    'price_id' => $product->pivot->price_id,
                    'shipping_cost' => $shipping_cost ? $shipping_cost : 0,
                    'carrier_id' => $carrier_id ? $carrier_id : null,
                    'delivery_date' => $delivery_date ? $delivery_date : null,
                    'attributes' => $allAttributes,
                ]);

                //$price->decrement('stock', $product->pivot->quantity);
            }

            if ($price->seller_id) {
                $sellerIds[] = $price->seller_id;
            }
        }

        if (count($sellerIds)) {
            $order->update(['seller_id' => json_encode(array_unique($sellerIds))]);
        }

        $cart->delete();

        $hour = option('order_cancel', 1);
        CancelOrder::dispatch($order)->delay(now()->addHours($hour));

        event(new OrderCreated($order));

        return $this->pay($order, $request);
    }
    private function calculateCarrierCost($carrier, $weight, $cityId)
    {
        if ($carrier->free_shipping_weight && $weight >= $carrier->free_shipping_weight) {
            return 0;
        }

        $destinationCity = City::find($cityId);
        if (!$destinationCity) {
            return false;
        }

        $destinationProvinceId = $destinationCity->province_id;
        $carrierProvinceId = $carrier->province_id;
        $isWithinProvince = ($destinationProvinceId == $carrierProvinceId);
        $type = $isWithinProvince ? 'within_province' : 'extra_province';

        $tariff = Tariff::where('carrier_id', $carrier->id)
            ->where('type', $type)
            ->where('max_weight', '>=', $weight)
            ->orderBy('max_weight', 'asc')
            ->first();

        if ($tariff) {
            return $tariff->shipping_cost;
        }

        if ($carrier->extra_cost) {
            return $weight * $carrier->extra_cost;
        }

        return false;
    }

    private function getDeliveryDates($carrier)
    {
        $rangeDays = (int) $carrier->user_select_ranges;
        if (!$rangeDays || $rangeDays <= 0) {
            $rangeDays = 7;
        }

        $startDate = now()->addDays($carrier->start_days_after_order);
        $dates = [];

        for ($i = 0; $i < $rangeDays; $i++) {
            $currentDate = clone $startDate;
            $currentDate->addDays($i);

            $jalali = \Morilog\Jalali\Jalalian::fromDateTime($currentDate);
            $isFriday = $currentDate->dayOfWeek == 5;
            $isHoliday = $this->isHoliday($currentDate);

            $isSelectable = true;
            if ($carrier->disable_fridays && $isFriday) $isSelectable = false;
            if ($carrier->disable_holidays && $isHoliday) $isSelectable = false;

            $dates[] = [
                'date' => $currentDate->format('Y-m-d'),
                'jalali' => $jalali->format('Y/m/d'),
                'display' => $jalali->format('l j F Y'),
                'displayDate' => $jalali->format('j F Y'),
                'day_name' => $jalali->format('l'),
                'is_friday' => $isFriday,
                'is_holiday' => $isHoliday,
                'is_selectable' => $isSelectable
            ];
        }

        return $dates;
    }

    private function isHoliday($date)
    {
        $jalali = \Morilog\Jalali\Jalalian::fromDateTime($date);
        $jalaliDate = $jalali->format('Y/m/d');

        // استفاده از HolidayService
        return $this->holidayService->isHoliday($jalaliDate);
    }



    public function pay(Order $order, Request $request)
    {
        if ($order->user_id != auth()->user()->id) {
            abort(404);
        }

        if ($order->status != 'unpaid') {
            return redirect()->route('front.orders.show', ['order' => $order])->with('error', 'سفارش شما لغو شده است یا قبلا پرداخت کرده اید');
        }

        // ========== رزرو موجودی قبل از پرداخت ==========
        try {
            DB::transaction(function () use ($order) {
                foreach ($order->items as $item) {
                    $price = $item->get_price;

                    if ($price) {
                        // بررسی موجودی کافی
                        $availableStock = $price->stock - $price->reserved_stock;
                        if ($availableStock < $item->quantity) {
                            throw new \Exception("موجودی کافی برای محصول {$item->product->title} وجود ندارد");
                        }

                        // رزرو موجودی
                        $this->stockService->reserve(
                            $price,
                            $item->quantity,
                            $order->id,
                            $item->id
                        );
                    }
                }
            });
        } catch (\Exception $e) {
            return redirect()->route('front.orders.show', ['order' => $order])
                ->with('error', $e->getMessage());
        }


        if ($order->price == 0) {
            return $this->orderPaid($order);
        }

        $gateways = Gateway::active()->pluck('key')->toArray();

        $request->validate([
            'gateway' => 'required|in:wallet,' . implode(',', $gateways)
        ]);

        $gateway = $request->gateway;

        if ($gateway == 'wallet') {
            return $this->payUsingWallet($order);
        }

        try {

            $gateway_configs = get_gateway_configs($gateway);

            return Payment::via($gateway)->config($gateway_configs)->callbackUrl(route('front.orders.verify', ['gateway' => $gateway]))->purchase(
                (new Invoice)->amount(intval($order->price)),
                function ($driver, $transactionId) use ($order, $gateway) {
                    DB::table('transactions')->insert([
                        'status'               => false,
                        'amount'               => $order->price,
                        'factorNumber'         => $order->id,
                        'mobile'               => auth()->user()->username,
                        'message'              => 'تراکنش ایجاد شد برای درگاه ' . $gateway,
                        'transID'              => (string) $transactionId,
                        'token'                => (string) $transactionId,
                        'user_id'              => auth()->user()->id,
                        'transactionable_type' => Order::class,
                        'transactionable_id'   => $order->id,
                        'gateway_id'           => Gateway::where('key', $gateway)->first()->id,
                        "created_at"           => Carbon::now(),
                        "updated_at"           => Carbon::now(),
                    ]);

                    session()->put('transactionId', (string) $transactionId);
                    session()->put('amount', $order->price);
                }
            )->pay()->render();
        } catch (Exception $e) {
            return redirect()
                ->route('front.orderResultInfo', ['order' => $order])
                ->with('transaction-error', $e->getMessage())
                ->with('order_id', $order->id);
        }
    }

    public function verify($gateway)
    {
        $transactionId = session()->get('transactionId');
        $amount = session()->get('amount');

        $transaction = Transaction::where('status', false)->where('transID', $transactionId)->firstOrFail();

        $order = $transaction->transactionable;

        $gateway_configs = get_gateway_configs($gateway);

        try {
            $receipt = Payment::via($gateway)->config($gateway_configs);

            if ($amount) {
                $receipt = $receipt->amount(intval($amount));
            }

            $receipt = $receipt->transactionId($transactionId)->verify();

            DB::table('transactions')->where('transID', (string) $transactionId)->update([
                'status'               => 1,
                'amount'               => $order->price,
                'factorNumber'         => $order->id,
                'mobile'               => $order->mobile,
                'traceNumber'          => $receipt->getReferenceId(),
                'message'              => $transaction->message . '<br>' . 'پرداخت موفق با درگاه ' . $gateway,
                'updated_at'           => Carbon::now(),
            ]);

            return $this->orderPaid($order);
        } catch (\Exception $exception) {

            DB::table('transactions')->where('transID', (string) $transactionId)->update([
                'message'              => $transaction->message . '<br>' . $exception->getMessage(),
                "updated_at"           => Carbon::now(),
            ]);

            return redirect()->route('front.orderResultInfo', ['order' => $order])->with('transaction-error', $exception->getMessage());
        }
    }

    private function payUsingWallet(Order $order)
    {
        $wallet  = $order->user->getWallet();
        $amount  = intval($wallet->balance() - $order->price);

        if ($amount >= 0) {
            $result = $order->payUsingWallet();
            if ($result) {
                return $this->orderPaid($order);
            }
        }

        $gateway = Gateway::active()->orderBy('ordering')->first();
        $amount  = abs($amount);

        if (!$gateway) {
            return redirect()->route('front.orders.show', ['order' => $order])
                ->with('transaction-error', 'درگاه فعالی برای پرداخت یافت نشد')
                ->with('order_id', $order->id);
        }

        $history = $wallet->histories()->create([
            'type'        => 'deposit',
            'amount'      => $amount,
            'description' => 'شارژ آنلاین کیف پول برای ثبت سفارش',
            'source'      => 'user',
            'status'      => 'fail',
            'order_id'    => $order->id
        ]);

        try {
            $gateway         = $gateway->key;
            $gateway_configs = get_gateway_configs($gateway);

            return Payment::via($gateway)->config($gateway_configs)->callbackUrl(route('front.wallet.verify', ['gateway' => $gateway]))->purchase(
                (new Invoice)->amount($amount),
                function ($driver, $transactionId) use ($history, $gateway, $amount) {
                    DB::table('transactions')->insert([
                        'status'               => false,
                        'amount'               => $amount,
                        'factorNumber'         => $history->id,
                        'mobile'               => auth()->user()->username,
                        'message'              => 'تراکنش ایجاد شد برای درگاه ' . $gateway,
                        'transID'              => $transactionId,
                        'token'                => $transactionId,
                        'user_id'              => auth()->user()->id,
                        'transactionable_type' => WalletHistory::class,
                        'transactionable_id'   => $history->id,
                        'gateway_id'           => Gateway::where('key', $gateway)->first()->id,
                        "created_at"           => Carbon::now(),
                        "updated_at"           => Carbon::now(),
                    ]);

                    session()->put('transactionId', $transactionId);
                    session()->put('amount', $amount);
                }
            )->pay()->render();
        } catch (Exception $e) {
            return redirect()->route('front.orderResultInfo', ['order' => $order])
                ->with('transaction-error', $e->getMessage())
                ->with('order_id', $order->id);
        }
    }

    /**
     * پرداخت سفارش و تایید نهایی
     */
    private function orderPaid(Order $order)
    {
        DB::beginTransaction();

        try {

            // ========== بررسی مجدد موجودی با قفل ==========
            foreach ($order->items as $item) {
                $price = $item->get_price;

                if (!$price) {
                    throw new Exception("محصول {$item->title} یافت نشد");
                }

                // قفل کردن رکورد قیمت برای بررسی اتمیک
                $lockedPrice = Price::where('id', $price->id)->lockForUpdate()->first();

                // محاسبه موجودی واقعی (با در نظر گرفتن رزروهای دیگر)
                $availableStock = $lockedPrice->stock - ($lockedPrice->reserved_stock ?? 0);

                if ($availableStock < $item->quantity) {
                    throw new Exception(
                        "موجودی محصول {$item->title} کافی نیست. " .
                        "موجودی فعلی: {$availableStock} - درخواستی: {$item->quantity}"
                    );
                }
            }

            // ========== ثبت حرکات انبار ==========
            foreach ($order->items as $item) {
                $price = $item->get_price;

                if ($price) {
                    // تایید رزرو و خروج از انبار
                    $this->stockService->confirmReservation(
                        $price,
                        $item->quantity,
                        $order->id,
                        $item->id
                    );

                    // به‌روزرسانی موجودی در خود آیتم
                    $item->update([
                        'stock_before' => $price->stock + $item->quantity,
                        'stock_after' => $price->stock,
                    ]);
                }
            }

            // به‌روزرسانی وضعیت سفارش
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            DB::commit();

            // ارسال ایونت پرداخت موفق
            event(new OrderPaidEvent($order));

            return redirect()->route('front.orderResultInfo', ['order' => $order])
                ->with('message', 'پرداخت با موفقیت انجام شد');

        } catch (Exception $e) {
            DB::rollBack();

            // لاگ خطا
            Log::warning('خطا در هنگام پرداخت سفارش', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            // ========== لغو سفارش و برگشت هزینه ==========
            return $this->cancelOrderAndRefund($order, $e->getMessage());
        }
    }

    /**
     * لغو سفارش و برگشت هزینه به کیف پول کاربر
     */
    private function cancelOrderAndRefund(Order $order, $errorMessage)
    {
        DB::beginTransaction();

        try {
            $refundAmount = 0;

            // 1. برگشت موجودی رزرو شده (اگر قبلاً رزرو شده)
            foreach ($order->items as $item) {
                $price = $item->get_price;
                if ($price && ($price->reserved_stock ?? 0) >= $item->quantity) {
                    $decremented = DB::table('prices')
                        ->where('id', $price->id)
                        ->where('reserved_stock', '>=', $item->quantity)
                        ->decrement('reserved_stock', $item->quantity);

                    if (!$decremented) {
                        Log::warning("خطا در برگشت موجودی رزرو شده", [
                            'order_id' => $order->id,
                            'item_id' => $item->id,
                            'price_id' => $price->id
                        ]);
                    }
                }
            }

            // 2. برگشت هزینه به کیف پول کاربر (اگر پرداخت شده باشد)
            // بررسی وجود تراکنش موفق برای این سفارش
            $transaction = $order->transactions()
                ->where('status', true)
                ->where('amount', $order->price)
                ->first();

            if ($transaction) {
                // افزایش موجودی کیف پول
                $wallet = $order->user->getWallet();
                $depositResult = $wallet->deposit(
                    $order->price,
                    "برگشت هزینه سفارش لغو شده #{$order->id} - دلیل: {$errorMessage}"
                );

                if ($depositResult) {
                    $refundAmount = $order->price;

                    // ثبت تاریخچه برگشت وجه
                    WalletHistory::create([
                        'user_id' => $order->user_id,
                        'type' => 'refund',
                        'amount' => $order->price,
                        'description' => "برگشت هزینه سفارش لغو شده #{$order->id} - {$errorMessage}",
                        'status' => 'success',
                        'order_id' => $order->id
                    ]);

                    Log::info("برگشت وجه به کیف پول کاربر", [
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                        'amount' => $order->price
                    ]);
                }
            }

            // 3. به‌روزرسانی وضعیت سفارش
            $order->update([
                'status' => 'cancelled',
                'failed_reason' => $errorMessage,
                'failed_at' => now(),
            ]);

            DB::commit();

            // 4. ارسال ایونت لغو سفارش
            event(new OrderCancelledNotification($order, $errorMessage, $refundAmount));

            // 5. ارسال نوتیفیکیشن به کاربر
            $this->sendOrderCancellationNotifications($order, $errorMessage, $refundAmount);

            // 6. ارسال نوتیفیکیشن به ادمین (در ایونت هندل می‌شود)

            return redirect()->route('front.orders.show', ['order' => $order])
                ->with('error', "سفارش شما لغو شد: {$errorMessage}")
                ->with('refund_message', $refundAmount > 0 ? "مبلغ " . number_format($refundAmount) . " تومان به کیف پول شما برگشت داده شد" : null);

        } catch (Exception $e) {
            DB::rollBack();

            // لاگ خطای بحرانی
            Log::critical('خطا در لغو سفارش و برگشت وجه', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'amount' => $order->price,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // در صورت خطا، مدیر سیستم باید مداخله کند
            $this->alertAdminForManualIntervention($order, $errorMessage, $e->getMessage());

            // حتی در صورت خطا، سعی کنید وضعیت سفارش را آپدیت کنید
            try {
                $order->update([
                    'status' => 'failed',
                    'failed_reason' => "خطای سیستمی: {$errorMessage}",
                    'failed_at' => now(),
                ]);
            } catch (\Exception $updateError) {
                // لاگ خطای آپدیت
                Log::error("خطا در آپدیت وضعیت سفارش: " . $updateError->getMessage());
            }

            return redirect()->route('front.orders.show', ['order' => $order])
                ->with('error', "خطای سیستمی: لطفاً با پشتیبانی تماس بگیرید. کد خطا: #{$order->id}");
        }
    }

    /**
     * ارسال نوتیفیکیشن‌های لغو سفارش به کاربر
     */
    private function sendOrderCancellationNotifications(Order $order, $errorMessage, $refundAmount = 0)
    {
        // 1. ارسال نوتیفیکیشن دیتابیس و WebPush
        try {
            $order->user->notify(new OrderCancelled($order, $errorMessage, $refundAmount));
        } catch (\Exception $e) {
            Log::warning("ارسال نوتیفیکیشن دیتابیس لغو سفارش ناموفق: " . $e->getMessage());

            // روش جایگزین: ذخیره مستقیم در دیتابیس
            try {
                $order->user->notifications()->create([
                    'type' => 'order_cancelled',
                    'title' => 'لغو سفارش',
                    'message' => "سفارش #{$order->id} به دلیل {$errorMessage} لغو شد." .
                        ($refundAmount > 0 ? " مبلغ " . number_format($refundAmount) . " تومان به کیف پول شما برگشت داده شد." : ""),
                    'data' => [
                        'order_id' => $order->id,
                        'reason' => $errorMessage,
                        'refund_amount' => $refundAmount
                    ]
                ]);
            } catch (\Exception $dbError) {
                Log::error("خطا در ذخیره نوتیفیکیشن در دیتابیس: " . $dbError->getMessage());
            }
        }

        // 2. ارسال SMS در صورت فعال بودن
        if (option('user_sms_on_order_cancelled', 'off') == 'on') {
            try {
                Notification::send($order->user, new OrderCancelledSms($order, $errorMessage, $refundAmount));
            } catch (\Exception $e) {
                Log::warning("ارسال SMS لغو سفارش به کاربر ناموفق: " . $e->getMessage());

                // روش جایگزین: استفاده از سرویس SMS مستقیم
                if (option('sms_order_cancel_enable', false)) {
                    try {

                        $smsServiceData=[
                            'refund_amount'=>number_format($refundAmount),
                            'reason'=>  $errorMessage,
                        ];
                        $smsService = new SmsService($order->user->mobile,$smsServiceData,Sms::TYPES['USER_ORDER_CANCELLED'],$order->user_id);
                        $smsService->sendSms();


                    } catch (\Exception $smsError) {
                        Log::error("خطا در ارسال SMS مستقیم: " . $smsError->getMessage());
                    }
                }
            }
        }
    }

    /**
     * اطلاع به مدیر برای مداخله دستی
     */
    private function alertAdminForManualIntervention(Order $order, $errorMessage, $systemError = null)
    {
        // 1. ذخیره در لاگ مخصوص
        Log::channel('refund_errors')->critical('نیاز به مداخله مدیر', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'user_mobile' => $order->mobile,
            'amount' => $order->price,
            'error' => $errorMessage,
            'system_error' => $systemError,
            'timestamp' => now()->toDateTimeString(),
            'order_status' => $order->status
        ]);

        // 2. ایجاد نوتیفیکیشن برای ادمین‌ها در دیتابیس
        try {
            $admins = \App\Models\Admin::whereIn('level', ['admin', 'creator'])->get();

            foreach ($admins as $admin) {
                $admin->notifications()->create([
                    'type' => 'order_cancellation_error',
                    'title' => 'خطا در لغو سفارش و برگشت وجه',
                    'message' => "سفارش #{$order->id} نیاز به بررسی دستی دارد. مبلغ: " . number_format($order->price) . " تومان",
                    'data' => [
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                        'amount' => $order->price,
                        'error' => $errorMessage,
                        'action_needed' => true
                    ]
                ]);
            }
        } catch (\Exception $e) {
            Log::error("خطا در ارسال نوتیفیکیشن به ادمین: " . $e->getMessage());
        }

        // 3. ارسال SMS به ادمین (اختیاری)
        if (option('admin_sms_on_critical_error', 'off') == 'on') {
            try {
                $adminMobile = option('admin_mobile_number');
                if ($adminMobile) {

                    $smsServiceData=[
                        'refund_amount'=>number_format($order->price),
                        'reason'=>  $errorMessage,
                    ];
                    $smsService = new SmsService($adminMobile,$smsServiceData,Sms::TYPES['USER_ORDER_CANCELLED'],$order->user_id);
                    $smsService->sendSms();

                }
            } catch (\Exception $e) {
                Log::warning("ارسال SMS به ادمین ناموفق: " . $e->getMessage());
            }
        }
   }

    public function print(Order $order)
    {
        //$this->authorize('orders.view');
        if (auth()->user()->id!=$order->user_id){
            abort(404);
        }
        return view('front::user.orders.print', compact('order'));
    }

}
