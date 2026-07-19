{{-- widget type picker modal --}}
<div class="modal fade widget-type-modal" id="widget-type-modal" tabindex="-1" role="dialog" aria-labelledby="widgetTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="widgetTypeModalLabel">انتخاب نوع ابزارک</h4>
                <p>الگوی موردنظر برای این بخش از صفحه اصلی را انتخاب کنید</p>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="text" class="modal-search" id="type-picker-search" placeholder="جستجوی نوع ابزارک...">

                <div class="template-gallery" id="template-gallery">
                    @php
                        // دریافت همه‌ی ویجت‌ها (اصلی + ماژول‌ها) برای این صفحه
                        $page = $page ?? 'home';
                        $allWidgets = \App\Services\WidgetTemplateRegistry::listForPage($page);
                    @endphp

                    @foreach ($allWidgets as $widget_item)
                        <div class="template-card" data-key="{{ $widget_item['key'] }}" data-title="{{ $widget_item['title'] }}">
                            <div class="thumb"
                                 @if($widget_item['image'])
                                     style="background-image:url('{{ $widget_item['image'] }}')"
                                @endif
                            >
                                @if(!$widget_item['image'])
                                    <i class="feather icon-layout"></i>
                                @endif
                                @if($widget_item['module'])
                                    <span class="module-badge" style="position:absolute;top:6px;right:6px;background:rgba(99,102,241,0.9);color:#fff;font-size:0.65rem;padding:2px 6px;border-radius:4px;font-weight:600;">{{ $widget_item['module'] }}</span>
                                @endif
                            </div>
                            <div class="label">
                                <span>{{ $widget_item['title'] }}</span>
                                <span class="check"></span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="no-results" id="type-picker-no-results">چیزی با این عنوان پیدا نشد</div>
            </div>
        </div>
    </div>
</div>
