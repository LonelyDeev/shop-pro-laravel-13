<?php

namespace App\Http\Controllers;

use App\Services\CaptchaImage;
use App\Services\CaptchaService;

class CaptchaController extends Controller
{
    public function image(CaptchaService $service)
    {
        $png = (new CaptchaImage)->render(
            $service->generate()
        );

        return response($png, 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
        ]);
    }
}
