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
                                    <li class="breadcrumb-item">تنظیمات
                                    </li>
                                    <li class="breadcrumb-item active">متن قرار داد فروشنده
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section class="card">
                    <form action="{{route('admin.settings.seller-econtract-store')}}" method="post" class="form-body">
                        @csrf
                        <div class="card-header">
                            <h4 class="card-title">متن بالای قرار داد</h4>
                        </div>
                        <div class="card-content" id="main-card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <textarea id="content" class="form-control" rows="1" name="header">{{$econtract->header}}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-header">
                            <h4 class="card-title">متن اصلی قرار داد</h4>
                        </div>
                        <div class="card-content" id="main-card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <textarea id="content2" class="form-control" rows="3" name="content">{{$econtract->content}}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                            <div class="col-12 text-right">
                                <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">ذخیره</button>
                            </div>

                    </form>
                </section>
            </div>
        </div>
    </div>
@endsection

@include('back.partials.plugins', ['plugins' => ['ckeditor', 'jquery-tagsinput', 'jquery-ui', 'persian-datepicker', 'jquery.validate']])

@push('scripts')
    <script>
        CKEDITOR.replace('content');
        CKEDITOR.replace('content2');
    </script>
@endpush
