<div class="row p-border mb-1 rounded" dir="rtl" style="width: 10cm; height:7cm; overflow:hidden">
    <div class="col-3 px-0 p-border-left">
        <div class="pt-2">
            @if (option('info_logo'))
                <div class="p-border-bottom">
                    <p class="text-center">
                        <img style="width:90%;height:80px" src="{{ asset(option('info_logo')) }}" alt="factor_logo" style="max-height: 50px;">
                    </p>
                </div>
            @endif

            <div class="p-border-bottom" style="padding: 5px">
                <p class="text-center m-0">
                <span>{{ $_SERVER['SERVER_NAME'] }}</span>
            </div>

            <div class="p-border-bottom p-1">
                <p class="text-center m-0">
                    {{ jdate($order->created_at)->format('Y/m/d') }}</p>
            </div>

            <div>
                <p class="text-center">
                    @if ($order->carrier?->image)
                        <img class="w-50" style="width:100%;object-fit: contain;height:48px" src="{{ asset($order->carrier->image) }}" alt="barcode">
                    @else
                        <p class="text-center m-0">
                            {{ $order->carrier?->title }}
                        </p>
                    @endif
                </p>
            </div>
        </div>
    </div>
    <div class="col-9 px-0">
        <div class="p-1">
            <div>
                <p>فرستنده: {{ option('info_site_title') }}</span></p>
            </div>
            <div class="p-border-bottom">
                <p>آدرس: {{ option('info_address') }} - کدپستی: {{ option('info_postal_code') }} - تلفن: {{ option('info_tel') }}</p>
            </div>
            <div class="mt-2">
                <p>آدرس گیرنده: {{ $order->province->name ?? '' }} - {{ $order->city->name ?? '' }} - {{ $order->address }}</p>
            </div>
            <div class="row mt-1" style="bottom: 0px; position:absolute">
                <div class="col-6 p-border-bottom p-border-top p-border-left">
                    <p>گیرنده : {{ $order->name }}</p>
                </div>
                <div class="col-6 p-border-bottom p-border-top">
                    <p>موبایل: {{ $order->mobile }}</p>
                </div>
                <div class="col-6 p-border-left">
                    <p>کد پستی: {{ $order->postal_code }}</p>
                </div>
                <div class="col-6">
                    <p>ش.سفارش: {{ $order->id }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
<div style="width: 10cm; height:2cm">
    <p class="text-center">
        <img style="object-fit: contain" src="{{ barcode($order->id) }}" alt="barcode">
    </p>
</div>
