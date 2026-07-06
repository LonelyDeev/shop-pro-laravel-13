@extends('front::user.layouts.master')

@push('styles')

@endpush

@section('user-content')
    <div id='messages-page' class="col-xl-9 col-lg-8 col-md-8 col-sm-12">
        <div class="row">
            <div class="col-12">
                <div
                    class="section-title text-sm-title title-wide mb-1 no-after-title-wide dt-sl mb-2 px-res-1">
                    <h2>{{ trans('front::messages.profile.all-notifications') }}</h2>
                </div>
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>


            <div class="content-body">

                <section id="statistics-card">
                    <div class="row">
                        <div class="col-lg-12 col-12">
                            <div class="card">

                                <div class="card-content">
                                    <div class="card-body">
                                        @if($notifications->count())
                                            <ul class="activity-timeline timeline-left list-unstyled">
                                                @foreach ($notifications as $notification)
                                                    @php
                                                        $notification_link = notification_link($notification);
                                                    @endphp

                                                    @if($notification->type == 'SendMessage')

                                                        <li class="{{ $notification->read_at ? 'text-muted' : '' }}" >
                                                            <div class="timeline-icon bg-primary">
                                                                <i class="mdi mdi-comment-outline font-medium-2 align-middle"></i>
                                                            </div>
                                                            <div class="timeline-info">
                                                                <p class="font-weight-bold mb-0">{{ $notification->data['title'] }}</p>
                                                                <span class="font-small-3">{{ $notification->data['message'] }}</span>
                                                            </div>
                                                            <small class="text-muted">{{ jdate($notification->created_at)->ago() }}</small>
                                                        </li>
                                                    @endif

                                                @endforeach
                                            </ul>

                                        @else
                                            <p>چیزی برای نمایش وجود ندارد!</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>

                {{ $notifications->links('front::components.paginate') }}

            </div>
        </div>
    </div>
    </div>
@endsection
@include('back.partials.plugins', ['plugins' => ['persian-datepicker','jquery.validate']])
@push('scripts')

@endpush
