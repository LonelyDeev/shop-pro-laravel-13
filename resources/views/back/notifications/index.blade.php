@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('back/assets/css/pages/notifications.css')}}">
@endpush
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
                                    <li class="breadcrumb-item">مدیریت</li>
                                    <li class="breadcrumb-item active">مدیریت اعلان‌ها</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                {{-- ===== نوار بالا ===== --}}
                @can('notifications.create')
                <div class="nt-topbar">
                    <h4 class="nt-page-title"><i class="feather icon-bell"></i> مدیریت اعلان‌ها</h4>
                    <button type="button" id="nt-add-btn" class="nt-btn nt-btn--primary">
                        <i class="feather icon-plus"></i> اعلان جدید
                    </button>
                </div>
                @endcan

                {{-- ===== آمار ===== --}}
                <div class="nt-stats">
                    <div class="nt-stat">
                        <div class="nt-stat__icon" style="--c1:#818CF8;--c2:#4F46E5;"><i class="feather icon-bell"></i></div>
                        <div><span class="nt-stat__value" id="nt-stat-total">0</span><span class="nt-stat__label">کل اعلان‌ها</span></div>
                    </div>
                    <div class="nt-stat">
                        <div class="nt-stat__icon" style="--c1:#34D399;--c2:#059669;"><i class="feather icon-monitor"></i></div>
                        <div><span class="nt-stat__value" id="nt-stat-popup">0</span><span class="nt-stat__label">نمایش پاپ‌آپی</span></div>
                    </div>
                    <div class="nt-stat">
                        <div class="nt-stat__icon" style="--c1:#60A5FA;--c2:#2563EB;"><i class="feather icon-users"></i></div>
                        <div><span class="nt-stat__value" id="nt-stat-broadcast">0</span><span class="nt-stat__label">ارسال گروهی</span></div>
                    </div>
                    <div class="nt-stat">
                        <div class="nt-stat__icon" style="--c1:#FB923C;--c2:#EA580C;"><i class="feather icon-alert-octagon"></i></div>
                        <div><span class="nt-stat__value" id="nt-stat-high">0</span><span class="nt-stat__label">اولویت بالا</span></div>
                    </div>
                </div>

                {{-- ===== کارت اصلی ===== --}}
                <section class="card nt-card">
                    <div class="nt-card__header">
                        <nav class="nt-pills">
                            <button type="button" data-type="all" class="nt-pill nt-pill--active">همه</button>
                            <button type="button" data-type="high" class="nt-pill">فوری</button>
                            <button type="button" data-type="medium" class="nt-pill">متوسط</button>
                            <button type="button" data-type="low" class="nt-pill">عادی</button>
                        </nav>
                        <div class="nt-search">
                            <i class="feather icon-search"></i>
                            <input type="text" id="nt-search" placeholder="جستجو در عنوان یا پیام...">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table mb-0 nt-table">
                            <thead>
                            <tr>
                                <th>عنوان</th>
                                <th>پیام</th>
                                <th>اولویت</th>
                                <th class="text-center">پاپ‌آپ</th>
                                <th>گیرندگان</th>
                                <th>تاریخ</th>
                                <th class="text-center">عملیات</th>
                            </tr>
                            </thead>
                            <tbody id="nt-tbody"></tbody>
                        </table>
                    </div>
                </section>

            </div>
        </div>
    </div>

    @can('notifications.create')
    {{-- ================= مودال ایجاد / ویرایش ================= --}}
    <div class="modal fade" id="nt-form-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content nt-modal">
                <form id="nt-form" novalidate>
                    <div class="modal-header nt-modal__header">
                        <h4 class="modal-title" id="nt-modal-title">اعلان جدید</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="بستن">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body nt-modal__body">
                        <input type="hidden" name="id" value="">

                        <div class="nt-field">
                            <label>عنوان <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="nt-input" maxlength="190" placeholder="مثلاً: تخفیف ویژه پایان فصل">
                            <span class="nt-error" id="nt-error-title"></span>
                        </div>

                        <div class="nt-field">
                            <label>پیام <span class="text-danger">*</span></label>
                            <textarea name="message" rows="3" class="nt-input" maxlength="2000" placeholder="متن اعلان..."></textarea>
                            <span class="nt-error" id="nt-error-message"></span>
                        </div>

                        <div class="nt-grid2">
                            <div class="nt-field">
                                <label>اولویت</label>
                                <select name="priority" class="nt-input">
                                    <option value="low">عادی</option>
                                    <option value="medium">متوسط</option>
                                    <option value="high">فوری</option>
                                </select>
                                <span class="nt-error" id="nt-error-priority"></span>
                            </div>

                            {{-- سوییچ پاپ‌آپ --}}
                            <div class="nt-field">
                                <label>نوع نمایش</label>
                                <div class="nt-popup-row">
                                    <label class="nt-switch">
                                        <input type="checkbox" name="popup" value="1">
                                        <span class="nt-switch__slider"></span>
                                    </label>
                                    <span class="nt-popup-row__text">نمایش به صورت پاپ‌آپ در سایت</span>
                                </div>
                            </div>
                        </div>

                        {{-- ============ کارت‌های انتخاب گیرنده ============ --}}
                        <div class="nt-field" id="nt-targets">
                            <label>گیرندگان <span class="text-danger">*</span></label>

                            <p class="nt-targets__note">
                                <i class="feather icon-info"></i>
                                گروه مورد نظر را فعال کنید. اگر شخص خاصی انتخاب نشود، اعلان به <b>همه اعضای همان گروه</b> ارسال می‌شود.
                            </p>

                            <div class="nt-target-grid">
                                {{-- ---- کارت کاربران ---- --}}
                                <div class="nt-target" id="nt-target-users">
                                    <label class="nt-target__head">
                                        <input type="checkbox" name="send_users" value="1">
                                        <span class="nt-target__icon nt-target__icon--users"><i class="feather icon-users"></i></span>
                                        <span class="nt-target__text">
                                        <span class="nt-target__title">ارسال به کاربران سایت</span>
                                        <span class="nt-target__sub">اعضای عادی سایت</span>
                                    </span>
                                        <span class="nt-target__checkbox"><i class="feather icon-check"></i></span>
                                    </label>
                                    <div class="nt-target__body">
                                        <div class="nt-target__body-inner">
                                            <div class="nt-picker" data-group="users">
                                                <div class="nt-picker__box">
                                                    <i class="feather icon-search"></i>
                                                    <input type="text" class="nt-picker__search" placeholder="جستجوی کاربر (نام یا موبایل)..." autocomplete="off">
                                                    <span class="nt-picker__spinner"></span>
                                                </div>
                                                <div class="nt-picker__results"></div>
                                            </div>
                                            <div class="nt-chips"></div>
                                            <p class="nt-target__hint"><i class="feather icon-info"></i> اگر کاربری انتخاب نشود، به <b>همه کاربران سایت</b> ارسال می‌شود.</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- ---- کارت فروشندگان ---- --}}
                                <div class="nt-target" id="nt-target-sellers">
                                    <label class="nt-target__head">
                                        <input type="checkbox" name="send_sellers" value="1">
                                        <span class="nt-target__icon nt-target__icon--sellers"><i class="feather icon-shopping-bag"></i></span>
                                        <span class="nt-target__text">
                                        <span class="nt-target__title">ارسال به فروشندگان</span>
                                        <span class="nt-target__sub">فروشندگان ثبت‌نام‌شده</span>
                                    </span>
                                        <span class="nt-target__checkbox"><i class="feather icon-check"></i></span>
                                    </label>
                                    <div class="nt-target__body">
                                        <div class="nt-target__body-inner">
                                            <div class="nt-picker" data-group="sellers">
                                                <div class="nt-picker__box">
                                                    <i class="feather icon-search"></i>
                                                    <input type="text" class="nt-picker__search" placeholder="جستجوی فروشنده (نام یا موبایل)..." autocomplete="off">
                                                    <span class="nt-picker__spinner"></span>
                                                </div>
                                                <div class="nt-picker__results"></div>
                                            </div>
                                            <div class="nt-chips"></div>
                                            <p class="nt-target__hint"><i class="feather icon-info"></i> اگر فروشنده‌ای انتخاب نشود، به <b>همه فروشندگان</b> ارسال می‌شود.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <span class="nt-error" id="nt-error-send_users"></span>
                        </div>

                        {{-- خلاصه گیرندگان در حالت ویرایش --}}
                        <div class="nt-field" id="nt-edit-targets" style="display:none">
                            <label>گیرندگان</label>
                            <div id="nt-edit-badges" class="nt-edit-badges"></div>
                            <p class="nt-target__hint nt-target__hint--lock">
                                <i class="feather icon-lock"></i> گیرندگان پس از ایجاد قابل تغییر نیستند؛ فقط محتوای اعلان ویرایش می‌شود.
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer nt-modal__footer">
                        <button type="button" class="nt-btn nt-btn--ghost" data-dismiss="modal">انصراف</button>
                        <button type="submit" id="nt-submit-btn" class="nt-btn nt-btn--primary">
                            <i class="feather icon-send"></i> ذخیره و ارسال
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
    {{-- ================= مودال حذف ================= --}}
    <div class="modal fade" id="nt-delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content nt-modal">
                <div class="nt-delete-box">
                    <div class="nt-delete-box__icon"><i class="feather icon-trash-2"></i></div>
                    <h5>حذف اعلان</h5>
                    <p>آیا از حذف «<b id="nt-delete-title"></b>» مطمئن هستید؟<br>این عمل قابل بازگشت نیست.</p>
                    <div class="nt-delete-box__btns">
                        <button type="button" class="nt-btn nt-btn--ghost" data-dismiss="modal">انصراف</button>
                        <button type="button" id="nt-confirm-delete" class="nt-btn nt-btn--danger">
                            <i class="feather icon-trash-2"></i> حذف کن
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="nt-toasts" class="nt-toasts"></div>


@endsection

@push('scripts')
    <script>
        window.NOTIF_ROUTES = {
            list:       '{{ route('admin.notifications.list') }}',
            recipients: '{{ route('admin.notifications.recipients') }}',
            store:      '{{ route('admin.notifications.store') }}',
            update:     '{{ route('admin.notifications.update', ['notificationManage' => ':id']) }}',
            togglePopup: '{{ route('admin.notifications.togglePopup', ['notificationManage' => ':id']) }}',
            destroy:    '{{ route('admin.notifications.destroy', ['notificationManage' => ':id']) }}'
        };
    </script>
    <script src="{{ asset('back/assets/js/pages/notifications/index.js') }}"></script>
@endpush
