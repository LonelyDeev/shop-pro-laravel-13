<?php

namespace Themes\WeblakShop\src\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Category;
use App\Models\City;
use App\Models\Favorite;
use App\Models\NewSeller;
use App\Models\OneTimeCode;
use App\Models\Province;
use App\Models\Seller;
use App\Models\SellerCommission;
use App\Models\SellerEcontract;
use App\Models\SellerHero;
use App\Models\SellerInfo;
use App\Models\SellerQuestion;
use App\Models\Slider;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\Seller\SellerCreated;
use App\Notifications\Seller\SellerRegistered;
use App\Notifications\Sms\NewSellerCodeSent;
use App\Notifications\Sms\VerifyCodeSent;
use App\Notifications\User\UserRegistered;
use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use Themes\WeblakShop\src\Requests\Store_registration_business_details;

class SellerController extends Controller
{
    public function __construct()
    {
        if(option('multi_vendor_system_status','false')=="false"){
            abort(404);
        }
    }
    public function index()
    {
        session()->forget('show-seller-business-details');
        session()->forget('show-seller-verification-form');
        session()->forget('show-seller-documents');
        session()->forget('show-seller-checkout');
        $sliders= Slider::where('page', 'sellers')->where('published', true)->orderBy('ordering')->get();
        $sellers_heroes=SellerHero::all();
        $sellers_commissions=SellerCommission::all();
        $sellers_questions=SellerQuestion::all();
        return view('front::sellers.index',compact('sliders','sellers_heroes','sellers_commissions','sellers_questions'));
    }


    public function login()
    {
        session()->forget('show-seller-business-details');
        session()->forget('show-seller-verification-form');
        session()->forget('show-seller-documents');
        session()->forget('show-seller-checkout');

        if (Auth::guard('seller')->user()){
            return redirect()->route('seller.dashboard');
        }else{
            return view('front::sellers.auth.login');
        }

    }

    public function username($login)
    {
        if(is_numeric($login)){
            $field = 'mobile';
        } else {
            $field = 'email';
        }
        request()->merge([$field => $login]);

        return $field;
    }
    public function login_check(Request $request)
    {

        $username=$this->username($request->username);
        $this->validate($request, [
            'username'   => 'required',
            'password'   => 'required',
        ],[
            'username.required'=>'لطفا این قسمت را خالی نگذارید'
        ]);

        if ($username=="mobile") {
            $this->validate($request, [
                'username' => 'digits:11|regex:/(09)[0-9]{9}/',
            ],[
                'username.regex'=>'شماره موبایل نادرست است',
                'username.digits'=>'شماره موبایل نادرست است',
            ]);
            $userTitle="شماره موبایل";
        }else if ($username=="email") {
            $this->validate($request, [
                'username' => 'string|email',
            ],[
                'username.email'=>'ایمیل را بطور صحیح وارد کنید',
                'username.exists'=>'حساب کاربری با مشخصات وارد شده وجود ندارد. لطفا از شماره تلفن همراه برای ساخت حساب کاربری استفاده نمایید.',
            ]);
            $userTitle="ایمیل";
        }

        $seller=Seller::where($username,$request->username)->first();
        if (!$seller){
            return response([
                'status'=>'error',
                'message'=>$userTitle.' وارد شده با اطلاعات ما سازگار نیست.'
            ]);
        }

            if (Auth::guard('seller')->attempt([$username => $request->username, 'password' => $request->password], $request->remember)) {
                if ($seller->status_register=="complete"){
                    // if successful, then redirect to their intended location
                    if (Auth::guard('seller')->user()->status=="ACTIVE"){
                        Seller::where('id', Auth::guard('seller')->user()->id)->update(['updated_at' => Carbon::now()->format('Y-m-d H:m:s')]);
                        $request->session()->regenerate();
                        return response([
                            'status'=>'success',
                            'message'=>'با موفقیت وارد شدید',
                            'redirect'=>route('seller.dashboard')
                        ]);
                    }
                    else{
                        Auth::guard('seller')->logout();
                        return response([
                            'status'=>'error',
                            'message'=>'حساب کاربری شما مسدود شده است'
                        ]);
                    }
                }
                elseif ($seller->status_register=="business-details"){
                    session()->put('show-seller-documents',$seller->mobile);
                    return response([
                        'status'=>'success',
                        'message'=>'با موفقیت وارد شدید',
                        'redirect'=>route('seller.registration_documents')
                    ]);
                }
                elseif ($seller->status_register=="documents"){
                    session()->put('show-seller-checkout',$seller->mobile);
                    return response([
                        'status'=>'success',
                        'message'=>'با موفقیت وارد شدید',
                        'redirect'=>route('seller.registration_checkout')
                    ]);
                }



            }
            else{
                return response([
                    'status'=>'error',
                    'message'=>'رمز عبور وارد شده صحیح نمی باشد.'
                ]);
            }



    }

