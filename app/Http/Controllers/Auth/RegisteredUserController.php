<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $view = config('front.pages.register');

        if (!$view) {
            abort(404);
        }

        return view($view);
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request)
    {
        $data             = $request->validated();

        $data['password']      = Hash::make($data['password']);
        $data['referral_code'] = Referral::generateCode();

        if ($request->referral_code && option('user_refrral_enable', 0) == 1) {
            $data['referral_id'] = User::where('referral_code', $request->referral_code)->first()->id;
        }

        $user = User::create($data);

        event(new Registered($user));

        Auth::login($user);

        return response('success');
    }
}
