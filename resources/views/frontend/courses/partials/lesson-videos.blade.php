@php
    $lessonVideos = ($lesson->videos ?? collect())->filter(function ($lessonVideo) {
        return !empty($lessonVideo->playback_url);
    });
@endphp

@if($lessonVideos->isNotEmpty())
    <div class="course-single-text lesson-video-section">
        <div class="lesson-video-heading">
            <h4>{{ __('course_pages.admin_lessons_create.lesson_videos') }}</h4>
            <span class="lesson-video-count">{{ $lessonVideos->count() }}</span>
        </div>
        <div class="lesson-video-stack">
            @foreach($lessonVideos as $lessonVideo)
                @php
                    $videoUrl = $lessonVideo->playback_url;
                    $embedId = $lessonVideo->plyr_embed_id;
                    $videoTitle = $lessonVideo->title ?: __('course_pages.admin_lessons_create.preview_video');
                @endphp

                @if($videoUrl)
                    <div class="video-container lesson-video-item" data-id="lesson-video-{{ $lessonVideo->id }}">
                        <div class="lesson-video-meta">
                            <h5 class="lesson-video-title">{{ $videoTitle }}</h5>
                            <span class="lesson-video-type">{{ $lessonVideo->type }}</span>
                        </div>

                        <div class="lesson-video-frame">
                        @if($lessonVideo->type === 'youtube')
                            <div class="js-player lesson-video-player"
                                 data-plyr-provider="youtube"
                                 data-plyr-embed-id="{{ $embedId }}"></div>
                        @elseif($lessonVideo->type === 'vimeo')
                            <div class="js-player lesson-video-player"
                                 data-plyr-provider="vimeo"
                                 data-plyr-embed-id="{{ $embedId }}"></div>
                        @elseif($lessonVideo->type === 'upload')
                            <video class="js-player lesson-video-player" playsinline controls>
                                <source src="{{ $videoUrl }}" type="video/mp4" />
                            </video>
                        @elseif($lessonVideo->type === 'embed')
                            {!! $videoUrl !!}
                        @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif
