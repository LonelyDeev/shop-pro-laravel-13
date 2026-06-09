@push('styles')
    <link rel="stylesheet" href="{{theme_asset('css/main-story.css')}}" />
    <link rel="stylesheet" href="{{theme_asset('js/plugins/plyr/plyr.css')}}" />
@endpush


@php
    $variables          = get_widget($widget);
    $stories   = $variables['main_story'];
       $storySeen = Illuminate\Support\Facades\Cookie::get('story_seen');
    $storySeen = json_decode($storySeen, true);
    if(empty($storySeen)){
        $storySeen = [];
    }
@endphp
    <!-- Start Partners -->
@if ($stories->count())

    <div class="col-lg-12 col-md-12 col-xs-12 pull-right mt-3">
        <div class="row">
            <div class="col-12">
                <div class="widget widget-product card">
                    @if($widget->option('title'))
                        <header class="card-header">
                            <span class="title-one">{{$widget->option('title')}}</span>
                        </header>
                    @endif

                    <div class="product-carousel-story owl-carousel owl-theme owl-rtl owl-loaded owl-drag allStoryIndex"
                         data-action='{{route('front.story.seen')}}' data-action-interaction="{{route('front.story.click')}}">
                        <div class="owl-stage-outer">
                            <div class="owl-stage d-flex">
                                @foreach ($stories as $story)
                                    @if($story->expiry_date > now())
                                        <div class="owl-item {{$loop->index<=10 ? 'active' : ''}}">
                                            <div data-toggle="modal" data-target="#story-modal" id='{{$story->id}}'
                                                 class="item storyItem @if(in_array($story->id, $storySeen)) unActive @endif">
                                                <a class="image-data-src">
                                                    <img class="img-fluid"
                                                         data-src="{{ $story->cover_image ? asset($story->cover_image) : asset('/no-image-product.svg') }}"
                                                         src="{{ theme_asset('images/600-600.png') }}"
                                                         alt="{{ $story->title }}">
                                                </a>

                                            </div>
                                            <span
                                                class="font-size-12">{{ \Illuminate\Support\Str::limit($story->title, 35) }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endif
<!-- End Partners -->

@push('scripts')

    <script src="{{theme_asset('js/main-story.js')}}"></script>

    <div class="modal fade" id="story-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div id="story-container">
                        <div class="text-center p-5">
                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                            <p class="mt-2">در حال بارگذاری...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    {{-- مودال کامنت استوری (خالی) --}}
    <div class="modal fade story-comments-modal" id="storyCommentsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-comment"></i> دیدگاه ها (<span class="comments-total-count">0</span>)
                    </h5>
                    <button type="button" class="btn-close close-comments-modal" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="story-comments-list" id="storyCommentsList">
                        {{-- کامنت‌ها با AJAX لود می‌شوند --}}
                        <div class="text-center p-4">
                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                            <p class="mt-2">در حال بارگذاری کامنت‌ها...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <form class="story-comment-form w-100" action="{{route('front.story.storeStoryComment')}}" id="storyCommentForm" method="post">
                        @csrf
                        <input type="hidden" name="story_id" id="comment_story_id">
                        <div class="d-flex gap-2">
                            <input type="text"
                                   class="form-control story-comment-input"
                                   id="comment_input"
                                   placeholder="نظر خود را بنویسید..."
                                   autocomplete="off">
                            <button type="submit" form="storyCommentForm" class="btn btn-primary btn-sm story-comment-submit">
                                <i class="fa fa-paper-plane"></i> ارسال
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{theme_asset('js/plugins/plyr/plyr.js')}}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const player = new Plyr('.story-video-player', {
            controls: ['current-time'],
            settings: ['captions', 'quality', 'speed']
        });
    });
</script>
@endpush
