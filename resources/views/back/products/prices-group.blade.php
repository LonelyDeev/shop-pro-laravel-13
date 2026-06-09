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
                                    <li class="breadcrumb-item">مدیریت
                                    </li>
                                    <li class="breadcrumb-item">مدیریت محصولات
                                    </li>
                                    <li class="breadcrumb-item active"> تغییر گروهی قیمت
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div id="main-card" class="content-body">



                <form class="form" id="product-prices-group-form" action="{{ route('admin.product.pricesGroup.update') }}"
                    method="post">
                    @csrf
                    @method('put')
                    <div class="row match-height">
                        <div class="col-md-12">
                            <div class="card overflow-hidden">
                                <div class="card-header">
                                    <h4 class="card-title"> تغییر گروهی قیمت </h4>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">

                                        <div class="nav-vertical">
                                            <ul class="nav nav-tabs nav-left flex-column" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" id="tab1" data-toggle="tab"
                                                        aria-controls="tabVerticalLeft1" href="#product-tab1" role="tab"
                                                        aria-selected="true"> بر اساس محصول</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="tab2" data-toggle="tab"
                                                        aria-controls="tabProductMeta" href="#category-tab2" role="tab"
                                                        aria-selected="false">بر اساس دسته بندی</a>
                                                </li>
                                                <li class="nav-item physical-item">
                                                    <a class="nav-link" id="tab3" data-toggle="tab"
                                                        aria-controls="product-prices-tab" href="#brand-tab3" role="tab"
                                                        aria-selected="false"> بر اساس برند</a>
                                                </li>

                                            </ul>
                                            <div class="tab-content">
                                                <div class="tab-pane active" id="product-tab1" role="tabpanel"
                                                    aria-labelledby="baseVerticalLeft-tab1">
                                                    <div class="col-md-12">
                                                        <div class="form-body">

                                                            <div class="row">


                                                                <input type="hidden" name="type" value="product">

                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>نوع تغییر</label>
                                                                        <select name="typeChenge" required
                                                                            class="form-control valid" aria-invalid="false">
                                                                            <option value="" disabled selected>انتخاب
                                                                                کنید</option>
                                                                            <option value="increase">افزایش قیمت</option>
                                                                            <option value="decrease">کاهش قیمت</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <fieldset class="checkbox">
                                                                        <div
                                                                            class="vs-checkbox-con vs-checkbox-primary mt-3">
                                                                            <input type="checkbox" name="sellerPrice">
                                                                            <span class="vs-checkbox">
                                                                                <span class="vs-checkbox--check">
                                                                                    <i
                                                                                        class="vs-icon feather icon-check"></i>
                                                                                </span>
                                                                            </span>
                                                                            <span> اعمال روی تنوع های فروشنده ها؟</span>
                                                                        </div>
                                                                    </fieldset>
                                                                </div>

                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>درصد تغییر (0,100)</label>
                                                                        <input type="text" class="form-control"
                                                                            name="percent" value="">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>مبلغ ثابت</label>
                                                                        <input type="text" class="form-control"
                                                                            name="price" value="">
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group position-relative">
                                                                        <label> افزودن محصولات</label>
                                                                        <input id="add-product-to-order"
                                                                            data-action="{{ route('admin.orders.productsList') }}"
                                                                            type="text"
                                                                            placeholder=" حداقل 3 کلمه وارد کنید"
                                                                            class="form-control">
                                                                        <div class="add-product-to-order-loader"></div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-12" id="order-products-list"></div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="tab-pane" id="category-tab2" role="tabpanel"
                                                    aria-labelledby="product-prices-tab-nav">

                                                    <div class="col-md-12">
                                                        <div class="form-body">
                                                            <div class="row">


                                                                <input type="hidden" name="type" value="category">


                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>نوع تغییر</label>
                                                                        <select name="typeChenge" required
                                                                            class="form-control valid"
                                                                            aria-invalid="false">
                                                                            <option value="" disabled selected>انتخاب
                                                                                کنید</option>
                                                                            <option value="increase">افزایش قیمت</option>
                                                                            <option value="decrease">کاهش قیمت</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <fieldset class="checkbox">
                                                                        <div
                                                                            class="vs-checkbox-con vs-checkbox-primary mt-3">
                                                                            <input type="checkbox" name="sellerPrice">
                                                                            <span class="vs-checkbox">
                                                                                <span class="vs-checkbox--check">
                                                                                    <i
                                                                                        class="vs-icon feather icon-check"></i>
                                                                                </span>
                                                                            </span>
                                                                            <span> اعمال روی تنوع های فروشنده ها؟</span>
                                                                        </div>
                                                                    </fieldset>
                                                                </div>

                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>درصد تغییر (0,100)</label>
                                                                        <input type="text" class="form-control"
                                                                            name="percent" value="">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>مبلغ ثابت</label>
                                                                        <input type="text" class="form-control"
                                                                            name="price" value="">
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label>دسته بندی ها</label>
                                                                        <select class="form-control product-categories"
                                                                            name="categories[]" multiple>
                                                                            @foreach ($categories as $category)
                                                                                <option
                                                                                    class="l{{ $category->parents()->count() + 1 }} {{ $category->categories()->count() ? 'non-leaf' : '' }}"
                                                                                    data-pup="{{ $category->category_id }}"
                                                                                    value="{{ $category->id }}">
                                                                                    {{ $category->title }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>



                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="tab-pane" id="brand-tab3" role="tabpanel"
                                                    aria-labelledby="product-files-tab-nav">

                                                    <div class="col-md-12">
                                                        <div class="form-body">
                                                            <div class="row">


                                                                <input type="hidden" name="type" value="brand">

                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>نوع تغییر</label>
                                                                        <select name="typeChenge" required
                                                                            class="form-control valid"
                                                                            aria-invalid="false">
                                                                            <option value="" disabled selected>انتخاب
                                                                                کنید</option>
                                                                            <option value="increase">افزایش قیمت</option>
                                                                            <option value="decrease">کاهش قیمت</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <fieldset class="checkbox">
                                                                        <div
                                                                            class="vs-checkbox-con vs-checkbox-primary mt-3">
                                                                            <input type="checkbox" name="sellerPrice">
                                                                            <span class="vs-checkbox">
                                                                                <span class="vs-checkbox--check">
                                                                                    <i
                                                                                        class="vs-icon feather icon-check"></i>
                                                                                </span>
                                                                            </span>
                                                                            <span> اعمال روی تنوع های فروشنده ها؟</span>
                                                                        </div>
                                                                    </fieldset>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>درصد تغییر (0,100)</label>
                                                                        <input type="text" class="form-control"
                                                                            name="percent" value="">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>مبلغ ثابت</label>
                                                                        <input type="text" class="form-control"
                                                                            name="price" value="">
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="form-group">
                                                                        <label>برندها</label>
                                                                        <select class="form-control product-categories"
                                                                            name="brands[]" multiple>
                                                                            @foreach ($brands as $brands)
                                                                                <option value="{{ $brands->id }}">
                                                                                    {{ $brands->name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
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

                    </div>

                    <div class="card">

                        <div class="card-content">
                            <div class="card-body">
                                <div class="form-body">


                                    <div class="row mt-3">
                                        <div class="col-12 text-center">
                                            <button type="submit"
                                                class="btn btn-primary mr-1 mb-1 waves-effect waves-light">اعمال </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>

                </form>
                <div id="form-progress" class="progress progress-bar-success progress-xl" style="display: none;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                        style="width:0%">
                        0%</div>
                </div>
            </div>
        </div>
    </div>

    @include('back.products.templates.product-price-group')
@endsection

@include('back.partials.plugins', ['plugins' => ['jquery-ui', 'persian-datepicker', 'jquery.validate']])


@push('scripts')
    <script src="{{ asset('back/app-assets/plugins/ejs/ejs.min.js') }}"></script>
    <script src="{{ asset('back/assets/js/pages/products/prices-group.js') }}?v=7"></script>
@endpush
