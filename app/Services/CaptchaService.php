<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CaptchaService
{
    // بدون حروف مبهم (0/O، 1/I/l، ...)
    private const CHARSET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    private const LENGTH  = 5;
    private const TTL     = 300;  // ۵ دقیقه اعتبار
    private const MAX_ATTEMPTS = 4; // حداکثر تلاش با یک کد

    public function generate(): string
    {
        $code = '';
        $max  = strlen(self::CHARSET) - 1;
        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= self::CHARSET[random_int(0, $max)]; // random_int = رمزنگاری‌امن
        }

        // فقط هش کد ذخیره می‌شود — هرگز متن خام
        Session::put('captcha', [
            'hash'     => $this->hash($code),
            'expires'  => now()->addSeconds(self::TTL)->getTimestamp(),
            'attempts' => 0,
        ]);

        return $code;
    }

    public function verify(?string $value): bool
    {
        $data = Session::get('captcha');

        if (! is_array($data) || ! isset($data['hash'], $data['expires'])) {
            return false;
        }

        // انقضا
        if (now()->getTimestamp() > $data['expires']) {
            $this->flush();
            return false;
        }

        // شمارش تلاش — ضد brute-force
        $data['attempts'] = ($data['attempts'] ?? 0) + 1;
        Session::put('captcha', $data);

        if ($data['attempts'] > self::MAX_ATTEMPTS) {
            $this->flush();
            return false;
        }

        // مقایسه سراسری-ثابت + بزرگ/کوچک حروف بی‌تفاوت
        $ok = hash_equals($data['hash'], $this->hash($value));

        if ($ok) {
            $this->flush(); // یک‌بارمصرف: بعد از موفقیت، همان کد دوباره قابل استفاده نیست
        }

        return $ok;
    }

    public function flush(): void
    {
        Session::forget('captcha');
    }

    private function hash(?string $code): string
    {
        // نمک با کلید اپ — حتی با افشای سشن، برگرداندن کد ممکن نیست
        return hash('sha256', config('app.key') . '|' . mb_strtolower(trim((string) $code)));
    }
}
