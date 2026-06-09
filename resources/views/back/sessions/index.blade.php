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
                                    <li class="breadcrumb-item active">نشست‌های فعال ادمین‌ها</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                @if(auth('adminPanel')->user()->isCreator())
                    <div class="content-header-right text-md-right col-md-3 col-12 d-md-block d-none">
                        <div class="btn-group">
                            <button type="button" id="logout-other-devices-btn" data-action="{{ route("admin.sessions.logout-other-devices") }}" class="btn btn-outline-warning btn-sm btn-outline-secondary">
                                <i class="feather icon-log-out"></i> خروج از سایر دستگاه‌ها
                            </button>
                            <button type="button" id="clear-inactive-btn" class="btn btn-outline-secondary btn-sm" data-action="{{ route("admin.sessions.clear-inactive") }}">
                                <i class="feather icon-trash-2"></i> پاکسازی غیرفعال
                            </button>
                        </div>
                    </div>

                @endif
            </div>

            <div class="content-body">
                <section class="card">
                    <div class="card-header">
                        <h4 class="card-title">نشست‌های فعال ادمین‌ها</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>ادمین</th>
                                        <th>دستگاه</th>
                                        <th>مرورگر</th>
                                        <th>سیستم عامل</th>
                                        <th>آی پی</th>
                                        <th>آخرین فعالیت</th>
                                        <th class="text-center">وضعیت</th>
                                        <th class="text-center">عملیات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($sessions as $session)
                                        <tr id="session-{{ $session->id }}">
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $session->admin->fullname ?? $session->admin->username ?? 'نامشخص' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $session->admin->email ?? '' }}</small>
                                            </td>
                                            <td>
                                                <i class="{{ $session->device_icon }} ml-1"></i>
                                                <span class="badge {{ $session->device_badge_class }}">
                                                    {{ $session->device_type_name }}
                                                </span>
                                                @if($session->device_name)
                                                    <br>
                                                    <small>{{ $session->device_name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $session->browser_name }}
                                            </td>
                                            <td>
                                                {{ $session->platform_name }}
                                            </td>
                                            <td>
                                                {{ $session->ip_address }}
                                            </td>
                                            <td>
                                                {{ jdate($session->last_activity)->format('d F Y H:i') }}
                                                <br>
                                                <small class="text-muted">{{ $session->last_activity_ago }}</small>
                                            </td>
                                            <td class="text-center">
                                                @if($session->session_id == $currentSessionId && $session->admin_id == $currentAdminId)
                                                    <span class="badge badge-success">جلسه فعلی</span>
                                                @else
                                                    <span class="badge badge-info">فعال</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(!($session->session_id == $currentSessionId && $session->admin_id == $currentAdminId))
                                                    <div class="dropdown dropdown-action">
                                                        <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            @can('sessions.exit')
                                                            <button type="button"
                                                                    class="dropdown-item delete-session" data-action="{{ route('admin.sessions.destroy', $session) }}"
                                                                    data-session-id="{{ $session->id }}"
                                                                    data-admin-name="{{ $session->admin->fullname ?? $session->admin->username ?? '' }}">
                                                                <i class="feather icon-log-out ml-1"></i> خروج اجباری
                                                            </button>
                                                            @endcan
                                                            @if(auth('adminPanel')->user()->isCreator())
                                                                @if($session->admin_id != $currentAdminId)
                                                                    <button type="button"
                                                                            class="dropdown-item delete-all-admin-sessions" data-action="{{ route('admin.sessions.destroy-all-admin', $session->admin_id) }}"
                                                                            data-admin-id="{{ $session->admin_id }}"
                                                                            data-admin-name="{{ $session->admin->fullname ?? $session->admin->username ?? '' }}">
                                                                        <i class="feather icon-alert-circle ml-1"></i> خروج از تمام دستگاه‌های این ادمین
                                                                    </button>
                                                                @endif


                                                            @endif

                                                                @can('sessions.blocked')

                                                            <button type="button"
                                                                           class="dropdown-item block-session" data-action="{{ route('admin.sessions.block', $session->id) }}"
                                                                           data-session-id="{{ $session->id }}"
                                                                           data-session-ip="{{ $session->ip_address }}"
                                                                           data-admin-name="{{ $session->admin->fullname ?? $session->admin->username ?? '' }}">
                                                                <i class="feather icon-shield ml-1"></i> بلاک دستگاه
                                                            </button>
                                                                @endcan
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">جلسه فعلی</span>
                                                @endif
                                            </td>


                                        @endforeach
                                    </tbody>
                                </table>


                            </div>

                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <div>
                                    {{ $sessions->links() }}
                                </div>
                                <div class="text-muted small">
                                    تعداد کل نشست‌های فعال: {{ $sessions->total() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>


    {{-- مودال بلاک دستگاه --}}
    <div class="modal fade" id="block-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">بلاک دستگاه</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="block-form" action="">
                        @csrf
                        <input type="hidden" name="session_id" id="block-session-id">

                        <div class="form-group">
                            <label>نوع بلاک</label>
                            <select name="block_type" id="block-type" class="form-control" required>
                                <option value="session">فقط این نشست</option>
                                <option value="ip">فقط این آیپی</option>
                                <option value="device">فقط این دستگاه (مرورگر + سیستم عامل)</option>
                                <option value="browser">فقط این مرورگر (همه نسخه‌ها)</option>
                                <option value="all">همه موارد (دستگاه + آیپی + مرورگر)</option>                            </select>
                        </div>

                        <div class="form-group">
                            <label>مدت زمان بلاک</label>
                            <select name="duration" id="block-duration" class="form-control" required>
                                <option value="permanent">دائمی</option>
                                <option value="1day">1 روز</option>
                                <option value="1week">1 هفته</option>
                                <option value="1month">1 ماه</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>دلیل بلاک (اختیاری)</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="دلیل بلاک کردن این دستگاه..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                    <button type="button" id="confirm-block-btn" data-action="" class="btn btn-danger">بلاک شود</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/sessions/index.js') }}"></script>
@endpush
