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
                                    <li class="breadcrumb-item">مدیریت وبلاگ
                                    </li>
                                    <li class="breadcrumb-item active">لیست نوشته ها
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                @if($posts->count())
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست نوشته ها</h4>
                        </div>
                        <div class="card-content" id="main-card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center">تصویر شاخص</th>
                                                <th>عنوان</th>
                                                <th class="text-center">ایجاد شده توسط</th>
                                                <th class="text-center">وضعیت</th>
                                                <th class="text-center">تاریخ انتشار</th>
                                                <th class="text-center" style='width: 150px'>عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($posts as $post)
                                                <tr id="post-{{ $post->id }}-tr">
                                                    <td class="text-center">
                                                        <img class="post-thumb" src="{{ $post->image ? asset($post->image) : asset('/empty.svg') }}" alt="image">
                                                    </td>
                                                    <td><span class="d-inline-block">{{ $post->title }}</span> <a href="{{ Route::has('front.articles.show') ? route('front.articles.show',  $post) : '' }}" target="_blank"><i class="feather icon-external-link"></i></a></td>
                                                    <td class="text-center">
                                                        @if($post->created_by=="admin")
                                                            @if($post->admin)
                                                                <a href="{{ route('admin.admins.show', ['admin' => $post->admin]) }}" target="_blank">{{$post->admin->full_name}} <i class="feather icon-external-link"></i></a>
                                                            @else
                                                                حذف شده
                                                            @endif
                                                        @else
                                                            <span class='d-block'>هوش مصنویی</span>
                                                            @if($post->status=="end")
                                                                <div class="badge badge-success">تکمیل شده</div>
                                                            @elseif($post->status=="waiting")
                                                                <div class="badge badge-warning">در انتضار تکمیل</div>
                                                            @else
                                                                <div class="badge badge-danger" title='برای برسی دقیق تر به پنل خود(ai.webtpro.ir) بروید.'>با مشکل مواجه شده</div>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($post->published)
                                                            <div class="badge badge-success">منتشر شده</div>
                                                        @else
                                                            <div class="badge badge-danger">پیش نویس</div>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{jdate($post->publish_date)->format('%d %B %Y | H:m')}}</td>
                                                    <td class="text-center">
                                                        <div class="dropdown dropdown-action">
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $post->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $post->id }}">
                                                                <a class="dropdown-item" target='_blank' href="{{ Route::has('front.posts.show') ? route('front.posts.show', ['post' => $post]) : '' }}"><i class="fa-regular fa-eye mr-1"></i>نمایش</a>
                                                                <div class="dropdown-divider"></div>
                                                                @can('posts.update')
                                                                    <a class="dropdown-item" href="{{ route('admin.posts.edit', ['post' => $post]) }}"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                                                                    <div class="dropdown-divider"></div>
                                                                @endcan
                                                                @can('posts.delete')
                                                                    <button class="dropdown-item btn-delete"  data-post="{{ $post->slug }}" data-id="{{ $post->id }}"  data-toggle="modal" data-target="#delete-modal"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>
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
                    </section>

                @else
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست نوشته ها</h4>
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
                {{ $posts->links() }}

            </div>

        </div>
    </div>

    {{-- delete post modal --}}
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
                    با حذف نوشته دیگر قادر به بازیابی آن نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="#" id="post-delete-form">
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
    <script src="{{ asset('back/assets/js/pages/posts/index.js') }}"></script>
@endpush
