@php
    $variables      = get_widget($widget);
    $main_sliders   = $variables['fullscreen_slider'];
    $mobile_sliders = $variables['mobile_sliders'];
@endphp
@push('styles')
    <style>
        /* Fullscreen Slider Styles */
        .fullscreen-slider-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 999;
            overflow: hidden;
        }

        .fullscreen-carousel,
        .fullscreen-carousel-inner,
        .fullscreen-carousel-item {
            width: 100%;
            height: 100vh;
            height: 100dvh; /* برای پشتیبانی از مرورگرهای جدید */
        }

        .fullscreen-image {
            width: 100%;
            height: 100%;
            object-fit: cover; /* تصویر کل صفحه را می‌پوشاند */
            object-position: center;
        }

        /* Custom Indicators */
        .custom-indicators {
            bottom: 30px;
            z-index: 15;
        }

        .custom-indicators li {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.5);
            border: 2px solid rgba(255, 255, 255, 0.8);
            margin: 0 5px;
            transition: all 0.3s ease;
        }

        .custom-indicators li.active {
            background-color: #ffffff;
            transform: scale(1.2);
        }

        /* Custom Controls */
        .custom-control {
            width: 5%;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .custom-control:hover {
            opacity: 1;
        }

        .custom-control .carousel-control-prev-icon,
        .custom-control .carousel-control-next-icon {
            width: 40px;
            height: 40px;
            background-size: 60% 60%;
        }

        /* Caption Styling */
        .custom-caption {
            bottom: 20%;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .custom-caption h2 {
            font-size: 3rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 20px;
        }

        .custom-caption p {
            font-size: 1.2rem;
            color: #ffffff;
            opacity: 0.9;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .custom-caption h2 {
                font-size: 1.8rem;
            }

            .custom-caption p {
                font-size: 1rem;
            }

            .custom-indicators li {
                width: 8px;
                height: 8px;
            }
        }
    </style>
@endpush
<!-- Start Main-Slider -->
<div class="fullscreen-slider-wrapper">
    <div id="mainCarousel" class="carousel slide fullscreen-carousel" data-ride="carousel" data-interval="5000">
        {{-- Indicators --}}
        @if(count($main_sliders) > 1)
            <ol class="carousel-indicators custom-indicators">
                @foreach($main_sliders as $key => $slider)
                    <li data-target="#mainCarousel" data-slide-to="{{ $key }}" class="@if($loop->first) active @endif"></li>
                @endforeach
            </ol>
        @endif

        {{-- Slides --}}
        <div class="carousel-inner fullscreen-carousel-inner">
            @foreach($main_sliders as $slider)
                <div class="carousel-item fullscreen-carousel-item @if($loop->first) active @endif">
                    <img class="d-block w-100 fullscreen-image"
                         src="{{ asset($slider->image) }}"
                         alt="{{ $slider->title ?: 'Slider Image' }}">

                    {{-- Optional: Overlay Text --}}
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