    public function registration()
    {
        session()->forget('show-seller-business-details');
        session()->forget('show-seller-verification-form');
        session()->forget('show-seller-documents');
        session()->forget('show-seller-checkout');
        if (Auth::guard('seller')->user()){
            return redirect()->route('seller.dashboard');
        }else{
            return view('front::sellers.auth.register');
        }

    }

    public function registration_new_seller(Request $request)
    {

         $this->validate($request, [
            'email'     => 'required|email|unique:sellers',
            'mobile' => 'required|regex:/(09)[0-9]{9}/|digits:11|unique:sellers',
            'password'  => 'required'
        ]);

        $code_mobile=random_int(10000, 99999);
        $seller=NewSeller::where(['mobile'=>$request->mobile])->first();
        if ($seller){
            $seller->code_mobile_verification = $code_mobile;
            $seller->email = $request->email;
            $seller->password = $request->password;
            $seller->save();
        }elseif(!$seller) {
            $seller = new NewSeller();
            $seller->email = $request->email;
            $seller->mobile = $request->mobile;
            $seller->password = $request->password;
            $seller->code_mobile_verification = $code_mobile;

            $seller->save();
        }
        Notification::send($seller, new NewSellerCodeSent($seller));
        //Sms
        session()->put('show-seller-verification-form',$request->mobile);
        return response([
            'redirect'=>route('seller.registration_mobile')
        ]);

    }

    public function registration_mobile()
    {
        $seller=NewSeller::where(['mobile'=>session('show-seller-verification-form')])->first();

        if (!$seller){
            return redirect()->route('seller.registration_new_seller');
        }
        $resend_time = $seller->updated_at->addSeconds(120)->timestamp;
        return view('front::sellers.auth.register-mobile',compact('seller','resend_time'));
    }

    public function registration_mobile_check(Request $request)
    {

        $user = NewSeller::where('mobile', $request->mobile)->first();
        $time = Carbon::now()->subMinutes(15);

        $request->validate([
            'mobile' => 'required|regex:/(09)[0-9]{9}/|digits:11|unique:sellers',
            'verify_code'     => [
                'required',
                Rule::exists('new_sellers', 'code_mobile_verification')->where(function ($query) use ($user, $time) {
                    $query->where('mobile', $user->mobile)->where('updated_at', '>=', $time);
                }),
            ]
        ], [
            'verify_code.exists' => 'کد وارد شده اشتباه است'
        ]);

        session()->put('show-seller-business-details',$request->mobile);
        session()->forget('show-seller-verification-form');
        return response([
            'redirect'=>route('seller.registration_business_details')
        ]);
    }

    public function registration_business_details()
    {

        if (session('show-seller-documents')){
            return redirect()->route('seller.registration_documents');
        }
        $seller=NewSeller::where(['mobile'=>session('show-seller-business-details')])->first();
        if (!$seller){
            return redirect()->route('seller.registration_new_seller');
        }

        $econtract=SellerEcontract::find(1);
        $provinces = Province::all();
        $categories=Category::where('category_id',null)->get();
        return view('front::sellers.auth.business-details',compact('econtract','provinces','categories'));
    }

