@extends('back.layouts.master')
@push('styles')
    <style>
        /* ===================================
   Dashboard Improved Styles
   =================================== */

        /* ----- Welcome Header Card ----- */
        .welcome-header-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            border: none;
            overflow: hidden;
            position: relative;
        }

        .welcome-header-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='80' cy='20' r='40' fill='rgba(255,255,255,0.05)'/><circle cx='20' cy='80' r='30' fill='rgba(255,255,255,0.04)'/></svg>");
            pointer-events: none;
        }

        .welcome-header-card .card-body {
            position: relative;
            z-index: 1;
        }

        .welcome-date-box {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 14px;
            padding: 16px 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* ----- Stats Cards ----- */
        .stat-card {
            border-radius: 16px;
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .stat-card.users-card {
            box-shadow: 0 4px 24px rgba(102, 126, 234, 0.15);
        }

        .stat-card.users-card:hover {
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.25);
        }

        .stat-card.products-card {
            box-shadow: 0 4px 24px rgba(253, 160, 133, 0.15);
        }

        .stat-card.products-card:hover {
            box-shadow: 0 8px 32px rgba(253, 160, 133, 0.25);
        }

        .stat-card.orders-card {
            box-shadow: 0 4px 24px rgba(67, 233, 123, 0.15);
        }

        .stat-card.orders-card:hover {
            box-shadow: 0 8px 32px rgba(67, 233, 123, 0.25);
        }

        .stat-icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }

        .stat-icon-circle.users-icon {
            background: linear-gradient(135deg, #4fc3f7, #00bcd4);
            box-shadow: 0 4px 20px rgba(79, 195, 247, 0.4);
        }

        .stat-icon-circle.products-icon {
            background: linear-gradient(135deg, #f6d365, #fda085);
            box-shadow: 0 4px 20px rgba(246, 211, 101, 0.4);
        }

        .stat-icon-circle.orders-icon {
            background: linear-gradient(135deg, #43e97b, #38f9d7);
            box-shadow: 0 4px 20px rgba(67, 233, 123, 0.4);
        }

        /* ----- Statistics Card Tabs ----- */
        .statistics-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        }

        .custom-tab-nav {
            background: #f8f9fc;
            border-radius: 12px;
            padding: 6px;
            margin-bottom: 16px;
        }

        .custom-tab-nav .nav-link {
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #666;
            transition: all 0.2s;
        }

        .custom-tab-nav .nav-link:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .custom-tab-nav .nav-link.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
        }

        /* ----- Stats Summary Boxes ----- */
        .stat-summary-box {
            border-radius: 12px;
            padding: 14px 16px;
            text-align: center;
        }

        .stat-summary-box.small {
            padding: 12px;
        }

        .stat-summary-box.total {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .stat-summary-box.avg {
            background: linear-gradient(135deg, #4fc3f7, #00bcd4);
        }

        .stat-summary-box.success {
            background: linear-gradient(135deg, #43e97b, #38f9d7);
        }

        .stat-summary-box.fail {
            background: linear-gradient(135deg, #ff6b6b, #ee0979);
        }

        .stat-summary-box small {
            color: rgba(255, 255, 255, 0.85);
            font-size: 11px;
        }

        .stat-summary-box p {
            color: #fff;
            font-weight: 700;
            margin-bottom: 0;
        }

        .stat-summary-box.small p {
            font-size: 16px;
        }

        /* ----- Section Cards ----- */
        .section-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 16px;
        }

        .section-card .card-header {
            border: none;
            padding: 16px 20px;
        }

        .section-card .card-header h4 {
            margin-bottom: 0;
        }

        /* ----- Card Header Gradients ----- */
        .header-gradient-purple {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .header-gradient-orange {
            background: linear-gradient(135deg, #f6d365, #fda085);
        }

        .header-gradient-pink {
            background: linear-gradient(135deg, #a18cd1, #fbc2eb);
        }

        .header-gradient-green {
            background: linear-gradient(135deg, #43e97b, #38f9d7);
        }

        .header-gradient-blue {
            background: linear-gradient(135deg, #4fc3f7, #00bcd4);
        }

        .header-gradient-red {
            background: linear-gradient(135deg, #ff6b6b, #ee0979);
        }

        /* ----- Tables ----- */
        .modern-table {
            font-size: 14px;
        }

        .modern-table thead tr {
            background: #f8f9fc;
        }

        .modern-table thead th {
            padding: 14px 20px;
            font-weight: 700;
            color: #555;
            border-bottom: 2px solid #eee;
        }

        .modern-table tbody tr {
            transition: background 0.15s;
        }

        .modern-table tbody tr:hover {
            background: #f8f9fc;
        }

        .modern-table tbody td {
            padding: 12px 20px;
            vertical-align: middle;
        }

        /* ----- Badges & Pills ----- */
        .pill-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            width: max-content;
            display: block;
        }

        .pill-badge.success {
            background: rgba(67, 233, 123, 0.15);
            color: #43e97b;
        }

        .pill-badge.danger {
            background: rgba(255, 107, 107, 0.15);
            color: #ff6b6b;
        }

        .pill-badge.warning {
            background: rgba(255, 159, 67, 0.15);
            color: #ff9f43;
        }

        .pill-badge.info {
            background: rgba(79, 195, 247, 0.15);
            color: #4fc3f7;
        }

        .pill-badge.purple {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
        }

        .pill-badge.orange {
            background: linear-gradient(135deg, #f6d365, #fda085);
            color: #fff;
        }

        .pill-badge.pink {
            background: linear-gradient(135deg, #a18cd1, #fbc2eb);
            color: #fff;
        }

        /* ----- Buttons ----- */
        .modern-btn {
            border: none;
            border-radius: 8px;
            padding: 6px 16px;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s;
            display: block;
            width: max-content;
        }

        .modern-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .modern-btn-purple {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
        }

        .modern-btn-orange {
            background: linear-gradient(135deg, #f6d365, #fda085);
            color: #fff;
        }

        .modern-btn-blue {
            background: linear-gradient(135deg, #4fc3f7, #00bcd4);
            color: #fff;
        }

        .modern-btn-green {
            background: linear-gradient(135deg, #43e97b, #38f9d7);
            color: #fff;
        }

        /* ----- List Items ----- */
        .modern-list-item {
            border: none;
            border-bottom: 1px solid #f0f0f0;
            padding: 14px 18px;
            transition: background 0.15s;
        }

        .modern-list-item:hover {
            background: #f8f9fc;
        }

        .modern-list-item:last-child {
            border-bottom: none;
        }

        /* ----- Avatar Images ----- */
        .avatar-bordered {
            border: 2px solid;
            border-radius: 50%;
        }

        .avatar-bordered.green {
            border-color: #43e97b;
        }

        .avatar-bordered.blue {
            border-color: #4fc3f7;
        }

        .avatar-bordered.orange {
            border-color: #f6d365;
        }

        .avatar-bordered.purple {
            border-color: #667eea;
        }

        /* ----- Empty States ----- */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #aaa;
        }

        .empty-state i {
            opacity: 0.4;
            margin-bottom: 12px;
        }

        .empty-state p {
            color: #aaa;
            margin-bottom: 0;
        }

        /* ----- Sidebar Cards ----- */
        .sidebar-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 16px;
        }

        .sidebar-card .card-header {
            border: none;
            padding: 14px 18px;
        }

        .sidebar-card .card-header h5 {
            font-size: 15px;
            margin-bottom: 0;
        }

        .sidebar-card .card-body {
            padding: 0;
        }

        /* ----- Chart Cards ----- */
        .chart-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 16px;
        }

        .chart-card .card-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            padding: 16px;
        }

        .chart-card .card-header .avatar {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chart-card .card-content {
            padding: 12px;
        }

        /* ----- Status Text ----- */
        .status-text {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 15px;
            display: inline-block;
        }

        .status-text.success {
            background: rgba(67, 233, 123, 0.15);
            color: #43e97b;
        }

        .status-text.warning {
            background: rgba(255, 159, 67, 0.15);
            color: #ff9f43;
        }

        .status-text.danger {
            background: rgba(255, 107, 107, 0.15);
            color: #ff6b6b;
        }

        /* ----- Product Thumbnail ----- */
        .product-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 10px;
        }

        .product-thumb.purple-border {
            border: 2px solid #a18cd1;
        }

        .product-thumb.orange-border {
            border: 2px solid #f6d365;
        }

        /* ----- Links ----- */
        .modern-link {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .modern-link:hover {
            color: #764ba2;
            text-decoration: none;
        }

        .modern-link.orange {
            color: #fda085;
        }

        .modern-link.orange:hover {
            color: #f6d365;
        }

        .modern-link.blue {
            color: #4fc3f7;
        }

        .modern-link.blue:hover {
            color: #00bcd4;
        }

        /* ----- Card Footer ----- */
        .modern-card-footer {
            background: #f8f9fc;
            border-top: 1px solid #eee;
            padding: 12px 20px;
        }

        /* ----- Online Badge ----- */
        .online-badge {
            background: rgba(67, 233, 123, 0.15);
            color: #43e97b;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
        }

        /* ----- Responsive Adjustments ----- */
        @media (max-width: 768px) {
            .stat-icon-circle {
                width: 50px;
                height: 50px;
            }

            .stat-icon-circle i {
                font-size: 22px;
            }

            .stat-card h2 {
                font-size: 24px;
            }

            .modern-table {
                font-size: 12px;
            }

            .modern-table thead th,
            .modern-table tbody td {
                padding: 10px 12px;
            }
        }

    </style>
@endpush
@section('content')

    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">

            {{-- Breadcrumb --}}
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item"><i class="feather icon-home"></i> مدیریت</li>
                                    <li class="breadcrumb-item active">داشبورد</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                {{-- ===== Welcome Header ===== --}}
                <div class="card mb-2 welcome-header-card">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="text-white font-weight-bold mb-1">
                                    <i class="feather icon-grid mr-2"></i> داشبورد مدیریت
                                </h2>
                                <p class="text-white mb-0" style="opacity: 0.85;">خوش آمدید! آمار و اطلاعات کسب‌وکار خود را دنبال کنید.</p>
                            </div>
                            <div class="col-md-4 text-left">
                                <div class="welcome-date-box">
                                    <p class="text-white mb-0" style="font-size: 12px; opacity: 0.8;">تاریخ امروز</p>
                                    <p class="text-white font-weight-bold mb-0" style="font-size: 18px;">{{ jdate()->format('d F Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== Stats Cards Row ===== --}}
                <div class="row mb-2">
                    @can('users.index')
                        <div class="col-lg-4 col-md-4 col-sm-6 mb-2">
                            <div class="card text-center stat-card users-card">
                                <div class="card-content">
                                    <div class="card-body p-3">
                                        <div class="stat-icon-circle users-icon">
                                            <i class="feather icon-users" style="font-size: 28px; color: #fff;"></i>
                                        </div>
                                        <h2 class="text-bold-700 mb-1" style="font-size: 32px; color: #667eea;">{{ number_format($users_count) }}</h2>
                                        <p class="mb-0 text-muted" style="font-size: 14px; font-weight: 600;">کاربران</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcan

                    @can('products.index')
                        <div class="col-lg-4 col-md-4 col-sm-6 mb-2">
                            <div class="card text-center stat-card products-card">
                                <div class="card-content">
                                    <div class="card-body p-3">
                                        <div class="stat-icon-circle products-icon">
                                            <i class="feather icon-shopping-cart" style="font-size: 28px; color: #fff;"></i>
                                        </div>
                                        <h2 class="text-bold-700 mb-1" style="font-size: 32px; color: #fda085;">{{ number_format($products_count) }}</h2>
                                        <p class="mb-0 text-muted" style="font-size: 14px; font-weight: 600;">محصولات</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcan

                    @can('orders.index')
                        <div class="col-lg-4 col-md-4 col-sm-6 mb-2">
                            <div class="card text-center stat-card orders-card">
                                <div class="card-content">
                                    <div class="card-body p-3">
                                        <div class="stat-icon-circle orders-icon">
                                            <i class="feather icon-briefcase" style="font-size: 28px; color: #fff;"></i>
                                        </div>
                                        <h2 class="text-bold-700 mb-1" style="font-size: 32px; color: #43e97b;">{{ number_format($orders_count) }}</h2>
                                        <p class="mb-0 text-muted" style="font-size: 14px; font-weight: 600;">سفارشات</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcan
                </div>

                <div class="row">
                    {{-- ===== Main Content (Left) ===== --}}
                    <div class="col-md-8">

                        @can('orders.index')
                            {{-- Statistics Card with Tabs --}}
                            <section class="card mb-2 statistics-card" id="statistics-card">
                                <div class="card-content">
                                    <div class="card-body p-2">
                                        {{-- Custom Tab Navigation --}}
                                        <ul class="nav nav-tabs custom-tab-nav" id="orderstab" role="tablist">
                                            <li class="nav-item flex-grow-1" role="presentation">
                                                <a class="nav-link text-center py-2" data-toggle="tab" href="#order-values" role="tab" aria-controls="order-values" aria-selected="true">
                                                    <i class="feather icon-dollar-sign mr-1"></i> ارزش
                                                </a>
                                            </li>
                                            <li class="nav-item flex-grow-1" role="presentation">
                                                <a class="nav-link text-center py-2" data-toggle="tab" href="#order-counts" role="tab" aria-controls="order-counts" aria-selected="false">
                                                    <i class="feather icon-shopping-bag mr-1"></i> تعداد
                                                </a>
                                            </li>
                                            <li class="nav-item flex-grow-1" role="presentation">
                                                <a class="nav-link text-center py-2" data-toggle="tab" href="#order-users" role="tab" aria-controls="order-users" aria-selected="false">
                                                    <i class="feather icon-users mr-1"></i> کاربران
                                                </a>
                                            </li>
                                            <li class="nav-item flex-grow-1" role="presentation">
                                                <a class="nav-link text-center py-2" data-toggle="tab" href="#order-products" role="tab" aria-controls="order-products" aria-selected="false">
                                                    <i class="feather icon-package mr-1"></i> محصولات
                                                </a>
                                            </li>
                                        </ul>

                                        <div class="tab-content" id="myTabContent">
                                            <div class="tab-pane fade show active" id="order-values" role="tabpanel" aria-labelledby="value">
                                                @include('back.statistics.orders.filter-tabs')
                                                <div id="order-values-chart" class="chart-area" style="min-height: 445px;" data-min-height="445px" data-action="{{ route('admin.statistics.orderValues') }}"></div>
                                                <div class="row mt-3">
                                                    <div class="col-md-4 mb-2">
                                                        <div class="stat-summary-box total">
                                                            <small class="text-white">کل سفارشات</small>
                                                            <p class="text-white font-weight-bold mb-0" style="font-size: 18px;"><span class="orders-total"></span></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-2">
                                                        <div class="stat-summary-box avg">
                                                            <small class="text-white">میانگین سفارشات</small>
                                                            <p class="text-white font-weight-bold mb-0" style="font-size: 18px;"><span class="orders-avg"></span></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-2">
                                                        <div class="stat-summary-box success">
                                                            <small class="text-white">سفارشات موفق</small>
                                                            <p class="text-white font-weight-bold mb-0" style="font-size: 18px;"><span class="orders-success"></span></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="tab-pane fade" id="order-counts" role="tabpanel" aria-labelledby="count">
                                                @include('back.statistics.orders.filter-tabs')
                                                <div id="order-counts-chart" class="chart-area" style="min-height: 445px;" data-min-height="445px" data-action="{{ route('admin.statistics.orderCounts') }}"></div>
                                                <div class="row mt-3">
                                                    <div class="col-md-3 mb-2">
                                                        <div class="stat-summary-box small total">
                                                            <small class="text-white">کل</small>
                                                            <p class="text-white font-weight-bold mb-0" style="font-size: 16px;"><span class="orders-total"></span></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <div class="stat-summary-box small avg">
                                                            <small class="text-white">میانگین</small>
                                                            <p class="text-white font-weight-bold mb-0" style="font-size: 16px;"><span class="orders-avg"></span></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <div class="stat-summary-box small success">
                                                            <small class="text-white">موفق</small>
                                                            <p class="text-white font-weight-bold mb-0" style="font-size: 16px;"><span class="orders-success"></span></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <div class="stat-summary-box small fail">
                                                            <small class="text-white">ناموفق</small>
                                                            <p class="text-white font-weight-bold mb-0" style="font-size: 16px;"><span class="orders-fail"></span></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="tab-pane fade" id="order-users" role="tabpanel" aria-labelledby="user">
                                                @include('back.statistics.orders.filter-tabs')
                                                <div id="order-users-chart" class="chart-area" style="min-height: 445px;" data-min-height="445px" data-action="{{ route('admin.statistics.orderUsers') }}"></div>
                                                <div class="row mt-3">
                                                    <div class="col-md-3 mb-2">
                                                        <div class="stat-summary-box small total">
                                                            <small class="text-white">کل</small>
                                                            <p class="text-white font-weight-bold mb-0" style="font-size: 16px;"><span class="orders-total"></span></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <div class="stat-summary-box small avg">
                                                            <small class="text-white">میانگین</small>
                                                            <p class="text-white font-weight-bold mb-0" style="font-size: 16px;"><span class="orders-avg"></span></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <div class="stat-summary-box small success">
                                                            <small class="text-white">موفق</small>
                                                            <p class="text-white font-weight-bold mb-0" style="font-size: 16px;"><span class="orders-success"></span></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <div class="stat-summary-box small fail">
                                                            <small class="text-white">ناموفق</small>
                                                            <p class="text-white font-weight-bold mb-0" style="font-size: 16px;"><span class="orders-fail"></span></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="tab-pane fade" id="order-products" role="tabpanel" aria-labelledby="product">
                                                @include('back.statistics.orders.filter-tabs')
                                                <div id="order-products-chart" class="chart-area" style="min-height: 445px;" data-min-height="445px" data-action="{{ route('admin.statistics.orderProducts') }}"></div>
                                                <div class="row mt-3">
                                                    <div class="col-md-3 mb-2">
                                                        <div class="stat-summary-box small total">
                                                            <small class="text-white">کل</small>
                                                            <p class="text-white font-weight-bold mb-0" style="font-size: 16px;"><span class="orders-total"></span></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <div class="stat-summary-box small avg">
                                                            <small class="text-white">میانگین</small>
                                                            <p class="text-white font-weight-bold mb-0" style="font-size: 16px;"><span class="orders-avg"></span></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <div class="stat-summary-box small success">
                                                            <small class="text-white">موفق</small>
                                                            <p class="text-white font-weight-bold mb-0" style="font-size: 16px;"><span class="orders-success"></span></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <div class="stat-summary-box small fail">
                                                            <small class="text-white">ناموفق</small>
                                                            <p class="text-white font-weight-bold mb-0" style="font-size: 16px;"><span class="orders-fail"></span></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            {{-- Latest Orders --}}
                            <section class="card mb-2 section-card">
                                <div class="card-header header-gradient-purple d-flex align-items-center justify-content-between">
                                    <h4 class="card-title text-white mb-0">
                                        <i class="feather icon-shopping-bag mr-2"></i> آخرین سفارشات
                                    </h4>
                                    <div class="heading-elements">
                                        <ul class="list-inline mb-0">
                                            <li><a data-action="collapse"><i class="feather icon-chevron-down text-white"></i></a></li>
                                            <li><a data-action="expand"><i class="feather icon-maximize text-white"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-content">
                                    @if (count($orders))
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table mb-0 modern-table">
                                                    <thead>
                                                    <tr>
                                                        <th>ردیف</th>
                                                        <th>شماره سفارش</th>
                                                        <th>تاریخ ثبت</th>
                                                        <th class="text-center">قیمت کل</th>
                                                        <th>وضعیت</th>
                                                        <th class="text-center">عملیات</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($orders as $order)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>
                                                                <span class="pill-badge purple">#{{ $order->id }}</span>
                                                            </td>
                                                            <td>
                                                                <i class="feather icon-calendar mr-1 text-muted"></i>
                                                                {{ jdate($order->created_at)->format('%d %B %Y') }}
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="pill-badge success">{{ trans('messages.currency.prefix') . number_format($order->price) . trans('messages.currency.suffix') }}</span>
                                                            </td>
                                                            <td>
                                                                @php
                                                                    if($order->status=="paid"){ $statusClass="success"; }
                                                                    elseif($order->status=="unpaid"){ $statusClass="danger"; }
                                                                    elseif($order->status=="canceled"){ $statusClass="warning"; }
                                                                @endphp
                                                                <span class="pill-badge {{ $statusClass }}">{{ $order->statusText() }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <a href="{{ route('admin.orders.show', ['order' => $order]) }}" class="modern-btn modern-btn-purple">
                                                                    <i class="feather icon-eye mr-1"></i> مشاهده
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="modern-card-footer">
                                            <a href="{{ route('admin.orders.index') }}" class="modern-link">
                                                مشاهده همه <i class="fa fa-angle-left"></i>
                                            </a>
                                        </div>
                                    @else
                                        <div class="card-body empty-state">
                                            <i class="feather icon-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">چیزی برای نمایش وجود ندارد!</p>
                                        </div>
                                    @endif
                                </div>
                            </section>
                        @endcan

                        @can('products.index')
                            {{-- Best Selling Products --}}
                            <section class="card mb-2 section-card">
                                <div class="card-header header-gradient-orange d-flex align-items-center justify-content-between">
                                    <h4 class="card-title text-white mb-0">
                                        <i class="feather icon-trending-up mr-2"></i> پرفروش‌ترین محصولات
                                    </h4>
                                    <div class="heading-elements">
                                        <ul class="list-inline mb-0">
                                            <li><a data-action="collapse"><i class="feather icon-chevron-down text-white"></i></a></li>
                                            <li><a data-action="expand"><i class="feather icon-maximize text-white"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-content">
                                    @if (count($sale_products))
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table mb-0 modern-table">
                                                    <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>تصویر</th>
                                                        <th>عنوان محصول</th>
                                                        <th>تاریخ ایجاد</th>
                                                        <th class="text-center">موجودی</th>
                                                        <th class="text-center">فروش</th>
                                                        <th class="text-center">وضعیت</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($sale_products as $product)
                                                        <tr>
                                                            <td>
                                                                <span style="color: #999; font-size: 13px;">#{{ $product->id }}</span>
                                                            </td>
                                                            <td>
                                                                <a target="_blank" href="{{ route('front.products.show', ['product' => $product]) }}">
                                                                    <img class="product-thumb orange-border" src="{{ $product->image ? asset($product->image) : asset('/empty.svg') }}" alt="{{ $product->title }}">
                                                                </a>
                                                            </td>
                                                            <td>
                                                                <a target="_blank" href="{{ route('front.products.show', ['product' => $product]) }}" class="modern-link">
                                                                    {{ $product->title }}
                                                                </a>
                                                            </td>
                                                            <td>
                                                                <i class="feather icon-calendar mr-1 text-muted"></i>
                                                                {{ jdate($product->created_at)->format('%d %B %Y') }}
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="pill-badge info">{{ $product->prices()->sum('stock') }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="pill-badge orange">{{ $product->sell }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <div style="display: inline-flex; flex-direction: column; gap: 4px;">
                                                                    @if($product->isPublished())
                                                                        <span class="status-text success">منتشر شده</span>
                                                                    @else
                                                                        <span class="status-text danger">پیش‌نویس</span>
                                                                    @endif
                                                                    @if($product->status=="Accept")
                                                                        <span class="status-text success">تایید شده</span>
                                                                    @elseif($product->status=="Waiting")
                                                                        <span class="status-text warning">در انتظار</span>
                                                                    @elseif($product->status=="Reject")
                                                                        <span class="status-text danger">رد شده</span>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @else
                                        <div class="card-body empty-state">
                                            <i class="feather icon-package fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">چیزی برای نمایش وجود ندارد!</p>
                                        </div>
                                    @endif
                                </div>
                            </section>

                            {{-- Most Viewed Products --}}
                            <section class="card mb-2 section-card">
                                <div class="card-header header-gradient-pink d-flex align-items-center justify-content-between">
                                    <h4 class="card-title text-white mb-0">
                                        <i class="feather icon-eye mr-2"></i> پربازدیدترین محصولات
                                    </h4>
                                    <div class="heading-elements">
                                        <ul class="list-inline mb-0">
                                            <li><a data-action="collapse"><i class="feather icon-chevron-down text-white"></i></a></li>
                                            <li><a data-action="expand"><i class="feather icon-maximize text-white"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-content">
                                    @if (count($view_products))
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table mb-0 modern-table">
                                                    <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>تصویر</th>
                                                        <th>عنوان محصول</th>
                                                        <th>تاریخ ایجاد</th>
                                                        <th class="text-center">موجودی</th>
                                                        <th class="text-center">بازدید</th>
                                                        <th class="text-center">وضعیت</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($view_products as $product)
                                                        <tr>
                                                            <td>
                                                                <span style="color: #999; font-size: 13px;">#{{ $product->id }}</span>
                                                            </td>
                                                            <td>
                                                                <a target="_blank" href="{{ route('front.products.show', ['product' => $product]) }}">
                                                                    <img class="product-thumb purple-border" src="{{ $product->image ? asset($product->image) : asset('/empty.svg') }}" alt="{{ $product->title }}">
                                                                </a>
                                                            </td>
                                                            <td>
                                                                <a target="_blank" href="{{ route('front.products.show', ['product' => $product]) }}" class="modern-link orange">
                                                                    {{ $product->title }}
                                                                </a>
                                                            </td>
                                                            <td>
                                                                <i class="feather icon-calendar mr-1 text-muted"></i>
                                                                {{ jdate($product->created_at)->format('%d %B %Y') }}
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="pill-badge info">{{ $product->prices()->sum('stock') }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="pill-badge pink">{{ $product->view }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <div style="display: inline-flex; flex-direction: column; gap: 4px;">
                                                                    @if($product->isPublished())
                                                                        <span class="status-text success">منتشر شده</span>
                                                                    @else
                                                                        <span class="status-text danger">پیش‌نویس</span>
                                                                    @endif
                                                                    @if($product->status=="Accept")
                                                                        <span class="status-text success">تایید شده</span>
                                                                    @elseif($product->status=="Waiting")
                                                                        <span class="status-text warning">در انتظار</span>
                                                                    @elseif($product->status=="Reject")
                                                                        <span class="status-text danger">رد شده</span>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @else
                                        <div class="card-body empty-state">
                                            <i class="feather icon-package fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">چیزی برای نمایش وجود ندارد!</p>
                                        </div>
                                    @endif
                                </div>
                            </section>
                        @endcan

                    </div>{{-- /col-md-8 --}}

                    {{-- ===== Sidebar (Right) ===== --}}
                    <div class="col-md-4">

                        @can('statistics.users')
                            {{-- Weekly Views Chart --}}
                            <div class="chart-card">
                                <div class="card-header header-gradient-purple d-flex flex-column align-items-center pb-0">
                                    <div class="avatar">
                                        <div class="avatar-content">
                                            <i class="feather icon-eye text-white font-medium-5"></i>
                                        </div>
                                    </div>
                                    <p class="text-white mb-0 mt-2" style="font-weight: 600;">بازدیدهای این هفته</p>
                                </div>
                                <div class="card-content">
                                    <div id="line-area-chart-1"></div>
                                </div>
                            </div>

                            {{-- Weekly Visitors Chart --}}
                            <div class="chart-card">
                                <div class="card-header header-gradient-red d-flex flex-column align-items-center pb-0">
                                    <div class="avatar">
                                        <div class="avatar-content">
                                            <i class="feather icon-user text-white font-medium-5"></i>
                                        </div>
                                    </div>
                                    <p class="text-white mb-0 mt-2" style="font-weight: 600;">بازدیدکنندگان این هفته</p>
                                </div>
                                <div class="card-content">
                                    <div id="line-area-chart-3"></div>
                                </div>
                            </div>
                        @endcan

                        @can('comments.index')
                            {{-- Latest Reviews --}}
                            <div class="sidebar-card">
                                <div class="card-header header-gradient-green d-flex align-items-center justify-content-between">
                                    <h5 class="card-title text-white mb-0">
                                        <i class="feather icon-message-square mr-2"></i> آخرین دیدگاه‌ها
                                    </h5>
                                    <a href="{{ route('admin.reviews.index') }}" class="card-link text-white" style="font-size: 12px; font-weight: 600; text-decoration: none;">
                                        مشاهده همه <i class="fa fa-angle-left"></i>
                                    </a>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        @if(count($reviews))
                                            @foreach($reviews as $review)
                                                <li class="modern-list-item">
                                                    <div class="d-flex align-items-start flex-wrap">
                                                        <div class="d-flex align-items-center">
                                                            <a target="_blank" href="{{ route('admin.users.show', ['user' => $review->user]) }}" class="avatar flex-shrink-0 me-3">
                                                                <img width="40" src="{{ $review->user->imageUrl }}" alt="Avatar" class="rounded-circle avatar-bordered green">
                                                            </a>
                                                            <div class="me-2">
                                                                <h6 class="mb-0" style="font-size: 13px;">
                                                                    <a target="_blank" href="{{ route('admin.users.show', ['user' => $review->user]) }}" class="modern-link" style="color: #555;">
                                                                        {{ $review->user->first_name ? $review->user->fullname : $review->user->username }}
                                                                    </a>
                                                                </h6>
                                                                <small class="text-muted" style="font-size: 11px;">
                                                                    {{ jdate($review->created_at) }} ({{ jdate($review->created_at)->ago() }})
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="ms-auto w-100 text-right">
                                                            @if($review->status == 'pending')
                                                                <span class="status-text warning">منتظر تایید</span>
                                                            @elseif($review->status == 'accepted')
                                                                <span class="status-text success">منتشر شده</span>
                                                            @else
                                                                <span class="status-text danger">تایید نشده</span>
                                                            @endif
                                                        </div>
                                                        <div class="w-100 font-13 mt-2" style="color: #666; font-size: 13px; line-height: 1.6;">
                                                            {{ short_content($review->body, 20, false) }}
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        @else
                                            <li class="modern-list-item text-center py-4">
                                                <i class="feather icon-message-square fa-2x d-block mb-2" style="opacity: 0.4;"></i>
                                                <span style="color: #aaa;">هنوز دیدگاهی ثبت نشده است.</span>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>

                            {{-- Latest Questions --}}
                            <div class="sidebar-card">
                                <div class="card-header header-gradient-blue d-flex align-items-center justify-content-between">
                                    <h5 class="card-title text-white mb-0">
                                        <i class="feather icon-help-circle mr-2"></i> آخرین پرسش‌ها
                                    </h5>
                                    <a href="{{ route('admin.comments.products') }}" class="card-link text-white" style="font-size: 12px; font-weight: 600; text-decoration: none;">
                                        مشاهده همه <i class="fa fa-angle-left"></i>
                                    </a>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        @if(count($questions))
                                            @foreach($questions as $question)
                                                <li class="modern-list-item">
                                                    <div class="d-flex align-items-start flex-wrap">
                                                        <div class="d-flex align-items-center">
                                                            <a target="_blank" href="{{ route('admin.users.show', ['user' => $question->user]) }}" class="avatar flex-shrink-0 me-3">
                                                                <img width="40" src="{{ $question->user->imageUrl }}" alt="Avatar" class="rounded-circle avatar-bordered blue">
                                                            </a>
                                                            <div class="me-2">
                                                                <h6 class="mb-0" style="font-size: 13px;">
                                                                    <a target="_blank" href="{{ route('admin.users.show', ['user' => $question->user]) }}" class="modern-link" style="color: #555;">
                                                                        {{ $question->user->first_name ? $question->user->fullname : $question->user->username }}
                                                                    </a>
                                                                </h6>
                                                                <small class="text-muted" style="font-size: 11px;">
                                                                    {{ jdate($question->created_at) }} ({{ jdate($question->created_at)->ago() }})
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="ms-auto w-100 text-right">
                                                            @if($question->status == 'pending')
                                                                <span class="status-text warning">منتظر تایید</span>
                                                            @elseif($question->status == 'accepted')
                                                                <span class="status-text success">منتشر شده</span>
                                                            @else
                                                                <span class="status-text danger">تایید نشده</span>
                                                            @endif
                                                        </div>
                                                        <div class="w-100 font-13 mt-2" style="color: #666; font-size: 13px; line-height: 1.6;">
                                                            {{ $question->body }}
                                                            @if($question->product())
                                                                <div class="blockquote-footer mt-2" style="font-size: 12px;">
                                                                    <a target="_blank" href="{{ route('front.products.show', ['product' => $question->product()]) }}" class="modern-link blue">
                                                                        <i class="feather icon-external-link mr-1"></i> نمایش محصول
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        @else
                                            <li class="modern-list-item text-center py-4">
                                                <i class="feather icon-help-circle fa-2x d-block mb-2" style="opacity: 0.4;"></i>
                                                <span style="color: #aaa;">هنوز پرسشی ثبت نشده است.</span>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        @endcan

                        @can('statistics.users')
                            {{-- Active Sellers --}}
                            <div class="sidebar-card">
                                <div class="card-header header-gradient-orange d-flex align-items-center justify-content-between">
                                    <h5 class="card-title text-white mb-0">
                                        <i class="feather icon-store mr-2"></i> آخرین فروشندگان فعال
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        @if(count($active_sellers))
                                            @foreach($active_sellers as $active_seller)
                                                <li class="modern-list-item">
                                                    <div class="d-flex justify-content-between">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar flex-shrink-0 me-3">
                                                                <img width="40" src="{{ $active_seller->seller->imageUrl }}" alt="{{ $active_seller->seller->first_name ? $active_seller->seller->fullname : $active_seller->seller->username }}" class="rounded-circle avatar-bordered orange">
                                                            </div>
                                                            <div class="me-2">
                                                                <h6 class="mb-0" style="font-size: 13px; font-weight: 600;">{{ $active_seller->seller->business_name }}</h6>
                                                                <small class="text-muted" style="font-size: 11px;">
                                                                    @if(jdate($active_seller->created_at)->ago()=="0 ثانیه پیش" or jdate($active_seller->created_at)->ago()=="1 ثانیه پیش")
                                                                        <span class="online-badge">آنلاین</span>
                                                                    @else
                                                                        {{ jdate($active_seller->created_at)->ago() }}
                                                                    @endif
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="ms-auto">
                                                            <a target="_blank" href="{{ route('admin.sellers.show', ['seller' => $active_seller->seller]) }}" class="modern-btn modern-btn-orange">
                                                                <i class="fa fa-user m-0"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        @else
                                            <li class="text-center py-4 empty-state">
                                                <i class="feather icon-store fa-2x d-block mb-2"></i>
                                                <span>چیزی برای نمایش وجود ندارد.</span>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>

                            {{-- Active Users --}}
                            <div class="sidebar-card">
                                <div class="card-header header-gradient-purple d-flex align-items-center justify-content-between">
                                    <h5 class="card-title text-white mb-0">
                                        <i class="feather icon-users mr-2"></i> آخرین مشتریان فعال
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        @if(count($active_users))
                                            @foreach($active_users as $active_user)
                                                <li class="modern-list-item">
                                                    <div class="d-flex justify-content-between">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar flex-shrink-0 me-3">
                                                                <img width="40" src="{{ $active_user->user->imageUrl }}" alt="{{ $active_user->user->first_name ? $active_user->user->fullname : $active_user->user->username }}" class="rounded-circle avatar-bordered purple">
                                                            </div>
                                                            <div class="me-2">
                                                                <h6 class="mb-0" style="font-size: 13px; font-weight: 600;">{{ $active_user->user->first_name ? $active_user->user->fullname : $active_user->user->username }}</h6>
                                                                <small class="text-muted" style="font-size: 11px;">
                                                                    @if(jdate($active_user->created_at)->ago()=="0 ثانیه پیش" or jdate($active_user->created_at)->ago()=="1 ثانیه پیش")
                                                                        <span class="online-badge">آنلاین</span>
                                                                    @else
                                                                        {{ jdate($active_user->created_at)->ago() }}
                                                                    @endif
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="ms-auto">
                                                            <a target="_blank" href="{{ route('admin.users.show', ['user' => $active_user->user]) }}" class="modern-btn modern-btn-purple">
                                                                <i class="fa fa-user m-0"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        @else
                                            <li class="text-center py-4 empty-state">
                                                <i class="feather icon-users fa-2x d-block mb-2"></i>
                                                <span>چیزی برای نمایش وجود ندارد.</span>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        @endcan

                    </div>{{-- /col-md-4 --}}
                </div>{{-- /row --}}

            </div>{{-- /content-body --}}
        </div>{{-- /content-wrapper --}}
    </div>{{-- /app-content --}}

@endsection

@include('back.partials.plugins', ['plugins' => ['apexcharts', 'persian-datepicker']])

@push('styles')
    <link rel="stylesheet" href="{{ asset('back/assets/css/improved-dashboard.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('back/app-assets/vendors/js/charts/apexcharts.min.js') }}"></script>
    <script>
        @php
            $data   = viewers_data(7);
            $labels = array_keys($data);
            $views  = array_values($data);
        @endphp

        var viewerChartLabels = [{!! array_to_string($labels) !!}];
        var ViewerChartData   = [{!! array_to_string($views) !!}];

        @php
            $data   = ip_data(7);
            $labels = array_keys($data);
            $views  = array_values($data);
        @endphp

        var ipChartLabels = [{!! array_to_string($labels) !!}];
        var ipChartData   = [{!! array_to_string($views) !!}];
    </script>
    <script src="{{ asset('back/assets/js/pages/statistics/orders.js') }}?v=2"></script>
    <script src="{{ asset('back/assets/js/pages/dashboard-ecommerce.js') }}"></script>
@endpush
