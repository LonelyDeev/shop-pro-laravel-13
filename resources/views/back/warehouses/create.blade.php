@extends('back.layouts.master')

@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">مدیریت انبارها</a></li>
                                    <li class="breadcrumb-item active">ایجاد انبار جدید</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">ایجاد انبار جدید</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <form id="warehouse-create-form" action="{{ route('admin.warehouses.store') }}" data-redirect="{{ route('admin.warehouses.index') }}" method="POST" class="form">
                                @csrf

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="name">نام انبار <span class="text-danger">*</span></label>
                                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                                   value="{{ old('name') }}" placeholder="مثال: انبار مرکزی" required>
                                            @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="type">نوع انبار <span class="text-danger">*</span></label>
                                                <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                                    <option value="main" {{ old('type') == 'main' ? 'selected' : '' }}>انبار اصلی (فروشگاه)</option>
                                                    <option value="seller" {{ old('type') == 'seller' ? 'selected' : '' }}>انبار فروشنده</option>
                                                    <option value="temp" {{ old('type') == 'temp' ? 'selected' : '' }}>انبار موقت (برگشتی، معیوب و ...)</option>
                                                </select>
                                                @error('type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">نوع انبار را مشخص کنید</small>
                                            </div>
                                        </div>

                                        <div class="col-md-6" id="seller-select-container" style="display: none;">
                                            <div class="form-group">
                                                <label for="seller_id">انتخاب فروشنده <span class="text-danger" id="seller-required" style="display: none;">*</span></label>
                                                <select name="seller_id" id="seller_id" class="form-control @error('seller_id') is-invalid @enderror">
                                                    <option value="">انتخاب فروشنده</option>
                                                    @foreach($sellers ?? [] as $seller)
                                                        <option value="{{ $seller->id }}" {{ old('seller_id') == $seller->id ? 'selected' : '' }}>
                                                            {{ $seller->business_name ?? $seller->name }} ({{ $seller->mobile ?? '-' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('seller_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">فروشنده‌ای که این انبار به او تعلق دارد</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" id="temp-description" style="display: none;">
                                        <div class="col-md-12">
                                            <div class="alert alert-warning">
                                                <i class="feather icon-info"></i>
                                                <strong>انبار موقت:</strong> برای کالاهای برگشتی، کالاهای معیوب، کالاهای تعویضی، حراجی‌های فصلی و ...
                                            </div>
                                        </div>
                                    </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="manager_name">مدیر انبار</label>
                                            <input type="text" name="manager_name" id="manager_name" class="form-control @error('manager_name') is-invalid @enderror"
                                                   value="{{ old('manager_name') }}" placeholder="نام مدیر انبار">
                                            @error('manager_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone">تلفن انبار</label>
                                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                                                   value="{{ old('phone') }}" placeholder="۰۲۱-XXXXXX">
                                            @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="province_id">استان</label>
                                            <select name="province_id" data-action="{{ route('provinces.get-cities') }}"  id="province" class="form-control">
                                                <option value="">انتخاب استان</option>
                                                @foreach($provinces ?? [] as $province)
                                                    <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>
                                                        {{ $province->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="city_id">شهر</label>
                                            <select name="city_id" id="city" class="form-control">
                                                <option value="">ابتدا استان را انتخاب کنید</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="address">آدرس انبار</label>
                                            <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror"
                                                      rows="3" placeholder="آدرس کامل انبار">{{ old('address') }}</textarea>
                                            @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="d-block">وضعیت انبار</label>
                                            <div class="custom-control custom-switch custom-switch-success">
                                                <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_active">
                                                    <span class="switch-icon-left"><i class="feather icon-check"></i></span>
                                                    <span class="switch-icon-right"><i class="feather icon-x"></i></span>
                                                </label>
                                            </div>
                                            <small class="text-muted">در صورت غیرفعال بودن، این انبار در سیستم قابل استفاده نیست</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="d-block">کد انبار</label>
                                            <div class="alert alert-info mb-0">
                                                <i class="feather icon-info"></i>
                                                کد انبار به صورت خودکار ساخته می‌شود (مثال: WH-0001)
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12 text-right">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="feather icon-save"></i> ایجاد انبار
                                        </button>
                                        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-secondary">
                                            <i class="feather icon-x"></i> انصراف
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@include('back.partials.plugins', ['plugins' => ['jquery.validate']])
@push('scripts')
    <script src="{{ asset('back/assets/js/pages/warehouses/all.js') }}"></script>
    <script src="{{ asset('back/assets/js/pages/warehouses/create.js') }}"></script>
    <script>
        var provinces = {!! json_encode($provinces) !!};
        var selected_cities = [];
    </script>
@endpush
