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
                                    <li class="breadcrumb-item">مدیریت سوالات</li>
                                    <li class="breadcrumb-item active">لیست سوالات متداول</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section class="card">
                    <div class="card-header">
                        <h4 class="card-title">لیست سوالات متداول</h4>

                        <div class="heading-elements">
                            <ul class="list-inline mb-0">
                                <li>
                                    <button type="button" id="btn-create-faq" class="btn personal-info-btn mb-1 waves-effect waves-light" data-action="{{ route("admin.faqs.store") }}"><i class="fa fa-plus"></i> افزودن سوال جدید</button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    @if ($items->count())
                        <div class="card-content" id="main-card">
                            <div class="card-body pb-0">
                                <div class="collapse datatable-actions" id="multiple-actions-bar" style="display: none;">
                                    <div class="d-flex align-items-center">
                                        <div class="font-weight-bold text-danger mr-3"><span id="datatable-selected-rows">0</span> مورد انتخاب شده: </div>
                                        <button class="btn personal-danger-btn mr-2" type="button" id="btn-multiple-delete" data-action="{{ route("admin.faqs.multipleDestroy") }}">حذف همه</button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="faq-list-container" id="faqAccordion">
                                    @foreach ($items as $item)
                                        <div class="faq-item card mb-1 border rounded" id="faq-{{ $item->id }}-tr" data-id="{{ $item->id }}">
                                            <div class="card-header d-flex justify-content-between align-items-center py-1">
                                                <div class="d-flex align-items-center flex-grow-1">
                                                    <fieldset class="checkbox mr-1">
                                                        <div class="vs-checkbox-con vs-checkbox-primary checkbox-single">
                                                            <input type="checkbox" class="faq-checkbox" value="{{ $item->id }}">
                                                            <span class="vs-checkbox"><span class="vs-checkbox--check"><i class="vs-icon feather icon-check"></i></span></span>
                                                        </div>
                                                    </fieldset>
                                                    <a id="question-{{ $item->id }}" class="collapsed text-body font-weight-bold mr-1 faq-question-text" data-toggle="collapse" href="#answer-{{ $item->id }}" style="text-decoration: none; font-size: 14px;">
                                                        {{ $item->question }}
                                                    </a>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    @if ($item->published)
                                                        <div class="badge badge-success mr-1 faq-status-badge">فعال</div>
                                                    @else
                                                        <div class="badge badge-danger mr-1 faq-status-badge">غیرفعال</div>
                                                    @endif

                                                    <div class="dropdown dropdown-action">
                                                        <button class="btn btn-secondary dropdown-toggle waves-effect waves-light" type="button" id="dropdownMenu{{ $item->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $item->id }}">
                                                            <a class="dropdown-item btn-edit" data-id="{{ $item->id  }}" data-action="{{ route("admin.faqs.edit",$item)  }}" data-action-update="{{ route("admin.faqs.update",$item)  }}" href="#"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                                                            <div class="dropdown-divider"></div>
                                                            <button class="dropdown-item btn-delete" data-id="{{ $item->id  }}" data-action="{{ route("admin.faqs.destroy",$item) }}"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="answer-{{ $item->id }}" class="collapse" data-parent="#faqAccordion">
                                                <div class="card-body pt-1 pb-1 faq-answer-text" style="background: #f8f8f8; font-size: 13px; line-height: 2;">
                                                    {{ $item->answer }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{$items->links()}}
                            </div>


                        </div>


                    @else
                        <div class="card-content">
                            <div class="card-body">
                                <div class="card-text">
                                    <p>چیزی برای نمایش وجود ندارد!</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>


    {{-- Create / Edit Modal --}}
    <div class="modal fade text-left" id="faq-form-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="faq-modal-title">افزودن سوال جدید</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="faq-form">
                    @csrf
                    <input type="hidden" id="faq_id" name="faq_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>سوال</label>
                            <input type="text" id="faq_question" name="question" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>جواب</label>
                            <textarea id="faq_answer" name="answer" rows="5" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>ترتیب نمایش</label>
                            <input type="number" id="faq_order" name="order" class="form-control" value="0">
                        </div>
                        <div class="form-group">
                            <label>وضعیت</label>
                            <select id="faq_published" name="published" class="form-control">
                                <option value="1">فعال</option>
                                <option value="0">غیرفعال</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn personal-info-btn waves-effect waves-light">ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Multiple Delete Modal --}}
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
                    با حذف سوالات دیگر قادر به بازیابی آنها نخواهید بود
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">خیر</button>
                    <button type="button" class="btn personal-danger-btn waves-effect waves-light" id="btn-confirm-multiple-delete">بله حذف شود</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Single Delete Modal --}}
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
                    با حذف سوال دیگر قادر به بازیابی آن نخواهید بود
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">خیر</button>
                    <button type="button" class="btn personal-danger-btn waves-effect waves-light" id="btn-confirm-delete">بله حذف شود</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@include('back.partials.plugins', ['plugins' => ['jquery.validate']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/faqs/index.js') }}"></script>
@endpush
