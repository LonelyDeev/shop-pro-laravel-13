<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Services\Messenger\BaleMessenger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BaleSettingsController extends Controller
{
    /**
     * تست اتصال ربات بله
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'bot_token' => 'nullable|string',
        ]);

        // اگر توکن از فرم ارسال شده، موقتاً ذخیره می‌کنیم تا تست انجام شود
        if ($request->filled('bot_token')) {
            option_update('BALE_BOT_TOKEN', $request->bot_token);
        }

        $bale = app(BaleMessenger::class);
        $result = $bale->testConnection();

        return response()->json($result);
    }

    /**
     * تنظیم وب‌هوک
     */
    public function setWebhook(Request $request)
    {
        $request->validate([
            'webhook_url' => 'required|url',
        ]);

        try {
            $bale = app(BaleMessenger::class);
            $result = $bale->setWebhook($request->webhook_url);
            $decoded = json_decode($result, true);

            if (isset($decoded['ok']) && $decoded['ok'] === true) {
                return response()->json([
                    'success' => true,
                    'message' => 'وب‌هوک با موفقیت تنظیم شد',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $decoded['description'] ?? 'خطا در تنظیم وب‌هوک',
            ]);

        } catch (\Exception $e) {
            Log::error('Bale setWebhook error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * حذف وب‌هوک
     */
    public function deleteWebhook(Request $request)
    {
        try {
            $bale = app(BaleMessenger::class);
            $result = $bale->deleteWebhook();
            $decoded = json_decode($result, true);

            if (isset($decoded['ok']) && $decoded['ok'] === true) {
                return response()->json([
                    'success' => true,
                    'message' => 'وب‌هوک با موفقیت حذف شد',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $decoded['description'] ?? 'خطا در حذف وب‌هوک',
            ]);

        } catch (\Exception $e) {
            Log::error('Bale deleteWebhook error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
