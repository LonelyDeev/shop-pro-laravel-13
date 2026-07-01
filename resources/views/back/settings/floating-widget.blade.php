@extends('back.layouts.master')

@section('title', 'مدیریت ویجت شناور')

@push('styles')
    <style>
        .fw-admin-wrap { max-width: 820px; margin: 0 auto; direction: rtl; }
        .fw-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0,0,0,.06);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .fw-card-head {
            padding: 18px 24px;
            background: linear-gradient(135deg, #5b6af7, #7c3aed);
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .fw-card-head i { font-size: 18px; }
        .fw-card-head h2 { font-size: 15px; font-weight: 700; margin: 0; }
        .fw-card-head small { opacity: .8; font-size: 12px; margin-right: auto; }
        .fw-card-body { padding: 24px; }

        /* Two-column grid */
        .fw-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        @media(max-width: 640px){ .fw-grid { grid-template-columns: 1fr; } }

        .fw-field { display: flex; flex-direction: column; gap: 6px; }
        .fw-field label { font-size: 12px; font-weight: 600; color: #555; }
        .fw-field input[type=text],
        .fw-field input[type=url],
        .fw-field input[type=email],
        .fw-field input[type=tel],
        .fw-field textarea {
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 9px 14px;
            font-size: 13px;
            outline: none;
            transition: border .18s;
            width: 100%;
            direction: rtl;
            font-family: inherit;
        }
        .fw-field input:focus, .fw-field textarea:focus { border-color: #5b6af7; box-shadow: 0 0 0 3px rgba(91,106,247,.1); }
        .fw-field input[type=color] {
            height: 40px;
            padding: 4px 8px;
            border-radius: 10px;
            border: 1.5px solid #e5e7eb;
            cursor: pointer;
            width: 100%;
        }

        /* Channel row */
        .fw-channel-row {
            display: grid;
            grid-template-columns: 40px 1fr 1fr;
            align-items: center;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .fw-channel-row:last-child { border-bottom: none; }
        .fw-channel-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            color: #fff;
            flex-shrink: 0;
        }

        /* Toggle switch */
        .fw-toggle { display: flex; align-items: center; gap: 12px; }
        .fw-switch { position: relative; width: 52px; height: 28px; flex-shrink: 0; }
        .fw-switch input { opacity: 0; width: 0; height: 0; }
        .fw-slider {
            position: absolute; inset: 0;
            background: #d1d5db;
            border-radius: 30px;
            cursor: pointer;
            transition: .3s;
        }
        .fw-slider::before {
            content: '';
            position: absolute;
            width: 22px; height: 22px;
            left: 3px; top: 3px;
            background: #fff;
            border-radius: 50%;
            transition: .3s;
            box-shadow: 0 1px 4px rgba(0,0,0,.2);
        }
        .fw-switch input:checked + .fw-slider { background: #5b6af7; }
        .fw-switch input:checked + .fw-slider::before { transform: translateX(24px); }

        /* Save button */
        .fw-btn-save {
            background: linear-gradient(135deg, #5b6af7, #7c3aed);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 13px 36px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s, transform .2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: inherit;
        }
        .fw-btn-save:hover { opacity: .92; transform: translateY(-1px); }

        /* Preview badge */
        .fw-preview-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .fw-preview-pill.on  { background:#ecfdf5; color:#16a34a; }
        .fw-preview-pill.off { background:#fef2f2; color:#dc2626; }
    </style>
@endpush

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item">مدیریت
                                    </li>
                                    <li class="breadcrumb-item">تنظیمات
                                    </li>
                                    <li class="breadcrumb-item active">ویجت شناور پشتیبانی
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <form action="{{ route('admin.settings.floating-widget.update') }}" method="POST">
                @csrf
                @method('PUT')

                {{-- ─── General Settings ─── --}}
                <div class="fw-card">
                    <div class="fw-card-head">
                        <i class="fa-solid fa-gear"></i>
                        <h2>تنظیمات عمومی</h2>
                    </div>
                    <div class="fw-card-body">

                        {{-- Enable toggle --}}
                        <div class="fw-toggle mb-4">
                            <label class="fw-switch">
                                <input type="checkbox" name="fw_enabled" value="1" {{ option('fw_enabled','1') == '1' ? 'checked' : '' }}>
                                <span class="fw-slider"></span>
                            </label>
                            <div>
                                <div style="font-size:14px;font-weight:700">فعال‌سازی ویجت</div>
                                <div style="font-size:12px;color:#888">نمایش دکمه شناور در سایت</div>
                            </div>
                            <span class="fw-preview-pill {{ option('fw_enabled','1') == '1' ? 'on' : 'off' }}" id="fw-status-pill">
                        <i class="fa-solid fa-circle" style="font-size:8px"></i>
                        {{ option('fw_enabled','1') == '1' ? 'فعال' : 'غیرفعال' }}
                    </span>
                        </div>

                        <div class="fw-grid">
                            <div class="fw-field">
                                <label>رنگ اصلی دکمه</label>
                                <input type="color" name="fw_main_color" value="{{ option('fw_main_color','#5b6af7') }}">
                            </div>
                            <div class="fw-field">
                                <label>متن روی دکمه شناور</label>
                                <input type="text" name="fw_button_label" value="{{ option('fw_button_label') }}" placeholder="تماس با ما">
                            </div>
                            <div class="fw-field">
                                <label>پیام خوشامدگویی</label>
                                <input type="text" name="fw_greeting" value="{{ option('fw_greeting') }}" placeholder="سلام، چطور می‌تونم کمکتون کنم؟">
                            </div>
                            <div class="fw-field">
                                <label>زیرعنوان پنل</label>
                                <input type="text" name="fw_sub_greeting" value="{{ option('fw_sub_greeting') }}" placeholder="تیم پشتیبانی ما آماده‌ی پاسخ‌گویی است">
                            </div>
                            <div class="fw-field">
                                <label>ساعات کاری <small class="text-muted">(نمایش در تب گفتگو)</small></label>
                                <input type="text" name="fw_working_hours" value="{{ option('fw_working_hours','') }}" placeholder="شنبه تا چهارشنبه ۹–۱۸">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── Chat Channels ─── --}}
                <div class="fw-card">
                    <div class="fw-card-head">
                        <i class="fa-solid fa-comments"></i>
                        <h2>کانال‌های گفتگوی آنلاین</h2>
                        <small>تب «گفتگو»</small>
                    </div>
                    <div class="fw-card-body">

                        @php
                            $chat_items = [
                                'whatsapp' => ['label' => 'واتساپ', 'color' => '#25D366', 'icon' => 'fab fa-whatsapp', 'placeholder_url' => 'https://wa.me/989123456789'],
                                'telegram' => ['label' => 'تلگرام',  'color' => '#2AABEE', 'icon' => 'fab fa-telegram', 'placeholder_url' => 'https://t.me/username'],
                            ];
                        @endphp

                        @foreach($chat_items as $key => $item)
                            <div class="fw-channel-row">
                                <div class="fw-channel-icon" style="background: {{ $item['color'] }}"><i class="{{ $item['icon'] }}"></i></div>
                                <div class="fw-field">
                                    <label>برچسب {{ $item['label'] }}</label>
                                    <input type="text" name="fw_{{ $key }}_label" value="{{ option('fw_'.$key.'_label', 'پشتیبانی '.$item['label']) }}">
                                </div>
                                <div class="fw-field">
                                    <label>لینک {{ $item['label'] }}</label>
                                    <input type="url" name="fw_{{ $key }}_url" value="{{ option('fw_'.$key.'_url', '') }}" placeholder="{{ $item['placeholder_url'] }}">
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>

                {{-- ─── Social Networks ─── --}}
                <div class="fw-card">
                    <div class="fw-card-head">
                        <i class="fa-solid fa-share-nodes"></i>
                        <h2>شبکه‌های اجتماعی</h2>
                        <small>تب «شبکه‌ها»</small>
                    </div>
                    <div class="fw-card-body">

                        @php
                            $social_items = [
                                'instagram' => ['label' => 'اینستاگرام', 'color' => '#E1306C', 'icon' => 'fab fa-instagram', 'placeholder_url' => 'https://instagram.com/yourpage'],
                                'twitter'   => ['label' => 'توییتر/ایکس', 'color' => '#000',    'icon' => 'fab fa-x-twitter',  'placeholder_url' => 'https://x.com/yourpage'],
                                'youtube'   => ['label' => 'یوتیوب',       'color' => '#FF0000', 'icon' => 'fab fa-youtube',    'placeholder_url' => 'https://youtube.com/@yourchannel'],
                                'linkedin'  => ['label' => 'لینکدین',      'color' => '#0077B5', 'icon' => 'fab fa-linkedin',   'placeholder_url' => 'https://linkedin.com/company/yourpage'],
                            ];
                        @endphp

                        @foreach($social_items as $key => $item)
                            <div class="fw-channel-row">
                                <div class="fw-channel-icon" style="background: {{ $item['color'] }}"><i class="{{ $item['icon'] }}"></i></div>
                                <div class="fw-field">
                                    <label>برچسب {{ $item['label'] }}</label>
                                    <input type="text" name="fw_{{ $key }}_label" value="{{ option('fw_'.$key.'_label', $item['label']) }}">
                                </div>
                                <div class="fw-field">
                                    <label>لینک {{ $item['label'] }}</label>
                                    <input type="url" name="fw_{{ $key }}_url" value="{{ option('fw_'.$key.'_url', '') }}" placeholder="{{ $item['placeholder_url'] }}">
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>

                {{-- ─── Contact Info ─── --}}
                <div class="fw-card">
                    <div class="fw-card-head">
                        <i class="fa-solid fa-phone"></i>
                        <h2>اطلاعات تماس</h2>
                        <small>تب «تماس»</small>
                    </div>
                    <div class="fw-card-body">
                        <div class="fw-grid">
                            <div class="fw-field">
                                <label><i class="fa-solid fa-phone text-muted me-1"></i> شماره تماس</label>
                                <input type="tel" name="fw_phone" value="{{ option('fw_phone','') }}" placeholder="021-12345678" style="direction:ltr;text-align:right">
                            </div>
                            <div class="fw-field">
                                <label><i class="fa-solid fa-envelope text-muted me-1"></i> ایمیل</label>
                                <input type="email" name="fw_email" value="{{ option('fw_email','') }}" placeholder="info@example.com" style="direction:ltr;text-align:right">
                            </div>
                        </div>
                        <div class="fw-field mt-3">
                            <label><i class="fa-solid fa-location-dot text-muted me-1"></i> آدرس</label>
                            <textarea name="fw_address" rows="2" placeholder="تهران، خیابان ...">{{ option('fw_address','') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Save --}}
                <div class="d-flex justify-content-start gap-3 mb-5">
                    <button type="submit" class="fw-btn-save">
                        <i class="fa-solid fa-floppy-disk"></i>
                        ذخیره تنظیمات
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Live toggle status pill
        document.querySelector('[name=fw_enabled]').addEventListener('change', function() {
            var pill = document.getElementById('fw-status-pill');
            if (this.checked) {
                pill.className = 'fw-preview-pill on';
                pill.innerHTML = '<i class="fa-solid fa-circle" style="font-size:8px"></i> فعال';
            } else {
                pill.className = 'fw-preview-pill off';
                pill.innerHTML = '<i class="fa-solid fa-circle" style="font-size:8px"></i> غیرفعال';
            }
        });
    </script>
@endpush
