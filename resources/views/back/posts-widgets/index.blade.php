@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('back/assets/css/pages/widgets/all.css')}}">
@endpush
@section('content')

    <div class="app-content content widgets-page">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item">مدیریت</li>
                                    <li class="breadcrumb-item">قالب ها</li>
                                    <li class="breadcrumb-item active">مدیریت صفحه اصلی مقالات</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-md-right col-md-3 col-12">
                    <div class="form-group breadcrum-right">
                        <a href="{{ route('admin.posts-widgets.create') }}" class="btn-create">
                            <i class="feather icon-plus"></i> ایجاد ابزارک
                        </a>
                    </div>
                </div>
            </div>

            <div class="content-body">

                <div class="page-heading">
                    <h2>مدیریت صفحه اصلی</h2>
                    <p>ترتیب نمایش ابزارک‌ها را با کشیدن دستگیره جابه‌جا کنید؛ تغییرات بلافاصله ذخیره می‌شود.</p>
                </div>

                @if($widgets->count())
                    <section class="widgets-card">
                        <div class="widgets-card-head">
                            <h4>ابزارک‌های صفحه اصلی</h4>
                            <span class="hint"><i class="feather icon-move"></i> برای تغییر ترتیب، بکشید و رها کنید</span>
                        </div>
                        <div class="card-content" id="main-card">
                            <div class="table-responsive">
                                <table class="table widget-table mb-0">
                                    <thead>
                                    <tr>
                                        <th class="text-center" style="width:56px">ترتیب</th>
                                        <th>عنوان</th>
                                        <th>نوع ابزارک</th>
                                        <th class="text-center">وضعیت</th>
                                        <th class="text-center">عملیات</th>
                                    </tr>
                                    </thead>
                                    <tbody id="widgets-sortable" data-action="{{ route('admin.posts-widgets.sort') }}">
                                    @foreach ($widgets as $widget)
                                        <tr id="widget-{{ $widget->id }}">
                                            <td class="text-center draggable-handler">
                                                <span class="drag-handle"><i class="feather icon-move"></i></span>
                                            </td>
                                            <td>
                                                <div class="widget-title">
                                                    {{ $widget->title }}
                                                    <small>#{{ $widget->id }}</small>
                                                </div>
                                            </td>
                                            <td><span class="type-chip">{{ $widget->type() }}</span></td>
                                            <td class="text-center">
                                                @if($widget->is_active)
                                                    <span class="status-pill is-active">فعال</span>
                                                @else
                                                    <span class="status-pill is-inactive">غیر فعال</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.posts-widgets.edit',$widget) }}" class="icon-btn edit">
                                                    <i class="feather icon-edit-2"></i> ویرایش
                                                </a>
                                                <button type="button"
                                                        data-action="{{ route('admin.posts-widgets.destroy',$widget) }}"
                                                        class="icon-btn delete btn-delete"
                                                        data-toggle="modal" data-target="#delete-modal">
                                                    <i class="feather icon-trash-2"></i> حذف
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                @else
                    <section class="widgets-card">
                        <div class="widgets-card-head">
                            <h4>ابزارک‌های صفحه اصلی</h4>
                        </div>
                        <div class="card-content">
                            <div class="empty-state">
                                <div class="icon-wrap"><i class="feather icon-layout"></i></div>
                                <h5>هنوز ابزارکی اضافه نشده</h5>
                                <p>برای شروع، اولین بخش صفحه اصلی سایت را ایجاد کنید.</p>
                                <a href="{{ route('admin.posts-widgets.create') }}" class="btn-create">
                                    <i class="feather icon-plus"></i> ایجاد ابزارک
                                </a>
                            </div>
                        </div>
                    </section>
                @endif

            </div>
        </div>
    </div>

    {{-- delete widget modal --}}
    <div class="modal fade text-left widgets-page-modal" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel19" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف ابزارک دیگر قادر به بازیابی آن نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="#" id="widget-delete-form">
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

@include('back.partials.plugins', ['plugins' => ['jquery-ui-sortable']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/posts-widgets/index.js') }}"></script>
@endpush
