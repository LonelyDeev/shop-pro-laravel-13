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
                                    <li class="breadcrumb-item">مدیریت برندها
                                    </li>
                                    <li class="breadcrumb-item active">لیست برندها
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                @if($brands->count())
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست برندها</h4>
                        </div>
                        <div class="card-content" id="main-card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center">تصویر</th>
                                                <th>عنوان</th>
                                                <th>slug</th>
                                                <th class="text-center" style='width: '>عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($brands as $brand)
                                                <tr id="brand-{{ $brand->id }}-tr">
                                                    <td class="text-center">
                                                        <img class="post-thumb" src="{{ $brand->image ? asset($brand->image) : asset('/empty.svg') }}" alt="image">
                                                    </td>
                                                    <td>
                                                        <span class="d-flex">
                                                            <span>{{ $brand->name }}</span>
                                                            @if (Route::has('front.brands.show'))
                                                                <a href="{{ route('front.brands.show', ['brand' => $brand]) }}" target="_blank"><i class="feather icon-external-link ml-1"></i></a>
                                                            @endif
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="d-flex">
                                                            @if (Route::has('front.brands.show'))
                                                                <span>{{ $brand->slug}}</span>
                                                            @else
                                                                <span></span>
                                                            @endif
                                                        </span>
                                                    </td>

                                                    <td class="text-center">
                                                        <div class="dropdown dropdown-action">
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $brand->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $brand->id }}">

                                                                <a class="dropdown-item" href="{{ route('admin.brands.edit', ['brand' => $brand]) }}"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                                                                <div class="dropdown-divider"></div>

                                                                <button class="dropdown-item btn-delete" data-id="{{ $brand->id }}" data-action="{{ route('admin.brands.destroy', ['brand' => $brand]) }}"  data-toggle="modal" data-target="#delete-modal"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>
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
                    </section>

                @else
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست برندها</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <div class="card-text">
                                    <p>چیزی برای نمایش وجود ندارد!</p>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
                {{ $brands->links() }}

            </div>
        </div>
    </div>

    {{-- delete brand modal --}}
    <div class="modal fade text-left" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel19" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف برند دیگر قادر به بازیابی آن نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="#" id="brand-delete-form">
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
    <script src="{{ asset('back/assets/js/pages/brands/index.js') }}?v=2"></script>
@endpush
