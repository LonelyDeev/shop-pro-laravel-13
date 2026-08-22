<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OneTimeCode;
use App\Models\User;
use App\Notifications\Sms\VerifyCodeSent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PasswordResetLinkController extends Controller
{
    public function create()
    {
        $view = config('front.pages.forgot-password');

        if (!$view || option('forgot_password_link', 'off') == 'off') {
            abort(404);
        }
        session()->forget('forget_password_link');
        return view($view);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mobile' => 'required|exists:users,username',
            'captcha' => ['required', 'captcha'],
        ]);

        $user = User::where('username', $request->mobile)->first();

        $this->sendCode($user);
        session()->put('forget_password_link', $request->mobile);
        return response('success');
    }

    public static function sendCode(User $user)
    {
        $verify_code = OneTimeCode::where('user_id', $user->id)->latest()->first();

        if ($verify_code) {
            $now = Carbon::now();
            $time = $verify_code->created_at;

            if ($time->diffInSeconds($now) < 120) {
                return;
            }
        }
        // send sms notification to user
        Notification::send($user, new VerifyCodeSent($user));
    }

    public function changePassword(Request $request)
    {
        if (!session()->has('forget_password_link') and session('forget_password_link') != $request->mobile) {
            abort(404);
        }
        $user = User::where('username', $request->mobile)->first();
        $time = Carbon::now()->subMinutes(15);
        // 2. پیدا کردن کاربر
        $user = User::where('username', $request->mobile)->first();

        if (!$user) {
            return redirect()->route('password.request')
                ->with('error', 'کاربر مورد نظر یافت نشد');
        }
        // 1. اعتبارسنجی ورودی‌ها
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|exists:users,username',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|min:8',
            'verify_code'     => [
                'required',
                Rule::exists('one_time_codes', 'code')->where(function ($query) use ($user, $time) {
                    $query->where('user_id', $user->id)->where('created_at', '>=', $time);
                }),
            ]
        ], [
            'verify_code.exists' => 'کد وارد شده اشتباه است'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }



        // 5. تغییر رمز عبور کاربر
        $user->password = Hash::make($request->password);
        $user->save();
        OneTimeCode::where('user_id', $user->id)->delete();
        // 7. حذف سشن forget_password_link
        session()->forget('forget_password_link');

        // 8. خروج از سیستم کاربر (در صورت لاگین بودن)
        auth()->logout();

        // 9. لاگین کردن کاربر با رمز عبور جدید (اختیاری)
        // auth()->login($user);

        // 10. ارسال پیام موفقیت و هدایت به صفحه ورود
        return redirect()->route('login')
            ->with('toast-success', 'رمز عبور شما با موفقیت تغییر یافت. لطفاً با رمز جدید وارد شوید');
    }
}
