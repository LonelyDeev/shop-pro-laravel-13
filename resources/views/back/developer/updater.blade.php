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
                                    <li class="breadcrumb-item">توسعه دهنده
                                    </li>
                                    <li class="breadcrumb-item active">بروزرسانی
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="content-body">
                <div class="alert alert-warning py-2" role="alert">
                    <i class="feather icon-alert-triangle mr-1 align-middle"></i>
                    <span>لطفا توجه داشته باشید در صورتی که نرم افزار را شخصی سازی کرده اید و در کدها تغییراتی ایجاد کرده اید، با بروزرسانی نرم افزار تغییرات از بین می روند.</span>
                </div>
                <section class="card">

                    <div id="main-card" class="card-content">
                        <div class="card-body">

                            <div class="col-md-12">
                                <h3>بروزرسانی نرم افزار</h3>
                                <hr>
                                <div class="alert alert-info" role="alert">
                                    <span>نسخه فعلی نرم افزار شما {{ $versionInstalled }}</span>
                                </div>

                                @if ($isNewVersionAvailable)
                                    <div class="alert alert-primary" role="alert">
                                        <i class="feather icon-check mr-1 align-middle"></i>
                                        <span>نسخه {{ $versionAvailable }} موجود است و هم اکنون میتوانید نرم افزار را بروزرسانی کنید.</span>
                                    </div>

                                    <button data-action="{{ route('admin.developer.updateApplication') }}" id="update-application" type="button" class="btn btn-lg btn-success mb-1 waves-effect waves-light"><i class="feather icon-refresh-ccw mr-1"></i> بروزرسانی </button>
                                @else
                                    <div class="alert alert-success" role="alert">
                                        <span>شما از آخرین نسخه ی موجود نرم افزار استفاده میکنید.</span>
                                    </div>
                                @endif

                                <button data-action="{{ route('admin.developer.updaterAfter') }}" id="updater-after" type="button" class="btn btn-lg btn-primary mb-1 waves-effect waves-light"><i class="feather icon-settings mr-1"></i> اجرای دستورات بعد از بروزرسانی </button>

                            </div>

                        </div>
                    </div>
                </section>

                <div id="form-progress" class="progress progress-bar-success progress-xl" style="display: none;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%"></div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">تغییرات بروزرسانی ها</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <ul class="activity-timeline timeline-left list-unstyled">

                                <li>
                                    <div class="timeline-icon bg-success">
                                        <i class="feather icon-chevron-left font-medium-2 align-middle"></i>
                                    </div>
                                    <div class="timeline-info">
                                        <p class="font-weight-bold mb-0">نسخه 1.0.0</p>
                                        <p class="font-small-3">- انتشار اولین نسخه</p>
                                    </div>
                                    <small class="text-primary">01 آبان 1402</small>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/developer/updater.js') }}"></script>
@endpush
