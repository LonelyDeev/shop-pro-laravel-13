<?php

namespace App\Http\Controllers;

use App\Services\Messenger\BaleMessenger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BaleWebhookController extends Controller
{
    /**
     * دریافت وب‌هوک از سرور بله
     *
     * این متد پیام‌های کاربران را دریافت کرده و chat_id آن‌ها را ذخیره می‌کند.
     * نکته: این روت نباید دارای middleware CSRF باشد.
     */
    public function handle(Request $request)
    {
        $update = $request->all();

        Log::info('Bale webhook received', $update);

        try {
            $bale = app(BaleMessenger::class);
            $result = $bale->handleWebhook($update);
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Bale webhook error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 200);
        }
    }
}
