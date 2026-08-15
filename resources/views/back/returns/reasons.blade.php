@extends('back.layouts.master')

@section('title', 'دلایل مرجوعی')

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0"><i class="fas fa-list text-primary"></i> دلایل مرجوعی</h4>
                    <a href="{{ route('admin.returns.index') }}" class="btn btn-link">بازگشت به مرجوعی‌ها</a>
                </div>

                {{-- افزودن دلیل --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <form id="reasonForm" method="POST" action="{{ route('admin.returns.reasons.store') }}">
                            @csrf
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label class="small text-muted">عنوان دلیل</label>
                                    <input type="text" name="title" id="title" class="form-control form-control-sm" required placeholder="مثلاً: خرابی محصول">
                                    <div id="titleError" class="text-danger small"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="small text-muted">توضیحات (اختیاری)</label>
                                    <input type="text" name="description" id="description" class="form-control form-control-sm" placeholder="توضیح بیشتر">
                                </div>
                                <div class="col-md-2">
                                    <div class="form-check">
                                        <fieldset class="checkbox">
                                            <div class="vs-checkbox-con vs-checkbox-primary  ">
                                                <input type="checkbox" name="is_active"  value="1" checked id="is_active_reason">
                                                <span class="vs-checkbox ">
                                                        <span class="vs-checkbox--check">
                                                           <i class="vs-icon feather icon-check"></i>
                                                        </span>
                                                    </span>
                                                فعال
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm " id="submitBtn">افزودن</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- لیست دلایل --}}
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size:0.85rem;">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>عنوان</th>
                                    <th>توضیحات</th>
                                    <th>وضعیت</th>
                                    <th>تعداد استفاده</th>
                                    <th>عملیات</th>
                                </tr>
                                </thead>
                                <tbody id="reasonsTableBody">
                                @include('back.returns.partials.reasons_rows', ['reasons' => $reasons])
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($reasons->hasPages())
                        <div class="card-footer" id="paginationLinks">{{ $reasons->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script src="{{ asset('back/assets/js/pages/returns/reasons.js') }}"></script>
@endpush
