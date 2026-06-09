@extends('back.layouts.master')

@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item">مدیریت</li>
                                    <li class="breadcrumb-item active">دستگاه‌های بلاک شده</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                @if($blockedDevices->count())
                <section class="card">
                    <div class="card-header">
                        <h4 class="card-title">لیست بلاک‌ها</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>ادمین</th>
                                        <th>نوع بلاک</th>
                                        <th>مقدار</th>
                                        <th>وضعیت</th>
                                        <th>دلیل</th>
                                        <th>تاریخ بلاک</th>
                                        <th>عملیات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($blockedDevices as $block)
                                        <tr id="row-{{$block->id}}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $block->admin->fullname ?? $block->admin->username ?? '' }}</td>
                                            <td>
                                                @if($block->session_id) <span class="badge badge-info">دستگاه</span> @endif
                                                @if($block->ip_address) <span class="badge badge-warning">آیپی</span> @endif
                                            </td>
                                            <td>{{ $block->session_id ?? $block->ip_address ?? '-' }}</td>
                                            <td>{{ $block->status_text }}</td>
                                            <td>{{ $block->reason ?? '-' }}</td>
                                            <td>{{ jdate($block->created_at)->format('d F Y H:i') }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-success unblock-btn" data-id="{{$block->id}}" data-action="{{ route('admin.sessions.unblock', $block) }}">
                                                    <i class="feather icon-unlock"></i> رفع بلاک
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{ $blockedDevices->links() }}
                        </div>
                    </div>
                </section>
                @else
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">لیست بلاک‌ها</h4>
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
@endsection

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/sessions/blocked.js') }}"></script>
@endpush
