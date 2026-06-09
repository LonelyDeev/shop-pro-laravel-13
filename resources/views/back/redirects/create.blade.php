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
                                    <li class="breadcrumb-item"> لیست ریدایرکت
                                    </li>
                                    <li class="breadcrumb-item active">ایجاد ریدایرکت
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="content-body">
                <!-- Description -->
                <section id="main-card" class="card">
                    <div class="card-header">
                        <h4 class="card-title">ایجاد ریدایرکت جدید</h4>
                    </div>

                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <div class="col-12 col-md-10 offset-md-1">
                                <form id="redirect-form" class="form"  action="{{ route('admin.redirects.store') }}"  method="post">
                                    @csrf
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>نوع ریدایرکت</label>
                                                    <select name="type"  class="form-control valid" aria-invalid="false">
                                                        <option value="0" >بدون وضعیت</option>
                                                        <option value="301" {{old('type')=="301" ? 'selected' : ''}} >301</option>
                                                        <option value="302" {{old('type')=="302" ? 'selected' : ''}}>302</option>
                                                        <option value="303" {{old('type')=="303" ? 'selected' : ''}}>303</option>
                                                        <option value="403" {{old('type')=="403" ? 'selected' : ''}}>403</option>
                                                        <option value="410" {{old('type')=="410" ? 'selected' : ''}}>410</option>
                                                        <option value="503" {{old('type')=="503" ? 'selected' : ''}}>503</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>ریدایرکت از</label>
                                                    <input type="text" class="form-control ltr" name="from" value="" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">

                                                    <label>ریدایرکت به</label>
                                                    <input type="text" class="form-control ltr" value="" name="to" required>
                                                </div>
                                            </div>


                                        </div>


                                        <div class="row">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">افزودن </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
                <!--/ Description -->

            </div>
        </div>
    </div>

@endsection

@include('back.partials.plugins', ['plugins' => ['jquery.validate','jquery-tagsinput']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/redirects/create.js') }}"></script>
@endpush
