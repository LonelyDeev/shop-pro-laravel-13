<?php

namespace App\Http\Resources\Datatable\Order;

use App\Models\Seller;
use Illuminate\Http\Resources\Json\JsonResource;

class Order extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {

        if ($request->seller) {
            $seller_id = $request->seller->id;
        } else {
            $seller_id = sellerID();
        }

        $price = [];
        $itemsArray = [];
        $sellersProcessed = [];
        foreach ($this->items()->get() as $order_item) {
            $sellerId = $order_item->seller_id ?? 'no_seller';

            if ($order_item->seller_id == $seller_id) {
                $price[] = $order_item->real_price * $order_item->quantity;
            }

            if (in_array($sellerId, $sellersProcessed)) {
                continue;
            }
            if ($order_item->seller_id && $order_item->seller) {
                // با فروشنده
                $seller = $order_item->seller->seller_info;
                $itemsArray[] = [
                    'id' => $order_item->id,
                    'status' => $order_item->shipping_status,
                    'sellerName' => $seller->business_name ?? 'فروشنده',
                    'sellerId' => $order_item->seller_id,
                    'link' => route('admin.orders.show-item', $order_item->id),
                ];
            } else {
                // بدون فروشنده (فروشگاه اصلی)
                $itemsArray[] = [
                    'id' => $order_item->id,
                    'status' => $order_item->shipping_status,
                    'sellerName' => 'فروشگاه اصلی',
                    'sellerId' => null,
                    'link' => route('admin.orders.show-item', $order_item->id),
                ];
            }

            $sellersProcessed[] = $sellerId;
        }
        $TotalPrice = array_sum($price);
        $price = $TotalPrice + $this->shippingCostSeller();
        $priceSeller = $price - $this->totalDiscountSeller($seller_id) ?: 0;

        $admin_seller_view = null;
        if ($seller_id) {
            $admin_seller_view = route('admin.sellers.orders.show', ['seller' => Seller::find($seller_id) ?: 0, 'order' => $this]);
        }


        return [
            'id' => $this->id,
            'order_id' => $this->id,
            'name' => htmlspecialchars($this->name),
            'created_at' => jdate($this->created_at)->format('%d %B %Y | H:i'),
            'price' => trans('messages.currency.prefix') . number_format($this->price) . trans('messages.currency.suffix'),
            'priceSeller' => trans('messages.currency.prefix') . number_format($priceSeller) . trans('messages.currency.suffix'),
            'status' => $this->status,
            'shipping_status' => $this->shipping_status,
            'items' => $itemsArray,

            'links' => [
                'view' => route('admin.orders.show', ['order' => $this]),
                'admin_seller_view' => $admin_seller_view,
                'seller_view' => route('seller.orders.show', ['order' => $this]),
            ]
        ];
    }
}
