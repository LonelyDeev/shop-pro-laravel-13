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
                                    <li class="breadcrumb-item active">انبارها</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-md-right col-md-3 col-12 d-md-block d-none">
                    <a href="{{ route('admin.warehouses.create') }}" class="btn btn-primary">
                        <i class="feather icon-plus"></i> ایجاد انبار جدید
                    </a>
                </div>
            </div>

            <div class="content-body">
                {{-- کارت‌های آماری --}}
                <div class="row">
                    <div class="col-xl-3 col-md-4 col-sm-6 mb-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="feather icon-home fa-2x text-primary mb-2"></i>
                                <h4>{{ number_format($stats['total']) }}</h4>
                                <p class="mb-0">کل انبارها</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-4 col-sm-6 mb-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="feather icon-check-circle fa-2x text-success mb-2"></i>
                                <h4>{{ number_format($stats['active']) }}</h4>
                                <p class="mb-0">انبار فعال</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-4 col-sm-6 mb-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="feather icon-box fa-2x text-info mb-2"></i>
                                <h4>{{ number_format($stats['total_products']) }}</h4>
                                <p class="mb-0">محصول منحصر به فرد</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-4 col-sm-6 mb-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="feather icon-package fa-2x text-warning mb-2"></i>
                                <h4>{{ number_format($stats['total_stock']) }}</h4>
                                <p class="mb-0">کل موجودی</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- فیلترها --}}
                <div class="card">
                    <div class="card-header filter-card">
                        <h4 class="card-title">فیلتر کردن</h4>
                        <div class="heading-elements">
                            <ul class="list-inline mb-0">
                                <li><a data-action="collapse"><i class="feather icon-chevron-down"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-content collapse">
                        <div class="card-body">
                            <form method="GET" id="filter-form">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>جستجو</label>
                                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="نام یا کد انبار">
                                    </div>
                                    <div class="col-md-3">
                                        <label>نوع انبار</label>
                                        <select name="type" class="form-control">
                                            <option value="all">همه</option>
                                            <option value="main" {{ request('type') == 'main' ? 'selected' : '' }}>اصلی</option>
                                            <option value="seller" {{ request('type') == 'seller' ? 'selected' : '' }}>فروشندگان</option>
                                            <option value="temp" {{ request('type') == 'temp' ? 'selected' : '' }}>موقت</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>وضعیت</label>
                                        <select name="status" class="form-control">
                                            <option value="all">همه</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>فعال</option>
                                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-outline-success form-control">فیلتر</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- لیست انبارها --}}
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">لیست انبارها</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>کد انبار</th>
                                        <th>نام انبار</th>
                                        <th>نوع</th>
                                        <th>تعداد محصول</th>
                                        <th>آدرس</th>
                                        <th>وضعیت</th>
                                        <th>عملیات</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($warehouses as $warehouse)
                                        <tr>
                                            <td>
                                                <code>{{ $warehouse->code }}</code>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="fw-bold">
                                                    {{ $warehouse->name }}
                                                </a>
                                            </td>
                                            <td>
                                                @if($warehouse->type == 'main')
                                                    <span class="badge bg-primary">اصلی</span>
                                                @elseif($warehouse->type == 'seller')
                                                    <span class="badge bg-info">فروشنده</span>
                                                @else
                                                    <span class="badge bg-secondary">موقت</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format(count($warehouse->products)) }}</td>
                                            <td>
                                                <small class="text-muted">{{ Str::limit($warehouse->address, 40) }}</small>
                                            </td>
                                            <td>
                                                @if($warehouse->is_active)
                                                    <span class="badge bg-success">فعال</span>
                                                @else
                                                    <span class="badge bg-danger">غیرفعال</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown dropdown-action">
                                                    <button class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">
                                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('admin.warehouses.show', $warehouse) }}">
                                                            <i class="feather icon-eye"></i> مشاهده
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('admin.warehouses.edit', $warehouse) }}">
                                                            <i class="feather icon-edit"></i> ویرایش
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('admin.warehouses.movements', $warehouse) }}">
                                                            <i class="feather icon-clock"></i> تاریخچه
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="#" onclick="toggleStatus({{ $warehouse->id }})">
                                                            <i class="feather {{ $warehouse->is_active ? 'icon-slash' : 'icon-check-circle' }}"></i>
                                                            {{ $warehouse->is_active ? 'غیرفعال کردن' : 'فعال کردن' }}
                                                        </a>
                                                        <form id="toggle-form-{{ $warehouse->id }}" action="{{ route('admin.warehouses.toggle-status', $warehouse) }}" method="POST" class="d-none">
                                                            @csrf
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">هیچ انباری یافت نشد</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $warehouses->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleStatus(id) {
            if (confirm('آیا از تغییر وضعیت این انبار مطمئن هستید؟')) {
                document.getElementById('toggle-form-' + id).submit();
            }
        }
    </script>
@endsection
