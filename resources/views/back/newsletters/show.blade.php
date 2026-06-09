<div class="row">
    <div class="col-md-6">
        <table class="table table-bordered">
            <tr>
                <th>شناسه:</th>
                <td>{{ $newsletter->id }}</td>
            </tr>
            <tr>
                <th>ایمیل/شماره:</th>
                <td><strong>{{ $newsletter->formatted_contact ?? $newsletter->contact }}</strong></td>
            </tr>
            <tr>
                <th>نوع:</th>
                <td>
                    @if($newsletter->contact_type == 'email')
                        <span class="badge badge-success">ایمیل</span>
                    @else
                        <span class="badge badge-info">شماره موبایل</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>وضعیت:</th>
                <td>
                    @if($newsletter->is_active)
                        <span class="badge badge-success">فعال</span>
                    @else
                        <span class="badge badge-danger">غیرفعال</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>تاریخ ثبت نام:</th>
                <td>{{ jdate($newsletter->created_at)->format('%d %B %Y | H:i:s') }}</td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <table class="table table-bordered">
            <tr>
                <th>IP:</th>
                <td><code>{{ $newsletter->ip_address ?? '-' }}</code></td>
            </tr>
            <tr>
                <th>مرورگر:</th>
                <td>{{ $newsletter->browser ?? '-' }}</td>
            </tr>
            <tr>
                <th>سیستم عامل:</th>
                <td>{{ $newsletter->os ?? '-' }}</td>
            </tr>
            <tr>
                <th>دستگاه:</th>
                <td> @if($newsletter->device_type == 'mobile')
                        <i class="fa fa-mobile-alt"></i> موبایل
                    @elseif($newsletter->device_type == 'tablet')
                        <i class="fa fa-tablet-alt"></i> تبلت
                    @elseif($newsletter->device_type == 'desktop')
                        <i class="fa fa-desktop"></i> دسکتاپ
                    @else
                        -
                    @endif</td>
            </tr>
            <tr>
                <th>صفحه ثبت نام:</th>
                <td><a target="_blank" href="{{ $newsletter->landing_page ?? '-' }}">نمایش صفحه</a></td>
            </tr>
        </table>
    </div>
</div>
