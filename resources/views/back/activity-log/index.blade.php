@extends('back.layouts.master')


@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/activity-log.css') }}">
    <style>
        /* Stats Container */
        .stats-container {
            margin-bottom: 2rem;
        }

        /* Stats Time Grid */
        .stats-time-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-time-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 1.25rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-time-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-time-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e9ecef;
        }

        .stat-time-header i {
            font-size: 1.25rem;
            color: #667eea;
        }

        .stat-time-header span {
            font-weight: 600;
            color: #2c3e50;
        }

        .stat-time-header small {
            margin-right: auto;
            font-size: 0.7rem;
            color: #6c757d;
        }

        .stat-time-body {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .stat-time-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            background: white;
            border-radius: 12px;
        }

        .stat-time-label {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .stat-time-value {
            font-size: 1rem;
            font-weight: 600;
        }

        /* Stats Analysis Grid */
        .stats-analysis-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .stat-analysis-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .stat-analysis-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .stat-analysis-header i {
            font-size: 1.1rem;
        }

        .stat-analysis-header span {
            font-weight: 600;
        }

        .stat-analysis-body {
            padding: 1.25rem;
        }

        .stat-analysis-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .stat-analysis-item:last-child {
            border-bottom: none;
        }

        .stat-user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .stat-user-badge {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }

        .stat-user-badge.admin {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .stat-user-badge.seller {
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }

        .stat-user-badge.user {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }

        .stat-user-name {
            font-weight: 600;
            color: #2c3e50;
        }

        .stat-user-type {
            font-size: 0.7rem;
            color: #6c757d;
        }

        .stat-user-count {
            font-weight: 600;
            color: #667eea;
        }

        .stat-daily-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-daily-label {
            width: 80px;
            font-size: 0.85rem;
            color: #6c757d;
        }

        .stat-daily-bar {
            flex: 1;
            height: 8px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }

        .stat-daily-progress {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .stat-daily-count {
            min-width: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #2c3e50;
            text-align: left;
        }

        .stat-empty {
            text-align: center;
            padding: 1rem;
            color: #6c757d;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-time-grid,
            .stats-analysis-grid {
                grid-template-columns: 1fr;
            }

            .stat-daily-item {
                flex-wrap: wrap;
            }

            .stat-daily-label {
                width: 100%;
            }

            .stat-daily-bar {
                width: 100%;
                order: 3;
            }
        }
    </style>
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
                                    <li class="breadcrumb-item">مدیریت
                                    </li>
                                    <li class="breadcrumb-item">گزارش فعالیت‌ها
                                    </li>
                                    <li class="breadcrumb-item active">لیست فعالیت‌ها
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">



                {{-- Statistics --}}
                {{-- Statistics Section --}}
                <div class="stats-container">
                    {{-- کارت‌های اصلی آمار --}}
                    <div class="stats-row fade-in">
                        <div class="stat-card">
                            <div class="stat-icon purple">
                                <i class="fas fa-list"></i>
                            </div>
                            <div class="stat-content">
                                <h4>{{ number_format($stats['total']) }}</h4>
                                <p>کل فعالیت‌ها</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="fas fa-circle-plus"></i>
                            </div>
                            <div class="stat-content">
                                <h4>{{ number_format($stats['created']) }}</h4>
                                <p>ایجاد شده</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon blue">
                                <i class="fas fa-pencil"></i>
                            </div>
                            <div class="stat-content">
                                <h4>{{ number_format($stats['updated']) }}</h4>
                                <p>ویرایش شده</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon red">
                                <i class="fas fa-trash-can"></i>
                            </div>
                            <div class="stat-content">
                                <h4>{{ number_format($stats['deleted']) }}</h4>
                                <p>حذف شده</p>
                            </div>
                        </div>
                    </div>

                    {{-- کارت‌های آمار زمانی --}}
                    <div class="stats-time-grid">
                        <div class="stat-time-card">
                            <div class="stat-time-header">
                                <i class="fas fa-calendar-day"></i>
                                <span>آمار امروز</span>
                                <small>{{ jdate(now())->format('Y/m/d') }}</small>
                            </div>
                            <div class="stat-time-body">
                                <div class="stat-time-item">
                                    <span class="stat-time-label">کل فعالیت‌ها:</span>
                                    <span class="stat-time-value">{{ number_format($stats['today']['total']) }}</span>
                                </div>
                                <div class="stat-time-item">
                                    <span class="stat-time-label text-success">ایجاد:</span>
                                    <span class="stat-time-value text-success">{{ number_format($stats['today']['created']) }}</span>
                                </div>
                                <div class="stat-time-item">
                                    <span class="stat-time-label text-warning">ویرایش:</span>
                                    <span class="stat-time-value text-warning">{{ number_format($stats['today']['updated']) }}</span>
                                </div>
                                <div class="stat-time-item">
                                    <span class="stat-time-label text-danger">حذف:</span>
                                    <span class="stat-time-value text-danger">{{ number_format($stats['today']['deleted']) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="stat-time-card">
                            <div class="stat-time-header">
                                <i class="fas fa-calendar-week"></i>
                                <span>آمار این هفته</span>
                                <small>{{ jdate(now())->format('Y/m/d') }}</small>
                            </div>
                            <div class="stat-time-body">
                                <div class="stat-time-item">
                                    <span class="stat-time-label">کل فعالیت‌ها:</span>
                                    <span class="stat-time-value">{{ number_format($stats['this_week']['total']) }}</span>
                                </div>
                                <div class="stat-time-item">
                                    <span class="stat-time-label text-success">ایجاد:</span>
                                    <span class="stat-time-value text-success">{{ number_format($stats['this_week']['created']) }}</span>
                                </div>
                                <div class="stat-time-item">
                                    <span class="stat-time-label text-warning">ویرایش:</span>
                                    <span class="stat-time-value text-warning">{{ number_format($stats['this_week']['updated']) }}</span>
                                </div>
                                <div class="stat-time-item">
                                    <span class="stat-time-label text-danger">حذف:</span>
                                    <span class="stat-time-value text-danger">{{ number_format($stats['this_week']['deleted']) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="stat-time-card">
                            <div class="stat-time-header">
                                <i class="fas fa-calendar-alt"></i>
                                <span>آمار این ماه</span>
                                <small>{{ jdate(now())->format('F Y') }}</small>
                            </div>
                            <div class="stat-time-body">
                                <div class="stat-time-item">
                                    <span class="stat-time-label">کل فعالیت‌ها:</span>
                                    <span class="stat-time-value">{{ number_format($stats['this_month']['total']) }}</span>
                                </div>
                                <div class="stat-time-item">
                                    <span class="stat-time-label text-success">ایجاد:</span>
                                    <span class="stat-time-value text-success">{{ number_format($stats['this_month']['created']) }}</span>
                                </div>
                                <div class="stat-time-item">
                                    <span class="stat-time-label text-warning">ویرایش:</span>
                                    <span class="stat-time-value text-warning">{{ number_format($stats['this_month']['updated']) }}</span>
                                </div>
                                <div class="stat-time-item">
                                    <span class="stat-time-label text-danger">حذف:</span>
                                    <span class="stat-time-value text-danger">{{ number_format($stats['this_month']['deleted']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- کارت‌های تحلیل پیشرفته --}}
                    <div class="stats-analysis-grid">
                        <div class="stat-analysis-card">
                            <div class="stat-analysis-header">
                                <i class="fas fa-users"></i>
                                <span>بیشترین کاربران فعال</span>
                            </div>
                            <div class="stat-analysis-body">
                                @foreach($stats['top_users'] as $user)
                                    <div class="stat-analysis-item">
                                        <div class="stat-user-info">
                            <span class="stat-user-badge {{ $user['type'] == 'Admin' ? 'admin' : ($user['type'] == 'Seller' ? 'seller' : 'user') }}">
                                {{ substr($user['name'], 0, 1) }}
                            </span>
                                            <span class="stat-user-name">{{ $user['name'] }}</span>
                                            <small class="stat-user-type">{{ $user['type'] }}</small>
                                        </div>
                                        <span class="stat-user-count">{{ number_format($user['total']) }} فعالیت</span>
                                    </div>
                                @endforeach
                                @if($stats['top_users']->isEmpty())
                                    <div class="stat-empty">هیچ فعالیتی ثبت نشده</div>
                                @endif
                            </div>
                        </div>

                        <div class="stat-analysis-card">
                            <div class="stat-analysis-header">
                                <i class="fas fa-chart-line"></i>
                                <span>فعالیت در روزهای هفته</span>
                            </div>
                            <div class="stat-analysis-body">
                                @foreach($stats['daily'] as $dayStat)
                                    <div class="stat-daily-item">
                                        <span class="stat-daily-label">{{ $dayStat['day'] }}</span>
                                        <div class="stat-daily-bar">
                                            <div class="stat-daily-progress" style="width: {{ $stats['total'] > 0 ? ($dayStat['total'] / $stats['total']) * 100 : 0 }}%"></div>
                                        </div>
                                        <span class="stat-daily-count">{{ number_format($dayStat['total']) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header filter-card">
                        <h4 class="card-title">فیلتر کردن</h4>
                        <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                        <div class="heading-elements">
                            <ul class="list-inline mb-0">
                                <li><a data-action="collapse"><i class="feather icon-chevron-down"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-content collapse {{ request()->except('page') ? 'show' : '' }}">
                        <form id="filter-comments-form" method="GET"
                              action=""{{ route('admin.activity-log.index') }}">
                            <div class="card-body">
                                <div class="row">


                                    <div class="col-md-3">
                                        <label> نوع مدل</label>
                                        <fieldset class="form-group">
                                            <select name="subject_type" class="form-control">
                                                <option value="">همه</option>
                                                @foreach($subjectTypes as $type)
                                                    <option value="{{ $type['value'] }}" {{ request('subject_type') == $type['value'] ? 'selected' : '' }}>
                                                        {{ $type['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </fieldset>
                                    </div>

                                    <div class="col-md-3">
                                        <label> نوع عملیات</label>
                                        <fieldset class="form-group">
                                            <select name="event" class="form-control">
                                                <option value="">همه</option>
                                                @foreach($events as $event)
                                                    <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                                                        @switch($event)
                                                            @case('created') ایجاد @break
                                                            @case('updated') ویرایش @break
                                                            @case('deleted') حذف @break
                                                            @case('restored') بازیابی @break
                                                            @default {{ $event }}
                                                        @endswitch
                                                    </option>
                                                @endforeach
                                            </select>
                                        </fieldset>
                                    </div>

                                    <div class="col-md-3">
                                        <label>کاربر</label>
                                        <fieldset class="form-group">
                                            <select name="causer_id" class="form-control select2">
                                                <option value="">همه کاربران</option>
                                                @foreach($causers as $causer)
                                                    <option value="{{ $causer['id'] }}"
                                                            data-type="{{ $causer['type'] }}"
                                                        {{ request('causer_id') == $causer['id'] ? 'selected' : '' }}>
                                                        {{ $causer['full_display'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </fieldset>
                                    </div>


                                    <div class="col-md-6">
                                        <label>جستجو</label>
                                        <fieldset class="form-group">
                                            <input class="form-control" name="search"  placeholder="جستجو در توضیحات..." value="{{ request('search') }}">
                                        </fieldset>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="pre-space" for="from">از تاریخ : </label>
                                        <div class="form-group">
                                            <input class="form-control"
                                                   id="from_date_picker"
                                                   name="from_date"
                                                   type="text"
                                                   value="{{ request('from_date') ?: old('from_date') }}"
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="pre-space" for="from">تا تاریخ : </label>
                                        <div class="form-group">
                                            <input class="form-control"
                                                   id="to_date_picker"
                                                   name="to_date"
                                                   type="text"
                                                   value="{{ request('to_date') ?: old('to_date') }}"
                                                   autocomplete="off">
                                        </div>
                                    </div>

                                </div>


                                <div class="row">
                                    <div class="col-12 text-right">
                                        <button type="submit" class="btn btn-outline-success square mb-1 waves-effect waves-light">
                                            فیلتر کردن
                                        </button>
                                        <a href="{{route('admin.activity-log.index')}}" class="btn btn-outline-warning square mb-1 waves-effect waves-light">
                                            پاک کردن فیلترها
                                        </a>
                                    </div>
                                </div>


                            </div>
                        </form>
                    </div>
                </div>

                @if($activities->count())
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست فعالیت‌ها</h4>
                            <button type="button" class="btn btn-danger" onclick="deleteOldActivities()">
                                <i class="fas fa-trash-can"></i>
                                حذف فعالیت‌های قدیمی
                            </button>
                        </div>
                        <div class="card-content" id="main-card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>گزارش</th>
                                            <th>تاریخ</th>
                                            <th>عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($formattedActivities as $item)
                                            <tr>
                                                <td>{{$item->id}}</td>
                                                <td>
                                                    {!! $item->report !!}

                                                    @if($item->extra_description)
                                                        <div class="activity-description" style="font-size: 12px; color: #6c757d; margin-top: 5px; padding-right: 15px;">
                                                            {{ $item->extra_description }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>{{ $item->date }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-info btn-details" data-toggle="modal" data-target="#activityModal" data-action="{{route('admin.activity-log.show',$item->id )}}">
                                                        <i class="far fa-eye"></i> جزئیات
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                    <!-- Pagination -->
                                    <div class="mt-3">
                                        {{ $activities->links() }}
                                    </div>

                                </div>



                            </div>
                        </div>
                    </section>

                @else
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست  گزارش فعالیت‌ها</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <div class="card-text">
                                    <p>چیزی برای نمایش وجود ندارد!</p>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif

            </div>

        </div>
    </div>


{{-- Modal for Activity Details --}}
<div class="modal fade" id="activityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle"></i>
                    جزئیات فعالیت
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" id="modalContent">
                <div class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">در حال بارگذاری...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    {{-- Modal for delete old activities --}}
    <div class="modal fade" id="deleteOldActivitiesModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">حذف فعالیت‌های قدیمی</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>تعداد روز:</label>
                        <input type="number" id="delete_days" class="form-control" placeholder="مثلاً 30" min="1" value="30">
                        <small class="text-muted">فعالیت‌های قدیمی‌تر از این تعداد روز حذف خواهند شد.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDeleteOldActivities()">حذف</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@include('back.partials.plugins', ['plugins' => [ 'persian-datepicker']])
@push('scripts')
    <script src="{{ asset('back/assets/js/pages/activity-log/index.js') }}"></script>
@endpush



