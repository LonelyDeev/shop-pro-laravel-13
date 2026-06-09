<table>
    <thead>
    <tr>
        <th>شناسه</th>
        <th>ایمیل / شماره موبایل</th>
        <th>نوع</th>
        <th>وضعیت</th>
        <th>IP</th>
        <th>مرورگر</th>
        <th>سیستم عامل</th>
        <th>دستگاه</th>
        <th>صفحه قبلی</th>
        <th>صفحه ثبت نام</th>
        <th>تاریخ ثبت نام</th>
    </tr>
    </thead>
    <tbody>
    @foreach($subscribers as $index => $subscriber)
        <tr>
            <td>{{ $subscriber->id }}</td>
            <td>{{ $subscriber->contact }}</td>
            <td>
                @if($subscriber->contact_type == 'email')
                    ایمیل
                @elseif($subscriber->contact_type == 'mobile')
                    شماره موبایل
                @else
                    نامشخص
                @endif
            </td>
            <td>{{ $subscriber->is_active ? 'فعال' : 'غیرفعال' }}</td>
            <td>{{ $subscriber->ip_address ?? '-' }}</td>
            <td>{{ $subscriber->browser ?? '-' }}</td>
            <td>{{ $subscriber->os ?? '-' }}</td>
            <td>
                @if($subscriber->device_type == 'mobile')
                    موبایل
                @elseif($subscriber->device_type == 'tablet')
                    تبلت
                @elseif($subscriber->device_type == 'desktop')
                    دسکتاپ
                @else
                    -
                @endif
            </td>
            <td>{{ $subscriber->referrer ?? '-' }}</td>
            <td>{{ $subscriber->landing_page ?? '-' }}</td>
            <td>{{ jdate($subscriber->created_at)->format('Y/m/d H:i:s') }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th colspan="3">تعداد کل: {{ $subscribers->count() }}</th>
        <th colspan="8"></th>
    </tr>
    </tfoot>
</table>
