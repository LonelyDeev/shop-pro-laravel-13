
<div class="submission-detail">
    <div class="detail-row">
        <div class="detail-label">شناسه پاسخ:</div>
        <div class="detail-value">#{{ $submission->id }}</div>
    </div>

    <div class="detail-row">
        <div class="detail-label">فرم مرتبط:</div>
        <div class="detail-value">
            <span class="badge badge-primary">{{ $form->title }}</span>
        </div>
    </div>

    <div class="detail-row">
        <div class="detail-label">اطلاعات ارسال شده:</div>
        <div class="detail-value">
            <div class="json-view">

                @foreach(json_decode($submission->data) as $key => $value)
                    <div class="detail-row" style="border-bottom: none; padding: 5px 0;">
                        <div class="detail-label" style="width: 150px;">{{ $key }}:</div>
                        <div class="detail-value">
                            @if($key=="image" and $value!="")
                                <a target="_blank" href="{{asset($value)}}"><img src="{{asset($value)}}" width="50%" ></a>
                            @elseif(is_array($value))

                                <pre style="margin: 0;">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                            @else
                                {{ $value ?? '-' }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="detail-row">
        <div class="detail-label">IP ارسال کننده:</div>
        <div class="detail-value"><code>{{ $submission->ip_address ?? '-' }}</code></div>
    </div>

    <div class="detail-row">
        <div class="detail-label">مرورگر:</div>
        <div class="detail-value">
            @if($submission->user_agent)
                @php
                    $browser = \App\Models\Newsletter::getBrowser($submission->user_agent);
                    $os = \App\Models\Newsletter::getOS($submission->user_agent);
                @endphp
                <span class="badge badge-info">{{ $browser }}</span> /
                <span class="badge badge-secondary">{{ $os }}</span>
            @else
                -
            @endif
        </div>
    </div>

    <div class="detail-row">
        <div class="detail-label">User Agent:</div>
        <div class="detail-value"><small>{{ $submission->user_agent ?? '-' }}</small></div>
    </div>

    <div class="detail-row">
        <div class="detail-label">تاریخ ارسال:</div>
        <div class="detail-value">{{ jdate($submission->submitted_at)->format('%d %B %Y | H:i:s') }}</div>
    </div>

    <div class="detail-row">
        <div class="detail-label">تاریخ ثبت در سیستم:</div>
        <div class="detail-value">{{ jdate($submission->created_at)->format('%d %B %Y | H:i:s') }}</div>
    </div>
</div>
