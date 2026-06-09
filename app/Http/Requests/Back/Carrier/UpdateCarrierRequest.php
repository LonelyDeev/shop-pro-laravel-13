<?php

namespace App\Http\Requests\Back\Carrier;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCarrierRequest extends FormRequest
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
        return [
            'title'                 => 'required|string',
            'image'                 => 'nullable|image',
            'waiting_time'          => 'nullable|string',
            'max_package_weight'    => 'nullable|numeric',
            'min_package_weight'    => 'nullable|numeric',
            'extra_cost'            => 'nullable|numeric',
            'description'           => 'nullable|string',
            'province_id'           => 'required|exists:provinces,id',
            'city_id'               => 'required|exists:cities,id',
            'free_shipping_weight'  => 'nullable|numeric',
            'free_shipping_price'   => 'nullable|numeric',
            'covered_cities'        => 'required|in:all,select_city',
            'included_cities'       => 'required_if:covered_cities,select_city',
            'included_cities.*'     => 'exists:cities,id',
            'carrige_forward'       => 'boolean',
            'is_active'             => 'boolean',

            'delivery_time_type' => 'required|in:default,user_select',
            'default_delivery_range' => 'required_if:delivery_time_type,default|nullable|string',
            'user_select_ranges' => 'required_if:delivery_time_type,user_select|min:1|max:20',
            'disable_holidays' => 'nullable|boolean',
            'start_days_after_order' => 'required|integer|min:1|max:12',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'included_cities.required_if' => 'لطفا شهرهای تحت پوشش را انتخاب کنید',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'carrige_forward'   => $this->has('carrige_forward'),
        ]);
    }
}
