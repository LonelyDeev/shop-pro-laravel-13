<?php

namespace Themes\WeblakShop\src\Requests;

use App\Models\Gateway;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $gateways = Gateway::active()->pluck('key')->toArray();
        $cart = get_cart();

        // بررسی وجود محصولات فیزیکی در سبد خرید
        $hasPhysical = $cart && $cart->products->filter(fn($p) => $p->type === 'physical')->isNotEmpty();

        $rules = [
            'gateway'     => 'required|in:wallet,' . implode(',', $gateways),
            'description' => 'nullable|string|max:1000',
        ];

        // اگر محصول فیزیکی وجود دارد، آدرس الزامی است
        if ($hasPhysical) {
            $rules['address'] = 'required|exists:addresses,id';
        } else {
            // اگر فقط محصول دانلودی است، آدرس اختیاری است
            $rules['address'] = 'nullable|exists:addresses,id';
        }

        return $rules;
    }
}
