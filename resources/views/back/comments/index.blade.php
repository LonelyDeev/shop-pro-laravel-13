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
                                    <li class="breadcrumb-item active">مدیریت دیدگاه ها
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                <!-- filter start -->
                <div class="card">
                    <div class="card-header filter-card">
                        <h4 class="card-title">فیلتر کردن</h4>
                        <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                        <div class="heading-elements">
                            <ul class="list-inline mb-0">
                                <li><a data-action="collapse"><i class="feather icon-chevron-down"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body pt-0">
                            <div class="users-list-filter">
                                <form id="filter-comments-form">
                                    <div class="row">
                                        <div class="col-12 col-sm-6 col-lg-3">
                                            <label for="filter-status">وضعیت</label>
                                            <fieldset class="form-group">
                                                <select class="form-control" name="status" id="filter-status">
                                                    <option value="">همه</option>
                                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>منتظر تایید</option>
                                                    <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>تایید شده</option>
                                                    <option value="unconfirmed" {{ request('status') == 'unconfirmed' ? 'selected' : '' }}>تایید نشده</option>
                                                </select>
                                            </fieldset>
                                        </div>
                                        <div class="col-12 col-sm-6 col-lg-3">
                                            <label for="filter-ordering">مرتب سازی</label>
                                            <fieldset class="form-group">
                                                <select class="form-control" name="ordering" id="filter-ordering">
                                                    <option value="latest" {{ request('ordering') == 'latest' ? 'selected' : '' }}>جدیدترین</option>
                                                    <option value="oldest" {{ request('ordering') == 'oldest' ? 'selected' : '' }}>قدیمی ترین</option>
                                                </select>
                                            </fieldset>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- filter end -->

                <div class="list-comments">
                    @if($comments->count())
                        <section class="card">
                            <div class="card-header">
                                <h4 class="card-title">مدیریت دیدگاه ها</h4>
                            </div>
                            <div class="card-content" id="main-card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0">
                                            <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>نام</th>
                                                <th>دیدگاه</th>
                                                <th class="text-center">نوع</th>
                                                <th class="text-center">تعداد پاسخ</th>  {{-- ستون جدید --}}
                                                <th class="text-center">وضعیت</th>
                                                <th class="text-center">عملیات</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($comments as $comment)
                                                <tr id="comment-{{ $comment->id }}-tr">
                                                    <td class="text-center">{{ $comment->id }}</td>
                                                    <td>
                                                        @if($comment->user)
                                                            [کاربر] {{ $comment->user->fullname ?? $comment->user->name ?? $comment->UserName() }}
                                                        @elseif($comment->admin)
                                                            [مدیر سایت] {{ $comment->admin->fullname ?? '' }}
                                                        @elseif($comment->seller)
                                                            [فروشنده] {{ $comment->seller->business_name ?? '' }}
                                                        @endif
                                                    </td>
                                                    <td style="max-width: 300px">{{ short_content($comment->body, 20, false) }}</td>
                                                    <td class="text-center">
                                                        @if($comment->comment_id)
                                                            <div class="badge badge-success">پاسخ</div>
                                                        @else
                                                            <div class="badge badge-warning">سوال</div>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        {{-- نمایش تعداد پاسخ‌ها --}}
                                                        <span class="badge badge-info">{{ $comment->replies_count ?? $comment->replies->count() }} پاسخ</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($comment->status == 'pending')
                                                            <div class="badge badge-warning">منتظر تایید</div>
                                                        @elseif($comment->status == 'noanswer')
                                                            <div class="badge badge-warning">در انتظار پاسخ</div>
                                                        @elseif($comment->status == 'accepted')
                                                            <div class="badge badge-success">تایید شده</div>
                                                        @else
                                                            <div class="badge badge-danger">تایید نشده</div>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="dropdown dropdown-action">
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $comment->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $comment->id }}">
                                                                <button type="button" data-comment="{{ $comment->id }}" class="dropdown-item show-comment">
                                                                    <i class="fa-regular fa-eye mr-1"></i>مشاهده جزئیات
                                                                </button>
                                                                <div class="dropdown-divider"></div>
                                                                <button data-comment="{{ $comment->id }}" data-action="{{ route('admin.comments.destroy', ['comment' => $comment]) }}" type="button" class="dropdown-item btn-delete" data-toggle="modal" data-target="#delete-modal">
                                                                    <i class="fa-solid fa-trash-can mr-1"></i>حذف
                                                                </button>
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
                                <h4 class="card-title">مدیریت دیدگاه ها</h4>
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
                    {{ $comments->appends(request()->all())->links() }}
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
                    با حذف دیدگاه دیگر قادر به بازیابی آن نخواهید بود و تمامی پاسخ های آن نیز حذف خواهند شد.
                </div>
                <div class="modal-footer">
                    <form action="#" id="comment-delete-form">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">خیر</button>
                        <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- show Modal -->
    <!-- Modal -->
    <div class="modal fade" id="show-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">مشاهده جزئیات</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="comment-detail" class="modal-body">


                </div>
                <div class="modal-footer">
                    <button id="comment-form-submit-btn" type="button" class="btn btn-outline-success">ذخیره</button>
                    <button type="button" class="btn btn-outline-primary" data-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('back/app-assets/plugins/autosize-js/autosize.min.js') }}"></script>

    <script src="{{ asset('back/assets/js/pages/comments/index.js') }}"></script>
@endpush
