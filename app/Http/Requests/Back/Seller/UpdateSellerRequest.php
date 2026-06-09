<?php

namespace App\Http\Requests\Back\Seller;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSellerRequest extends FormRequest
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
        $rules = [
            'email'     => 'required|email|unique:sellers,id,'.$this->seller->id,
            'mobile' => 'required|regex:/(09)[0-9]{9}/|digits:11|unique:sellers,id,'. $this->seller->id,
            'state_id' => 'required',
            'city_id' => 'required',
            'address' => 'required|persian_alpha_eng_num',
            'post_code' => 'required|digits:10',
            'phone' => 'required|digits:11',
            'business_name' => 'required|persian_alpha_eng_num|unique:sellers_info,seller_id,'. $this->seller->id,
            'shaba_number' => 'required',
            'main_supply_category_id' => 'required',
            'number_of_products' => 'required',
            'password'   => ['nullable', 'string', 'min:6', 'confirmed:confirmed'],
        ];

        if ($this->input('private_business')=="private") {
            $rules = array_merge($rules, [
                'first_name' => 'required|persian_alpha',
                'last_name' => 'required|persian_alpha',
                'birth_day' => 'required',
                'gender' => 'required',
                'identity_card_number' => 'required',
                'national_identity_number' => 'required|digits:10',
            ]);
        }

        if ($this->input('private_business')=="business") {
            $rules = array_merge($rules, [
                'company_name' => 'required|persian_alpha',
                'company_type' => 'required',
                'company_registration_number' => 'required',
                'company_national_identity_number' => 'required|digits:11',
                'company_economic_number' => 'nullable|digits:12',
            ]);
        }

        return $rules;
    }
}
