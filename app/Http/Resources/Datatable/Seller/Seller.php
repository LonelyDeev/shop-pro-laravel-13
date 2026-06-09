<?php

namespace App\Http\Resources\Datatable\Seller;

use App\Models\SellerVariant;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route;

class Seller extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $variant_count=0;
        $seller_product_id=SellerVariant::where('seller_id',$this->seller_id)->where('product_id','!=',null)->get()->pluck('product_id')->toArray();
        if (count($seller_product_id)){
            $variant_count=count($seller_product_id);
        }


            $seller=\App\Models\Seller::find($this->seller_id);
            return [
                'id'                => $this->seller_id,
                'image'             => $this->logo ? asset($this->logo) : asset('/empty.svg'),
                'full_name'         => $this->full_name,
                'business_name'     => $this->business_name,
                'mobile'            => $this->mobile,
                'created_at'        => jdate($this->created_at)->format('%d %B %Y'),
                'category'          => $this->category,
                'brand'             => $this->brand,
                'price'             => number_format(@$this->lowestPrice->price),
                'price_discount'    => number_format(@$this->lowestPrice->discount_price),
                'seller_id'         => $this->seller_id,
                'status_register'   => $this->seller->status_register,
                'status_work'       => $this->seller->status_work,
                'status_documents'  => $this->seller->status_documents,
                'status'            => $this->seller->status,
                'variant'           => route('seller.products.variant', ['product_id' => $this->id]),
                'seller_info'       => $this->seller_info,
                'variant_count'     => $variant_count,
                'links' => [
                    'edit'    => route('admin.sellers.edit', ['seller' => $seller]),
                    'destroy' => route('admin.sellers.destroy', ['seller' => $seller]),
                    'show' => route('admin.sellers.show', ['seller' => $seller]),
                ]
            ];

    }
}
