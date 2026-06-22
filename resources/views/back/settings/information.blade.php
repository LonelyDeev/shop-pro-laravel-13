@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/settings-information.css') }}">
@endpush
@section('content')

    <div class="app-content content gs-page" dir="rtl">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>

        <div class="content-wrapper">

            {{-- ===== Breadcrumb ===== --}}
            <div class="gs-breadcrumb-bar">
                <ol class="gs-breadcrumb">
                    <li>مدیریت</li>
                    <li>تنظیمات</li>
                    <li class="active">تنظیمات کلی</li>
                </ol>

            </div>

            {{-- ===== Page Header ===== --}}
            <div class="gs-page-header">
                <div class="gs-page-header-text">
                    <h1 class="gs-page-title">تنظیمات کلی</h1>
                    <p class="gs-page-subtitle">اطلاعات پایه و پیکربندی عمومی وبسایت</p>
                </div>

            </div>

            {{-- ===== Tab Navigation ===== --}}
            <div class="gs-tabs-nav">
                <ul class="gs-tabs-list" id="settingsTabs" role="tablist">
                    <li>
                        <button class="gs-tab-btn active" data-tab="info" role="tab">
                            اطلاعات کلی
                        </button>
                    </li>
                    <li>
                        <button class="gs-tab-btn" data-tab="seo" role="tab">
                            سئو و متا
                        </button>
                    </li>
                    <li>
                        <button class="gs-tab-btn" data-tab="scripts" role="tab">
                            اسکریپت‌ها
                        </button>
                    </li>
                    <li>
                        <button class="gs-tab-btn" data-tab="map" role="tab">
                            نقشه
                        </button>
                    </li>
                    <li>
                        <button class="gs-tab-btn" data-tab="torobEmalls" role="tab">
                            ترب و ایمالز
                        </button>
                    </li>
                </ul>
            </div>

            {{-- ===== Form ===== --}}
            <form id="information-form" action="{{ route('admin.settings.information') }}" method="POST"
                  enctype="multipart/form-data">
                @csrf

                {{-- ============================================================ --}}
                {{--  TAB 1 — اطلاعات کلی                                         --}}
                {{-- ============================================================ --}}
                <div class="gs-tab-pane mb-4 active" id="tab-info" role="tabpanel">

                    {{-- ── تصاویر و لوگو ── --}}
                    <div class="gs-card">
                        <div class="gs-card-header">
                            <div class="gs-card-header-text">
                                <h6 class="gs-card-title">تصاویر و لوگو</h6>
                                <p class="gs-card-sub">آیکون و لوگوهای سایت و پنل فروشندگان</p>
                            </div>
                            <div class="gs-card-icon gs-icon-indigo">
                                <i class=" fa fa-image"></i>
                            </div>
                        </div>
                        <div class="gs-card-body">
                            <div class="gs-img-grid">


                                @include('back.settings.partials.image-uploader', [
                                    'label'   => 'آیکون سایت',
                                    'name'    => 'info_icon',
                                    'inputId' => 'img_icon',
                                    'btnId'   => 'btn_icon',
                                    'value'   => option('info_icon'),
                                    'hint'    => 'بهترین اندازه: ۳۲×۳۲ پیکسل',
                                ])

                                @include('back.settings.partials.image-uploader', [
                                    'label'   => 'لوگوی شرکت',
                                    'name'    => 'info_logo',
                                    'inputId' => 'img_logo',
                                    'btnId'   => 'btn_logo',
                                    'value'   => option('info_logo'),
                                    'hint'    => 'بهترین اندازه: ۲۰۰×۶۰ پیکسل',
                                ])

                                @include('back.settings.partials.image-uploader', [
                                    'label'   => 'لوگوی شرکت فروشندگان',
                                    'name'    => 'info_logo_seller',
                                    'inputId' => 'img_logo_seller',
                                    'btnId'   => 'btn_logo_seller',
                                    'value'   => option('info_logo_seller'),
                                    'hint'    => 'بهترین اندازه: ۲۰۰×۶۰ پیکسل',
                                ])

                                @include('back.settings.partials.image-uploader', [
                                    'label'   => 'لوگوی پنل فروشندگان',
                                    'name'    => 'info_logo_panel_seller',
                                    'inputId' => 'img_logo_panel',
                                    'btnId'   => 'btn_logo_panel',
                                    'value'   => option('info_logo_panel_seller'),
                                    'hint'    => 'بهترین اندازه: ۲۰۰×۶۰ پیکسل',
                                ])


                            </div>
                        </div>
                    </div>

                    {{-- ── اطلاعات پایه ── --}}
                    <div class="gs-card">
                        <div class="gs-card-header">
                            <div class="gs-card-header-text">
                                <h6 class="gs-card-title">اطلاعات پایه</h6>
                                <p class="gs-card-sub">اطلاعات اصلی وبسایت و راه‌های ارتباطی</p>
                            </div>
                            <div class="gs-card-icon gs-icon-blue">
                                <i class=" fa fa-circle-info"></i>
                            </div>
                        </div>
                        <div class="gs-card-body">
                            <div class="gs-form-grid gs-grid-2">

                                <div class="gs-form-group">
                                    <label class="gs-label">عنوان وبسایت</label>
                                    <input type="text" name="info_site_title" class="gs-input"
                                           value="{{ option('info_site_title') }}" placeholder="فروشگاه آنلاین ایران">
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">ایمیل</label>
                                    <div class="gs-input-group">
                                        <span class="gs-input-addon"><i class="fa fa-envelope"></i></span>
                                        <input type="email" name="info_email" class="gs-input"
                                               value="{{ option('info_email') }}" placeholder="info@site.ir">
                                    </div>
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">تلفن</label>
                                    <div class="gs-input-group">
                                        <span class="gs-input-addon"><i class="fa fa-phone-alt"></i></span>
                                        <input type="text" name="info_tel" class="gs-input"
                                               value="{{ option('info_tel') }}" placeholder="021-12345678">
                                    </div>
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">فکس</label>
                                    <div class="gs-input-group">
                                        <span class="gs-input-addon"><i class="fa fa-fax"></i></span>
                                        <input type="text" name="info_fax" class="gs-input"
                                               value="{{ option('info_fax') }}" placeholder="021-12345679">
                                    </div>
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">کد پستی</label>
                                    <input type="text" name="info_postal_code" class="gs-input"
                                           value="{{ option('info_postal_code') }}" placeholder="1234567890">
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">شماره پشتیبانی</label>
                                    <div class="gs-input-group">
                                        <span class="gs-input-addon"><i class="fa fa-headset"></i></span>
                                        <input type="text" name="info_support_phone" class="gs-input"
                                               value="{{ option('info_support_phone') }}" placeholder="021-00000000">
                                    </div>
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">ساعات کاری</label>
                                    <input type="text" name="info_working_hours" class="gs-input"
                                           value="{{ option('info_working_hours') }}"
                                           placeholder="شنبه تا پنجشنبه ۸ تا ۱۷">
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">رنگ اصلی سایت</label>
                                    <div class="gs-color-row">
                                        <input type="color" name="info_primary_color" class="gs-color-preview"
                                               value="{{ option('info_primary_color', '#6366f1') }}">
                                        <input type="text" name="info_primary_color_text" class="gs-input gs-ltr"
                                               value="{{ option('info_primary_color', '#6366f1') }}"
                                               placeholder="#6366f1" maxlength="7">
                                    </div>
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">استان</label>
                                    <select id="province" name="info_province_id"
                                            data-action="{{ route('provinces.get-cities') }}"
                                            class="gs-input gs-select">
                                        @foreach ($provinces as $province)
                                            <option value="{{ $province->id }}"
                                                @selected($province->id == option('info_province_id'))>
                                                {{ $province->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">شهر</label>
                                    <select id="city" name="info_city_id" class="gs-input gs-select">
                                        @foreach ($cities as $city)
                                            <option value="{{ $city->id }}"
                                                @selected($city->id == option('info_city_id'))>
                                                {{ $city->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="gs-form-group gs-span-2">
                                    <label class="gs-label">آدرس</label>
                                    <textarea name="info_address" class="gs-input gs-textarea" rows="3"
                                              placeholder="تهران، خیابان ولیعصر، پلاک ۱۰۰">{{ option('info_address') }}</textarea>
                                </div>

                            </div>
                        </div>
                    </div>


                    {{-- ── پیکربندی پیشرفته ── --}}
                    <div class="gs-card">
                        <div class="gs-card-header">
                            <div class="gs-card-header-text">
                                <h6 class="gs-card-title">پیکربندی پیشرفته</h6>
                                <p class="gs-card-sub">تنظیمات مسیر ادمین و قابلیت چند زبانه</p>
                            </div>
                            <div class="gs-card-icon gs-icon-violet">
                                <i class="  fa fa-sliders"></i>
                            </div>
                        </div>
                        <div class="gs-card-body">
                            <div class="gs-form-grid gs-grid-2">

                                <div class="gs-form-group">
                                    <label class="gs-label">پیشوند آدرس ورود به بخش مدیریت</label>
                                    <div class="gs-input-group">
                                        <input type="text" name="admin_route_prefix" class="gs-input gs-ltr"
                                               value="{{ config('general.admin_route_prefix') }}" placeholder="admin">
                                        <span class="gs-input-addon gs-addon-suffix">/site.ir</span>
                                    </div>
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">فعال کردن سایت چند زبانه<span
                                            class="gs-badge gs-badge-info">بزودی</span></label>
                                    <input type="hidden" name="multi_language_enabled" id="multiLangValue"
                                           value="{{ option('multi_language_enabled', '0') }}">
                                    <div class="gs-toggle-group" id="multiLangToggle">
                                        <button type="button" disabled
                                                class="gs-toggle-btn {{ option('multi_language_enabled') == '0' ? 'gs-toggle-off' : '' }}"
                                                data-value="0">
                                            🔒 غیرفعال
                                        </button>
                                        <button type="button" disabled
                                                class="gs-toggle-btn {{ option('multi_language_enabled') == '1' ? 'gs-toggle-on' : '' }}"
                                                data-value="1">
                                            🌐 فعال
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>{{-- /tab-info --}}


                {{-- ============================================================ --}}
                {{--  TAB 2 — سئو و متا                                           --}}
                {{-- ============================================================ --}}
                <div class="gs-tab-pane mb-4 " id="tab-seo" role="tabpanel">

                    <div class="gs-card">
                        <div class="gs-card-header">
                            <div class="gs-card-header-text">
                                <h6 class="gs-card-title">سئو و متا</h6>
                                <p class="gs-card-sub">بهینه‌سازی موتورهای جستجو</p>
                            </div>
                            <div class="gs-card-icon gs-icon-green">
                                <i class="fa fa-search"></i>
                            </div>
                        </div>
                        <div class="gs-card-body">
                            <div class="gs-form-grid gs-grid-2">

                                <div class="gs-form-group">
                                    <label class="gs-label">کلمات کلیدی</label>
                                    <input id="tags" type="text" name="info_tags" class="gs-input"
                                           value="{{ option('info_tags') }}"
                                           placeholder="کلمات کلیدی را با کاما جدا کنید">
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">آدرس کانونیکال</label>
                                    <input type="url" name="info_canonical" class="gs-input gs-ltr"
                                           value="{{ option('info_canonical') }}" placeholder="https://site.ir">
                                </div>

                                <div class="gs-form-group gs-span-2">
                                    <label class="gs-label">
                                        توضیحات کوتاه (Meta Description)
                                        <span class="gs-badge gs-badge-info">حداکثر ۱۶۰ کاراکتر</span>
                                    </label>
                                    <textarea name="info_short_description" class="gs-input gs-textarea" rows="3"
                                              maxlength="160"
                                              placeholder="توضیحات مختصری درباره سایت...">{{ option('info_short_description') }}</textarea>
                                    <span class="gs-char-count" data-max="160"
                                          data-target="info_short_description"></span>
                                </div>

                                <div class="gs-form-group gs-span-2">
                                    <label class="gs-label">متن فوتر</label>
                                    <textarea name="info_footer_text" class="gs-input gs-textarea" rows="3"
                                              placeholder="متن پایین صفحه سایت...">{{ option('info_footer_text') }}</textarea>
                                </div>

                                <div class="gs-form-group gs-span-2">
                                    <label class="gs-label">Robots.txt</label>
                                    <textarea name="info_robots_txt" class="gs-input gs-textarea gs-ltr" rows="5"
                                              placeholder="User-agent: *&#10;Allow: /">{{ option('info_robots_txt', "User-agent: *\nAllow: /") }}</textarea>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- OG Image --}}
                    <div class="gs-card">
                        <div class="gs-card-header">
                            <div class="gs-card-header-text">
                                <h6 class="gs-card-title">تصویر اشتراک‌گذاری (OG Image)</h6>
                                <p class="gs-card-sub">تصویری که هنگام اشتراک‌گذاری در شبکه‌های اجتماعی نمایش داده
                                    می‌شود</p>
                            </div>
                            <div class="gs-card-icon gs-icon-sky">
                                <i class="fa fa-share-square"></i>
                            </div>
                        </div>
                        <div class="gs-card-body">
                            <div style="max-width:280px;">
                                @include('back.settings.partials.image-uploader', [
                                    'label'   => 'تصویر OG',
                                    'name'    => 'info_og_image',
                                    'inputId' => 'img_og',
                                    'btnId'   => 'btn_og',
                                    'value'   => option('info_og_image'),
                                    'hint'    => 'اندازه پیشنهادی: ۱۲۰۰×۶۳۰ پیکسل',
                                ])
                            </div>
                        </div>
                    </div>

                    {{-- Trust Seals --}}
                    <div class="gs-card">
                        <div class="gs-card-header">
                            <div class="gs-card-header-text">
                                <h6 class="gs-card-title">نمادهای اعتماد</h6>
                                <p class="gs-card-sub">کدهای نماد اعتماد و ساماندهی</p>
                            </div>
                            <div class="gs-card-icon gs-icon-orange">
                                <i class="fa fa-shield-alt"></i>
                            </div>
                        </div>
                        <div class="gs-card-body">
                            <div class="gs-form-grid gs-grid-2">
                                <div class="gs-form-group">
                                    <label class="gs-label">اسکریپت نماد اعتماد (eNamad)</label>
                                    <textarea name="info_enamad" class="gs-input gs-textarea gs-ltr gs-code" rows="6"
                                              placeholder='<a referrerpolicy="..." href="...">&#10;  <img src="..." />&#10;</a>'>{{ option('info_enamad') }}</textarea>
                                </div>
                                <div class="gs-form-group">
                                    <label class="gs-label">کد ساماندهی</label>
                                    <textarea name="info_samandehi" class="gs-input gs-textarea gs-ltr gs-code" rows="6"
                                              placeholder="کد ساماندهی...">{{ option('info_samandehi') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>{{-- /tab-seo --}}


                {{-- ============================================================ --}}
                {{--  TAB 3 — اسکریپت‌ها                                          --}}
                {{-- ============================================================ --}}
                <div class="gs-tab-pane mb-4 " id="tab-scripts" role="tabpanel">

                    <div class="gs-card">
                        <div class="gs-card-header">
                            <div class="gs-card-header-text">
                                <h6 class="gs-card-title">اسکریپت‌ها و کدها</h6>
                                <p class="gs-card-sub">کدهای سفارشی هدر و اسکریپت‌های اضافه</p>
                            </div>
                            <div class="gs-card-icon gs-icon-yellow">
                                <i class="fa fa-code"></i>
                            </div>
                        </div>
                        <div class="gs-card-body">
                            <div class="gs-form-grid">

                                <div class="gs-form-group">
                                    <label class="gs-label">
                                        شناسه Google Tag Manager
                                        <span class="gs-badge gs-badge-info">اختیاری</span>
                                    </label>
                                    <div class="gs-input-group">
                                    <span class="gs-input-addon" style="color:#ea4335">
                                        <i class="fab fa-google"></i>
                                    </span>
                                        <input type="text" name="info_gtm_id" class="gs-input gs-ltr"
                                               value="{{ option('info_gtm_id') }}" placeholder="GTM-XXXXXXX">
                                    </div>
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">
                                        شناسه Meta Pixel (Facebook)
                                        <span class="gs-badge gs-badge-info">اختیاری</span>
                                    </label>
                                    <div class="gs-input-group">
                                    <span class="gs-input-addon" style="color:#1877f2">
                                        <i class="fab fa-facebook-f"></i>
                                    </span>
                                        <input type="text" name="info_meta_pixel" class="gs-input gs-ltr"
                                               value="{{ option('info_meta_pixel') }}" placeholder="000000000000000">
                                    </div>
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">کدهای هدر</label>
                                    <p class="gs-hint-text">این کدها داخل تگ <code>&lt;head&gt;</code> قرار می‌گیرند</p>
                                    <textarea name="info_header_codes" class="gs-input gs-textarea gs-ltr gs-code"
                                              rows="7"
                                              placeholder="<!-- Google Analytics, Meta Pixel, etc. -->">{{ option('info_header_codes') }}</textarea>
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">اسکریپت‌های اضافه (قبل از بسته شدن body)</label>
                                    <p class="gs-hint-text">این کدها قبل از بسته شدن تگ <code>&lt;/body&gt;</code> قرار
                                        می‌گیرند</p>
                                    <textarea name="info_scripts" class="gs-input gs-textarea gs-ltr gs-code" rows="7"
                                              placeholder="<script>...</script>">{{ option('info_scripts') }}</textarea>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>{{-- /tab-scripts --}}


                {{-- ============================================================ --}}
                {{--  TAB 4 — نقشه                                                --}}
                {{-- ============================================================ --}}
                <div class="gs-tab-pane mb-4 " id="tab-map" role="tabpanel">

                    <div class="gs-card">
                        <div class="gs-card-header">
                            <div class="gs-card-header-text">
                                <h6 class="gs-card-title">نقشه صفحه تماس</h6>
                                <p class="gs-card-sub">موقعیت جغرافیایی نمایش داده شده در صفحه تماس با ما</p>
                            </div>
                            <div class="gs-card-icon gs-icon-red">
                                <i class="fa fa-map-marker-alt"></i>
                            </div>
                        </div>
                        <div class="gs-card-body">
                            <div class="gs-form-grid gs-grid-2">

                                <div class="gs-form-group">
                                    <label class="gs-label">عرض جغرافیایی (Latitude)</label>
                                    <input type="number" step="any" id="latitude" name="info_latitude"
                                           class="gs-input gs-ltr"
                                           value="{{ option('info_latitude', '38.07709880960678') }}"
                                           placeholder="38.07709880960678">
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">طول جغرافیایی (Longitude)</label>
                                    <input type="number" step="any" id="Longitude" name="info_Longitude"
                                           class="gs-input gs-ltr"
                                           value="{{ option('info_Longitude', '46.28582686185837') }}"
                                           placeholder="46.28582686185837">
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">سطح زوم</label>
                                    <input type="number" min="1" max="20" name="info_map_zoom"
                                           class="gs-input gs-ltr"
                                           value="{{ option('info_map_zoom', '13') }}" placeholder="13">
                                </div>

                                <div class="gs-form-group">
                                    <label class="gs-label">کلید API نقشه (map.ir)</label>
                                    <input type="text" name="map_api" class="gs-input gs-ltr gs-code"
                                           value="{{ option('map_api') }}" placeholder="eyJ0eXAiOiJKV1Qi...">
                                </div>

                                {{-- Map Type --}}
                                <div class="gs-form-group gs-span-2">
                                    <label class="gs-label">نوع نقشه</label>
                                    <div class="gs-map-type-grid">

                                        <label
                                            class="gs-map-type-card {{ option('info_map_type') == 'google' ? 'gs-map-selected' : '' }}"
                                            for="map_type_google">
                                            <input type="radio" class="gs-map-radio" id="map_type_google"
                                                   name="info_map_type" value="google"
                                                {{ option('info_map_type') == 'google' ? 'checked' : '' }}>
                                            <span style="font-size:1.4rem;color:#ea4335"><i
                                                    class="fab fa-google"></i></span>
                                            <div class="gs-map-type-info">
                                                <div class="gs-map-type-name">نقشه گوگل</div>
                                                <div class="gs-map-type-sub">Google Maps</div>
                                            </div>
                                        </label>

                                        <label
                                            class="gs-map-type-card {{ option('info_map_type') != 'google' ? 'gs-map-selected' : '' }}"
                                            for="map_type_mapir">
                                            <input type="radio" class="gs-map-radio" id="map_type_mapir"
                                                   name="info_map_type" value="mapir"
                                                {{ option('info_map_type') != 'google' ? 'checked' : '' }}>
                                            <span style="font-size:1.4rem;color:#6366f1"><i
                                                    class="fa fa-map"></i></span>
                                            <div class="gs-map-type-info">
                                                <div class="gs-map-type-name">نقشه Map.ir</div>
                                                <div class="gs-map-type-sub">map.ir</div>
                                            </div>
                                        </label>

                                    </div>
                                </div>

                                {{-- Map containers --}}
                                <div class="gs-form-group gs-span-2">
                                    <label class="gs-label">پیش‌نمایش نقشه</label>

                                    {{-- کانتینر دوتایی با فلکس‌باکس --}}
                                    <div style="display: flex; gap: 15px; width: 100%;">

                                        {{-- نقشه گوگل --}}
                                        <div style="flex: 1; display: flex; flex-direction: column;">
                                            <small style="margin-bottom: 5px; font-weight: bold;">Google Map</small>
                                            <div class="gs-map-container" id="googleMap"
                                                 style="width: 100%; height: 400px;"></div>
                                        </div>

                                        {{-- نقشه مپیر --}}
                                        <div style="flex: 1; display: flex; flex-direction: column;">
                                            <small style="margin-bottom: 5px; font-weight: bold;">نقشه Map.ir</small>
                                            <div class="gs-map-container" id="mapIr"
                                                 style="width: 100%; height: 400px;"></div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>{{-- /tab-map --}}




                {{-- ============================================================ --}}
                {{--  TAB 5 — ترب و ایمالز                                        --}}
                {{-- ============================================================ --}}
                <div class="gs-tab-pane mb-4 " id="tab-torobEmalls" role="tabpanel">

                    <div class="gs-card">
                        <div class="gs-card-header">
                            <div class="gs-card-header-text">
                                <h6 class="gs-card-title">API ترب و ایمالز</h6>
                                <p class="gs-card-sub">موقعیت جغرافیایی نمایش داده شده در صفحه تماس با ما</p>
                            </div>
                            <div class="gs-card-icon gs-icon-red">
                                <i class="fa fa-code"></i>
                            </div>
                        </div>
                        <div class="gs-card-body">

                                <div class="card-body pb-2">
                                    <div class="tab-title lts-05 mb-2">
                                        <div>آدرس api ترب <span>اطلاعات meta tag ترب برای محصولات قرار داده شده است. همچنین با ارائه api زیر به ترب میتوانید مکانیزم ایندکس شدن محصولات در ترب را بصورت اتوماتیک و سریع انجام دهید.</span>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="alert alert-info d-inline-block clickable" draggable="false"
                                                 data-pd-tooltip="true" style="direction: ltr; user-select: none;" onclick="copyText(this)">
                                                {{route('api.torob.products')}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-title lts-05 mb-2">
                                        <div>آدرس api ایمالز <span>اطلاعات meta tag ایمالز برای محصولات قرار داده شده است. همچنین با ارائه api زیر به ایمالز میتوانید مکانیزم ایندکس شدن محصولات در ایمالز را بصورت اتوماتیک و سریع انجام دهید.</span>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="alert alert-info d-inline-block clickable" draggable="false"
                                                 data-pd-tooltip="true" style="direction: ltr; user-select: none;" onclick="copyText(this)">
                                                {{route('api.emalls.products')}}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                        </div>
                    </div>

                </div>{{-- /tab-map --}}


            </form>{{-- /form --}}

            {{-- ===== Sticky Footer ===== --}}
            <div class="gs-sticky-footer">
                <div class="gs-footer-meta">
                    <i class="far fa-clock"></i>
                    <span>آخرین ذخیره: چند لحظه پیش</span>
                </div>
                <div class="gs-footer-actions">
                    <a href="{{ route('admin.settings.information') }}" class="gs-btn gs-btn-outline">
                        انصراف
                    </a>
                    <button type="submit" id="information-form-btn" form="information-form"
                            class="gs-btn gs-btn-primary">
                        <i class="fa fa-check"></i>
                        ذخیره تغییرات
                    </button>
                </div>
            </div>

        </div>{{-- /content-wrapper --}}
    </div>
@endsection

@include('back.partials.plugins', ['plugins' => ['jquery-tagsinput', 'jquery.validate', 'map']])

@push('scripts')
    <script>
        var info_latitude = "{{ option('info_latitude', '38.07709880960678') }}";
        var info_Longitude = "{{ option('info_Longitude', '46.28582686185837') }}";

        var mapIrApiKey = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjYwMTBjYWE1OWU4ZDAyYzM0YWI2MGFhZDE5MTBhNjM5ZTZkYTI0MzA1ZmMwNzQzY2NmMjRkZmQ2Y2FlMzFjOThmODg4MjExYWY4ZDkwMGE1In0.eyJhdWQiOiIxMjcxOSIsImp0aSI6IjYwMTBjYWE1OWU4ZDAyYzM0YWI2MGFhZDE5MTBhNjM5ZTZkYTI0MzA1ZmMwNzQzY2NmMjRkZmQ2Y2FlMzFjOThmODg4MjExYWY4ZDkwMGE1IiwiaWF0IjoxNjEyODY3Mjc2LCJuYmYiOjE2MTI4NjcyNzYsImV4cCI6MTYxNTM3Mjg3Niwic3ViIjoiIiwic2NvcGVzIjpbImJhc2ljIl19.QNujb2BIyM8mIMy2AhivkMTpVCRyanpUIifJguxoEe4hXB1MESD2CWnO0WPq854Bi6yQyfD2w-oqjOi5N1aZmX4prggmrYelHy_mC1JEwAhWien_6QviFAvkhGDC-aPW4zjFKG2REUkQzXaeL2em543P6-hWdjFaUVSibm1XL4_CUnjJiafQsMQ67ZJ5E7Cpk92L89nJ0LMaBocex56tRqz7_7wZQUAtDYjfal90h2XaGh3QZ2rMwl69ZfMTrOEeTM9O6YCynT3IoTpDnNSXExJeMDuGv4zCD37UYG1gpVtNfipwgvc2J_LzLMXS4rnVAV2ednLKEYu7-jUXr68psg';
    </script>

    <script src="{{ asset('back/assets/js/pages/settings/information.js') }}"></script>
    <script src="{{ asset('back/app-assets/js/scripts/navs/navs.js') }}"></script>
    <script>

    </script>
@endpush

