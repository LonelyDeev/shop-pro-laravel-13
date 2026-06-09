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
                                    <li class="breadcrumb-item">مدیریت
                                    </li>
                                    <li class="breadcrumb-item">تنظیمات
                                    </li>
                                    <li class="breadcrumb-item active">تنظیمات نمایش فروشندگان
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                @if($sellers_heroes->count())
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">چرا {{ option('info_site_title', 'او پی شاپ') }} جای خوبی برای فروش کالاست؟</h4>
                            <a class="btn btn-success waves-effect waves-light" href="{{route('admin.settings.seller-hero-create')}}">افزودن</a>
                        </div>
                        <div class="card-content" id="main-card">
                            <div class="card-body">
                                <div class="table-responsive overflow-unset">
                                    <table class="table table-striped mb-0 ">
                                        <thead>
                                            <tr>
                                                <th class="text-center">آیکون</th>
                                                <th>عنوان</th>
                                                <th>توضیحات</th>
                                                <th class="text-center">عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($sellers_heroes as $sellers_hero)
                                                <tr id="brand-{{ $sellers_hero->id }}-tr">
                                                    <td class="text-center">
                                                        {!! $sellers_hero->icon !!}
                                                    </td>
                                                    <td>
                                                        <span class="d-flex">
                                                            <span>{{ $sellers_hero->title }}</span>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="d-flex">
                                                            {{ $sellers_hero->description }}
                                                        </span>
                                                    </td>

                                                    <td class="text-center">
                                                        <div class="dropdown dropdown-action">
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $sellers_hero->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu" aria-labelledby="dropdownMenu{{ $sellers_hero->id }}">
                                                                <a class="dropdown-item" href="{{ route('admin.settings.seller-hero-edit', ['sellerHero' => $sellers_hero]) }}"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                                                                <div class="dropdown-divider"></div>

                                                                <button class="dropdown-item btn-delete" data-id="{{ $sellers_hero->id }}" data-action="{{ route('admin.settings.seller-hero-destroy', ['sellerHero' => $sellers_hero]) }}" data-toggle="modal" data-target="#delete-modal"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>

                @else
                    <section class="card">
                        <div class="card-header">
                            <h4 class="card-title">چرا {{ option('info_site_title', 'او پی شاپ') }} جای خوبی برای فروش کالاست؟</h4>
                            <a class="btn btn-success waves-effect waves-light" href="{{route('admin.settings.seller-hero-create')}}">افزودن</a>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <div class="card-text">
                                    <p>چیزی برای نمایش وجود ندارد!</p>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif


            </div>
        </div>
    </div>

    {{-- delete brand modal --}}
    <div class="modal fade text-left" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel19" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف برند دیگر قادر به بازیابی آن نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="#" id="hero-delete-form">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn personal-success-btn waves-effect waves-light" data-dismiss="modal">خیر</button>
                        <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/settings/sellers/seller-hero-index.js') }}?v=2"></script>
@endpush
