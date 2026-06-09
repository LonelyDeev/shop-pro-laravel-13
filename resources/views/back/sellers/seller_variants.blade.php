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
                                    <li class="breadcrumb-item active">تنوع
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                @if($variants->count())
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست تنوع ها </h4>
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
                                            <th class="text-center" style="width: 160px">عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($variants as $variant)
                                            @php $product=\App\Models\Product::find($variant->product_id); @endphp
                                            @if($product)
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

                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif

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
                            <h4 class="card-title">لیست تنوع ها</h4>
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

                {{ $variants->links() }}

            </div>
        </div>
    </div>


@endsection
