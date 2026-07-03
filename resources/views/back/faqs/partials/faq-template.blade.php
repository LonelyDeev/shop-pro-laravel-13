<div class="faq-item card mb-1 border rounded" id="faq-{{ $faq->id }}-tr" data-id="{{ $faq->id }}">
    <div class="card-header d-flex justify-content-between align-items-center py-1">
        <div class="d-flex align-items-center flex-grow-1">
            <fieldset class="checkbox mr-1">
                <div class="vs-checkbox-con vs-checkbox-primary checkbox-single">
                    <input type="checkbox" class="faq-checkbox" value="{{ $faq->id }}">
                    <span class="vs-checkbox"><span class="vs-checkbox--check"><i class="vs-icon feather icon-check"></i></span></span>
                </div>
            </fieldset>
            <a id="question-{{ $faq->id }}" class="collapsed text-body font-weight-bold mr-1 faq-question-text" data-toggle="collapse" href="#answer-{{ $faq->id }}" style="text-decoration: none; font-size: 14px;">
                {{ $faq->question }}
            </a>
        </div>
        <div class="d-flex align-items-center">
            @if ($faq->published)
                <div class="badge badge-success mr-1 faq-status-badge">فعال</div>
            @else
                <div class="badge badge-danger mr-1 faq-status-badge">غیرفعال</div>
            @endif

            <div class="dropdown dropdown-action">
                <button class="btn btn-icon btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenu{{ $faq->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $faq->id }}">
                    <a class="dropdown-item btn-edit" data-id="{{ $faq->id  }}" data-action="{{ route("admin.faqs.edit",$faq)  }}" data-action-update="{{ route("admin.faqs.update",$faq)  }}" href="#"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                    <div class="dropdown-divider"></div>
                    <button class="dropdown-item btn-delete" data-id="{{ $faq->id  }}" data-action="{{ route("admin.faqs.destroy",$faq) }}"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>
                </div>
            </div>
        </div>
    </div>
    <div id="answer-{{ $faq->id }}" class="collapse" data-parent="#faqAccordion">
        <div class="card-body pt-1 pb-1 faq-answer-text" style="background: #f8f8f8; font-size: 13px; line-height: 2;">
            {{ $faq->answer }}
        </div>
    </div>
</div>
