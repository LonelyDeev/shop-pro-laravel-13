<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <strong>عنوان تگ:</strong>
                            <span class="badge badge-primary badge-lg">{{ $tag->name }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <strong>اسلاگ:</strong>
                            <code>{{ $tag->slug }}</code>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-item">
                            <strong>تعداد کل استفاده:</strong>
                            <span class="badge badge-info">{{ $total_usage }} بار</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-item">
                            <strong>بازدید کل:</strong>
                            <span class="badge badge-success">{{ number_format($tag->view_count) }} بار</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-item">
                            <strong>تاریخ ایجاد:</strong>
                            <span>{{ jdate($tag->created_at)->format('d %B Y | H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- آمار استفاده در بخش‌های مختلف -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">آمار استفاده در بخش‌های مختلف</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($usage_details as $detail)
                        <div class="col-md-4 mb-3">
                            <div class="stat-box text-center p-3 border rounded">
                                <i class="fa fa-{{ $detail['model'] == 'Post' ? 'file-text' : ($detail['model'] == 'Product' ? 'box' : 'tag') }} fa-2x text-primary mb-2"></i>
                                <h5>{{ $detail['model'] }}</h5>
                                <h6 class="badge badge-info">{{ $detail['count'] }} بار استفاده</h6>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-warning text-center">
                                این تگ هنوز در هیچ بخشی استفاده نشده است
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- لیست موارد استفاده -->
        @if($taggables->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">لیست موارد استفاده</h5>
                </div>
                <div class="card-body">
                    @foreach($taggables as $type => $items)
                        @php
                            $modelName = class_basename($type);
                        @endphp
                        <h6 class="mt-3 mb-2">{{ $modelName }}‌ها:</h6>
                        <ul class="list-group">
                            @foreach($items as $item)
                                <li class="list-group-item">
                                    <strong>شناسه:</strong> {{ $item->taggable_id }}
                                    <span class="text-muted">({{ $item->taggable_type }})</span>
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    .info-item {
        padding: 8px 0;
        font-size: 14px;
    }
    .stat-box {
        transition: all 0.3s ease;
    }
    .stat-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .badge-lg {
        font-size: 14px;
        padding: 8px 15px;
    }
</style>
