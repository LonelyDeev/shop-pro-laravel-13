@extends('back.layouts.master')
@push('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('back/assets/css/pages/statistics/sms-log.css')}}">
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

@endsection

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/statistics/sms.js') }}"></script>
@endpush
