<?php

namespace App\Services\Messenger;

use App\Contracts\BaleMessengerContract;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class BaleMessenger implements BaleMessengerContract
{
    protected $data;
    protected $mobile;
    protected $chatId;
    protected $client;

    /** آدرس پایه API ربات بله */
    protected const BALE_API_BASE = 'https://api.bale.ai/v1/bot';

    public function __construct($mobile = null, $data = [])
    {
        $this->mobile = $mobile;
        $this->data   = $data;
        $this->client = new Client([
            'timeout' => 30,
            'verify'  => false,
        ]);
        $this->chatId = $mobile ? $this->getChatIdByMobile($mobile) : null;
    }

    /* =====================================================================
     |  مدیریت Chat ID
     |===================================================================== */

    /**
     * دریافت Chat ID ذخیره‌شده بر اساس شماره موبایل
     */
    public function getChatIdByMobile($mobile)
    {
        return option('bale_chat_id_' . $mobile) ?? null;
    }

    /**
     * ذخیره Chat ID برای یک شماره موبایل (برای استفاده در وب‌هوک)
     */
    public static function storeChatId($mobile, $chatId)
    {
        option(['bale_chat_id_' . $mobile => (string) $chatId]);
    }

    /**
     * حذف Chat ID ذخیره‌شده (برای قطع ارتباط کاربر)
     */
    public static function removeChatId($mobile)
    {
        option()->remove('bale_chat_id_' . $mobile);
    }

    /* =====================================================================
     |  ارسال پیام
     |===================================================================== */

    /**
     * ارسال پیام به بله
     */
    public function send()
    {
        $bot_token = option('BALE_BOT_TOKEN');
        if (!$bot_token) {
            throw new Exception('توکن ربات بله تنظیم نشده است');
        }

        // اطمینان از وجود chatId
        if (!$this->chatId && $this->mobile) {
            $this->chatId = $this->getChatIdByMobile($this->mobile);
        }

        if (!$this->chatId) {
            throw new Exception('Chat ID برای این کاربر پیدا نشد. کاربر باید ابتدا ربات را استارت کند');
        }

        $method      = $this->method();
        $data        = $this->$method();
        $pattern_code = $data['pattern_code'] ?? null;
        $attributes  = $data['attributes'] ?? [];

        try {
            if ($pattern_code) {
                $response = $this->sendTemplateMessage($bot_token, $pattern_code, $attributes);
            } else {
                $response = $this->sendSimpleMessage($bot_token, $attributes);
            }

            Log::info('Bale message sent', [
                'mobile'  => $this->mobile,
                'chat_id' => $this->chatId,
                'pattern' => $pattern_code,
            ]);

            return $response;

        } catch (Exception $e) {
            Log::error('Bale message failed', [
                'mobile'  => $this->mobile,
                'chat_id' => $this->chatId,
                'pattern' => $pattern_code ?? 'simple',
                'error'   => $e->getMessage(),
            ]);
            return $e->getMessage();
        }
    }

    /**
     * ارسال پیام به ادمین (برای اطلاع‌رسانی‌های مدیریتی)
     */
    public function sendToAdmin()
    {
        $adminChatId = option('bale_admin_chat_id');
        if (!$adminChatId) {
            throw new Exception('Chat ID ادمین بله تنظیم نشده است');
        }

        $this->chatId = $adminChatId;
        return $this->send();
    }

    /**
     * ارسال پیام الگو (Template)
     */
    protected function sendTemplateMessage($bot_token, $pattern_code, $attributes)
    {
        $text = $this->buildTemplateText($pattern_code, $attributes);

        $body = [
            'chat_id'    => $this->chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ];

        $response = $this->client->post(self::BALE_API_BASE . $bot_token . '/sendMessage', [
            'headers' => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json'    => $body,
        ]);

        return $response->getBody()->getContents();
    }

    /**
     * ارسال پیام ساده
     */
    protected function sendSimpleMessage($bot_token, $attributes)
    {
        $text = $attributes['text'] ?? 'پیام از طرف سیستم';

        $body = [
            'chat_id'    => $this->chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ];

        $response = $this->client->post(self::BALE_API_BASE . $bot_token . '/sendMessage', [
            'headers' => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json'    => $body,
        ]);

        return $response->getBody()->getContents();
    }

    /**
     * ارسال عکس (برای آینده - ارسال فاکتور و ...)
     */
    public function sendPhoto($photoUrl, $caption = '')
    {
        $bot_token = option('BALE_BOT_TOKEN');
        if (!$bot_token || !$this->chatId) {
            throw new Exception('توکن ربات یا Chat ID تنظیم نشده است');
        }

        $body = [
            'chat_id' => $this->chatId,
            'photo'   => $photoUrl,
        ];
        if ($caption) {
            $body['caption'] = $caption;
        }

        $response = $this->client->post(self::BALE_API_BASE . $bot_token . '/sendPhoto', [
            'headers' => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json'    => $body,
        ]);

        return $response->getBody()->getContents();
    }

    /* =====================================================================
     |  ساخت متن پیام الگو
     |===================================================================== */

    /**
     * ساخت متن پیام الگو بر اساس پترن‌های موجود
     */
    protected function buildTemplateText($pattern_code, $attributes)
    {
        $templates = [
            // ثبت‌نام و احراز هویت
            'seller_register'        => "%fullname% فروشنده عزیز خوش آمدید.\n او پی شاپ",
            'user_register'          => "%fullname% عزیز خوش آمدید.\n او پی شاپ",
            'user_verify'            => "کد تایید: %code%\n او پی شاپ",

            // پرداخت سفارش
            'order_paid'             => "سفارش جدید با شماره سفارش %order_id% ثبت و پرداخت شد.\n او پی شاپ",
            'seller_order_paid'      => "سفارش شما با شماره سفارش %order_id% با موفقیت ثبت شد.\n او پی شاپ",
            'user_order_paid'        => "سفارش شما با شماره سفارش %order_id% با موفقیت ثبت شد.\n او پی شاپ",

            // لغو سفارش
            'order_cancelled'        => "سفارش شماره %order_id% لغو شد.\n او پی شاپ",
            'seller_order_cancelled' => "سفارش شماره %order_id% لغو شد. در صورت نیاز با پشتیبانی تماس بگیرید.\n او پی شاپ",
            'user_order_cancelled'   => "سفارش شماره %order_id% به دلیل %reason% لغو شد. مبلغ %refund_amount% تومان به کیف پول شما برگشت داده شد.\n او پی شاپ",

            // کیف پول
            'wallet_refund'          => "مبلغ %amount% تومان بابت لغو سفارش %order_id% به کیف پول شما برگشت داده شد.\n او پی شاپ",
            'wallet_increase'        => "مبلغ %amount% تومان به اعتبار کیف پول شما اضافه شد.\n او پی شاپ",
            'wallet_decrease'        => "مبلغ %amount% تومان از اعتبار کیف پول شما کسر شد.\n او پی شاپ",

            // تبریک تولد
            'happy_birthday'         => "%fullname% عزیز زندگی بسیار کوتاه است از هر لحظه آن لذت ببرید و با تکیه بر تجربه های سال های گذشته سال های آتی زندگی را به بهترین شکل ممکن بگذرانید تولدتان مبارک\n او پی شاپ",

            // پیام کاربران
            'user_message'           => "%message%",
        ];

        $template = $templates[$pattern_code] ?? "پیام از طرف سیستم";

        // جایگزینی متغیرها
        foreach ($attributes as $key => $value) {
            $template = str_replace("%{$key}%", $value, $template);
        }

        return $template;
    }

    /* =====================================================================
     |  تعیین متد اجرایی
     |===================================================================== */

    /**
     * تعیین متد مورد نظر برای اجرا
     */
    protected function method()
    {
        if (isset($this->data['type'])) {
            return $this->data['type'];
        }

        // تشخیص خودکار بر اساس داده‌های موجود
        if (isset($this->data['code'])) {
            return 'verifyCode';
        } elseif (isset($this->data['fullname'])) {
            if (isset($this->data['birthday']) && $this->data['birthday']) {
                return 'happyBirthday';
            }
            return isset($this->data['seller']) ? 'sellerCreated' : 'userCreated';
        } elseif (isset($this->data['order_id'])) {
            if (isset($this->data['cancelled']) && $this->data['cancelled']) {
                if (isset($this->data['seller']) && $this->data['seller']) {
                    return 'sellerOrderCancelled';
                } elseif (isset($this->data['user']) && $this->data['user']) {
                    return 'userOrderCancelled';
                }
                return 'orderCancelled';
            }
            if (isset($this->data['seller']) && $this->data['seller']) {
                return 'sellerOrderPaid';
            } elseif (isset($this->data['user']) && $this->data['user']) {
                return 'userOrderPaid';
            }
            return 'orderPaid';
        } elseif (isset($this->data['amount'])) {
            if (isset($this->data['refund']) && $this->data['refund']) {
                return 'walletRefund';
            } elseif (isset($this->data['decrease']) && $this->data['decrease']) {
                return 'walletAmountDecreased';
            }
            return 'walletAmountIncreased';
        } elseif (isset($this->data['message']) && isset($this->data['users'])) {
            return 'sendMessageUsers';
        }

        return 'simpleMessage';
    }

    /* =====================================================================
     |  متدهای تولید داده الگو
     |===================================================================== */

    /**
     * پیام ساده (در صورت عدم تشخیص الگو)
     */
    public function simpleMessage()
    {
        return [
            'pattern_code' => null,
            'attributes'   => [
                'text' => $this->data['text'] ?? 'پیام از طرف سیستم',
            ],
        ];
    }

    /**
     * ارسال کد تایید
     */
    public function verifyCode()
    {
        return [
            'pattern_code' => option('user_verify_pattern_code_bale') ?? 'user_verify',
            'attributes'   => [
                'code' => $this->data['code'],
            ],
        ];
    }

    /**
     * پیام خوش آمدگویی فروشنده
     */
    public function sellerCreated()
    {
        return [
            'pattern_code' => option('seller_register_pattern_code_bale') ?? 'seller_register',
            'attributes'   => [
                'fullname' => $this->data['fullname'],
            ],
        ];
    }

    /**
     * پیام خوش آمدگویی کاربر
     */
    public function userCreated()
    {
        return [
            'pattern_code' => option('user_register_pattern_code_bale') ?? 'user_register',
            'attributes'   => [
                'fullname' => $this->data['fullname'],
            ],
        ];
    }

    /**
     * پیام پرداخت سفارش به ادمین
     */
    public function orderPaid()
    {
        return [
            'pattern_code' => option('order_paid_pattern_code_bale') ?? 'order_paid',
            'attributes'   => [
                'order_id' => $this->data['order_id'],
            ],
        ];
    }

    /**
     * پیام پرداخت سفارش به فروشنده
     */
    public function sellerOrderPaid()
    {
        return [
            'pattern_code' => option('seller_order_paid_pattern_code_bale') ?? 'seller_order_paid',
            'attributes'   => [
                'order_id' => $this->data['order_id'],
            ],
        ];
    }

    /**
     * پیام پرداخت سفارش به کاربر
     */
    public function userOrderPaid()
    {
        return [
            'pattern_code' => option('user_order_paid_pattern_code_bale') ?? 'user_order_paid',
            'attributes'   => [
                'order_id' => $this->data['order_id'],
            ],
        ];
    }

    /**
     * ارسال پیام لغو سفارش به ادمین
     */
    public function orderCancelled()
    {
        return [
            'pattern_code' => option('order_cancelled_pattern_code_bale') ?? 'order_cancelled',
            'attributes'   => [
                'order_id' => $this->data['order_id'],
            ],
        ];
    }

    /**
     * ارسال پیام لغو سفارش به فروشنده
     */
    public function sellerOrderCancelled()
    {
        return [
            'pattern_code' => option('seller_order_cancelled_pattern_code_bale') ?? 'seller_order_cancelled',
            'attributes'   => [
                'order_id' => $this->data['order_id'],
            ],
        ];
    }

    /**
     * ارسال پیام لغو سفارش به کاربر
     */
    public function userOrderCancelled()
    {
        $attributes = [
            'order_id' => $this->data['order_id'],
        ];

        if (isset($this->data['reason']) && !empty($this->data['reason'])) {
            $attributes['reason'] = $this->data['reason'];
        } else {
            $attributes['reason'] = 'نامشخص';
        }

        if (isset($this->data['refund_amount']) && $this->data['refund_amount'] > 0) {
            $attributes['refund_amount'] = (string) $this->data['refund_amount'];
        } else {
            $attributes['refund_amount'] = '0';
        }

        return [
            'pattern_code' => option('user_order_cancelled_pattern_code_bale') ?? 'user_order_cancelled',
            'attributes'   => $attributes,
        ];
    }

    /**
     * ارسال پیام برگشت وجه به کیف پول
     */
    public function walletRefund()
    {
        $attributes = [
            'amount' => $this->data['amount'],
        ];

        if (isset($this->data['order_id']) && !empty($this->data['order_id'])) {
            $attributes['order_id'] = $this->data['order_id'];
        } else {
            $attributes['order_id'] = '-';
        }

        return [
            'pattern_code' => option('wallet_refund_pattern_code_bale') ?? 'wallet_refund',
            'attributes'   => $attributes,
        ];
    }

    /**
     * پیام کاهش موجودی کیف پول
     */
    public function walletAmountDecreased()
    {
        return [
            'pattern_code' => option('wallet_decrease_pattern_code_bale') ?? 'wallet_decrease',
            'attributes'   => [
                'amount' => $this->data['amount'],
            ],
        ];
    }

    /**
     * پیام افزایش موجودی کیف پول
     */
    public function walletAmountIncreased()
    {
        return [
            'pattern_code' => option('wallet_increase_pattern_code_bale') ?? 'wallet_increase',
            'attributes'   => [
                'amount' => $this->data['amount'],
            ],
        ];
    }

    /**
     * پیام تبریک تولد
     */
    public function happyBirthday()
    {
        return [
            'pattern_code' => option('happy_birthday_pattern_code_bale') ?? 'happy_birthday',
            'attributes'   => [
                'fullname' => $this->data['fullname'],
            ],
        ];
    }

    /**
     * ارسال پیام گروهی به کاربران
     */
    public function sendMessageUsers()
    {
        $message = $this->data['message'] ?? '';

        return [
            'pattern_code' => option('user_message_pattern_code_bale') ?? 'user_message',
            'attributes'   => [
                'message' => $message,
            ],
        ];
    }

    /* =====================================================================
     |  متدهای کمکی
     |===================================================================== */

    /**
     * متد کمکی برای تنظیم Chat ID
     */
    public function setChatId($chatId)
    {
        $this->chatId = $chatId;
        return $this;
    }

    /**
     * متد کمکی برای دریافت Chat ID
     */
    public function getChatId()
    {
        return $this->chatId;
    }

    /* =====================================================================
     |  مدیریت وب‌هوک و اتصال ربات
     |===================================================================== */

    /**
     * تنظیم وب‌هوک برای دریافت پیام‌های ربات
     */
    public function setWebhook($url)
    {
        $bot_token = option('BALE_BOT_TOKEN');
        if (!$bot_token) {
            throw new Exception('توکن ربات بله تنظیم نشده است');
        }

        $response = $this->client->post(self::BALE_API_BASE . $bot_token . '/setWebhook', [
            'headers' => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json'    => [
                'url' => $url,
            ],
        ]);

        return $response->getBody()->getContents();
    }

    /**
     * حذف وب‌هوک
     */
    public function deleteWebhook()
    {
        $bot_token = option('BALE_BOT_TOKEN');
        if (!$bot_token) {
            throw new Exception('توکن ربات بله تنظیم نشده است');
        }

        $response = $this->client->post(self::BALE_API_BASE . $bot_token . '/deleteWebhook', [
            'headers' => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        return $response->getBody()->getContents();
    }

    /**
     * دریافت اطلاعات ربات (برای تست اتصال)
     */
    public function getBotInfo()
    {
        $bot_token = option('BALE_BOT_TOKEN');
        if (!$bot_token) {
            throw new Exception('توکن ربات بله تنظیم نشده است');
        }

        $response = $this->client->get(self::BALE_API_BASE . $bot_token . '/getMe', [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * تست اتصال به ربات
     *
     * @return array
     */
    public function testConnection()
    {
        try {
            $bot_token = option('BALE_BOT_TOKEN');
            if (!$bot_token) {
                return [
                    'success' => false,
                    'message' => 'توکن ربات بله تنظیم نشده است',
                ];
            }

            $info = $this->getBotInfo();

            if (isset($info['ok']) && $info['ok'] === true) {
                $bot = $info['result'] ?? [];
                return [
                    'success'   => true,
                    'message'   => 'اتصال با موفقیت برقرار شد',
                    'bot_name'  => $bot['first_name'] ?? 'نامشخص',
                    'username'  => $bot['username'] ?? 'نامشخص',
                    'bot_id'    => $bot['id'] ?? 'نامشخص',
                ];
            }

            return [
                'success' => false,
                'message' => 'پاسخ نامعتبر از سرور بله',
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * پردازش وب‌هوک - دریافت پیام‌های کاربران و ذخیره Chat ID
     *
     * این متد در کنترلر وب‌هوک فراخوانی می‌شود.
     * جریان اتصال کاربر:
     * 1. کاربر در سایت کد اتصال دریافت می‌کند (6 رقمی)
     * 2. کد در option با کلید bale_link_code_{code} و مقدار=موبایل ذخیره می‌شود
     * 3. کاربر کد را به ربات بله ارسال می‌کند
     * 4. این متد کد را پیدا کرده و chat_id را برای موبایل کاربر ذخیره می‌کند
     *
     * @param array $update داده دریافتی از بله
     * @return array
     */
    public function handleWebhook(array $update)
    {
        try {
            $message = $update['message'] ?? null;
            if (!$message) {
                return ['ok' => true];
            }

            $chatId = $message['chat']['id'] ?? null;
            $text   = trim($message['text'] ?? '');

            if (!$chatId) {
                return ['ok' => true];
            }

            $bot_token = option('BALE_BOT_TOKEN');

            // پیام استارت ربات
            if ($text === '/start') {
                $this->client->post(self::BALE_API_BASE . $bot_token . '/sendMessage', [
                    'json' => [
                        'chat_id' => $chatId,
                        'text'    => "سلام! به ربات او پی شاپ خوش آمدید.\nبرای اتصال حساب خود، کد ۶ رقمی که از سایت دریافت کرده‌اید را ارسال کنید.",
                    ],
                ]);
                return ['ok' => true];
            }

            // بررسی کد اتصال (۶ رقم)
            if (preg_match('/^\d{6}$/', $text)) {
                $mobile = option('bale_link_code_' . $text);

                if ($mobile) {
                    // ذخیره chat_id برای موبایل کاربر
                    self::storeChatId($mobile, $chatId);

                    // حذف کد یکبارمصرف
                    option()->remove('bale_link_code_' . $text);

                    // ارسال پیام موفقیت
                    $this->client->post(self::BALE_API_BASE . $bot_token . '/sendMessage', [
                        'json' => [
                            'chat_id' => $chatId,
                            'text'    => "حساب شما با موفقیت متصل شد!\nشما از این پس پیام‌های سیستم را از طریق بله دریافت خواهید کرد.",
                        ],
                    ]);

                    Log::info('Bale user linked', [
                        'mobile'  => $mobile,
                        'chat_id' => $chatId,
                    ]);

                    return ['ok' => true];
                } else {
                    $this->client->post(self::BALE_API_BASE . $bot_token . '/sendMessage', [
                        'json' => [
                            'chat_id' => $chatId,
                            'text'    => "کد وارد شده نامعتبر یا منقضی شده است.\nلطفاً از سایت کد جدید دریافت کنید.",
                        ],
                    ]);
                    return ['ok' => true];
                }
            }

            // پیام ناشناخته
            $this->client->post(self::BALE_API_BASE . $bot_token . '/sendMessage', [
                'json' => [
                    'chat_id' => $chatId,
                    'text'    => "لطفاً کد ۶ رقمی اتصال را ارسال کنید.",
                ],
            ]);

            return ['ok' => true];

        } catch (Exception $e) {
            Log::error('Bale webhook error', [
                'error' => $e->getMessage(),
            ]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * تولید کد اتصال برای کاربر (برای فراخوانی در سایت)
     *
     * @param string $mobile شماره موبایل کاربر
     * @return string کد ۶ رقمی
     */
    public static function generateLinkCode($mobile)
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // ذخیره کد با اعتبار ۱۰ دقیقه
        option(['bale_link_code_' . $code => $mobile]);

        return $code;
    }
}
