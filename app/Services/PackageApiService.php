<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PackageApiService
{
    private string $baseUrl;
    private string $token;
    private string $projectKey;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl   = rtrim(config('packages.api.base_url'), '/');
        $this->token     = config('packages.api.token');
        $this->projectKey = config('packages.api.project_key');
        $this->timeout   = (int) config('packages.api.timeout', 30);
    }

    /* ===================================================================
     *  لیست پکیج‌ها
     * =================================================================== */
    public function listPackages(array $query = []): array
    {
        return $this->request('GET', '/api/v1/packages', $query);
    }

    /* ===================================================================
     *  جزئیات یک پکیج
     * =================================================================== */
    public function getPackage(string $slug): array
    {
        return $this->request('GET', "/api/v1/packages/{$slug}");
    }

    /* ===================================================================
     *  ایجاد درخواست خرید - API یک payment_url برمی‌گرداند
     *  Response: { payment_url, transaction_id, amount, expires_in }
     * =================================================================== */
    public function createPurchase(string $slug, string $callbackUrl, ?int $adminId = null, ?int $pricingPlanId = null): array
    {
        return $this->request('POST', "/api/v1/packages/{$slug}/purchase", [
            'callback_url'    => $callbackUrl,
            'project_key'     => $this->projectKey,
            'admin_id'        => $adminId,
            'pricing_plan_id' => $pricingPlanId,
        ]);
    }

    /* ===================================================================
     *  بررسی وضعیت پرداخت بعد از برگشت از درگاه
     *  Response: { paid: bool, license_key, expires_at, transaction_id }
     * =================================================================== */
    public function verifyPayment(string $transactionId): array
    {
        return $this->request('POST', "/api/v1/payments/{$transactionId}/verify");
    }

    /* ===================================================================
     *  بررسی اعتبار لایسنس (همچنین برای آپدیت‌ها استفاده می‌شود)
     *  Response: { valid, expires_at, download_token, version }
     * =================================================================== */
    public function verifyLicense(string $slug, string $licenseKey): array
    {
        return $this->request('POST', "/api/v1/packages/{$slug}/verify-license", [
            'license_key' => $licenseKey,
            'project_key' => $this->projectKey,
        ]);
    }

    /* ===================================================================
     *  چک آپدیت
     *  Response: { has_update, latest_version, current_version, changelog }
     * =================================================================== */
    public function checkUpdate(string $slug, string $currentVersion): array
    {
        return $this->request('GET', "/api/v1/packages/{$slug}/check-update", [
            'current_version' => $currentVersion,
        ]);
    }

    /* ===================================================================
     *  دانلود URL (با token موقت)
     * =================================================================== */
    public function getDownloadUrl(string $downloadToken): string
    {
        return "{$this->baseUrl}/api/v1/packages/download/{$downloadToken}";
    }

    /* ===================================================================
     *  درخواست HTTP مشترک
     * =================================================================== */
    private function request(string $method, string $endpoint, array $data = []): array
    {
        try {
            $projectUrl = request()->root();
            $http = Http::timeout($this->timeout)
                ->withToken($this->token)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'X-Project-Key' => $this->projectKey,
                    'X-Project-Url' => $projectUrl,
                ]);

            $response = $method === 'GET'
                ? $http->get($this->baseUrl . $endpoint, $data)
                : $http->post($this->baseUrl . $endpoint, $data);


            if (!$response->successful()) {
                $errorBody = $response->body();
                $errorJson = $response->json();

                // اگر پاسخ JSON است و دارای کلید error یا message است
                $errorMessage = $errorJson['message'] ?? $errorJson['error'] ?? $errorBody;

                Log::error('Packages API failed', [
                    'endpoint' => $endpoint,
                    'status'   => $response->status(),
                    'body'     => $errorBody,
                ]);

                throw new RuntimeException(
                    'خطا در ارتباط با سرور پکیج‌ها (کد: ' . $response->status() . '): '.$errorMessage
                );
            }

            return $response->json();
        } catch (ConnectionException $e) {
            Log::error('Packages API connection error', [
                'endpoint' => $endpoint,
                'error'    => $e->getMessage(),
            ]);
            throw new RuntimeException('ارتباط با سرور پکیج‌ها برقرار نشد. لطفاً مجدد تلاش کنید.');
        }
    }
}
