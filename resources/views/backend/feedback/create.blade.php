@extends('backend.layouts.app')
@section('title', __('labels.backend.courses.title').' | '.app_name())

@section('content')

<form method="POST" action="{{ route('admin.feedback.store') }}" enctype="multipart/form-data">
@csrf

<div class="card">
    <div class="card-header">
        <h3 class="page-title float-left">Feedback Question</h3>
        <div class="float-right">
            <a href="{{ route('admin.feedback_question.index') }}" class="btn btn-success">Submit</a>
        </div>
    </div>

    <div class="card-body">
        @if (Auth::user()->isAdmin())   
        <div class="row">

            <div class="col-12 form-group">
                <label for="question" class="control-label">{{ trans('labels.backend.courses.fields.description') }}</label>
                <textarea class="form-control editor" placeholder="{{ trans('labels.backend.courses.fields.description') }}" name="question" cols="50" rows="10">{{ old('question') }}</textarea>

            </div>
        </div>
        @endif
        <div class="row">
            <div class="col-12 text-center form-group">
                <button class="btn btn-lg btn-danger" type="submit">{{ trans('strings.backend.general.app_save') }}</button>
            </div>
        </div>
    </div>
</div>
</form>
@stop

@push('after-scripts')
<script type="text/javascript" src="{{asset('/vendor/unisharp/laravel-ckeditor/ckeditor.js')}}"></script>
<script type="text/javascript" src="{{asset('/vendor/unisharp/laravel-ckeditor/adapters/jquery.js')}}"></script>
<script type="text/javascript">
$(function() {
    $('#your_textarea').ckeditor({
        toolbar: 'Full',
        enterMode : CKEDITOR.ENTER_BR,
        shiftEnterMode: CKEDITOR.ENTER_P
    });
});
</script>
<script src="{{asset('/vendor/laravel-filemanager/js/lfm.js')}}"></script>
<script>
    $('.editor').each(function() {
        CKEDITOR.replace($(this).attr('id'), {
            filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
            filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token={{csrf_token()}}',
            filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
            filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token={{csrf_token()}}',
            extraPlugins: 'smiley,lineutils,widget,codesnippet,prism,flash,colorbutton,colordialog',
        });
    });
</script>
@endpush