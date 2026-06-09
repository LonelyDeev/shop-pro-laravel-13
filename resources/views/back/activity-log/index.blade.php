@extends('back.layouts.master')


@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/activity-log.css') }}">
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
                                <i class=" fas fa-trash-can"></i>
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


@endsection
@include('back.partials.plugins', ['plugins' => [ 'persian-datepicker']])
@push('scripts')
    <script src="{{ asset('back/assets/js/pages/activity-log/index.js') }}"></script>
@endpush



