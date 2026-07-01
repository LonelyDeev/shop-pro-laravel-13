@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/pages/users/show.css') }}">
    <style>
        :root {
            --seller-blue: #3b82f6;
            --seller-blue-light: #eff6ff;
            --seller-green: #10b981;
            --seller-green-light: #ecfdf5;
            --seller-amber: #f59e0b;
            --seller-amber-light: #fffbeb;
            --seller-purple: #8b5cf6;
            --seller-purple-light: #f5f3ff;
            --seller-red: #ef4444;
            --seller-red-light: #fef2f2;
            --seller-gray: #6b7280;
            --card-radius: 14px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04);
        }

        /* ===== Layout ===== */
        .seller-page { padding: 1.5rem; }

        /* ===== Breadcrumb ===== */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.75rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .page-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }
        .breadcrumb-row {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            color: #9ca3af;
            margin-top: 0.25rem;
        }
        .breadcrumb-row a { color: var(--seller-blue); text-decoration: none; }
        .breadcrumb-row span { color: #d1d5db; }

        /* ===== Action buttons ===== */
        .action-bar {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 1rem;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .btn-action:hover { opacity: 0.88; transform: translateY(-1px); }
        .btn-action:active { transform: translateY(0); }
        .btn-primary-action { background: var(--seller-blue); color: #fff; }
        .btn-success-action { background: var(--seller-green); color: #fff; }
        .btn-danger-action { background: var(--seller-red); color: #fff; }
        .btn-warning-action { background: var(--seller-amber); color: #fff; }

        /* ===== Stat cards ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.75rem;
        }
        .stat-card {
            background: #fff;
            border-radius: var(--card-radius);
            padding: 1.2rem 1.25rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f3f4f6;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            transition: box-shadow 0.2s;
        }
        .stat-card:hover { box-shadow: var(--shadow-md); }
        .stat-card.large { grid-column: span 2; }
        .stat-info { flex: 1; min-width: 0; }
        .stat-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: #9ca3af;
            margin-bottom: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .stat-value {
            font-size: 1.65rem;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .stat-link {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.75rem;
            color: var(--seller-blue);
            text-decoration: none;
            margin-top: 0.5rem;
            font-weight: 500;
        }
        .stat-link:hover { text-decoration: underline; }
        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.1rem;
        }
        .icon-blue  { background: var(--seller-blue-light);   color: var(--seller-blue); }
        .icon-green { background: var(--seller-green-light);  color: var(--seller-green); }
        .icon-amber { background: var(--seller-amber-light);  color: var(--seller-amber); }
        .icon-purple{ background: var(--seller-purple-light); color: var(--seller-purple); }

        /* ===== Status selects row ===== */
        .status-row {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            background: #f9fafb;
            border-radius: var(--card-radius);
            padding: 1.1rem 1.25rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e5e7eb;
        }
        .status-field label {
            display: block;
            font-size: 0.72rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .status-field select {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.4rem 0.75rem;
            font-size: 0.82rem;
            color: #374151;
            background: #fff;
            outline: none;
            transition: border-color 0.15s;
        }
        .status-field select:focus { border-color: var(--seller-blue); }

        /* ===== Main card ===== */
        .info-card {
            background: #fff;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid #f3f4f6;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .info-card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .info-card-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #111827;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .info-card-body { padding: 1.25rem 1.5rem; }

        /* ===== Vertical tabs ===== */
        .tabs-layout {
            display: flex;
            gap: 1.25rem;
        }
        .tabs-nav {
            width: 190px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .tabs-nav a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            font-size: 0.83rem;
            font-weight: 500;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.15s;
            border: 1px solid transparent;
        }
        .tabs-nav a:hover { background: #f3f4f6; color: #374151; }
        .tabs-nav a.active {
            background: var(--seller-blue-light);
            color: var(--seller-blue);
            border-color: #bfdbfe;
        }
        .tabs-nav a i { font-size: 0.9rem; width: 16px; text-align: center; }
        .tabs-content { flex: 1; min-width: 0; }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        /* ===== Form fields ===== */
        .form-section-title {
            font-size: 0.78rem;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin: 1.25rem 0 0.75rem;
            padding-bottom: 0.4rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .form-section-title:first-child { margin-top: 0; }
        .field-group { margin-bottom: 1rem; }
        .field-group label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.35rem;
        }
        .field-group input,
        .field-group select,
        .field-group textarea {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.5rem 0.85rem;
            font-size: 0.85rem;
            color: #111827;
            background: #fff;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .field-group input:focus,
        .field-group select:focus,
        .field-group textarea:focus {
            border-color: var(--seller-blue);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        .field-group input:disabled { background: #f9fafb; color: #9ca3af; }

        /* ===== Logo section ===== */
        .logo-section {
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 10px;
            border: 1px dashed #e5e7eb;
            margin-bottom: 1rem;
        }
        .logo-preview {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
            background: #fff;
        }
        .logo-upload-info { flex: 1; }
        .logo-upload-hint { font-size: 0.75rem; color: #9ca3af; margin-top: 0.4rem; }

        /* ===== Document image cards ===== */
        .doc-img-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            background: #f9fafb;
        }
        .doc-img-card img {
            width: 100%;
            max-height: 180px;
            object-fit: cover;
            background: #fff;
        }
        .doc-img-card .doc-footer {
            padding: 0.6rem 0.75rem;
            border-top: 1px solid #e5e7eb;
        }

        /* ===== Alert info ===== */
        .alert-info-custom {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 0.65rem 1rem;
            font-size: 0.8rem;
            color: #1d4ed8;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* ===== Data tables ===== */
        .data-table-wrap { overflow-x: auto; }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.83rem;
        }
        .data-table thead th {
            padding: 0.65rem 1rem;
            text-align: right;
            font-size: 0.72rem;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid #f3f4f6;
            background: #f9fafb;
            white-space: nowrap;
        }
        .data-table tbody td {
            padding: 0.75rem 1rem;
            color: #374151;
            border-bottom: 1px solid #f9fafb;
            vertical-align: middle;
        }
        .data-table tbody tr:last-child td { border-bottom: none; }
        .data-table tbody tr:hover td { background: #f9fafb; }
        .data-table .cell-center { text-align: center; }

        /* ===== Badges ===== */
        .badge-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.65rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            gap: 0.25rem;
        }
        .badge-success { background: var(--seller-green-light); color: #065f46; }
        .badge-warning { background: var(--seller-amber-light); color: #92400e; }
        .badge-danger  { background: var(--seller-red-light);   color: #991b1b; }
        .badge-info    { background: var(--seller-blue-light);  color: #1d4ed8; }

        /* ===== Product thumb ===== */
        .product-thumb {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
        }

        /* ===== Dropdown action ===== */
        .action-dropdown { position: relative; display: inline-block; }
        .action-dropdown-menu {
            position: absolute;
            left: 0;
            top: calc(100% + 4px);
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: var(--shadow-md);
            min-width: 150px;
            z-index: 100;
            display: none;
            overflow: hidden;
        }
        .action-dropdown:hover .action-dropdown-menu,
        .action-dropdown.open .action-dropdown-menu { display: block; }
        .action-dropdown-menu a,
        .action-dropdown-menu button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 0.9rem;
            font-size: 0.8rem;
            color: #374151;
            background: none;
            border: none;
            width: 100%;
            text-align: right;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.1s;
        }
        .action-dropdown-menu a:hover,
        .action-dropdown-menu button:hover { background: #f3f4f6; }
        .action-dropdown-menu .danger-item { color: var(--seller-red); }
        .action-dropdown-menu .divider { height: 1px; background: #f3f4f6; margin: 0.25rem 0; }
        .dropdown-toggle-btn {
            width: 32px; height: 32px;
            border-radius: 7px;
            border: 1px solid #e5e7eb;
            background: #fff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: #6b7280;
            font-size: 0.9rem;
            transition: all 0.15s;
        }
        .dropdown-toggle-btn:hover { background: #f3f4f6; border-color: #d1d5db; }

        /* ===== View row (last seen) ===== */
        .view-row td:first-child { direction: ltr; text-align: left; }

        /* ===== Card footer link ===== */
        .card-footer-link {
            display: flex;
            justify-content: flex-end;
            padding: 0.75rem 1.5rem;
            border-top: 1px solid #f3f4f6;
        }
        .card-footer-link a {
            font-size: 0.8rem;
            color: var(--seller-blue);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .card-footer-link a:hover { text-decoration: underline; }

        /* ===== Empty state ===== */
        .empty-state {
            text-align: center;
            padding: 2.5rem 1rem;
            color: #9ca3af;
        }
        .empty-state i { font-size: 2rem; margin-bottom: 0.75rem; display: block; }
        .empty-state p { font-size: 0.85rem; margin: 0; }

        /* ===== Carrier status ===== */
        .status-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            display: inline-block;
            margin-left: 5px;
        }
        .dot-active { background: var(--seller-green); }
        .dot-inactive { background: #d1d5db; }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .tabs-layout { flex-direction: column; }
            .tabs-nav { width: 100%; flex-direction: row; flex-wrap: wrap; }
            .stat-card.large { grid-column: span 1; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
@endpush

@section('content')
    @php
        $orderItem_ids = \App\Models\OrderItem::where('seller_id', $seller->id)->get();
        $order_ids = [];
        foreach ($orderItem_ids as $oi) { $order_ids[] = $oi->order_id; }
        $order_ids = array_unique($order_ids);
    @endphp

    <div class="app-content content seller-page">
        <div class="content-overlay"></div>
        <div class="content-wrapper">

            {{-- ===== Page header ===== --}}
            <div class="page-header">
                <div>
                    <h1 class="page-title">{{ $seller->seller_info->fullname }}</h1>
                    <div class="breadcrumb-row">
                        <span>مدیریت</span>
                        <span>&rsaquo;</span>
                        <a href="{{ route('admin.sellers.index') }}">فروشندگان</a>
                        <span>&rsaquo;</span>
                        <span style="color:#374151">{{ $seller->seller_info->fullname }}</span>
                    </div>
                </div>
                <div class="action-bar">
                    <a href="{{ route('admin.sellers.notification.create', ['seller' => $seller]) }}" class="btn-action btn-success-action">
                        <i class="fa-solid fa-bell"></i> ارسال اعلان
                    </a>
                    @can('sellers.update')
                        <button type="submit" form="seller-edit-form" class="btn-action btn-warning-action">
                            <i class="feather icon-save"></i> ذخیره اطلاعات
                        </button>
                    @endcan
                    @can('sellers.delete')
                        @if($seller->id != auth()->user()->id)
                            <button type="button" data-user="{{ $seller->id }}"
                                    class="btn-action btn-danger-action btn-user-delete"
                                    data-toggle="modal" data-target="#user-delete-modal">
                                <i class="fa-solid fa-trash"></i> حذف فروشنده
                            </button>
                        @endif
                    @endcan
                </div>
            </div>

            {{-- ===== Stats grid ===== --}}
            <div class="stats-grid">
                {{-- Wallet --}}
                <div class="stat-card large">
                    <div class="stat-info">
                        <div class="stat-label">موجودی کیف پول</div>
                        <div class="stat-value" title="{{ convert_number($seller->getWallet()->balance()) }}">
                            {{ number_format($seller->getWallet()->balance()) }} <small style="font-size:0.9rem;font-weight:500;color:#9ca3af">تومان</small>
                        </div>
                        <a href="{{ route('admin.wallets.show', ['wallet' => $seller->getWallet()]) }}" class="stat-link">
                            تاریخچه تراکنش‌ها <i class="fa fa-angle-left"></i>
                        </a>
                    </div>
                    <div class="stat-icon icon-blue"><i class="fa fa-credit-card"></i></div>
                </div>
                {{-- Orders value --}}
                @php $orders_sum = $seller->orders()->paid()->sum('price') @endphp
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-label">ارزش سفارشات موفق</div>
                        <div class="stat-value" title="{{ number_format($orders_sum) }} تومان">{{ formatPriceUnits($orders_sum) }}</div>
                        <a href="{{ route('admin.orders.index', ['username' => $seller->username]) }}" class="stat-link">مشاهده همه <i class="fa fa-angle-left"></i></a>
                    </div>
                    <div class="stat-icon icon-green"><i class="feather icon-briefcase"></i></div>
                </div>
                {{-- Today views --}}
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-label">بازدید امروز</div>
                        <div class="stat-value" title="کل: {{ $seller->views()->count() }}">{{ $seller->views()->whereDate('created_at', now())->count() }}</div>
                        <a href="{{ route('admin.sellers.views', ['seller' => $seller]) }}" class="stat-link">جزئیات <i class="fa fa-angle-left"></i></a>
                    </div>
                    <div class="stat-icon icon-amber"><i class="feather icon-eye"></i></div>
                </div>
                {{-- Order count --}}
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-label">تعداد سفارشات</div>
                        <div class="stat-value">{{ count($order_ids) }}</div>
                        <a href="{{ route('admin.sellers.seller_orders', ['seller' => $seller]) }}" class="stat-link">مشاهده همه <i class="fa fa-angle-left"></i></a>
                    </div>
                    <div class="stat-icon icon-purple"><i class="feather icon-shopping-bag"></i></div>
                </div>
                {{-- Products --}}
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-label">محصولات</div>
                        <div class="stat-value">{{ count($products) }}</div>
                        <a href="{{ route('admin.sellers.seller_products', ['seller' => $seller]) }}" class="stat-link">مشاهده همه <i class="fa fa-angle-left"></i></a>
                    </div>
                    <div class="stat-icon icon-amber"><i class="feather icon-shopping-cart"></i></div>
                </div>
                {{-- Variants --}}
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-label">تنوع کالا</div>
                        <div class="stat-value">{{ count($variants) }}</div>
                        <a href="{{ route('admin.sellers.seller_variants', ['seller' => $seller]) }}" class="stat-link">مشاهده همه <i class="fa fa-angle-left"></i></a>
                    </div>
                    <div class="stat-icon icon-blue"><i class="feather icon-layers"></i></div>
                </div>
                {{-- Notifications --}}
                <div class="stat-card">
                    <div class="stat-info">
                        <div class="stat-label">اعلان‌های ارسالی</div>
                        <div class="stat-value">{{ count($notifications) }}</div>
                        <a href="{{ route('admin.sellers.notifications', ['seller' => $seller]) }}" class="stat-link">مشاهده همه <i class="fa fa-angle-left"></i></a>
                    </div>
                    <div class="stat-icon icon-green"><i class="fa-regular fa-envelope"></i></div>
                </div>
            </div>

            {{-- ===== Edit form ===== --}}
            <form id="seller-edit-form"
                  action="{{ route('admin.sellers.update', ['seller' => $seller]) }}"
                  method="post"
                  data-redirect="{{ route('admin.sellers.index') }}"
                  enctype="multipart/form-data">
                @csrf
                @method('put')

                {{-- Status row --}}
                <div class="status-row">
                    <div class="status-field">
                        <label>وضعیت حساب</label>
                        <select name="status">
                            <option value="ACTIVE"   @selected($seller->status=="ACTIVE")>✅ فعال</option>
                            <option value="INACTIVE" @selected($seller->status=="INACTIVE")>⛔ غیر فعال</option>
                        </select>
                    </div>
                    <div class="status-field">
                        <label>وضعیت ثبت‌نام</label>
                        <select name="status_register">
                            <option value="business-details" @selected($seller->status_register=="business-details")>در حال تکمیل اطلاعات</option>
                            <option value="documents"        @selected($seller->status_register=="documents")>بارگذاری مدارک</option>
                            <option value="complete"         @selected($seller->status_register=="complete")>تکمیل ثبت‌نام</option>
                        </select>
                    </div>
                    <div class="status-field">
                        <label>وضعیت مدارک</label>
                        <select name="status_documents">
                            <option value="Accept"  @selected($seller->status_documents=="Accept")>✅ تأیید شده</option>
                            <option value="Waiting" @selected($seller->status_documents=="Waiting")>⏳ در انتظار</option>
                            <option value="Reject"  @selected($seller->status_documents=="Reject")>❌ رد شده</option>
                        </select>
                    </div>
                    <div class="status-field">
                        <label>وضعیت کاری</label>
                        <select name="status_work">
                            <option value="ACTIVE"      @selected($seller->status_work=="ACTIVE")>فعال</option>
                            <option value="EditProfile" @selected($seller->status_work=="EditProfile")>ویرایش اطلاعات</option>
                            <option value="Stop"        @selected($seller->status_work=="Stop")>متوقف</option>
                        </select>
                    </div>
                </div>

                {{-- ===== Main info card with tabs ===== --}}
                <div class="info-card">
                    <div class="info-card-header">
                        <h4 class="info-card-title"><i class="feather icon-user" style="color:var(--seller-blue)"></i> اطلاعات فروشنده</h4>
                    </div>
                    <div class="info-card-body">
                        <div class="tabs-layout">
                            {{-- Nav --}}
                            <nav class="tabs-nav" id="seller-tabs-nav">
                                <a href="#tab-info"     class="active" data-tab="tab-info">
                                    <i class="feather icon-user"></i> اطلاعات پایه
                                </a>
                                <a href="#tab-bank"     data-tab="tab-bank">
                                    <i class="feather icon-credit-card"></i> حساب بانکی
                                </a>
                                <a href="#tab-contact"  data-tab="tab-contact">
                                    <i class="feather icon-map-pin"></i> تماس و آدرس
                                </a>
                                <a href="#tab-contract" data-tab="tab-contract">
                                    <i class="feather icon-file-text"></i> قرارداد
                                </a>
                                <a href="#tab-docs"     data-tab="tab-docs">
                                    <i class="feather icon-paperclip"></i> مدارک
                                </a>
                                <a href="#tab-login"    data-tab="tab-login">
                                    <i class="feather icon-lock"></i> اطلاعات ورود
                                </a>
                                <a href="#tab-perf"     data-tab="tab-perf">
                                    <i class="feather icon-bar-chart-2"></i> عملکرد
                                </a>
                            </nav>

                            {{-- Content --}}
                            <div class="tabs-content">

                                {{-- TAB: اطلاعات پایه --}}
                                <div class="tab-pane active" id="tab-info">
                                    <div class="form-section-title">مشخصات هویتی</div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="field-group">
                                                <label>کد فروشنده</label>
                                                <input type="text" value="{{ $seller->id }}" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="field-group">
                                                <label>نوع فروشنده</label>
                                                <select name="private_business" id="private_business_select">
                                                    <option value="private"  @selected($seller->seller_info->private_business=="private")>حقیقی</option>
                                                    <option value="business" @selected($seller->seller_info->private_business=="business")>حقوقی</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="field-group">
                                                <label>نام تجاری</label>
                                                <input type="text" name="business_name" value="{{ $seller->seller_info->business_name }}">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Private fields --}}
                                    <div id="private-div" class="{{ $seller->seller_info->private_business=='business' ? 'd-none' : '' }}">
                                        <div class="form-section-title">اطلاعات شخصی</div>
                                        <div class="row">
                                            <div class="col-md-4"><div class="field-group"><label>نام</label><input type="text" name="first_name" value="{{ $seller->seller_info->first_name }}"></div></div>
                                            <div class="col-md-4"><div class="field-group"><label>نام خانوادگی</label><input type="text" name="last_name" value="{{ $seller->seller_info->last_name }}"></div></div>
                                            <div class="col-md-4">
                                                <div class="field-group">
                                                    <label>جنسیت</label>
                                                    <select name="gender">
                                                        <option value="male"   @selected($seller->seller_info->gender=="male")>مرد</option>
                                                        <option value="female" @selected($seller->seller_info->gender=="female")>زن</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4"><div class="field-group"><label>تاریخ تولد</label><input type="text" name="birth_day" value="{{ $seller->seller_info->birth_day }}"></div></div>
                                            <div class="col-md-4"><div class="field-group"><label>کد ملی</label><input type="number" name="national_identity_number" value="{{ $seller->seller_info->national_identity_number }}"></div></div>
                                            <div class="col-md-4"><div class="field-group"><label>شماره شناسنامه</label><input type="number" name="identity_card_number" value="{{ $seller->seller_info->identity_card_number }}"></div></div>
                                        </div>
                                    </div>

                                    {{-- Business fields --}}
                                    <div id="business-div" class="{{ $seller->seller_info->private_business=='private' ? 'd-none' : '' }}">
                                        <div class="form-section-title">اطلاعات شرکت</div>
                                        <div class="row">
                                            <div class="col-md-4"><div class="field-group"><label>نام شرکت</label><input type="text" name="company_name" value="{{ $seller->seller_info->company_name }}"></div></div>
                                            <div class="col-md-4">
                                                <div class="field-group">
                                                    <label>نوع شرکت</label>
                                                    <select name="company_type">
                                                        <option value="public"      @selected($seller->seller_info->company_type=="public")>سهامی عام</option>
                                                        <option value="joint_stock" @selected($seller->seller_info->company_type=="joint_stock")>سهامی خاص</option>
                                                        <option value="ltd"         @selected($seller->seller_info->company_type=="ltd")>مسئولیت محدود</option>
                                                        <option value="coop"        @selected($seller->seller_info->company_type=="coop")>تعاونی</option>
                                                        <option value="solidarity"  @selected($seller->seller_info->company_type=="solidarity")>تضامنی</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4"><div class="field-group"><label>شماره ثبت</label><input type="text" name="company_registration_number" value="{{ $seller->seller_info->company_registration_number }}"></div></div>
                                            <div class="col-md-4"><div class="field-group"><label>شناسه ملی</label><input type="text" name="company_national_identity_number" value="{{ $seller->seller_info->company_national_identity_number }}"></div></div>
                                            <div class="col-md-4"><div class="field-group"><label>کد اقتصادی</label><input type="text" name="company_economic_number" value="{{ $seller->seller_info->company_economic_number }}"></div></div>
                                        </div>
                                    </div>

                                    <div class="form-section-title">کالا و فروشگاه</div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label>دسته‌بندی اصلی کالا</label>
                                                <select name="main_supply_category_id">
                                                    <option value="">انتخاب کنید</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}"
                                                                data-pup="{{ $category->category_id }}"
                                                            @selected($seller->seller_info && $seller->seller_info->main_supply_category_id == $category->id)>
                                                            {{ $category->title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label>تعداد تقریبی تنوع کالا</label>
                                                <select name="number_of_products">
                                                    <option value="">انتخاب کنید</option>
                                                    @foreach(['10'=>'۱–۱۰','50'=>'۱۱–۵۰','100'=>'۵۱–۱۰۰','300'=>'۱۰۱–۳۰۰','1000'=>'۳۰۱–۱۰۰۰','3000'=>'۱۰۰۱–۳۰۰۰','10000'=>'۳۰۰۱–۱۰۰۰۰','30000'=>'۱۰۰۰۱–۳۰۰۰۰'] as $val => $label)
                                                        <option value="{{ $val }}" @selected($seller->seller_info && $seller->seller_info->number_of_products==$val)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-section-title">لوگو و معرفی</div>
                                    <div class="logo-section">
                                        <img src="{{ $seller->seller_info->logo ? asset($seller->seller_info->logo) : asset('/empty.svg') }}"
                                             class="logo-preview" alt="logo">
                                        <div class="logo-upload-info">
                                            <div class="field-group" style="margin-bottom:0.5rem">
                                                <label>تغییر لوگو</label>
                                                <input type="file" accept="image/*" name="image">
                                            </div>
                                            <p class="logo-upload-hint">بهترین اندازه <strong>600×600</strong> پیکسل — فرمت JPG یا PNG</p>
                                        </div>
                                    </div>
                                    <div class="field-group">
                                        <label>درباره فروشنده</label>
                                        <textarea name="bio" rows="3">{{ $seller->seller_info->bio }}</textarea>
                                    </div>
                                </div>

                                {{-- TAB: حساب بانکی --}}
                                <div class="tab-pane" id="tab-bank">
                                    <div class="form-section-title">اطلاعات بانکی</div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label>شماره شبا</label>
                                                <input type="number" name="shaba_number" value="{{ $seller->seller_info->shaba_number }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label>صاحب حساب</label>
                                                <input type="text" value="{{ $seller->seller_info->full_name }}" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- TAB: تماس و آدرس --}}
                                <div class="tab-pane" id="tab-contact">
                                    <div class="form-section-title">اطلاعات تماس</div>
                                    <div class="row">
                                        <div class="col-md-4"><div class="field-group"><label>ایمیل</label><input type="email" name="email" value="{{ $seller->email }}"></div></div>
                                        <div class="col-md-4"><div class="field-group"><label>تلفن همراه</label><input type="number" name="mobile" value="{{ $seller->seller_info->mobile }}"></div></div>
                                        <div class="col-md-4"><div class="field-group"><label>تلفن ثابت</label><input type="number" name="phone" value="{{ $seller->seller_info->phone }}"></div></div>
                                        <div class="col-md-4"><div class="field-group"><label>وب‌سایت</label><input type="text" name="website" value="{{ $seller->seller_info->website }}"></div></div>
                                    </div>
                                    <div class="form-section-title">آدرس</div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="field-group">
                                                <label>استان</label>
                                                <select id="province" data-action="{{ route('provinces.get-cities') }}" name="state_id">
                                                    <option value="">انتخاب کنید</option>
                                                    @foreach ($provinces as $province)
                                                        <option value="{{ $province->id }}" @selected($seller->seller_info->state_id == $province->id)>{{ $province->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="field-group">
                                                <label>شهر</label>
                                                <select id="city" name="city_id">
                                                    @foreach ($seller->seller_info->province->cities as $city)
                                                        <option value="{{ $city->id }}" @selected($seller->seller_info->city_id == $city->id)>{{ $city->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4"><div class="field-group"><label>کد پستی</label><input type="number" name="post_code" value="{{ $seller->seller_info->post_code }}"></div></div>
                                        <div class="col-md-12"><div class="field-group"><label>آدرس کامل</label><input type="text" name="address" value="{{ $seller->seller_info->address }}"></div></div>
                                        <div class="col-md-6"><div class="field-group"><label>موقعیت مکانی</label><input type="text" name="location" value="{{ $seller->seller_info->location }}"></div></div>
                                    </div>
                                </div>

                                {{-- TAB: قرارداد --}}
                                <div class="tab-pane" id="tab-contract">
                                    <div class="empty-state">
                                        <i class="feather icon-file-text"></i>
                                        <p>اطلاعات قرارداد هنوز ثبت نشده است.</p>
                                    </div>
                                </div>

                                {{-- TAB: مدارک --}}
                                <div class="tab-pane" id="tab-docs">
                                    <div class="form-section-title">تصاویر مدارک هویتی</div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="doc-img-card">
                                                <img src="{{ $seller->seller_info->card_image ? asset($seller->seller_info->card_image) : asset('/empty.svg') }}" alt="کارت ملی">
                                                <div class="doc-footer">
                                                    <label style="font-size:0.78rem;font-weight:600;color:#374151;display:block;margin-bottom:0.35rem">روی کارت ملی</label>
                                                    <input type="file" accept="image/*" name="card_image">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="doc-img-card">
                                                <img src="{{ $seller->seller_info->card_image_back ? asset($seller->seller_info->card_image_back) : asset('/empty.svg') }}" alt="پشت کارت ملی">
                                                <div class="doc-footer">
                                                    <label style="font-size:0.78rem;font-weight:600;color:#374151;display:block;margin-bottom:0.35rem">پشت کارت ملی</label>
                                                    <input type="file" accept="image/*" name="card_image_back">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-section-title" style="margin-top:1.5rem">مالیات بر ارزش افزوده</div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="field-group">
                                                <label>مشمول مالیات ارزش افزوده</label>
                                                <select name="vat_free" id="vat_free_select">
                                                    <option value="1" @selected($seller->seller_info->vat_free=="1")>بله</option>
                                                    <option value="2" @selected($seller->seller_info->vat_free=="2")>خیر</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-8" id="vat_free_div" style="{{ $seller->seller_info->vat_free=='2' ? 'display:none' : '' }}">
                                            <div class="doc-img-card">
                                                <img src="{{ $seller->seller_info->vat_image ? asset($seller->seller_info->vat_image) : asset('/empty.svg') }}" alt="گواهی ارزش افزوده">
                                                <div class="doc-footer">
                                                    <label style="font-size:0.78rem;font-weight:600;color:#374151;display:block;margin-bottom:0.35rem">گواهی ارزش افزوده</label>
                                                    <input type="file" accept="image/*" name="vat_image">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- TAB: اطلاعات ورود --}}
                                <div class="tab-pane" id="tab-login">
                                    <div class="form-section-title">تغییر رمز عبور</div>
                                    <div class="row">
                                        <div class="col-md-6"><div class="field-group"><label>رمز عبور جدید</label><input type="password" name="password"></div></div>
                                        <div class="col-md-6"><div class="field-group"><label>تکرار رمز عبور</label><input type="password" name="password_confirmation"></div></div>
                                        <div class="col-12">
                                            <div class="alert-info-custom">
                                                <i class="feather icon-info"></i>
                                                در صورت عدم تغییر رمز، فیلدها را خالی بگذارید.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- TAB: عملکرد --}}
                                <div class="tab-pane" id="tab-perf">
                                    <div class="form-section-title">شاخص‌های عملکرد</div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label>امتیاز عملکرد (از ۵)</label>
                                                <select name="operation">
                                                    <option value="">انتخاب کنید</option>
                                                    @for($i=0.5; $i<=5; $i+=0.5)
                                                        <option value="{{ $i }}" @selected($seller->seller_info->operation==$i)>{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-group">
                                                <label>رضایت از کالا (درصد)</label>
                                                <input type="number" name="satisfaction" value="{{ $seller->seller_info->satisfaction }}" max="100" min="0">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="alert-info-custom">
                                                <i class="feather icon-info"></i>
                                                اگر خالی بماند، فروشنده به‌عنوان «تازه‌وارد» نمایش داده می‌شود.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>{{-- /.tabs-content --}}
                        </div>{{-- /.tabs-layout --}}
                    </div>
                </div>

            </form>

            {{-- ===== Notifications table ===== --}}
            <div class="info-card">
                <div class="info-card-header">
                    <h4 class="info-card-title"><i class="fa-regular fa-envelope" style="color:var(--seller-green)"></i> آخرین اعلان‌های ارسالی</h4>
                </div>
                @if(count($notifications))
                    <div class="data-table-wrap">
                        <table class="data-table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>عنوان</th>
                                <th>متن پیام</th>
                                <th>اولویت</th>
                                <th class="cell-center">خوانده شده</th>
                                <th class="cell-center">عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($notifications as $notification)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td style="font-weight:500">{{ $notification->title }}</td>
                                    <td style="color:#6b7280;max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{!! $notification->message !!}</td>
                                    <td>
                                        @php $p = $notification->priorityText(); @endphp
                                        <span class="badge-pill badge-{{ $p['color'] }}">{{ $p['title'] }}</span>
                                    </td>
                                    <td class="cell-center">
                                        @php $read = \Illuminate\Support\Facades\DB::table('notification_manage_users')
                                        ->where(['notification_manage_id'=>$notification->id,'seller_id'=>$seller->id])
                                        ->first()->read; @endphp
                                        @if($read)
                                            <span class="badge-pill badge-success"><i class="fa-regular fa-eye"></i> خوانده شد</span>
                                        @else
                                            <span style="color:#d1d5db;font-size:0.8rem">—</span>
                                        @endif
                                    </td>
                                    <td class="cell-center">
                                        <div class="action-dropdown">
                                            <button class="dropdown-toggle-btn" onclick="this.closest('.action-dropdown').classList.toggle('open')">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <div class="action-dropdown-menu">
                                                <a href="{{ route('admin.sellers.notification.show', ['seller'=>$seller,'notification'=>$notification]) }}">
                                                    <i class="fa-solid fa-pencil"></i> ویرایش
                                                </a>
                                                <div class="divider"></div>
                                                <button class="danger-item btn-delete-ticket"
                                                        data-action="{{ route('admin.notifications.destroy', ['notification'=>$notification]) }}"
                                                        data-toggle="modal" data-target="#delete-ticket-modal">
                                                    <i class="fa-solid fa-trash-can"></i> حذف
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer-link">
                        <a href="{{ route('admin.sellers.notifications', ['seller'=>$seller]) }}">مشاهده همه اعلان‌ها <i class="fa fa-angle-left"></i></a>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fa-regular fa-envelope" style="color:#d1d5db"></i>
                        <p>هنوز اعلانی ارسال نشده است.</p>
                    </div>
                @endif
            </div>

            {{-- ===== Orders table ===== --}}
            @if(count($order_ids))
                <div class="info-card">
                    <div class="info-card-header">
                        <h4 class="info-card-title"><i class="feather icon-shopping-bag" style="color:var(--seller-purple)"></i> آخرین سفارشات</h4>
                    </div>
                    <div class="data-table-wrap">
                        <table class="data-table">
                            <thead>
                            <tr>
                                <th>شماره سفارش</th>
                                <th>تاریخ</th>
                                <th>مبلغ</th>
                                <th>وضعیت</th>
                                <th class="cell-center">عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td style="font-weight:600;color:var(--seller-blue)">#{{ $order->id }}</td>
                                    <td title="{{ jdate($order->created_at) }}">{{ jdate($order->created_at)->ago() }}</td>
                                    <td title="{{ convert_number($order->priceSeller($seller->id)) }} تومان">{{ number_format($order->priceSeller($seller->id)) }} <small style="color:#9ca3af">تومان</small></td>
                                    <td><span class="badge-pill badge-info">{{ $order->statusText() }}</span></td>
                                    <td class="cell-center">
                                        <a href="{{ route('admin.sellers.orders.show', ['order'=>$order,'seller'=>$seller]) }}"
                                           class="btn-action btn-primary-action" style="font-size:0.76rem;padding:0.3rem 0.7rem">
                                            مشاهده
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer-link">
                        <a href="{{ route('admin.sellers.seller_orders', ['seller'=>$seller->id]) }}">مشاهده همه سفارشات <i class="fa fa-angle-left"></i></a>
                    </div>
                </div>
            @endif

            {{-- ===== Products table ===== --}}
            <div class="info-card">
                <div class="info-card-header">
                    <h4 class="info-card-title"><i class="feather icon-shopping-cart" style="color:var(--seller-amber)"></i> آخرین محصولات</h4>
                </div>
                @if(count($products))
                    <div class="data-table-wrap">
                        <table class="data-table">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>تصویر</th>
                                <th>عنوان محصول</th>
                                <th>تاریخ ایجاد</th>
                                <th class="cell-center">موجودی</th>
                                <th>وضعیت</th>
                                <th class="cell-center">عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td style="color:#9ca3af;font-size:0.78rem">#{{ $product->id }}</td>
                                    <td><img class="product-thumb" src="{{ $product->image ? asset($product->image) : asset('/empty.svg') }}" alt="{{ $product->title }}"></td>
                                    <td style="font-weight:500">{{ $product->title }}</td>
                                    <td style="color:#6b7280;font-size:0.8rem">{{ jdate($product->created_at)->format('%d %B %Y') }}</td>
                                    <td class="cell-center">{{ $product->prices()->sum('stock') }}</td>
                                    <td>
                                        <div style="display:flex;flex-wrap:wrap;gap:4px">
                                            @if($product->isPublished())
                                                <span class="badge-pill badge-success">منتشر شده</span>
                                            @else
                                                <span class="badge-pill badge-danger">پیش‌نویس</span>
                                            @endif
                                            @if($product->status=="Accept")
                                                <span class="badge-pill badge-success">تأیید شده</span>
                                            @elseif($product->status=="Waiting")
                                                <span class="badge-pill badge-warning">در انتظار</span>
                                            @elseif($product->status=="Reject")
                                                <span class="badge-pill badge-danger">رد شده</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="cell-center">
                                        <div class="action-dropdown">
                                            <button class="dropdown-toggle-btn" onclick="this.closest('.action-dropdown').classList.toggle('open')">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <div class="action-dropdown-menu">
                                                <a href="{{ Route::has('front.products.show') ? route('front.products.show', ['product'=>$product]) : '#' }}" target="_blank">
                                                    <i class="fa-regular fa-eye"></i> نمایش
                                                </a>
                                                <div class="divider"></div>
                                                <a href="{{ route('admin.products.edit', ['product'=>$product]) }}">
                                                    <i class="fa-solid fa-pencil"></i> ویرایش
                                                </a>
                                                <div class="divider"></div>
                                                <button class="danger-item btn-delete"
                                                        data-action="{{ route('admin.products.destroy', ['product'=>$product]) }}"
                                                        data-toggle="modal" data-target="#delete-modal-product">
                                                    <i class="fa-solid fa-trash-can"></i> حذف
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer-link">
                        <a href="{{ route('admin.sellers.seller_products', ['seller'=>$seller]) }}">مشاهده همه محصولات <i class="fa fa-angle-left"></i></a>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="feather icon-shopping-cart" style="color:#d1d5db"></i>
                        <p>هنوز محصولی ثبت نشده است.</p>
                    </div>
                @endif
            </div>

            {{-- ===== Variants table ===== --}}
            <div class="info-card">
                <div class="info-card-header">
                    <h4 class="info-card-title"><i class="feather icon-layers" style="color:var(--seller-blue)"></i> آخرین تنوع‌ها</h4>
                </div>
                @if(count($variants))
                    <div class="data-table-wrap">
                        <table class="data-table">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>تصویر</th>
                                <th>محصول</th>
                                <th>تاریخ</th>
                                <th class="cell-center">موجودی</th>
                                <th>وضعیت</th>
                                <th class="cell-center">عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($variants as $variant)
                                @php $product = \App\Models\Product::find($variant->product_id); @endphp
                                @if($product)
                                    <tr>
                                        <td style="color:#9ca3af;font-size:0.78rem">#{{ $product->id }}</td>
                                        <td><img class="product-thumb" src="{{ $product->image ? asset($product->image) : asset('/empty.svg') }}" alt="{{ $product->title }}"></td>
                                        <td style="font-weight:500">{{ $product->title }}</td>
                                        <td style="color:#6b7280;font-size:0.8rem">{{ jdate($product->created_at)->format('%d %B %Y') }}</td>
                                        <td class="cell-center">{{ $product->prices()->sum('stock') }}</td>
                                        <td>
                                            <div style="display:flex;flex-wrap:wrap;gap:4px">
                                                @if($product->isPublished())
                                                    <span class="badge-pill badge-success">منتشر شده</span>
                                                @else
                                                    <span class="badge-pill badge-danger">پیش‌نویس</span>
                                                @endif
                                                @if($product->status=="Accept")
                                                    <span class="badge-pill badge-success">تأیید شده</span>
                                                @elseif($product->status=="Waiting")
                                                    <span class="badge-pill badge-warning">در انتظار</span>
                                                @elseif($product->status=="Reject")
                                                    <span class="badge-pill badge-danger">رد شده</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="cell-center">
                                            <div class="action-dropdown">
                                                <button class="dropdown-toggle-btn" onclick="this.closest('.action-dropdown').classList.toggle('open')">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                                <div class="action-dropdown-menu">
                                                    <a href="{{ Route::has('front.products.show') ? route('front.products.show', ['product'=>$product]) : '#' }}" target="_blank">
                                                        <i class="fa-regular fa-eye"></i> نمایش
                                                    </a>
                                                    <div class="divider"></div>
                                                    <a href="{{ route('admin.products.edit', ['product'=>$product]) }}">
                                                        <i class="fa-solid fa-pencil"></i> ویرایش
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer-link">
                        <a href="{{ route('admin.sellers.seller_variants', ['seller'=>$seller]) }}">مشاهده همه تنوع‌ها <i class="fa fa-angle-left"></i></a>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="feather icon-layers" style="color:#d1d5db"></i>
                        <p>هنوز تنوعی ثبت نشده است.</p>
                    </div>
                @endif
            </div>

            {{-- ===== Views table ===== --}}
            <div class="info-card">
                <div class="info-card-header">
                    <h4 class="info-card-title"><i class="feather icon-eye" style="color:var(--seller-amber)"></i> آخرین بازدیدها</h4>
                </div>
                @if($seller->views()->count())
                    <div class="data-table-wrap">
                        <table class="data-table">
                            <thead>
                            <tr>
                                <th>تاریخ</th>
                                <th>IP</th>
                                <th>پلتفرم</th>
                                <th>صفحه</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($seller->views()->latest()->take(10)->get() as $view)
                                <tr class="view-row">
                                    <td>{{ jdate($view->created_at) }}</td>
                                    <td style="font-family:monospace;font-size:0.8rem;color:#6b7280">{{ $view->ip }}</td>
                                    <td>{{ get_option_property($view->options, 'platform') }}</td>
                                    <td style="direction:ltr;text-align:left;font-size:0.78rem">
                                        <a href="{{ url(urldecode($view->path)) }}" target="_blank" style="color:var(--seller-blue);text-decoration:none">{{ Str::limit(urldecode($view->path), 60) }}</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer-link">
                        <a href="{{ route('admin.sellers.views', ['seller'=>$seller]) }}">مشاهده همه بازدیدها <i class="fa fa-angle-left"></i></a>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="feather icon-eye" style="color:#d1d5db"></i>
                        <p>هنوز بازدیدی ثبت نشده است.</p>
                    </div>
                @endif
            </div>

            {{-- ===== Carriers table ===== --}}
            <div class="info-card">
                <div class="info-card-header">
                    <h4 class="info-card-title"><i class="feather icon-truck" style="color:var(--seller-green)"></i> روش‌های ارسال</h4>
                </div>
                @if($sellerCarriers->count())
                    <div class="data-table-wrap">
                        <table class="data-table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>عنوان</th>
                                <th>شهر فروشگاه</th>
                                <th>شهرهای تحت پوشش</th>
                                <th>پس‌کرایه</th>
                                <th class="cell-center">وضعیت</th>
                                <th class="cell-center">عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($sellerCarriers as $carrier)
                                <tr>
                                    <td style="color:#9ca3af">{{ $loop->iteration }}</td>
                                    <td style="font-weight:500">{{ $carrier->title }}</td>
                                    <td style="font-size:0.8rem">{{ $carrier->province->name }} — {{ $carrier->city->name }}</td>
                                    <td>
                                        @if($carrier->covered_cities == 'all')
                                            <span class="badge-pill badge-info">همه شهرها</span>
                                        @else
                                            <a href="{{ route('admin.carriers.cities', ['carrier'=>$carrier]) }}" class="carrier-cities-show" style="font-size:0.8rem;color:var(--seller-blue)">مشاهده لیست</a>
                                        @endif
                                    </td>
                                    <td>
                                        @if($carrier->carrige_forward)
                                            <span class="badge-pill badge-warning">بله</span>
                                        @else
                                            <span style="color:#9ca3af;font-size:0.8rem">خیر</span>
                                        @endif
                                    </td>
                                    <td class="cell-center">
                                        @if($carrier->is_active)
                                            <span class="status-dot dot-active"></span><span style="font-size:0.78rem;color:#065f46">فعال</span>
                                        @else
                                            <span class="status-dot dot-inactive"></span><span style="font-size:0.78rem;color:#9ca3af">غیر فعال</span>
                                        @endif
                                    </td>
                                    <td class="cell-center">
                                        <div class="action-dropdown">
                                            <button class="dropdown-toggle-btn" onclick="this.closest('.action-dropdown').classList.toggle('open')">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <div class="action-dropdown-menu">
                                                @if($carrier->carrige_forward)
                                                    <button><i class="fa-solid fa-bars"></i> تعرفه‌ها</button>
                                                @else
                                                    <a href="{{ route('admin.tariffs.index', ['carrier'=>$carrier]) }}"><i class="fa-solid fa-bars"></i> تعرفه‌ها</a>
                                                @endif
                                                <div class="divider"></div>
                                                <a href="{{ route('admin.carriers.edit', ['carrier'=>$carrier]) }}"><i class="fa-solid fa-pencil"></i> ویرایش</a>
                                                <div class="divider"></div>
                                                <button class="danger-item btn-delete"
                                                        data-action="{{ route('admin.carriers.destroy', ['carrier'=>$carrier]) }}"
                                                        data-toggle="modal" data-target="#delete-modal">
                                                    <i class="fa-solid fa-trash-can"></i> حذف
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer-link">{{ $sellerCarriers->links() }}</div>
                @else
                    <div class="empty-state">
                        <i class="feather icon-truck" style="color:#d1d5db"></i>
                        <p>هنوز روش ارسالی تعریف نشده است.</p>
                    </div>
                @endif
            </div>

        </div>{{-- /.content-wrapper --}}
    </div>

    {{-- ===== Modals ===== --}}
    <div class="modal fade text-left" id="user-delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm"><div class="modal-content">
                <div class="modal-header"><h4 class="modal-title">حذف فروشنده</h4>
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button></div>
                <div class="modal-body">با حذف فروشنده تمام داده‌های مرتبط از بین می‌رود.</div>
                <div class="modal-footer">
                    <form action="#" id="user-delete-form">@csrf @method('delete')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-danger">بله، حذف شود</button>
                    </form>
                </div>
            </div></div>
    </div>

    <div class="modal fade text-left" id="delete-ticket-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content">
                <div class="modal-header"><h4 class="modal-title">حذف اعلان</h4>
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button></div>
                <div class="modal-body">این اعلان پس از حذف قابل بازیابی نخواهد بود.</div>
                <div class="modal-footer">
                    <form action="#" id="ticket-delete-form">@csrf @method('delete')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-danger">بله، حذف شود</button>
                    </form>
                </div>
            </div></div>
    </div>

    <div class="modal fade text-left" id="delete-modal-product" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content">
                <div class="modal-header"><h4 class="modal-title">حذف محصول</h4>
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button></div>
                <div class="modal-body">محصول پس از حذف قابل بازیابی نیست.</div>
                <div class="modal-footer">
                    <form action="#" id="product-delete-form">@csrf @method('delete')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-danger">بله، حذف شود</button>
                    </form>
                </div>
            </div></div>
    </div>

    <div class="modal fade text-left" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm"><div class="modal-content">
                <div class="modal-header"><h4 class="modal-title">حذف روش ارسال</h4>
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button></div>
                <div class="modal-body">روش ارسال پس از حذف قابل بازیابی نیست.</div>
                <div class="modal-footer">
                    <form action="#" id="carrier-delete-form">@csrf @method('delete')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-danger">بله، حذف شود</button>
                    </form>
                </div>
            </div></div>
    </div>

@endsection

@include('back.partials.plugins', ['plugins' => ['jquery-tagsinput','jquery-ui','jquery.validate']])

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/sellers/show.js') }}"></script>
    <script>
        (function() {
            // ===== Tab switching =====
            var nav = document.getElementById('seller-tabs-nav');
            if (nav) {
                nav.querySelectorAll('a[data-tab]').forEach(function(link) {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        nav.querySelectorAll('a').forEach(function(a) { a.classList.remove('active'); });
                        document.querySelectorAll('.tab-pane').forEach(function(p) { p.classList.remove('active'); });
                        link.classList.add('active');
                        var target = document.getElementById(link.getAttribute('data-tab'));
                        if (target) target.classList.add('active');
                    });
                });
            }

            // ===== Private/Business toggle =====
            var typeSelect = document.getElementById('private_business_select');
            if (typeSelect) {
                typeSelect.addEventListener('change', function() {
                    var isPrivate = this.value === 'private';
                    document.getElementById('private-div').classList.toggle('d-none', !isPrivate);
                    document.getElementById('business-div').classList.toggle('d-none', isPrivate);
                });
            }

            // ===== VAT toggle =====
            var vatSelect = document.getElementById('vat_free_select');
            if (vatSelect) {
                vatSelect.addEventListener('change', function() {
                    var div = document.getElementById('vat_free_div');
                    if (div) div.style.display = this.value === '2' ? 'none' : '';
                });
            }

            // ===== Close dropdowns on outside click =====
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.action-dropdown')) {
                    document.querySelectorAll('.action-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
                }
            });
        })();
    </script>
@endpush
