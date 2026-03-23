@extends('backend.layouts.app')
@section('title', __('labels.backend.lessons.title').' | '.app_name())

@section('content')

<div class="pb-3">
    <h4 class="">@lang('labels.backend.lessons.title')</h4>
</div>
    <div class="card">
        
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered table-striped">
                        @foreach($lesson->videos as $video)

<h5>{{ $video->title }}</h5>

@if($video->type == 'youtube')

<iframe width="100%" height="400"
src="https://www.youtube.com/embed/{{ $video->url }}"></iframe>

@endif

@if($video->type == 'upload')

<video controls width="100%">
<source src="{{ asset('storage/'.$video->file_path) }}">
</video>

@endif

@endforeach
                        <tr>
                            <th>@lang('labels.backend.lessons.fields.course')</th>
                            <td>{{ $lesson->course->title or '' }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.lessons.fields.title')</th>
                            <td>{{ $lesson->title }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.lessons.fields.slug')</th>
                            <td>{{ $lesson->slug }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.lessons.fields.lesson_image')</th>
                            <td>@if($lesson->lesson_image)<a href="{{ asset('storage/uploads/' . $lesson->lesson_image) }}" target="_blank"><img
                                            src="{{ asset('storage/uploads/' . $lesson->lesson_image) }}" height="100px"/></a>@endif</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.lessons.fields.short_text')</th>
                            <td>{!! $lesson->short_text !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.lessons.fields.full_text')</th>
                            <td>{!! $lesson->full_text !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.lessons.fields.position')</th>
                            <td>{{ $lesson->position }}</td>
                        </tr>
                        <tr>
                            <th>Lesson Start Date</th>
                            <td>{{ !empty($lesson->lesson_start_date) ? date('d-m-Y H:i:s',strtotime($lesson->lesson_start_date)) : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Duration [Minutes]</th>
                            <td>{{ $lesson->duration }}</td>
                        </tr>

                        <tr>
                            <th>@lang('labels.backend.lessons.fields.media_pdf')</th>
                            <td>
                                @if($lesson->mediaPDF)
                                <p class="form-group">
                                    <a href="{{$lesson?->mediaPDF?->url}}" target="_blank">{{$lesson?->mediaPDF?->url}}</a>
                                </p>
                                @else
                                    <p>No PDF</p>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.lessons.fields.media_audio')</th>
                            <td>
                                @if($lesson->media != null &&  $lesson->media->count())
                                {{-- <p class="form-group">
                                    <a href="{{$lesson->mediaAudio->url}}" target="_blank">{{$lesson->mediaAudio->url}}</a>
                                </p> --}}
                                @foreach($lesson->media as $media)
                                        @if($media->type == 'lesson_audio')
                                        <p class="form-group">
                                            <a href="{{ $media->url }}"
                                               target="_blank">{{ $media->url }}
                                                ({{ $media->size }} KB)</a>
                                        </p>
                                        @endif
                                @endforeach
                                @else
                                    <p>No Audio</p>
                                @endif
                            </td>
                        </tr>

                        <tr>

                            <th>@lang('labels.backend.lessons.fields.downloadable_files')</th>
                            <td>
                                @if($lesson->downloadableMedia && $lesson->downloadableMedia->count())
                                    @foreach($lesson->downloadableMedia as $media)
                                        <p class="form-group">
                                            <a href="{{ $media->url }}"
                                               target="_blank">{{ $media->url }}
                                                ({{ $media->size }} KB)</a>
                                        </p>
                                    @endforeach
                                @else
                                    <p>No Files</p>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.lessons.fields.media_video')</th>
                            <td>
                                @if($lesson->mediaVideo !=  null )
                                        <p class="form-group">
                                           <a href="{{$lesson->mediaVideo->url}}" target="_blank">{{$lesson->mediaVideo->url}}</a>
                                        </p>
                                @else
                                    <p>No Videos</p>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.lessons.fields.published')</th>
                            <td>
                                @if($lesson->published)
                                    <span class="badge badge-success">Published</span>
                                @else
                                    <span class="badge badge-secondary">Draft</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div><!-- Nav tabs -->



            <a href="{{ route('admin.lessons.index') }}"
               class="btn cancel-btn">@lang('strings.backend.general.app_back_to_list')</a>
        </div>
        <div>
            
        </div>
    </div>
@stop