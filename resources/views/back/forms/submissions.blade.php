@extends('back.layouts.master')

@push('styles')
    <style>
        .submission-detail {
            line-height: 2;
        }
        .submission-detail .detail-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .submission-detail .detail-label {
            width: 200px;
            font-weight: bold;
            color: #333;
        }
        .submission-detail .detail-value {
            flex: 1;
            color: #666;
        }
        .badge-submission {
            background-color: #17a2b8;
        }
        .json-view {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            font-size: 13px;
            overflow-x: auto;
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
                                    <li class="breadcrumb-item">مدیریت</li>
                                    <li class="breadcrumb-item">فرم‌ها</li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.forms.index') }}">لیست فرم‌ها</a>
                                    </li>
                                    <li class="breadcrumb-item active">پاسخ‌های فرم: {{ $form->title }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right col-md-3 col-12 mb-2">
                    <div class="btn-group float-md-right">
                        <a href="{{ route('admin.forms.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> بازگشت
                        </a>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">پاسخ‌های فرم: {{ $form->title }}</h4>
                        <div class="heading-elements">
                            <span class="badge badge-primary">تعداد کل: {{ $submissions->total() }}</span>
                        </div>
                    </div>

                    <div class="card-content" id="main-card">
                        <div class="card-body">
                            @if($submissions->count())
                                <div class="mb-2 collapse datatable-actions">
                                    <div class="d-flex align-items-center">
                                        <div class="font-weight-bold text-danger mr-3">
                                            <span id="datatable-selected-rows">0</span> مورد انتخاب شده:
                                        </div>
                                        @can('forms.delete')
                                            <button class="btn personal-danger-btn mr-2" type="button" data-toggle="modal" data-target="#multiple-delete-modal">حذف موارد انتخاب شده</button>
                                        @endcan
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                        <tr>
                                            <th data-field="id" class="datatable-cell-center">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary checkbox-all">
                                                        <input type="checkbox" id="select-all">
                                                        <span class="vs-checkbox">
                                                                <span class="vs-checkbox--check">
                                                                    <i class="vs-icon feather icon-check"></i>
                                                                </span>
                                                            </span>
                                                    </div>
                                                </fieldset>
                                            </th>
                                            <th>شناسه</th>
                                            <th>اطلاعات ارسال</th>
                                            <th class="text-center">IP</th>
                                            <th class="text-center">مرورگر</th>
                                            <th class="text-center">تاریخ ارسال</th>
                                            <th class="text-center" style="width: 100px">عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($submissions as $submission)
                                            <tr id="row-{{ $submission->id }}">
                                                <td>
                                                    <fieldset class="checkbox">
                                                        <div class="vs-checkbox-con vs-checkbox-primary checkbox-single">
                                                            <input type="checkbox" class="submission-checkbox" value="{{ $submission->id }}">
                                                            <span class="vs-checkbox">
                                                                    <span class="vs-checkbox--check">
                                                                        <i class="vs-icon feather icon-check"></i>
                                                                    </span>
                                                                </span>
                                                        </div>
                                                    </fieldset>
                                                </td>
                                                <td>{{ $submission->id }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="story-ring mr-1">
                                                            <i class="fa fa-file-text" style="font-size: 20px; margin: 10px;"></i>
                                                        </div>
                                                        <div>
                                                            <strong>فرم: {{ $form->title }}</strong><br>
                                                            <small class="text-muted">
                                                                @php
                                                                    $firstData = collect($submission->data)->first();
                                                                    echo 'اولین مقدار: ' . (is_string($firstData) ? Str::limit($firstData, 30) : '---');
                                                                @endphp
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center"><code>{{ $submission->ip_address ?? '-' }}</code></td>
                                                <td class="text-center">
                                                    {{ $submission->user_agent ? \App\Models\Newsletter::getBrowser($submission->user_agent) : '-' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ jdate($submission->submitted_at)->format('%d %B %Y | H:i') }}
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-info btn-show-details font-s-10 d-flex"
                                                            data-id="{{ $submission->id }}"
                                                            data-action="{{ route('admin.forms.submissions.show', [$form->id, $submission->id]) }}">
                                                        <i class="fa fa-users"></i> جزئیات
                                                    </button>
                                                </td>

                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    {{ $submissions->links() }}
                                </div>
                            @else
                                <div class="text-center p-5">
                                    <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">هیچ پاسخی برای این فرم ثبت نشده است</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال نمایش جزئیات پاسخ -->
    <div class="modal fade" id="submissionDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">جزئیات پاسخ فرم</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="submission-details-container">
                    <div class="text-center p-5">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">در حال بارگذاری...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>

    @can('forms.delete')
        {{-- multiple delete modal --}}
        <div class="modal fade text-left" id="multiple-delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">آیا مطمئن هستید؟</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        با حذف این پاسخ‌ها، دیگر قادر به بازیابی آنها نخواهید بود
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('admin.forms.submissions.multipleDestroy', ['form'=>$form]) }}" id="multiple-delete-form" method="POST">
                            @csrf
                            @method('delete')
                            <input type="hidden" name="ids" id="delete-ids">
                            <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">خیر</button>
                            <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- delete single modal --}}
        <div class="modal fade text-left" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">آیا مطمئن هستید؟</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        با حذف این پاسخ، دیگر قادر به بازیابی آن نخواهید بود
                    </div>
                    <div class="modal-footer">
                        <form action="#" id="delete-form" method="POST">
                            @csrf
                            @method('delete')
                            <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">خیر</button>
                            <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/forms/submission.js') }}"></script>

@endpush