    public function registration_business_details_store(Store_registration_business_details $request)
    {
        $newSeller=NewSeller::where(['mobile'=>session('show-seller-business-details')])->first();
        $seller=Seller::where(['mobile'=>session('show-seller-business-details'),'email'=>$newSeller->email])->first();
        if ($seller){
            session()->forget('show-seller-business-details');
            return response([
                'status'=>'error',
                'message'=>'ایمیل و شماره از قبل وجود دارد.',
                'redirect'=>route('seller.login')
            ]);
        }

        if (Seller::where('slug',Str::slug($request->business_name))->first()){
            $slug=Str::slug($request->business_name).Seller::latest()->first()->id+1;
        }else{
            $slug=Str::slug($request->business_name);
        }

        $seller=new Seller();
        $seller->slug=$slug;
        $seller->email=$newSeller->email;
        $seller->mobile=$newSeller->mobile;
        $seller->password=Hash::make($newSeller->password);
        $seller->mobile_verification="YES";
        $seller->remember_token=Str::random(60);
        $seller->save();



        $seller_info=new SellerInfo();
        $seller_info->seller_id=$seller->id;
        $seller_info->first_name=$request->first_name;
        $seller_info->last_name=$request->last_name;
        $seller_info->birth_day=$request->birth_year.'/'.$request->birth_month.'/'.$request->birth_day;
        $seller_info->gender=$request->gender;
        $seller_info->identity_card_number=$request->identity_card_number;
        $seller_info->national_identity_number=$request->national_identity_number;

        $seller_info->company_name=$request->company_name;
        $seller_info->company_type=$request->company_type;
        $seller_info->company_registration_number=$request->company_registration_number;
        $seller_info->company_national_identity_number=$request->company_national_identity_number;
        $seller_info->company_economic_number=$request->company_economic_number;

        $seller_info->state_id=$request->state_id;
        $seller_info->city_id=$request->city_id;
        $seller_info->address=$request->address;
        $seller_info->phone=$request->phone;
        $seller_info->post_code=$request->post_code;
        $seller_info->location=$request->lat_and_long;
        $seller_info->mobile=$newSeller->mobile;

        $seller_info->business_name=$request->business_name;
        $seller_info->shaba_number=$request->shaba_number;
        $seller_info->main_supply_category_id=$request->main_supply_category_id;
        $seller_info->number_of_products=$request->number_of_products;
        $seller_info->econtract=$request->econtract;
        $seller_info->save();

        $wallet=new Wallet();
        $wallet->seller_id=$seller->id;
        $wallet->save();


        $newSeller->delete();
        if ($seller_info){
            session()->forget('show-seller-business-details');
            session()->put('show-seller-documents',$seller_info->mobile);
            return response([
                'status'=>'success',
                'redirect'=>route('seller.registration_documents')
            ]);
        }

    }

    public function registration_documents()
    {
        $seller=Seller::where(['mobile'=>session('show-seller-documents')])->first();
        if (!$seller){
            return redirect()->route('seller.registration_new_seller');
        }
        return view('front::sellers.auth.documents');

    }

    public function registration_documents_store(Request $request)
    {
        $seller=Seller::where(['mobile'=>session('show-seller-documents')])->first();
        if (!$seller){
            return abort(404);
        }
        $sellerInfo=SellerInfo::where('seller_id',$seller->id)->first();
        if ($request->hasFile($request->imageFor)) {
            if (!file_exists(public_path("/uploads/sellers/documents/".$seller->id))) {
                Storage::disk('public')->makeDirectory("/uploads/sellers/documents/".$seller->id);
            }
            if ($sellerInfo[$request->imageFor] && Storage::disk('public')->exists($sellerInfo[$request->imageFor])) {
                Storage::disk('public')->delete($sellerInfo[$request->imageFor]);
            }

            $file = $request[$request->imageFor];

            $name = uploadOptimizedImage($file, 'sellers/documents',$seller->id);
            $sellerInfo[$request->imageFor] =$name;
            $sellerInfo->save();
        }
    }

