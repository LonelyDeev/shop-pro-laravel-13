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
                                    <li class="breadcrumb-item">مدیریت</li>
                                    <li class="breadcrumb-item active">اعلان ها</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                {{-- ============ کارت‌های آمار ============ --}}
                <div class="sms-stats">
                    <div class="sms-stat">
                        <div class="sms-stat__icon" style="--c1:#60A5FA; --c2:#2563EB;">
                            <i class="feather icon-send"></i>
                        </div>
                        <div class="sms-stat__info">
                            <span class="sms-stat__value">{{ number_format($stats['total']) }}</span>
                            <span class="sms-stat__label">کل پیامک‌های ارسالی</span>
                        </div>
                    </div>

                    <div class="sms-stat">
                        <div class="sms-stat__icon" style="--c1:#34D399; --c2:#059669;">
                            <i class="feather icon-calendar"></i>
                        </div>
                        <div class="sms-stat__info">
                            <span class="sms-stat__value">{{ number_format($stats['today']) }}</span>
                            <span class="sms-stat__label">ارسال‌های امروز</span>
                        </div>
                    </div>

                    <div class="sms-stat">
                        <div class="sms-stat__icon" style="--c1:#FB923C; --c2:#EA580C;">
                            <i class="feather icon-trending-up"></i>
                        </div>
                        <div class="sms-stat__info">
                            <span class="sms-stat__value">{{ number_format($stats['week']) }}</span>
                            <span class="sms-stat__label">۷ روز اخیر</span>
                        </div>
                    </div>

                    <div class="sms-stat">
                        <div class="sms-stat__icon" style="--c1:#A78BFA; --c2:#7C3AED;">
                            <i class="feather icon-bar-chart-2"></i>
                        </div>
                        <div class="sms-stat__info">
                            <span class="sms-stat__value">{{ number_format($stats['month']) }}</span>
                            <span class="sms-stat__label">۳۰ روز اخیر</span>
                        </div>
                    </div>
                </div>

                {{-- ============ کارت اصلی ============ --}}
                <section class="card sms-card">
                    <div class="sms-card__header">
                        <h4 class="sms-card__title">
                            <i class="feather icon-message-square"></i>
                            لاگ پیامک‌های ارسالی
                        </h4>

                        <form method="GET" action="{{ request()->url() }}" class="sms-filters">
                            <div class="sms-filter">
                                <i class="feather icon-search"></i>
                                <input type="text" name="mobile" value="{{ $filters['mobile'] }}"
                                       placeholder="جستجوی شماره موبایل...">
                            </div>

                            <select name="period" class="sms-select">
                                <option value="">همه زمان‌ها</option>
                                <option value="today" {{ $filters['period'] === 'today' ? 'selected' : '' }}>امروز</option>
                                <option value="week"  {{ $filters['period'] === 'week'  ? 'selected' : '' }}>۷ روز اخیر</option>
                                <option value="month" {{ $filters['period'] === 'month' ? 'selected' : '' }}>۳۰ روز اخیر</option>
                            </select>

                            <button type="submit" class="sms-btn">اعمال</button>

                            @if($filters['mobile'] || $filters['period'])
                                <a href="{{ request()->url() }}" class="sms-btn sms-btn--ghost" title="حذف فیلترها">
                                    <i class="feather icon-x"></i>
                                </a>
                            @endif
                        </form>
                    </div>

                    @if($sms->count())
                        <div class="table-responsive">
                            <table class="table mb-0 sms-table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>شماره همراه</th>
                                    <th>نوع</th>
                                    <th>زمان ارسال</th>
                                    <th class="text-center">عملیات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($sms as $s)
                                    <tr>
                                        <td class="sms-row-n">{{ $sms->firstItem() + $loop->index }}</td>
                                        <td>
                                            <div class="sms-phone">
                                                <span class="sms-phone__avatar"><i class="feather icon-smartphone"></i></span>
                                                <span class="sms-phone__num">{{ $s->mobile }}</span>
                                            </div>
                                        </td>
                                        <td>
                                                <span class="sms-type">
                                                    <span class="sms-type__dot"></span>
                                                    {{ $s->type() }}
                                                </span>
                                        </td>
                                        <td>
                                                <span class="sms-time" title="{{ jdate($s->created_at) }}">
                                                    <i class="feather icon-clock"></i>
                                                    {{ jdate($s->created_at)->ago() }}
                                                </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                    data-action="{{ route('admin.sms.show', ['sms' => $s]) }}"
                                                    class="show-sms sms-view-btn"
                                                    title="مشاهده جزئیات">
                                                <i class="feather icon-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="sms-empty">
                            <div class="sms-empty__icon"><i class="feather icon-inbox"></i></div>
                            <h5>{{ ($filters['mobile'] || $filters['period']) ? 'نتیجه‌ای یافت نشد!' : 'چیزی برای نمایش وجود ندارد!' }}</h5>
                            @if($filters['mobile'] || $filters['period'])
                                <p>هیچ پیامکی با این فیلترها پیدا نشد؛ فیلترها را تغییر دهید.</p>
                                <a href="{{ request()->url() }}" class="sms-empty__btn">
                                    <i class="feather icon-rotate-ccw"></i> حذف فیلترها
                                </a>
                            @endif
                        </div>
                    @endif
                </section>

                {{-- ============ صفحه‌بندی ============ --}}
                @if($sms->count())
                    <div class="sms-pagination">
                        <div class="sms-pagination__meta">
                            نمایش <b>{{ $sms->firstItem() }}</b> تا <b>{{ $sms->lastItem() }}</b> از
                            <b>{{ number_format($sms->total()) }}</b> پیامک
                        </div>
                        {{ $sms->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- ============ Show Modal ============ --}}
    <div class="modal fade text-left" id="show-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content sms-modal">
                <div class="modal-header sms-modal__header">
                    <h4 class="modal-title">
                        <i class="feather icon-message-square"></i>
                        جزئیات پیامک
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="بستن">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="sms-detail" class="modal-body sms-modal__body"></div>
            </div>
        </div>
    </div>

    <style>
        :root { --sms-p: #7c3aed; --sms-p-dark: #6d28d9; --sms-p-soft: #f5f3ff; }

        /* ---------- آمار ---------- */
        .sms-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(215px, 1fr)); gap: 14px; margin-bottom: 20px; }
        .sms-stat {
            display: flex; align-items: center; gap: 14px;
            background: #fff; border: 1px solid #eef0f5; border-radius: 16px;
            padding: 16px 18px; box-shadow: 0 2px 8px rgba(15, 23, 42, .04);
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .sms-stat:hover { transform: translateY(-3px); box-shadow: 0 12px 24px -8px rgba(15, 23, 42, .12); }
        .sms-stat__icon {
            width: 50px; height: 50px; border-radius: 14px; flex-shrink: 0;
            display: grid; place-items: center; color: #fff; font-size: 21px;
            background: linear-gradient(135deg, var(--c1), var(--c2));
            box-shadow: 0 8px 16px -6px var(--c2);
        }
        .sms-stat__value { display: block; font-size: 21px; font-weight: 800; color: #1e293b; line-height: 1.3; }
        .sms-stat__label { font-size: 12.5px; color: #64748b; }

        /* ---------- کارت اصلی ---------- */
        .sms-card { border: 1px solid #eef0f5; border-radius: 16px; box-shadow: 0 2px 8px rgba(15, 23, 42, .04); overflow: hidden; }
        .sms-card__header {
            display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between;
            padding: 14px 20px; background: #fbfcfe; border-bottom: 1px solid #f0f2f7;
        }
        .sms-card__title { margin: 0; font-size: 16px; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 8px; }
        .sms-card__title i { color: var(--sms-p); }

        /* ---------- فیلترها ---------- */
        .sms-filters { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .sms-filter { position: relative; }
        .sms-filter i { position: absolute; right: 11px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 15px !important; pointer-events: none; }
        .sms-filter input {
            border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 34px 8px 12px;
            font-size: 13px; min-width: 210px; outline: none; background: #fff;
            transition: border-color .2s, box-shadow .2s;
        }
        .sms-filter input:focus { border-color: var(--sms-p); box-shadow: 0 0 0 3px rgba(124, 58, 237, .12); }
        .sms-select {
            border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 12px;
            font-size: 13px; background: #fff; outline: none; cursor: pointer;
        }
        .sms-select:focus { border-color: var(--sms-p); }
        .sms-btn {
            background: var(--sms-p); color: #fff; border: none; border-radius: 10px;
            padding: 8px 20px; font-size: 13px; cursor: pointer; transition: .2s;
        }
        .sms-btn:hover { background: var(--sms-p-dark); box-shadow: 0 6px 14px -4px rgba(124, 58, 237, .5); }
        .sms-btn--ghost { background: #f1f5f9; color: #64748b; padding: 8px 12px; }
        .sms-btn--ghost:hover { background: #e2e8f0; box-shadow: none; }

        /* ---------- جدول ---------- */
        .sms-table thead th {
            background: #f8fafc; color: #64748b; font-size: 12px; font-weight: 700;
            padding: 12px 16px; white-space: nowrap; border-bottom: 1px solid #eef0f5;
        }
        .sms-table tbody td { padding: 13px 16px; border-bottom: 1px solid #f4f6f9; color: #334155; font-size: 13.5px; vertical-align: middle; }
        .sms-table tbody tr { transition: background .15s; }
        .sms-table tbody tr:hover { background: #faf8ff; }
        .sms-table tbody tr:last-child td { border-bottom: none; }
        .sms-row-n { color: #94a3b8; font-size: 12px; }
        .sms-phone { display: flex; align-items: center; gap: 10px; }
        .sms-phone__avatar {
            width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
            background: var(--sms-p-soft); color: var(--sms-p);
            display: grid; place-items: center; font-size: 14px;
        }
        .sms-phone__num { direction: ltr; display: inline-block; font-weight: 700; color: #1e293b; }
        .sms-type {
            display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;
            background: var(--sms-p-soft); color: var(--sms-p-dark);
            padding: 4px 12px; border-radius: 99px; font-size: 12px; font-weight: 600;
        }
        .sms-type__dot { width: 6px; height: 6px; border-radius: 50%; background: var(--sms-p); }
        .sms-time { display: inline-flex; align-items: center; gap: 6px; color: #64748b; font-size: 12.5px; white-space: nowrap; }
        .sms-view-btn {
            width: 34px; height: 34px; border-radius: 10px; border: 1px solid #e2e8f0;
            background: #fff; color: var(--sms-p); display: inline-grid; place-items: center;
            cursor: pointer; transition: .2s;
        }
        .sms-view-btn:hover { background: var(--sms-p); border-color: var(--sms-p); color: #fff; box-shadow: 0 6px 14px -4px rgba(124, 58, 237, .45); }
        .sms-view-btn i { font-size: 15px !important; }

        /* ---------- حالت خالی ---------- */
        .sms-empty { text-align: center; padding: 48px 20px; }
        .sms-empty__icon {
            width: 72px; height: 72px; margin: 0 auto 16px; border-radius: 50%;
            background: var(--sms-p-soft); color: var(--sms-p);
            display: grid; place-items: center; font-size: 30px;
        }
        .sms-empty h5 { font-weight: 800; color: #1e293b; margin-bottom: 6px; }
        .sms-empty p { color: #94a3b8; font-size: 13px; margin-bottom: 14px; }
        .sms-empty__btn {
            display: inline-flex; align-items: center; gap: 6px; text-decoration: none;
            background: var(--sms-p); color: #fff; border-radius: 10px; padding: 8px 18px; font-size: 13px;
        }
        .sms-empty__btn:hover { color: #fff; }

        /* ---------- صفحه‌بندی و مودال ---------- */
        .sms-pagination { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-top: 16px; }
        .sms-pagination__meta { font-size: 12.5px; color: #64748b; }
        .sms-pagination__meta b { color: #1e293b; }
        .sms-modal__header { border-bottom: 1px solid #f0f2f7; padding: 16px 20px; }
        .sms-modal__header .modal-title { display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 800; color: #1e293b; }
        .sms-modal__header .modal-title i { color: var(--sms-p); }
        .sms-modal__body { padding: 20px; }

        @media (max-width: 768px) {
            .sms-card__header { flex-direction: column; align-items: stretch; }
            .sms-filters { width: 100%; }
            .sms-filter, .sms-filter input { flex: 1; min-width: 0; width: 100%; }
            .sms-select { flex: 1; }
        }
    </style>

@endsection

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/statistics/sms.js') }}"></script>
@endpush
