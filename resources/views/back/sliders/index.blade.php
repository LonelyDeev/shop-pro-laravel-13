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
                                    <li class="breadcrumb-item">مدیریت اسلایدرها
                                    </li>
                                    <li class="breadcrumb-item active">لیست اسلایدرها
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-md-right col-md-3 col-12 d-md-block d-none">
                    <div class="form-group breadcrum-right">
                        <div id="save-changes" class="spinner-border text-success" role="status" style="display: none">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body" id="main-card">
                <div class="nav-vertical">
                    <div class=" nav nav-tabs flex-column nav-left ">
                        <ul class="nav nav-tabs flex-column nav-vertical-right" role="tablist">
                            @if(config('front.slider_sections'))
                                @foreach (config('front.slider_sections')  as $key => $sliderSections)
                                    <li class="nav-item">
                                        <a class="nav-link {{$key==0 ? 'active' : '' }}"
                                           id="baseVerticalLeft-{{$sliderSections['key']}}" data-toggle="tab"
                                           aria-controls="tabVerticalLeft1-{{$sliderSections['key']}}"
                                           href="#tabVerticalLeft1-{{$sliderSections['key']}}" role="tab"
                                           aria-selected="false"><i style="margin-left: 5px"
                                                                    class=" fas fa-clipboard-list"></i>{{$sliderSections['name']}}
                                        </a>
                                    </li>
                                @endforeach
                            @endif
                        </ul>

                    </div>
                    <div class="tab-content">
                        @if(config('front.slider_sections'))
                            @foreach (config('front.slider_sections')  as $key => $sliderSections)

                                <div class="tab-pane {{$key==0 ? 'active' : '' }}"
                                     id="tabVerticalLeft1-{{$sliderSections['key']}}" role="tabpanel"
                                     aria-labelledby="baseVerticalLeft-{{$sliderSections['key']}}">
                                    @if($sliders->where('page',$sliderSections['key'])->count())
                                        @if (config('front.sliderGroups'))
                                            @foreach (config('front.sliderGroups.'.$sliderSections['key']) as $sliderGroup)
                                                @include('back.partials.sliders', ['sliders' => $sliders->where('group', $sliderGroup['group'])->where('page',$sliderSections['key']), 'group' => $sliderGroup['group'], 'title' => $sliderGroup['name'],'pageTitle'=>$sliderSections['name']])
                                            @endforeach
                                        @endif
                                    @else
                                        <section class="card">
                                            <div class="card-header">
                                                <h4 class="card-title"></h4>
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

                            @endforeach

                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- delete slider modal --}}
    <div class="modal fade text-left" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel19"
         style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف اسلایدر دیگر قادر به بازیابی آن نخواهید بود
                </div>
                <div class="modal-footer">
                    <form action="#" id="slider-delete-form">
                        @csrf
                        @method('delete')
                        <button type="button" class="btn personal-success-btn waves-effect waves-light"
                                data-dismiss="modal">خیر
                        </button>
                        <button type="submit" class="btn personal-danger-btn waves-effect waves-light">بله حذف شود
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('back.partials.plugins', ['plugins' => ['jquery-ui-sortable']])

@push('scripts')
    <!-- Page Js codes -->
    <script src="{{ asset('back/assets/js/pages/sliders/index.js') }}"></script>
@endpush
