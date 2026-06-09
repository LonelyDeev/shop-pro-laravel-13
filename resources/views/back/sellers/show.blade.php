@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/users/show.css') }}">
@endpush


@section('content')
    @php
        $orderItem_ids=\App\Models\OrderItem::where('seller_id',$seller->id)->get();
        $order_ids=[];
            foreach ($orderItem_ids as $orderItem_id){
                $order_ids[]=$orderItem_id->order_id;
            }
            $order_ids=array_unique($order_ids);
    @endphp
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-left mb-0">مشخصات فروشنده</h2>
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">مدیریت
                                    </li>
                                    <li class="breadcrumb-item">مدیریت فروشندگان
                                    </li>
                                    <li class="breadcrumb-item active">مشخصات فروشنده
                                        <a href="{{ route('admin.sellers.show', ['seller' => $seller]) }}">{{ $seller->seller_info->fullname }}</a>
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="card user-statistics-card">
                            <div class="card-header d-flex align-items-start pb-0">
                                <div>
                                    <h2 title="{{ convert_number($seller->getWallet()->balance()) }}" class="text-bold-700 mb-0">{{ number_format($seller->getWallet()->balance()) }}</h2>
                                    <p>موجودی کیف پول</p>
                                </div>
                                <div class="avatar bg-rgba-info p-50 m-0">
                                    <div class="avatar-content">
                                        <i class="fa fa-credit-card text-info font-medium-5"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <span>
                                    <a href="{{ route('admin.wallets.show', ['wallet' => $seller->getWallet()]) }}" class="card-link">تاریخچه کیف پول <i class="fa fa-angle-left"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>


                    @php
                        $orders_sum = $seller->orders()->paid()->sum('price')
                    @endphp

                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="card user-statistics-card">
                            <div class="card-header d-flex align-items-start pb-0">
                                <div>
                                    <h2 title="ارزش سفارشات: {{ number_format($orders_sum) }} تومان" class="text-bold-700 mb-0">{{ formatPriceUnits($orders_sum) }}</h2>
                                    <p>ارزش سفارشات موفق</p>
                                </div>
                                <div class="avatar bg-rgba-primary p-50 m-0">
                                    <div class="avatar-content">
                                        <i class="feather icon-briefcase text-primary font-medium-5"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <span>
                                    <a href="{{ route('admin.orders.index', ['username' => $seller->username]) }}" class="card-link">مشاهده همه <i class="fa fa-angle-left"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="card user-statistics-card">
                            <div class="card-header d-flex align-items-start pb-0">
                                <div>
                                    <h2 title="کل بازدید: {{ $seller->views()->count() }}" class="text-bold-700 mb-0">{{ $seller->views()->whereDate('created_at', now())->count() }}</h2>
                                    <p>بازدید امروز</p>
                                </div>
                                <div class="avatar bg-rgba-warning p-50 m-0">
                                    <div class="avatar-content">
                                        <i class="feather icon-eye text-warning font-medium-5"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <span>
                                    <a href="{{ route('admin.sellers.views', ['seller' => $seller]) }}" class="card-link">مشاهده همه <i class="fa fa-angle-left"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="card user-statistics-card">
                            <div class="card-header d-flex align-items-start pb-0">
                                <div>

                                    <h2 class="text-bold-700 mb-0">{{ count($order_ids) }}</h2>
                                    <p>تعداد سفارشات</p>
                                </div>
                                <div class="avatar bg-rgba-primary p-50 m-0">
                                    <div class="avatar-content">
                                        <i class="feather icon-briefcase text-primary font-medium-5"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <span>
                                    <a href="{{ route('admin.sellers.seller_orders', ['seller' => $seller]) }}" class="card-link">مشاهده همه <i class="fa fa-angle-left"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>


                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="card user-statistics-card">
                            <div class="card-header d-flex align-items-start pb-0">
                                <div>
                                    <h2 title="تعداد محصولات: {{ count($products) }}" class="text-bold-700 mb-0">{{ count($products) }}</h2>
                                    <p>تعداد محصولات</p>
                                </div>
                                <div class="avatar bg-rgba-warning p-50 m-0">
                                    <div class="avatar-content">
                                        <i class="feather icon-shopping-cart text-warning font-medium-5"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <span>
                                    <a href="{{ route('admin.sellers.seller_products', ['seller' => $seller]) }}" class="card-link">مشاهده همه <i class="fa fa-angle-left"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="card user-statistics-card">
                            <div class="card-header d-flex align-items-start pb-0">
                                <div>
                                    <h2 title="تعداد تنوع: {{ count($variants) }}" class="text-bold-700 mb-0">{{ count($variants) }}</h2>
                                    <p>تعداد تنوع</p>
                                </div>
                                <div class="avatar bg-rgba-warning p-50 m-0">
                                    <div class="avatar-content">
                                        <i class="feather icon-shopping-cart text-warning font-medium-5"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <span>
                                    <a href="{{ route('admin.sellers.seller_variants', ['seller' => $seller]) }}" class="card-link">مشاهده همه <i class="fa fa-angle-left"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="card user-statistics-card">
                            <div class="card-header d-flex align-items-start pb-0">
                                <div>
                                    <h2 class="text-bold-700 mb-0">{{ count($notifications) }}</h2>
                                    <p>پیام "اعلان" ارسال شده</p>
                                </div>
                                <div class="avatar bg-rgba-success p-50 m-0">
                                    <div class="avatar-content">
                                        <i class="fa-regular fa-envelope text-success font-medium-5"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <span>
                                    <a href="{{route('admin.sellers.notifications',['seller'=>$seller])}}" class="card-link">مشاهده همه <i class="fa fa-angle-left"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <form id="seller-edit-form" action="{{ route('admin.sellers.update', ['seller' => $seller]) }}" method="post" data-redirect="{{ route('admin.sellers.index') }}">
                    @csrf
                    @method('put')
                <section  class="card">
                    <div class="card-header justify-content-sm-between">
                        <h4 class="card-title">مشخصات فروشنده</h4>
                        <div class="col-md-10 text-right d-flex justify-content-end">

                            <a class="mr-1" href="{{route('admin.sellers.notification.create',['seller'=>$seller])}}"><div class="btn personal-success-btn uk-margin-remove">
                                    ارسال پیام یا اعلان جدید
                                    <i class="fa-solid fa-plus mr-0-5"></i>
                                </div>
                            </a>

                            @can('sellers.update')
                                <button type="submit"  class="btn personal-warning-btn mr-1"><i class="feather icon-edit-1"></i> ذخیره اطلاعات</button>
                            @endcan

                            @can('sellers.delete')

                                @if($seller->id != auth()->user()->id)
                                    <button type="button" data-user="{{ $seller->id }}" class="btn personal-danger-btn  waves-effect waves-light btn-user-delete"  data-toggle="modal" data-target="#user-delete-modal">حذف</button>
                                @else
                                    <button type="button" class="btn personal-danger-btn waves-effect waves-light" disabled>حذف</button>
                                @endif

                            @endcan


                        </div>
                    </div>
                    <div class="card-content" id="main-card">
                        <div class="card-body">
                            <section class="page-users-view">

                                    <div class="row">
                                        <!-- account start -->
                                        <div class="col-12">
                                            <div class="card mb-0">
                                                <div class="card-body pl-0">

                                                    <div class="row mb-2">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>وضعیت</label>
                                                                <select name="status" class="form-control">
                                                                    <option value="ACTIVE" @if($seller->status=="ACTIVE")selected @endif>فعال</option>
                                                                    <option value="INACTIVE" @if($seller->status=="INACTIVE")selected @endif>غیر فعال</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>وضعیت ثبت نام</label>
                                                                <select name="status_register" class="form-control">
                                                                    <option value="business-details" @if($seller->status_register=="business-details")selected @endif>در حال تکمیل اطلاعات</option>
                                                                    <option value="documents" @if($seller->status_register=="documents")selected @endif>بارگذاری مدارک</option>
                                                                    <option value="complete" @if($seller->status_register=="complete")selected @endif>تکمیل ثبت نام</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>وضعیت مدارک</label>
                                                                <select name="status_documents" class="form-control">
                                                                    <option value="Accept" @if($seller->status_documents=="Accept")selected @endif>تایید شده</option>
                                                                    <option value="Waiting" @if($seller->status_documents=="Waiting")selected @endif>در انتظار تایید</option>
                                                                    <option value="Reject" @if($seller->status_documents=="Reject")selected @endif>رد مدارک</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>وضعیت کاری</label>
                                                                <select name="status_work" class="form-control">
                                                                    <option value="ACTIVE" @if($seller->status_work=="ACTIVE")selected @endif>فعال</option>
                                                                    <option value="EditProfile" @if($seller->status_work=="EditProfile")selected @endif>ویرایش اطلاعات</option>
                                                                    <option value="Stop" @if($seller->status_work=="Stop")selected @endif>متوقف شده</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                    </div>

                                                    <div class="nav-vertical">
                                                        <ul class="nav nav-tabs nav-left flex-column" role="tablist">
                                                            <li class="nav-item">
                                                                <a class="nav-link active" id="baseVerticalLeft-tab1" data-toggle="tab" aria-controls="tabVerticalLeft1" href="#tabVerticalLeft1" role="tab" aria-selected="false">اطلاعات فروشنده</a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" id="productMetaTab" data-toggle="tab" aria-controls="tabVerticalLeft2" href="#tabVerticalLeft2" role="tab" aria-selected="false">اطلاعات حساب بانکی</a>
                                                            </li>
                                                            <li class="nav-item physical-item">
                                                                <a class="nav-link " id="product-prices-tab-nav" data-toggle="tab" aria-controls="tabVerticalLeft2" href="#tabVerticalLeft3" role="tab" aria-selected="true">اطلاعات تماس و آدرس</a>
                                                            </li>
                                                            <li class="nav-item download-item">
                                                                <a class="nav-link" id="product-files-tab-nav" data-toggle="tab" aria-controls="tabVerticalLeft4" href="#tabVerticalLeft4" role="tab" aria-selected="false">اطلاعات قرارداد</a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" id="productImageTab" data-toggle="tab" aria-controls="tabVerticalLeft5" href="#tabVerticalLeft5" role="tab" aria-selected="false"> مدارک</a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" id="productImageTab" data-toggle="tab" aria-controls="tabVerticalLeft6" href="#tabVerticalLeft6" role="tab" aria-selected="false"> اطلاعات ورود</a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" id="productRateTab" data-toggle="tab" aria-controls="tabVerticalLeft7" href="#tabVerticalLeft7" role="tab" aria-selected="false"> عملکرد</a>
                                                            </li>
                                                        </ul>
                                                        <div class="tab-content">
                                                            <div class="tab-pane active" id="tabVerticalLeft1" role="tabpanel" aria-labelledby="tabVerticalLeft1">
                                                                <div class="card-header d-flex justify-content-between align-items-end p-0 mb-2">
                                                                    <h4 class="card-title">اطلاعات فروشنده</h4>
                                                                </div>

                                                                    <div class="form-body col-md-12">

                                                                            <div class=" w-100 row">
                                                                                <div class="col-md-4">
                                                                                    <div class="form-group">
                                                                                        <label>کد فروشنده</label>
                                                                                        <input type="text" class="form-control valid" value="{{$seller->id}}" disabled aria-invalid="false">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-4">
                                                                                    <div class="form-group">
                                                                                        <label>نوع فروشنده</label>
                                                                                        <select name="private_business" class="form-control">
                                                                                            <option value="private" @if($seller->seller_info->private_business=="private")selected @endif>حقیقی</option>
                                                                                            <option value="business" @if($seller->seller_info->private_business=="business")selected @endif>حقوقی</option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-4">
                                                                                    <div class="form-group">
                                                                                        <label>نام تجاری</label>
                                                                                        <input type="text" class="form-control" name="business_name" value="{{$seller->seller_info->business_name}}">
                                                                                    </div>
                                                                                </div>
                                                                            </div>


                                                                            <div id="private-div" class="@if($seller->seller_info->private_business=="business")d-none @endif w-100 row">
                                                                                    <div class="col-md-4">
                                                                                        <div class="form-group">
                                                                                            <label>نام</label>
                                                                                            <input type="text" class="form-control valid" value="{{$seller->seller_info->first_name}}" name="first_name" aria-invalid="false">
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="col-md-4">
                                                                                        <div class="form-group">
                                                                                            <label>نام خانوادگی</label>
                                                                                            <input type="text" class="form-control valid" value="{{$seller->seller_info->last_name}}" name="last_name" aria-invalid="false">
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="col-md-4">
                                                                                        <div class="form-group">
                                                                                            <label>جنسیت</label>
                                                                                            <select name="gender" class="form-control">
                                                                                                <option value="male" @if($seller->seller_info->gender=="male")selected @endif>مرد</option>
                                                                                                <option value="female" @if($seller->seller_info->gender=="female")selected @endif>زن</option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="col-md-4">
                                                                                        <div class="form-group">
                                                                                            <label>تاریخ تولد</label>
                                                                                            <input type="text" class="form-control valid" value="{{$seller->seller_info->birth_day}}" name="birth_day" aria-invalid="false">
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="col-md-4">
                                                                                        <div class="form-group">
                                                                                            <label>کد ملی</label>
                                                                                            <input type="number" class="form-control valid" value="{{$seller->seller_info->national_identity_number}}" name="national_identity_number" aria-invalid="false">
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="col-md-4">
                                                                                        <div class="form-group">
                                                                                            <label>شماره شناسنامه</label>
                                                                                            <input type="number" class="form-control valid" value="{{$seller->seller_info->identity_card_number}}" name="identity_card_number" aria-invalid="false">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                           <div id="business-div" class="@if($seller->seller_info->private_business=="private")d-none @endif w-100 row">
                                                                                    <div class="col-md-4">
                                                                                        <div class="form-group">
                                                                                            <label>نام شرکت</label>
                                                                                            <input type="text" class="form-control valid" value="{{$seller->seller_info->company_name}}" name="company_name" aria-invalid="false">
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="col-md-4">
                                                                                        <div class="form-group">
                                                                                            <label>نوع شرکت</label>
                                                                                            <select name="company_type" class="form-control">
                                                                                                <option value="public" @if($seller->seller_info->company_type=="public")selected @endif>سهامی عام</option>
                                                                                                <option value="joint_stock" @if($seller->seller_info->company_type=="joint_stock")selected @endif>سهامی خاص</option>
                                                                                                <option value="ltd" @if($seller->seller_info->company_type=="ltd")selected @endif>مسولیت محدود</option>
                                                                                                <option value="coop" @if($seller->seller_info->company_type=="coop")selected @endif>تعاونی</option>
                                                                                                <option value="solidarity" @if($seller->seller_info->company_type=="solidarity")selected @endif>تضامنی</option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="col-md-4">
                                                                                        <div class="form-group">
                                                                                            <label>شماره ثبت</label>
                                                                                            <input type="text" class="form-control valid" value="{{$seller->seller_info->company_registration_number}}" name="company_registration_number" aria-invalid="false">
                                                                                        </div>
                                                                                    </div>


                                                                                    <div class="col-md-4">
                                                                                        <div class="form-group">
                                                                                            <label>شناسه ملی</label>
                                                                                            <input type="text" class="form-control valid" value="{{$seller->seller_info->company_national_identity_number}}" name="company_national_identity_number" aria-invalid="false">
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="col-md-4">
                                                                                        <div class="form-group">
                                                                                            <label>کد اقتصادی</label>
                                                                                            <input type="text" class="form-control valid" value="{{$seller->seller_info->company_economic_number}}" name="company_economic_number" aria-invalid="false">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                        <div class="row w-100">
                                                                            <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                    <label>قصد فروش چه کالاهایی را دارید؟</label>
                                                                                    <select class="form-control product-category" name="main_supply_category_id">
                                                                                        <option value="">انتخاب کنید</option>
                                                                                        @foreach ($categories as $category)
                                                                                            <option  class="l{{ $category->parents()->count() + 1 }} {{ $category->categories()->count() ? 'non-leaf' : '' }}"
                                                                                                     data-pup="{{ $category->category_id }}" value="{{ $category->id }}"
                                                                                                {{ ($seller->seller_info && $seller->seller_info->main_supply_category_id == $category->id) ? 'selected' : '' }}>{{ $category->title }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                    <label>تعداد حدودی تنوع کالای آماده فروش</label>
                                                                                    <select name="number_of_products" id="number_of_products" class="w-100 valid form-control" aria-invalid="false">
                                                                                        <option value="">انتخاب کنید</option>
                                                                                        <option value="10" {{ ($seller->seller_info && $seller->seller_info->number_of_products == "10") ? 'selected' : '' }}>1-10</option>
                                                                                        <option value="50" {{ ($seller->seller_info && $seller->seller_info->number_of_products == "50") ? 'selected' : '' }} data-select2-id="107">11-50</option>
                                                                                        <option value="100" {{ ($seller->seller_info && $seller->seller_info->number_of_products == "100") ? 'selected' : '' }} data-select2-id="108">51-100</option>
                                                                                        <option value="300" {{ ($seller->seller_info && $seller->seller_info->number_of_products == "300") ? 'selected' : '' }} data-select2-id="109">101-300</option>
                                                                                        <option value="1000" {{ ($seller->seller_info && $seller->seller_info->number_of_products == "1000") ? 'selected' : '' }} data-select2-id="110">301-1000</option>
                                                                                        <option value="3000" {{ ($seller->seller_info && $seller->seller_info->number_of_products == "3000") ? 'selected' : '' }} data-select2-id="111">1001-3000</option>
                                                                                        <option value="10000" {{ ($seller->seller_info && $seller->seller_info->number_of_products == "10000") ? 'selected' : '' }} data-select2-id="112">3001-10000</option>
                                                                                        <option value="30000" {{ ($seller->seller_info && $seller->seller_info->number_of_products == "30000") ? 'selected' : '' }} data-select2-id="113">10001-30000</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                       <div class="row w-100">
                                                                           <div class="col-md-2">
                                                                               <label>لوگوی فروشنده</label>
                                                                               <div class="users-view-image">
                                                                                   <img src="{{ $seller->seller_info->logo ? asset($seller->seller_info->logo) : asset('/empty.svg') }}" class="users-avatar-shadow w-100 rounded mb-2 ml-1" alt="avatar">
                                                                               </div>


                                                                           </div>

                                                                           <div class="col-md-10">
                                                                               <div class="form-group">
                                                                                   <label>درباره فروشنده</label>
                                                                                   <textarea name="bio" class="form-control valid" rows="3">{{$seller->seller_info->bio}}</textarea>
                                                                               </div>
                                                                           </div>

                                                                           <div class="col-md-4">
                                                                               <fieldset class="form-group">
                                                                                   <label>تغییر لوگو</label>
                                                                                   <div class="custom-file">
                                                                                       <input type="file" accept="image/*" name="image" class="custom-file-input valid" aria-invalid="false">
                                                                                       <label class="custom-file-label" for="image"></label>
                                                                                       <p><small>بهترین اندازه <span class="text-danger">600 * 600</span> پیکسل میباشد.</small></p>
                                                                                   </div>
                                                                               </fieldset>
                                                                           </div>
                                                                       </div>


                                                                    </div>

                                                            </div>

                                                            <div class="tab-pane" id="tabVerticalLeft2" role="tabpanel" aria-labelledby="tabVerticalLeft2">
                                                                <div class="card-header d-flex justify-content-between align-items-end p-0 mb-2">
                                                                    <h4 class="card-title">اطلاعات حساب بانکی</h4>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>شماره شبا</label>
                                                                                <input type="number" class="form-control valid" value="{{$seller->seller_info->shaba_number}}" name="shaba_number" aria-invalid="false">
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <div class="form-group">
                                                                                <label>صاحب / صاحبان حساب</label>
                                                                                <input type="text" class="form-control valid" value="{{$seller->seller_info->full_name}}" disabled aria-invalid="false">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>

                                                            <div class="tab-pane" id="tabVerticalLeft3" role="tabpanel" aria-labelledby="tabVerticalLeft3">
                                                                <div class="card-header d-flex justify-content-between align-items-end p-0 mb-2">
                                                                    <h4 class="card-title">اطلاعات تماس و آدرس</h4>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="row">
                                                                        <div class="col-md-4">
                                                                            <div class="form-group">
                                                                                <label>ایمیل</label>
                                                                                <input type="email" class="form-control valid" value="{{$seller->email}}" name="email" aria-invalid="false">
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <div class="form-group">
                                                                                <label>تلفن همراه</label>
                                                                                <input type="number" class="form-control valid" value="{{$seller->seller_info->mobile}}" name="mobile" aria-invalid="false">
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <div class="form-group">
                                                                                <label>وب سایت</label>
                                                                                <input type="text" class="form-control valid" value="{{$seller->seller_info->website}}" name="website" aria-invalid="false">
                                                                            </div>
                                                                        </div>

                                                                        @php
                                                                            $cities = [];
                                                                            $city_id = null;
                                                                        @endphp

                                                                        <div class="col-md-4">
                                                                            <div class="form-group">
                                                                                <label>استان فروشگاه</label>
                                                                                <select id="province"  data-action="{{ route('provinces.get-cities') }}"  name="state_id" class="form-control">
                                                                                    <option value="">انتخاب کنید</option>

                                                                                    @foreach ($provinces as $province)
                                                                                        <option value="{{ $province->id }}" {{ $seller->seller_info->state_id == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                        </div>


                                                                        <div class="col-md-4">
                                                                            <div class="form-group">
                                                                                <label>شهر فروشگاه</label>
                                                                                <select id="city" name="city_id" class="form-control">
                                                                                    @foreach ($seller->seller_info->province->cities as $city)
                                                                                        <option value="{{ $city->id }}" {{ $seller->seller_info->city_id == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <div class="form-group">
                                                                                <label>موقعیت مکانی</label>
                                                                                <input type="text" class="form-control valid" value="{{$seller->seller_info->location}}" name="location" aria-invalid="false">
                                                                            </div>
                                                                        </div>


                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label>آدرس</label>
                                                                                <input type="text" class="form-control valid" value="{{$seller->seller_info->address}}" name="address" aria-invalid="false">
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>کد پستی</label>
                                                                                <input type="number" class="form-control valid" value="{{$seller->seller_info->post_code}}" name="post_code" aria-invalid="false">
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>تلفن ثابت</label>
                                                                                <input type="number" class="form-control valid" value="{{$seller->seller_info->phone}}" name="phone" aria-invalid="false">
                                                                            </div>
                                                                        </div>


                                                                    </div>
                                                                </div>

                                                            </div>

                                                            <div class="tab-pane" id="tabVerticalLeft4" role="tabpanel" aria-labelledby="tabVerticalLeft4">
                                                                <div class="card-header d-flex justify-content-between align-items-end p-0 mb-2">
                                                                    <h4 class="card-title">اطلاعات قرارداد</h4>
                                                                </div>
                                                                <div  class="card">
                                                                    <div class="card-content">
                                                                        <div class="card-body ">


                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>

                                                            <div class="tab-pane" id="tabVerticalLeft5" role="tabpanel" aria-labelledby="tabVerticalLeft5">
                                                                <div class="card-header d-flex justify-content-between align-items-end p-0 mb-2">
                                                                    <h4 class="card-title"> مدارک</h4>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="form-body">

                                                                        <div class="row w-100">
                                                                            <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                    <label>تصویر کارت ملی</label>
                                                                                    <img style="max-height: 200px;" src="{{ $seller->seller_info->card_image ? asset($seller->seller_info->card_image) : asset('/empty.svg') }}" class="users-avatar-shadow w-100 rounded mb-2 " alt="card_image">
                                                                                    <div class="custom-file">
                                                                                        <input id="card_image" type="file" accept="image/*" name="card_image" class="custom-file-input">
                                                                                        <label class="custom-file-label" for="card_image"></label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                    <label>تصویر پشت کارت ملی</label>
                                                                                    <img style="max-height: 200px;" src="{{ $seller->seller_info->card_image_back ? asset($seller->seller_info->card_image_back) : asset('/empty.svg') }}" class="users-avatar-shadow w-100 rounded mb-2 " alt="card_image_back">

                                                                                    <div class="custom-file">
                                                                                        <input id="card_image_back" style="max-height: 220px;" type="file" accept="image/*" name="card_image_back" class="custom-file-input">
                                                                                        <label class="custom-file-label" for="card_image_back"></label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                            <div class="row">
                                                                                <div class="col-md-4">
                                                                                    <div class="form-group">
                                                                                        <label>مشمولیت مالیات بر ارزش افزوده</label>
                                                                                        <select name="vat_free" class="form-control">
                                                                                            <option value="1" @if($seller->seller_info->vat_free=="1")selected @endif>بله</option>
                                                                                            <option value="2" @if($seller->seller_info->vat_free=="2")selected @endif>خیر</option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>



                                                                            <div class="row w-100">
                                                                             <div id="vat_free_div" class="col-md-6 @if($seller->seller_info->vat_free=="2")d-none @endif ">
                                                                                <div class="form-group">
                                                                                    <label>تصویر گواهی ارزش افزوده</label>
                                                                                    <img style="max-height: 200px;" src="{{ $seller->seller_info->vat_image ? asset($seller->seller_info->vat_image) : asset('/empty.svg') }}" class="users-avatar-shadow w-100 rounded mb-2" alt="card_image_back">

                                                                                    <div class="custom-file">
                                                                                        <input id="vat_image" style="max-height: 220px;" type="file" accept="image/*" name="vat_image" class="custom-file-input">
                                                                                        <label class="custom-file-label" for="vat_image"></label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                             </div>

                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="tab-pane" id="tabVerticalLeft6" role="tabpanel" aria-labelledby="tabVerticalLeft6">
                                                                <div class="card-header d-flex justify-content-between align-items-end p-0 mb-2">
                                                                    <h4 class="card-title">اطلاعات قرارداد</h4>
                                                                </div>
                                                                <div  class="card">
                                                                    <div class="card-content">
                                                                        <div class="card-body ">
                                                                            <div class="row">
                                                                                <div class="col-md-6 col-12">
                                                                                    <div class="form-group">
                                                                                        <label>گذرواژه</label>
                                                                                        <input type="password" id="password" class="form-control" name="password">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-6 col-12">
                                                                                    <div class="form-group">
                                                                                        <label>تکرار گذرواژه</label>
                                                                                        <input type="password" class="form-control ltr" name="password_confirmation">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-12">
                                                                                    <div class="alert alert-info mt-1 alert-validation-msg" role="alert">
                                                                                        <i class="feather icon-info ml-1 align-middle"></i>
                                                                                        <span>در صورتی که نمیخواهید گذرواژه  را عوض کنید، فیلدهای گذرواژه را خالی بگذارید.</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>

                                                            <div class="tab-pane" id="tabVerticalLeft7" role="tabpanel" aria-labelledby="tabVerticalLeft7">
                                                                <div class="card-header d-flex justify-content-between align-items-end p-0 mb-2">
                                                                    <h4 class="card-title">رضایت و عملکرد</h4>
                                                                </div>
                                                                <div  class="card">
                                                                    <div class="card-content">
                                                                        <div class="card-body ">
                                                                            <div class="row">
                                                                                <div class="col-md-6 col-12">
                                                                                    <div class="form-group">
                                                                                        <label>عملکرد</label>
                                                                                        <select name="operation" class="form-control valid" aria-invalid="false">
                                                                                            <option value='{{null}}'>انتخاب کنید</option>
                                                                                            @for($i=0.5;$i<=5;$i+=0.5)
                                                                                                <option {{$seller->seller_info->operation==$i ? 'selected' : '' }}  value="{{$i}}">{{$i}}</option>
                                                                                            @endfor

                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-6 col-12">
                                                                                    <div class="form-group">
                                                                                        <label>رضایت از کالا (درصد)</label>
                                                                                        <input type="number" value='{{$seller->seller_info->satisfaction}}' max='100' class="form-control ltr" name="satisfaction">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-12">
                                                                                    <div class="alert alert-info mt-1 alert-validation-msg" role="alert">
                                                                                        <i class="feather icon-info ml-1 align-middle"></i>
                                                                                        <span>در صورتی که مقادیر را خالی بگذارید، به عنوان فروشنده جدید نمایش داده می شود.</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                        </div>
                                        <!-- account end -->
                                    </div>

                            </section>
                            <!-- page users view end -->

                        </div>
                    </div>
                </section>

                </form>

                <div class="row match-height">
                    <div class="col-md-12">
                        <section class="card">
                            <div class="card-header">
                                <h4 class="card-title">آخرین پیام "اعلان" ارسال شده به کاربر</h4>
                                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                                <div class="heading-elements">
                                    <ul class="list-inline mb-0">
                                        <li><a data-action="collapse"><i class="feather icon-chevron-down"></i></a></li>
                                        <li><a data-action="expand"><i class="feather icon-maximize"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-content collapse show">
                                @if (count($notifications))
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped mb-0">
                                                <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>عنوان</th>
                                                    <th>پیام </th>
                                                    <th>اولویت</th>
                                                    <th class="text-center">وضعیت بازدید</th>
                                                    <th class="text-center" style='width: 150px'>عملیات</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($notifications as $notification)
                                                    <tr>

                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $notification->title }}</td>
                                                        <td>{!! $notification->message !!}</td>
                                                        <td>
                                                            <div class="badge badge-{{$notification->priorityText()['color']}}">{{ $notification->priorityText()['title'] }}</div>
                                                        </td>
                                                        <td class="text-center">
                                                            @php $read=\Illuminate\Support\Facades\DB::table('notification_manage_users')->where(['notification_manage_id'=>$notification->id,'seller_id'=>$seller->id])->first()->read; @endphp
                                                            @if($read)
                                                                <i class="fa-regular fa-eye" title="seen"></i>
                                                            @endif

                                                        </td>
                                                        <td class="text-center">
                                                            <div class="dropdown dropdown-action">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $notification->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                                </button>
                                                                <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $notification->id }}">
                                                                    <a class="dropdown-item" href="{{ route('admin.sellers.notification.show', ['seller'=>$seller,'notification' => $notification]) }}"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                                                                    <div class="dropdown-divider"></div>
                                                                    <button class="dropdown-item btn-delete-ticket"  data-action="{{ route('admin.notifications.destroy', ['notification' => $notification]) }}"  data-toggle="modal" data-target="#delete-ticket-modal"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>

                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card-footer text-muted text-right">
                                        <span>
                                            <a href="{{route('admin.sellers.notifications',['seller'=>$seller])}}" class="card-link">مشاهده همه <i class="fa fa-angle-left"></i></a>
                                        </span>
                                    </div>
                                @else
                                    <div class="card-body">
                                        <div class="card-text">
                                            <p>چیزی برای نمایش وجود ندارد!</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </section>
                    </div>
                    @if (count($order_ids))
                        <div class="col-md-12">
                            <section class="card">
                                <div class="card-header">
                                    <h4 class="card-title">آخرین سفارشات</h4>
                                    <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                                    <div class="heading-elements">
                                        <ul class="list-inline mb-0">
                                            <li><a data-action="collapse"><i class="feather icon-chevron-down"></i></a></li>
                                            <li><a data-action="expand"><i class="feather icon-maximize"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-content collapse show">

                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>شماره سفارش</th>
                                                        <th>تاریخ</th>
                                                        <th>مبلغ</th>
                                                        <th>وضعیت</th>
                                                        <th class="text-center">عملیات</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($orders as $order)
                                                        <tr>
                                                            <td>{{ $order->id }}</td>
                                                            <td title="{{ jdate($order->created_at) }}">{{ jdate($order->created_at)->ago() }}</td>
                                                            <td title="{{ convert_number($order->priceSeller($seller->id)) }} تومان">{{ number_format($order->priceSeller($seller->id)) }}</td>
                                                            <td>{{ $order->statusText() }}</td>
                                                            <td class="text-center">
                                                                <a href="{{ route('admin.sellers.orders.show', ['order' => $order,'seller'=>$seller]) }}" class="btn btn-info waves-effect waves-light">مشاهده</a>
                                                            </td>

                                                        </tr>
                                                    @endforeach

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card-footer text-muted text-right">
                                        <span>
                                            <a href="{{ route('admin.sellers.seller_orders', ['seller' => $seller->id]) }}" class="card-link">مشاهده همه <i class="fa fa-angle-left"></i></a>
                                        </span>
                                    </div>
                                </div>
                            </section>
                        </div>
                    @endif
                        <div class="col-md-12">
                            <section class="card">
                                <div class="card-header">
                                    <h4 class="card-title">آخرین محصولات اضافه شده</h4>
                                    <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                                    <div class="heading-elements">
                                        <ul class="list-inline mb-0">
                                            <li><a data-action="collapse"><i class="feather icon-chevron-down"></i></a></li>
                                            <li><a data-action="expand"><i class="feather icon-maximize"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-content">
                                    @if (count($products))
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped mb-0">
                                                    <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>تصویر شاخص</th>
                                                        <th>عنوان محصول</th>
                                                        <th>تاریخ ایجاد</th>
                                                        <th class="text-center">تعداد موجودی</th>
                                                        <th>وضعیت انتشار</th>
                                                        <th class="text-center" style="width: 150px">عملیات</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($products as $product)
                                                        <tr>
                                                            <td>{{ $product->id }}</td>
                                                            <td>
                                                                <img class="post-thumb" src="{{ $product->image ? asset($product->image) : asset('/empty.svg') }}" alt="{{ $product->title }}">
                                                            </td>
                                                            <td>{{ $product->title }}</td>
                                                            <td>{{ jdate($product->created_at)->format('%d %B %Y') }}</td>
                                                            <td class="text-center">{{ $product->prices()->sum('stock') }}</td>
                                                            <td class="text-center">
                                                                <span style="width: 100px;">
                                                                    @if($product->isPublished())
                                                                        <div class="badge badge-success">منتشر شده</div>
                                                                    @else
                                                                        <div class="badge badge-danger">پیش نویس</div>
                                                                    @endif

                                                                    @if($product->status=="Accept")
                                                                            <div class="badge badge-success">تایید شده</div>
                                                                    @elseif($product->status=="Waiting")
                                                                            <div class="badge badge-warning">در انتضار تایید</div>
                                                                    @elseif($product->status=="Reject")
                                                                            <div class="badge badge-danger">تایید نشده</div>
                                                                    @endif

                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="dropdown dropdown-action">
                                                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $product->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                                                    </button>
                                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $product->id }}">
                                                                        <a class="dropdown-item" target='_blank' href="{{ Route::has('front.products.show') ? route('front.products.show', ['product' => $product]) : '' }}"><i class="fa-regular fa-eye mr-1"></i>نمایش</a>
                                                                        <div class="dropdown-divider"></div>

                                                                        <a class="dropdown-item" href="{{route('admin.products.edit', ['product' => $product])}}"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                                                                        <div class="dropdown-divider"></div>

                                                                        <button data-toggle="modal" data-target="#delete-modal-product" data-action="{{route('admin.products.destroy', ['product' => $product])}}" class="dropdown-item btn-delete"><i class="fa-solid fa-trash-can mr-1"></i>حذف</button>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="card-footer text-muted text-right">
                                        <span>
                                            <a href="{{ route('admin.sellers.seller_products', ['seller' => $seller]) }}" class="card-link">مشاهده همه <i class="fa fa-angle-left"></i></a>
                                        </span>
                                        </div>
                                    @else
                                        <div class="card-body">
                                            <div class="card-text">
                                                <p>چیزی برای نمایش وجود ندارد!</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </section>
                        </div>

                        <div class="col-md-12">
                            <section class="card">
                                <div class="card-header">
                                    <h4 class="card-title">آخرین لیست تنوع ها</h4>
                                    <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                                    <div class="heading-elements">
                                        <ul class="list-inline mb-0">
                                            <li><a data-action="collapse"><i class="feather icon-chevron-down"></i></a></li>
                                            <li><a data-action="expand"><i class="feather icon-maximize"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-content">
                                    @if (count($variants))
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped mb-0">
                                                    <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>تصویر شاخص</th>
                                                        <th>عنوان محصول</th>
                                                        <th>تاریخ ایجاد</th>
                                                        <th class="text-center">تعداد موجودی</th>
                                                        <th>وضعیت انتشار</th>
                                                        <th class="text-center" style="width: 150px">عملیات</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($variants as $variant)
                                                        @php $product=\App\Models\Product::find($variant->product_id); @endphp
                                                        @if($product)
                                                            <tr>
                                                                <td>{{ $product->id }}</td>
                                                                <td>
                                                                    <img class="post-thumb" src="{{ $product->image ? asset($product->image) : asset('/empty.svg') }}" alt="{{ $product->title }}">
                                                                </td>
                                                                <td>{{ $product->title }}</td>
                                                                <td>{{ jdate($product->created_at)->format('%d %B %Y') }}</td>
                                                                <td class="text-center">{{ $product->prices()->sum('stock') }}</td>
                                                                <td class="text-center">
                                                                <span style="width: 100px;">
                                                                    @if($product->isPublished())
                                                                        <div class="badge badge-success">منتشر شده</div>
                                                                    @else
                                                                        <div class="badge badge-danger">پیش نویس</div>
                                                                    @endif

                                                                    @if($product->status=="Accept")
                                                                        <div class="badge badge-success">تایید شده</div>
                                                                    @elseif($product->status=="Waiting")
                                                                        <div class="badge badge-warning">در انتضار تایید</div>
                                                                    @elseif($product->status=="Reject")
                                                                        <div class="badge badge-danger">تایید نشده</div>
                                                                    @endif

                                                                </span>
                                                                </td>
                                                                <td>
                                                                    <div class="dropdown dropdown-action">
                                                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $product->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                                                        </button>
                                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $product->id }}">
                                                                            <a class="dropdown-item" target='_blank' href="{{ Route::has('front.products.show') ? route('front.products.show', ['product' => $product]) : '' }}"><i class="fa-regular fa-eye mr-1"></i>نمایش</a>
                                                                            <div class="dropdown-divider"></div>

                                                                            <a class="dropdown-item" href="{{route('admin.products.edit', ['product' => $product])}}"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>

                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endif

                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="card-footer text-muted text-right">
                                        <span>
                                            <a href="{{ route('admin.sellers.seller_variants', ['seller' => $seller]) }}" class="card-link">مشاهده همه <i class="fa fa-angle-left"></i></a>
                                        </span>
                                        </div>
                                    @else
                                        <div class="card-body">
                                            <div class="card-text">
                                                <p>چیزی برای نمایش وجود ندارد!</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </section>
                        </div>



                    <div class="col-md-12">
                        <section class="card">

                            <div class="card-header">
                                <h4 class="card-title">آخرین بازدیدها</h4>
                                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                                <div class="heading-elements">
                                    <ul class="list-inline mb-0">
                                        <li><a data-action="collapse"><i class="feather icon-chevron-down"></i></a></li>
                                        <li><a data-action="expand"><i class="feather icon-maximize"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-content">
                                @if ($seller->views()->count())
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="min-width: 200px;">تاریخ</th>
                                                        <th>ip</th>
                                                        <th>platform</th>
                                                        <th class="text-center">آدرس</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($seller->views()->latest()->take(10)->get() as $view)
                                                        <tr>
                                                            <td class="ltr">{{ jdate($view->created_at) }}</td>
                                                            <td>{{ $view->ip }}</td>
                                                            <td>{{ get_option_property($view->options, 'platform') }}</td>
                                                            <td class="ltr text-right"><a class="text-dark" target="_blank" href="{{ url(urldecode($view->path)) }}">{{ urldecode($view->path) }}</a></td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card-footer text-muted text-right">
                                        <span>
                                            <a href="{{ route('admin.sellers.views', ['seller' => $seller]) }}" class="card-link">مشاهده همه <i class="fa fa-angle-left"></i></a>
                                        </span>
                                    </div>
                                @else
                                    <div class="card-body">
                                        <div class="card-text">
                                            <p>چیزی برای نمایش وجود ندارد!</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </section>
                    </div>


                    <div class="col-md-12">
                        <section class="card">

                            <div class="card-header">
                                <h4 class="card-title">لیست روش های ارسال</h4>
                                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                                <div class="heading-elements">
                                    <ul class="list-inline mb-0">
                                        <li><a data-action="collapse"><i class="feather icon-chevron-down"></i></a></li>
                                        <li><a data-action="expand"><i class="feather icon-maximize"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-content">
                                @if ($sellerCarriers->count())
                                    <div class="card-body">
                                        <div class="table-responsive overflow-unset">
                                            <table class="table table-striped mb-0">
                                                <thead>
                                                <tr>
                                                    <th>ردیف</th>
                                                    <th>عنوان</th>
                                                    <th>شهر فروشگاه</th>
                                                    <th>شهرهای تحت پوشش</th>
                                                    <th>پس کرایه</th>
                                                    <th class="text-center">وضعیت</th>
                                                    <th class="text-center">عملیات</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($sellerCarriers as $carrier)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $carrier->title }}</td>
                                                        <td>{{ $carrier->province->name }} - {{ $carrier->city->name }}</td>
                                                        <td>
                                                            @if ($carrier->covered_cities == 'all')
                                                                <span>همه</span>
                                                            @else
                                                                <abbr title="مشاهده لیست شهرها"><a class="carrier-cities-show" href="{{ route('admin.carriers.cities', ['carrier' => $carrier]) }}">لیست شهرها</a></abbr>
                                                            @endif
                                                        </td>
                                                        <td>{{ $carrier->carrige_forward ? 'بله' : 'خیر' }}</td>
                                                        <td class="text-center">
                                                            @if($carrier->is_active)
                                                                <div class="badge badge-success">فعال</div>
                                                            @else
                                                                <div class="badge badge-danger">غیر فعال</div>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="dropdown dropdown-action">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $carrier->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                                </button>
                                                                <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $carrier->id }}">
                                                                    @if ($carrier->carrige_forward)
                                                                        <button class="dropdown-item " ><i class="fa-solid fa-bars"></i> تعرفه ها</button>
                                                                    @else
                                                                        <a class="dropdown-item" href="{{ route('admin.tariffs.index', ['carrier' => $carrier]) }}"><i class="fa-solid fa-bars"></i>تعرفه ها</a>
                                                                    @endif
                                                                    <div class="dropdown-divider"></div>

                                                                    <a class="dropdown-item" href="{{ route('admin.carriers.edit', ['carrier' => $carrier]) }}"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                                                                    <div class="dropdown-divider"></div>

                                                                    <button class="dropdown-item btn-delete" data-action="{{ route('admin.carriers.destroy', ['carrier' => $carrier]) }}"  data-toggle="modal" data-target="#delete-modal"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card-footer text-muted text-right">
                                        {{$sellerCarriers->links()}}
                                    </div>
                                @else
                                    <div class="card-body">
                                        <div class="card-text">
                                            <p>چیزی برای نمایش وجود ندارد!</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </section>
                    </div>



                </div>
            </div>
        </div>
    </div>

    {{-- delete user modal --}}
    <div class="modal fade text-left" id="user-delete-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel19" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف فروشنده دیگر قادر به بازیابی آن نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="#" id="user-delete-form">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">خیر</button>
                        <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- delete post modal --}}
    <div class="modal fade text-left" id="delete-ticket-modal" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف درخواست  دیگر قادر به بازیابی آن نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="#" id="ticket-delete-form">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">خیر</button>
                        <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- delete product modal --}}
    <div class="modal fade text-left" id="delete-modal-product" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف محصول دیگر قادر به بازیابی آن نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="#" id="product-delete-form">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">
                            خیر
                        </button>
                        <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- delete carrier modal --}}
    <div class="modal fade text-left" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel19" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف روش ارسال دیگر قادر به بازیابی آن نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="#" id="carrier-delete-form">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">خیر</button>
                        <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
@include('back.partials.plugins', ['plugins' => [ 'jquery-tagsinput', 'jquery-ui', 'jquery.validate']])
@push('scripts')

    <script src="{{ asset('back/assets/js/pages/sellers/show.js') }}"></script>

@endpush
