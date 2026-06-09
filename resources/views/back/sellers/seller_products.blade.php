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
                                    <li class="breadcrumb-item">مدیریت فروشندگان
                                    </li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.sellers.show', ['seller' => $seller]) }}">{{ $seller->seller_info->fullname }}</a></li>
                                    <li class="breadcrumb-item active">محصولات
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                @if($products->count())
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست محصولات </h4>
                        </div>
                        <div class="card-content" id="main-card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>تصویر شاخص</th>
                                            <th>عنوان محصول</th>
                                            <th>تاریخ ایجاد</th>
                                            <th class="text-center">تعداد موجودی</th>
                                            <th>وضعیت انتشار</th>
                                            <th class="text-center" style="width: 150px">عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($products as $product)
                                            <tr>
                                                <td>{{ $product->id }}</td>
                                                <td>
                                                    <img class="post-thumb" src="{{ $product->image ? asset($product->image) : asset('/empty.svg') }}" alt="{{ $product->title }}">
                                                </td>
                                                <td>{{ $product->title }}</td>
                                                <td>{{ jdate($product->created_at)->format('%d %B %Y') }}</td>
                                                <td class="text-center">{{ $product->prices()->sum('stock') }}</td>
                                                <td class="text-center">
                                                                <span style="width: 100px;">
                                                                    @if($product->isPublished())
                                                                        <div class="badge badge-success">منتشر شده</div>
                                                                    @else
                                                                        <div class="badge badge-danger">پیش نویس</div>
                                                                    @endif

                                                                    @if($product->status=="Accept")
                                                                        <div class="badge badge-success">تایید شده</div>
                                                                    @elseif($product->status=="Waiting")
                                                                        <div class="badge badge-warning">در انتضار تایید</div>
                                                                    @elseif($product->status=="Reject")
                                                                        <div class="badge badge-danger">تایید نشده</div>
                                                                    @endif

                                                                </span>
                                                </td>
                                                <td class='text-center'>
                                                    <div class="dropdown dropdown-action">
                                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $product->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $product->id }}">
                                                            <a class="dropdown-item" target='_blank' href="{{ Route::has('front.products.show') ? route('front.products.show', ['product' => $product]) : '' }}"><i class="fa-regular fa-eye mr-1"></i>نمایش</a>
                                                            <div class="dropdown-divider"></div>

                                                            <a class="dropdown-item" href="{{route('admin.products.edit', ['product' => $product])}}"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                                                            <div class="dropdown-divider"></div>

                                                            <button data-toggle="modal" data-target="#delete-modal-product" data-action="{{route('admin.products.destroy', ['product' => $product])}}" class="dropdown-item btn-delete"><i class="fa-solid fa-trash-can mr-1"></i>حذف</button>
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
                            <h4 class="card-title">لیست محصولات</h4>
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

                {{ $products->links() }}

            </div>
        </div>
    </div>

    {{-- delete product modal --}}
    <div class="modal fade text-left" id="delete-modal-product" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف محصول دیگر قادر به بازیابی آن نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="#" id="product-delete-form">
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

@endsection
@include('back.partials.plugins', ['plugins' => [ 'jquery-tagsinput', 'jquery-ui', 'jquery.validate']])
@push('scripts')
    <script src="{{ asset('back/assets/js/pages/sellers/show.js') }}"></script>

@endpush
