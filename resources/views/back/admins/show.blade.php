@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/admins/show.css') }}">
@endpush


@section('content')

    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-left mb-0">مشخصات کاربر</h2>
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">مدیریت
                                    </li>
                                    <li class="breadcrumb-item">مدیریت کاربران
                                    </li>
                                    <li class="breadcrumb-item active">مشخصات کاربر
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="content-body">

                <section  class="card">
                    <div class="card-header">
                        <h4 class="card-title">مشخصات کاربر</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <section class="page-users-view">
                                <div class="row">
                                    <!-- account start -->
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="users-view-image">
                                                        <img src="{{ $admin->imageUrl }}" class="users-avatar-shadow w-100 rounded mb-2 pr-2 ml-1" alt="avatar">
                                                    </div>
                                                    <div class="col-12 col-sm-9 col-md-6 col-lg-5">
                                                        <table>
                                                            <tr>
                                                                <td class="font-weight-bold">نام</td>
                                                                <td>{{ $admin->first_name }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="font-weight-bold">نام خانوادگی</td>
                                                                <td>{{ $admin->last_name }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="font-weight-bold">ایمیل</td>
                                                                <td>{{ $admin->email ?: '--' }}</td>
                                                            </tr>

                                                        </table>
                                                    </div>
                                                    <div class="col-12 col-md-12 col-lg-5">
                                                        <table class="ml-0 ml-sm-0 ml-lg-0">
                                                            <tr>
                                                                <td class="font-weight-bold">نام کاربری</td>
                                                                <td>{{ $admin->username }}</td>
                                                            </tr>
                                                            <tr>
                                                            <tr>
                                                                <td class="font-weight-bold">تاریخ ثبت نام</td>
                                                                <td>
                                                                    <abbr title="{{ jdate($admin->created_at) }}">{{ jdate($admin->created_at)->ago() }}</abbr>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="font-weight-bold">آیدی</td>
                                                                <td>{{ $admin->id }}</td>
                                                            </tr>


                                                        </table>
                                                    </div>
                                                    <div class="col-12 mt-2">
                                                        @can('admins.update')
                                                            <a href="{{ route('admin.admins.edit', ['admin' => $admin]) }}" class="btn btn-warning mr-1"><i class="feather icon-edit-1"></i> ویرایش</a>
                                                        @endcan

                                                        @can('admins.delete')

                                                            @if($admin->id != auth()->user()->id)
                                                                <button type="button" data-user="{{ $admin->id }}" class="btn btn-danger mr-1 waves-effect waves-light btn-user-delete"  data-toggle="modal" data-target="#user-delete-modal">حذف</button>
                                                            @else
                                                                <button type="button" class="btn btn-danger mr-1 waves-effect waves-light" disabled>حذف</button>
                                                            @endif

                                                        @endcan
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


            </div>
        </div>
    </div>

    {{-- delete user modal --}}
    <div class="modal fade text-left" id="user-delete-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel19" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف کاربر دیگر قادر به بازیابی آن نخواهید بود
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
    <div class="modal fade text-left" id="delete-modal" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف کاربر دیگر قادر به بازیابی آن نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="#" id="agency-delete-form">
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

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/admins/show.js') }}"></script>
@endpush
