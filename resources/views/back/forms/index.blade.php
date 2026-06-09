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
                                    <li class="breadcrumb-item">مدیریت</li>
                                    <li class="breadcrumb-item">فرم ها</li>
                                    <li class="breadcrumb-item active">لیست فرم ها</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                @if($forms->count())
                <section class="card">
                    <div class="card-header">
                        <h4 class="card-title">لیست فرم ها</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="mb-2 collapse datatable-actions">
                                <div class="d-flex align-items-center">
                                    <div class="font-weight-bold text-danger mr-3"><span id="datatable-selected-rows">0</span> مورد انتخاب شده: </div>
                                    @can('forms.delete')
                                        <button class="btn personal-danger-btn mr-2" type="button" data-toggle="modal" data-target="#multiple-delete-modal">حذف همه</button>
                                    @endcan
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th data-field="id" class="datatable-cell-center datatable-cell datatable-cell-check">

                                            <fieldset class="checkbox">
                                                <div class="vs-checkbox-con vs-checkbox-primary checkbox-all ">
                                                    <input type="checkbox">
                                                    <span class="vs-checkbox ">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                </div>
                                            </fieldset>
                                        </th>
                                        <th>شناسه</th>
                                        <th>عنوان</th>
                                        <th>لینک</th>
                                        <th>وضعیت</th>
                                        <th>تعداد فیلد</th>
                                        <th>تعداد ارسال</th>
                                        <th>تاریخ ایجاد</th>
                                        <th>عملیات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($forms as $form)
                                        <tr id="row-{{$form->id}}">
                                            <td data-field="id" aria-label="{{$form->id}}">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary checkbox-single">
                                                        <input type="checkbox" value="{{$form->id}}">
                                                        <span class="vs-checkbox ">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                    </div>
                                                </fieldset>

                                            </td>
                                            <td>{{$form->id}}</td>
                                            <td>{{ $form->title }} <a href="{{ Route::has('front.form.show') ? route('front.form.show',$form->slug) : '' }}" target="_blank"><i class="feather icon-external-link ml-1"></i></a></td>
                                            <td>
                                                @if (Route::has('front.form.show'))
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <button class="btn btn-success" type="button">
                                                                <i class="d-flex justify-content-center flex-column feather icon-file cursor-pointer copy_btn"></i>
                                                            </button>
                                                        </div>
                                                        <input onClick="this.select();" class="ltr page_link form-control" type="text" value="{{ route('front.form.show',$form->slug) }}" readonly>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($form->published)
                                                    <div class="badge badge-success">منتشر شده</div>
                                                @else
                                                    <div class="badge badge-danger">پیش نویس</div>
                                                @endif
                                            </td>
                                            <td>{{ $form->fields->count() }} فیلد</td>
                                            <td>
                                                <a href="{{ route('admin.forms.submissions', $form) }}">
                                                    {{ $form->submissions_count }} ارسال
                                                </a>
                                            </td>
                                            <td>{{ jdate($form->created_at)->format('Y/m/d') }}</td>
                                            <td>
                                                <div class="dropdown dropdown-action">
                                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $form->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $form->id }}">

                                                        @can('forms.submissions')
                                                            <a class="dropdown-item" href="{{ route('admin.forms.submissions', $form) }}"><i class=" fas fa-message"></i> مشاهده پاسخ ها</a>
                                                            <div class="dropdown-divider"></div>
                                                        @endcan
                                                        @can('forms.update')
                                                            <a class="dropdown-item" href="{{ route('admin.forms.edit', $form) }}"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                                                            <div class="dropdown-divider"></div>
                                                        @endcan
                                                        @can('forms.preview')
                                                            <a class="dropdown-item" href="{{ route('admin.forms.preview', $form->id) }}"><i class="fa fa-eye mr-1"></i> پیش‌نمایش</a>
                                                            <div class="dropdown-divider"></div>
                                                        @endcan
                                                        @can('forms.delete')
                                                            <button class="dropdown-item btn-delete" data-toggle="modal" data-id="{{$form->id}}" data-target="#delete-modal" data-action="{{route('admin.forms.destroy',$form)}}"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{ $forms->links() }}
                        </div>
                    </div>
                </section>
                @else
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست فرم ها</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <div class="card-text ">
                                    <p>چیزی برای نمایش وجود ندارد!</p>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </div>

    </div>
    @can('forms.delete')
        {{-- multiple delete modal --}}
        <div class="modal fade text-left" id="multiple-delete-modal" tabindex="-1" role="dialog"  aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        با حذف فرم ها دیگر قادر به بازیابی آنها نخواهید بود
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('admin.forms.multipleDestroy') }}" id="multiple-delete-form">
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
        <div class="modal fade text-left" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        با حذف فرم دیگر قادر به بازیابی آن نخواهید بود
                    </div>
                    <div class="modal-footer">
                        <form action="#" id="delete-form">
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
    @endcan
@endsection

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/forms/index.js') }}"></script>

@endpush
