<?php

namespace App\Services\Sms;

use App\Models\Sms;

class SmsService
{
    protected $mobile, $data, $type, $user_id,$seller_id;

    public function __construct($mobile, $data, $type, $user_id=null,$seller_id=null)
    {
        $this->mobile  = $mobile;
        $this->data    = $data;
        $this->type    = $type;
        $this->user_id = $user_id;
        $this->seller_id = $seller_id;
    }

    public function sendSms()
    {
        $provider = $this->provider();
        $response = $provider->send();

        Sms::create([
            'mobile'     => $this->mobile(),
            'ip'         => request()->ip(),
            'type'       => $this->type['key'],
            'seller_id'  => $this->seller_id,
            'user_id'    => $this->user_id,
            'response'   => $response,
            'provider'   => option('sms_panel_provider', 'ippanel')
        ]);
    }

    private function providersList()
    {
        return [
            'ippanel'     => IppanelSms::class,
            'kavenegar'   => KavenegarSms::class,
            'melipayamak' => MelipayamakSms::class,
            'idehpardazan' => IdehPardazanSms::class,
            'farazsms'    => FarazSms::class,
        ];
    }

    private function provider()
    {
        $providersList = $this->providersList();

        return new $providersList[option('sms_panel_provider', 'ippanel')](
            $this->mobile,
            $this->data,
            $this->type,
            $this->user_id,
            $this->seller_id
        );
    }

    public function mobile()
    {
        return $this->mobile;
    }

    public function method()
    {
        return $this->type['method'];
    }
}