    public function registration_documents_delete(Request $request)
    {

        $seller=Seller::where(['mobile'=>session('show-seller-documents')])->first();
        if (!$seller){
            return abort(404);
        }
        $sellerInfo=SellerInfo::where('seller_id',$seller->id)->first();
        Storage::disk('public')->delete($sellerInfo[$request->imageFor]);
        $sellerInfo[$request->imageFor]=null;
        $sellerInfo->save();
    }

    public function registration_documents_check(Request $request)
    {
        $seller=Seller::where(['mobile'=>session('show-seller-documents')])->first();
        if (!$seller){
            return abort(404);
        }
        $sellerInfo=SellerInfo::where('seller_id',$seller->id)->first();

        if ($request->vat_free==1){
            if ($sellerInfo->card_image=="" or $sellerInfo->card_image_back=="" or $sellerInfo->vat_image==""){
                return response([
                    'error'=>'انتخاب تصویر گواهی ارزش افزوده و تصویر کارت ملی اجباری است'
                ]);
            }
        }elseif ($request->vat_free==2){
            if ($sellerInfo->card_image=="" or $sellerInfo->card_image_back==""){
                return response([
                    'status'=>'error',
                    'message'=>'انتخاب تصویر کارت ملی اجباری است'
                ]);
            }
        }

        $sellerInfo->vat_free=$request->vat_free;
        $sellerInfo->save();

        $seller->status_register='documents';
        $seller->save();
        session()->forget('show-seller-documents');
        session()->put('show-seller-checkout',$sellerInfo->mobile);
        return response([
            'status'=>'success',
            'redirect'=>route('seller.registration_checkout')
        ]);
    }

    public function registration_checkout()
    {
        $seller=Seller::where(['mobile'=>session('show-seller-checkout')])->first();
        if (!$seller){
            return redirect()->route('seller.login');
        }
        $categories=Category::where('commission','!=',null)->get();
        return view('front::sellers.auth.checkout',compact('categories'));
    }

    public function registration_checkout_store(Request $request)
    {
        $seller=Seller::where(['mobile'=>session('show-seller-checkout')])->first();
        if (!$seller){
            return response([
                'status'=>'success',
                'redirect'=>route('seller.registration_new_seller')
            ]);
        }
        $seller->status_register='complete';
        $seller->save();
        session()->forget('show-seller-checkout');
        $admins = Admin::whereIn('level', ['admin', 'creator'])->get();
        Notification::send($admins, new SellerRegistered($seller));
        if (option('sms_on_seller_register', 'off') == 'on') {
            Notification::send($seller, new SellerCreated($seller));
        }
        Auth::guard('seller')->login($seller);

        return response([
            'status'=>'success',
            'redirect'=>route('seller.dashboard')
        ]);
    }

    public function logout()
    {
        Auth::guard('seller')->logout();
        return redirect()->route('seller.dashboard');
    }

    public function get_new_code(Request $request)
    {
        $newSeller=NewSeller::where('mobile',$request->mobile)->first();
        if ($newSeller){
            $now = Carbon::now();
            $time = $newSeller->updated_at;
            if ($time->diffInSeconds($now) < 1) {
                return response([
                    'status'=>'errorTime',
                ]);
            }
            if (option('sms_to_verify_user', 'on') == 'on') {
                Notification::send($newSeller, new NewSellerCodeSent($newSeller));
            }
            return response([
                'status'=>'success',
            ]);
        }else{
            return response([
                'status'=>'error',
                'redirect'=>route('seller.registration')
            ]);
        }
    }


}
