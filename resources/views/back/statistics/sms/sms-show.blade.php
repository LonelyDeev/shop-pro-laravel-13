@php
    $raw     = (string) $sms->response;
    $decoded = json_decode($raw);
    $isJson  = $decoded !== null;
    $pretty  = $isJson
        ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        : $raw;

    // هایلایت JSON — اول امن‌سازی، بعد رنگ‌آمیزی کلید/رشته/عدد
    $highlighted = htmlspecialchars($pretty, ENT_NOQUOTES);
    if ($isJson) {
        $highlighted = preg_replace('/"([^"]+)"(\s*:)/', '<span class="sj-key">"$1"</span>$2', $highlighted);
        $highlighted = preg_replace('/(:\s*)"([^"]*)"/', '$1<span class="sj-str">"$2"</span>', $highlighted);
        $highlighted = preg_replace('/(:\s*)(-?\d+(?:\.\d+)?)/', '$1<span class="sj-num">$2</span>', $highlighted);
        $highlighted = preg_replace('/: (true|false|null)\b/', ': <span class="sj-bool">$1</span>', $highlighted);
    }
@endphp

<style>

</style>

<div class="sdetail">

    {{-- هدر: شماره + بج‌ها --}}
    <div class="sdetail__hero">
        <div class="sdetail__avatar"><i class="feather icon-smartphone"></i></div>
        <div>
            <span class="sdetail__num">{{ $sms->mobile }}</span>
            <div class="sdetail__badges">
                <span class="sdetail__badge sdetail__badge--type">{{ $sms->type() }}</span>
                @if($sms->provider)
                    <span class="sdetail__badge sdetail__badge--provider">
                        <i class="feather icon-server"></i> {{ $sms->provider }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- اطلاعات --}}
    <div class="sdetail__grid">
        @if($sms->user)
            <div class="sdetail__item">
                <i class="feather icon-user"></i>
                <div>
                    <span class="sdetail__label">کاربر</span>
                    <a href="{{ route('admin.users.show', ['user' => $sms->user]) }}" target="_blank" class="sdetail__link">
                        {{ $sms->user->fullname }} <i class="feather icon-external-link"></i>
                    </a>
                </div>
            </div>
        @endif

        <div class="sdetail__item">
            <i class="feather icon-clock"></i>
            <div>
                <span class="sdetail__label">تاریخ ارسال</span>
                <span class="sdetail__value">{{ jdate($sms->created_at)->format('Y/m/d - H:i') }}</span>
                <span class="sdetail__muted">({{ jdate($sms->created_at)->ago() }})</span>
            </div>
        </div>

        <div class="sdetail__item">
            <i class="feather icon-globe"></i>
            <div>
                <span class="sdetail__label">IP</span>
                <span class="sdetail__value sdetail__ltr">{{ $sms->ip }}</span>
            </div>
        </div>

        <div class="sdetail__item">
            <i class="feather icon-hash"></i>
            <div>
                <span class="sdetail__label">شناسه رکورد</span>
                <span class="sdetail__value sdetail__ltr">#{{ $sms->id }}</span>
            </div>
        </div>
    </div>

    {{-- پاسخ پنل پیامکی --}}
    <div class="sdetail__json">
        <div class="sdetail__json-head">
            <span><i class="feather icon-code"></i> پاسخ پنل پیامکی</span>
            <button type="button" class="sdetail__copy" id="sms-copy-btn">
                <i class="feather icon-copy"></i> کپی
            </button>
        </div>
        <pre class="sdetail__pre" id="sms-response-pre" dir="ltr">@if(trim($pretty) === '')<span class="sj-none">— بدون پاسخ —</span>@else{!! $highlighted !!}@endif</pre>
    </div>
</div>

<script>
    (function () {
        var btn = document.getElementById('sms-copy-btn');
        var pre = document.getElementById('sms-response-pre');
        if (!btn || !pre) return;

        function done() {
            btn.classList.add('sdetail__copy--done');
            btn.innerHTML = '<i class="feather icon-check"></i> کپی شد';
            setTimeout(function () {
                btn.classList.remove('sdetail__copy--done');
                btn.innerHTML = '<i class="feather icon-copy"></i> کپی';
            }, 1800);
        }

        btn.addEventListener('click', function () {
            var text = pre.textContent;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(done);
            } else {
                var ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                done();
            }
        });
    })();
</script>
