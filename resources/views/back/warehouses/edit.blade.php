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
                                    <li class="breadcrumb-item active">ویرایش انبار</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">ویرایش انبار: {{ $warehouse->name }}</h4>
                        <div>
                            <span class="badge bg-secondary">کد: {{ $warehouse->code }}</span>
                            @if($warehouse->is_active)
                                <span class="badge bg-success">فعال</span>
                            @else
                                <span class="badge bg-danger">غیرفعال</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <form id="warehouse-edit-form" action="{{ route('admin.warehouses.update', $warehouse) }}" data-redirect="{{ route('admin.warehouses.index') }}" method="POST" class="form">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">نام انبار <span class="text-danger">*</span></label>
                                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                                   value="{{ old('name', $warehouse->name) }}" placeholder="مثال: انبار مرکزی" required>
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

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="manager_name">مدیر انبار</label>
                                            <input type="text" name="manager_name" id="manager_name" class="form-control @error('manager_name') is-invalid @enderror"
                                                   value="{{ old('manager_name', $warehouse->manager_name) }}" placeholder="نام مدیر انبار">
                                            @error('manager_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone">تلفن انبار</label>
                                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                                                   value="{{ old('phone', $warehouse->phone) }}" placeholder="۰۲۱-XXXXXX">
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
                                            <select name="province_id" id="province_id" class="form-control">
                                                <option value="">انتخاب استان</option>
                                                @foreach($provinces ?? [] as $province)
                                                    <option value="{{ $province->id }}" {{ old('province_id', $warehouse->province_id) == $province->id ? 'selected' : '' }}>
                                                        {{ $province->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="city_id">شهر</label>
                                            <select name="city_id" id="city_id" class="form-control">
                                                <option value="">ابتدا استان را انتخاب کنید</option>
                                                @if($warehouse->province_id)
                                                    @foreach($cities ?? [] as $city)
                                                        <option value="{{ $city->id }}" {{ old('city_id', $warehouse->city_id) == $city->id ? 'selected' : '' }}>
                                                            {{ $city->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="address">آدرس انبار</label>
                                            <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror"
                                                      rows="3" placeholder="آدرس کامل انبار">{{ old('address', $warehouse->address) }}</textarea>
                                            @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>وضعیت انبار</label>
                                            <div class="custom-control custom-switch custom-switch-success">
                                                <input type="checkbox" name="is_active" class="custom-control-input" id="is_active" value="1" {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_active">
                                                    <span class="switch-icon-left"><i class="feather icon-check"></i></span>
                                                    <span class="switch-icon-right"><i class="feather icon-x"></i></span>
                                                </label>
                                            </div>
                                            <small class="text-muted">در صورت غیرفعال بودن، این انبار در سیستم قابل استفاده نیست</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12 text-right">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="feather icon-save"></i> ذخیره تغییرات
                                        </button>
                                        <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="btn btn-info">
                                            <i class="feather icon-eye"></i> مشاهده
                                        </a>
                                        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-secondary">
                                            <i class="feather icon-x"></i> انصراف
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- هشدار حذف --}}
                <div class="card mt-3 border-danger">
                    <div class="card-header bg-danger text-white p-1">
                        <h4 class="card-title text-white">منطقه خطر</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-danger">
                            <i class="feather icon-alert-triangle"></i>
                            با حذف این انبار، تمام محصولات مرتبط با این انبار نیز حذف خواهند شد.
                            این عملیات غیرقابل بازگشت است.
                        </p>
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">
                            <i class="feather icon-trash-2"></i> حذف انبار
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- مودال حذف --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تأیید حذف انبار</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>آیا از حذف انبار <strong>{{ $warehouse->name }}</strong> اطمینان دارید؟</p>
                    <p class="text-danger">توجه: کلیه محصولات موجود در این انبار نیز حذف خواهند شد!</p>
                </div>
                <div class="modal-footer">
                    <form id="delete-warehouses-form" action="{{ route('admin.warehouses.destroy', $warehouse) }}" method="POST" data-redirect="{{ route('admin.warehouses.index') }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-danger">بله، حذف شود</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@include('back.partials.plugins', ['plugins' => ['jquery.validate']])
@push('scripts')
    <script src="{{ asset('back/assets/js/pages/warehouses/all.js') }}"></script>
    <script src="{{ asset('back/assets/js/pages/warehouses/edit.js') }}"></script>
    <script>
        var provinces = {!! json_encode($provinces) !!};
        var selected_cities = [];
    </script>
@endpush
