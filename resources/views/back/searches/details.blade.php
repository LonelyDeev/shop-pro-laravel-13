<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <strong>کلمه جستجو: </strong> <span id="modal-keyword">{{$keyword}}</span>
            <br>
            <strong>نوع جستجو: </strong> <span id="modal-type">
             @if($type == 'products')
                    <span class="">محصولات</span>
                @else
                    <span class="">پست‌ها</span>
                @endif
            </span>
            <br>
            <strong>تعداد کل جستجوها: </strong> <span id="modal-total">{{$total_searches}} بار</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <h6>لیست کاربران جستجو کننده:</h6>
        @if($searches->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>ردیف</th>
                        <th>نام کاربر</th>
                        <th>ایمیل</th>
                        <th>IP</th>
                        <th>تاریخ جستجو</th>
                        <th>تعداد نتایج</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($searches as $index => $search)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $search->user ? $search->user->full_name : 'کاربر مهمان' }}</td>
                            <td>{{ $search->user ? $search->user->email : '-' }}</td>
                            <td>{{ $search->ip_address }}</td>
                            <td>{{ jdate($search->searched_at)->format('%d %B %Y | H:i:s') }}</td>
                            <td>
                                @if($search->search_type == 'products')
                                    محصولات: {{ $search->products_count }} |
                                    دسته‌ها: {{ $search->categories_count }} |
                                    برندها: {{ $search->brands_count }}
                                @else
                                    پست‌ها: {{ $search->posts_count }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-warning text-center">
                هیچ کاربری این کلمه را جستجو نکرده است
            </div>
        @endif

    </div>
</div>

