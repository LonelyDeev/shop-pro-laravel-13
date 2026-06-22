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
                                    <li class="breadcrumb-item">مدیریت فیلدها
                                    </li>
                                    <li class="breadcrumb-item active">لیست ریدایرکت
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section class="card">
                    <div class="card-header">
                        <h4 class="card-title">لیست ریدایرکت</h4>

                        <div class="heading-elements">
                            <ul class="list-inline mb-0">
                                <li>
                                    <a href="{{ route('admin.redirects.create') }}">
                                        <button type="button"
                                            class="btn personal-info-btn mb-1 waves-effect waves-light"><i
                                                class="fa fa-plus"></i> افزودن جدید</button>
                                    </a>
                                </li>
                            </ul>
                        </div>

                    </div>
                    @if ($items->count())
                        <div class="card-content" id="main-card">

                            <div class="card-body pb-0">
                                <div class=" collapse datatable-actions">
                                    <div class="d-flex align-items-center">
                                        <div class="font-weight-bold text-danger mr-3"><span id="datatable-selected-rows">0</span> مورد انتخاب شده: </div>

                                        <button class="btn personal-danger-btn mr-2" type="button" data-toggle="modal" data-target="#multiple-delete-modal">حذف همه</button>
                                    </div>
                                </div>
                            </div>


                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>
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
                                                <th> نوع</th>
                                                <th>ریدایرکت از</th>
                                                <th>ریدایرکت به</th>
                                                <th class="text-center" style='width: 150px'>عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($items as $item)
                                                <tr id="fild-{{ $item->id }}-tr">
                                                    <td>
                                                        <fieldset class="checkbox">
                                                            <div
                                                                class="vs-checkbox-con vs-checkbox-primary checkbox-single">
                                                                <input type="checkbox" value="{{$item->id}}">
                                                                <span class="vs-checkbox ">
                                                        <span class="vs-checkbox--check">
                                                            <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                            </div>
                                                        </fieldset>
                                                    </td>

                                                    <td >{{$item->type}}</td>
                                                    <td >{{$item->from}}</td>

                                                    <td>{{$item->to}}</td>
                                                    <td class="text-center">
                                                        <div class="dropdown dropdown-action">
                                                            <button class="btn btn-secondary dropdown-toggle" type="button"
                                                                id="dropdownMenu{{ $item->id }}" data-toggle="dropdown"
                                                                aria-haspopup="true" aria-expanded="false">
                                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu"
                                                                aria-labelledby="dropdownMenu{{ $item->id }}">


                                                                @can('posts.update')
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('admin.redirects.edit',$item) }}"><i
                                                                            class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                                                                    <div class="dropdown-divider"></div>
                                                                @endcan
                                                                @can('posts.delete')
                                                                    <button class="dropdown-item btn-delete"
                                                                        data-post="{{ $item->id }}"
                                                                        data-id="{{ $item->id }}" data-toggle="modal"
                                                                        data-target="#delete-modal"><i
                                                                            class="fa-solid fa-trash-can mr-1"></i> حذف</button>
                                                                @endcan
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card-content">
                            <div class="card-body">
                                <div class="card-text">
                                    <p>چیزی برای نمایش وجود ندارد!</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </section>
                {{ $items->links() }}

            </div>

        </div>
    </div>


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
                        با حذف ریدایرکت ها دیگر قادر به بازیابی آنها نخواهید بود
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('admin.redirects.multipleDestroy') }}" id="redirects-multiple-delete-form">
                            @csrf
                            @method('delete')
                            <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">خیر</button>
                            <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    {{-- delete  modal --}}
    <div class="modal fade text-left" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel19"
        style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف ریدایرکت دیگر قادر به بازیابی آن نخواهید بود
                </div>
                <div class="modal-footer">
                    <form  id="redirects-delete-form">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn personal-success-btn waves-effect waves-light"
                            data-dismiss="modal">خیر</button>
                        <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف
                            شود</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
@include('back.partials.plugins', ['plugins' => ['jquery.validate','jquery-tagsinput']])
@push('scripts')

    <script src="{{ asset('back/assets/js/pages/redirects/index.js') }}"></script>
@endpush
