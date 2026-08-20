@php
    $variables      = get_widget($widget);
    $main_sliders   = $variables['fullscreen_slider'];
    $mobile_sliders = $variables['mobile_sliders'];
@endphp
@push('styles')
    <style>
        /* حذف padding و margin اضافی */
        .full-width-slider-wrapper {
            width: 100%;
            max-width: 100%;
            overflow: hidden;
            position: relative;
            background: #000;
            padding: 0;
            margin: 0;
        }

        /* ارتفاع 300px در همه دستگاه‌ها */
        .full-width-carousel,
        .full-width-carousel-inner,
        .full-width-carousel-item {
            width: 100%;
            height: 300px !important; /* ارتفاع ثابت 300px */
        }

        .full-width-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        /* Indicators */
        .custom-indicators {
            bottom: 20px;
            z-index: 15;
        }

        .custom-indicators li {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            border: 2px solid rgba(255,255,255,0.8);
            margin: 0 5px;
            transition: all 0.3s ease;
        }

        .custom-indicators li.active {
            background: #fff;
            transform: scale(1.2);
        }

        /* Controls */
        .custom-control {
            opacity: 0.7;
            transition: opacity 0.3s;
            width: 5%;
            z-index: 10;
        }

        .custom-control:hover {
            opacity: 1;
        }

        /* Responsive - در موبایل هم 300px */
        @media (max-width: 768px) {
            .full-width-carousel,
            .full-width-carousel-inner,
            .full-width-carousel-item {
                height: 300px !important; /* در موبایل هم 300px */
            }

            .custom-indicators li {
                width: 8px;
                height: 8px;
            }
        }
    </style>
@endpush
<!-- Start Main-Slider -->
<div class="full-width-slider-wrapper">
    <div id="mainCarousel" class="carousel slide full-width-carousel" data-ride="carousel" data-interval="5000">
        {{-- Indicators --}}
        @if(count($main_sliders) > 1)
            <ol class="carousel-indicators custom-indicators">
                @foreach($main_sliders as $key => $slider)
                    <li data-target="#mainCarousel" data-slide-to="{{ $key }}" class="@if($loop->first) active @endif"></li>
                @endforeach
            </ol>
        @endif

        {{-- Slides --}}
        <div class="carousel-inner full-width-carousel-inner">
            @foreach($main_sliders as $slider)
                <div class="carousel-item full-width-carousel-item @if($loop->first) active @endif">
                    <img class="d-block w-100 full-width-image"
                         src="{{ asset($slider->image) }}"
                         alt="{{ $slider->title ?: 'Slider Image' }}">

                    @if($slider->title || $slider->description)
                        <div class="carousel-caption d-none d-md-block custom-caption">
                            @if($slider->title)
                                <h2>{{ $slider->title }}</h2>
                            @endif
                            @if($slider->description)
                                <p>{{ $slider->description }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Controls --}}
        @if(count($main_sliders) > 1)
            <a class="carousel-control-prev custom-control" href="#mainCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next custom-control" href="#mainCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        @endif
    </div>
</div>
