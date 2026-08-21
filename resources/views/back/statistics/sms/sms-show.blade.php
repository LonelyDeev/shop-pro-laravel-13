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
    .sdetail { font-size: 13.5px; color: #334155; }

    /* هدر */
    .sdetail__hero {
        display: flex; align-items: center; gap: 14px;
        padding: 16px; border-radius: 14px; margin-bottom: 16px;
        background: linear-gradient(135deg, #f5f3ff, #ede9fe); border: 1px solid #ddd6fe;
    }
    .sdetail__avatar {
        width: 54px; height: 54px; border-radius: 16px; flex-shrink: 0;
        background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: #fff;
        display: grid; place-items: center; font-size: 24px;
        box-shadow: 0 8px 16px -6px rgba(124, 58, 237, .5);
    }
    .sdetail__num { direction: ltr; display: inline-block; font-size: 17px; font-weight: 800; color: #1e293b; }
    .sdetail__badges { display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap; }
    .sdetail__badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 99px; font-size: 11.5px; font-weight: 600; }
    .sdetail__badge--type { background: #ede9fe; color: #6d28d9; }
    .sdetail__badge--provider { background: #fff; color: #475569; border: 1px solid #e2e8f0; }
    .sdetail__badge--provider i { font-size: 12px !important; }

    /* اطلاعات */
    .sdetail__grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 16px; }
    .sdetail__item { display: flex; gap: 10px; align-items: flex-start; background: #f8fafc; border: 1px solid #eef1f6; border-radius: 12px; padding: 12px 14px; }
    .sdetail__item > i {
        width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0; margin-top: 2px;
        background: #fff; border: 1px solid #e8ecf3; color: #7c3aed;
        display: grid; place-items: center; font-size: 14px !important;
    }
    .sdetail__label { display: block; font-size: 11px; color: #94a3b8; margin-bottom: 2px; }
    .sdetail__value { font-weight: 700; color: #1e293b; }
    .sdetail__muted { font-size: 11px; color: #94a3b8; margin-inline-start: 6px; }
    .sdetail__ltr { direction: ltr; display: inline-block; }
    .sdetail__link { color: #6d28d9; font-weight: 700; text-decoration: none; }
    .sdetail__link:hover { text-decoration: underline; }
    .sdetail__link i { font-size: 12px !important; }

    /* پاسخ JSON */
    .sdetail__json { border-radius: 14px; overflow: hidden; border: 1px solid #1e293b; }
    .sdetail__json-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 14px; background: #1e293b; color: #e2e8f0; font-size: 12.5px; font-weight: 600;
    }
    .sdetail__copy {
        background: #334155; color: #e2e8f0; border: none; border-radius: 8px;
        padding: 5px 12px; font-size: 11.5px; cursor: pointer;
        display: inline-flex; align-items: center; gap: 5px; transition: .2s;
    }
    .sdetail__copy:hover { background: #475569; }
    .sdetail__copy--done { background: #059669 !important; }
    .sdetail__copy i { font-size: 13px !important; }
    .sdetail__pre {
        margin: 0; background: #0f172a; color: #e2e8f0; padding: 14px;
        font-size: 12.5px; line-height: 1.8; max-height: 300px; overflow: auto;
        font-family: Consolas, Monaco, monospace; text-align: left;
    }
    .sj-key { color: #93c5fd; }
    .sj-str { color: #86efac; }
    .sj-num { color: #fdba74; }
    .sj-bool { color: #f0abfc; }
    .sj-none { color: #64748b; }

    @media (max-width: 576px) { .sdetail__grid { grid-template-columns: 1fr; } }
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
