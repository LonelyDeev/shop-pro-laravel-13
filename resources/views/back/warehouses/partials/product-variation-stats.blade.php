
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="feather icon-package"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">تنوع در این انبار</span>
                        <span class="info-box-number">{{ number_format($stats['current_count'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="feather icon-grid"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">تنوع در سایر انبارهای شما</span>
                        <span class="info-box-number">{{ number_format($stats['main_count'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="feather icon-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">تنوع فروشندگان دیگر</span>
                        <span class="info-box-number">{{ number_format($stats['other_sellers_count'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="feather icon-database"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">کل موجودی این انبار</span>
                        <span class="info-box-number">{{ number_format($stats['total_stock_current'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-dark"><i class="feather icon-truck"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">کل موجودی سایر انبارهای شما</span>
                        <span class="info-box-number">{{ number_format(($stats['total_stock_other_seller'] ?? 0) + ($stats['total_stock_main'] ?? 0)) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-danger"><i class="feather icon-bar-chart-2"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">جمع کل تنوع‌ها</span>
                        <span class="info-box-number">{{ number_format(($stats['current_count'] ?? 0) + ($stats['other_current_count'] ?? 0) + ($stats['main_count'] ?? 0) + ($stats['other_sellers_count'] ?? 0)) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-gradient-orange"><i class="feather icon-trending-up"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">کل فروش این انبار</span>
                        <span class="info-box-number">{{ number_format($stats['total_sold_current'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="feather icon-star"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">پرفروش‌ترین تنوع</span>
                        <span class="info-box-number">{{ number_format($stats['best_seller_count'] ?? 0) }} فروش</span>
                        <small class="text-muted">{{ $stats['best_seller_attributes'] ?? 'ندارد' }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="feather icon-award"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">میانگین فروش هر تنوع</span>
                        <span class="info-box-number">{{ number_format($stats['avg_sold_per_variation'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
