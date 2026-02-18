@extends('backend.layouts.app')
@section('title', __('labels.backend.tests.title').' | '.app_name())

@push('after-styles')
    <style>
        .select2-container--default .select2-selection--single {
            height: 35px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 35px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 35px;
        }

    </style>
@endpush
@section('content')

    <form method="POST" action="{{ route('admin.tests.update', $test->id) }}" class="form-horizontal">
        @csrf
        @method('PUT')

    <div class="pb-3 d-flex justify-content-between align-items-center">
        <h3 class="page-title float-left mb-0">@lang('labels.backend.tests.edit')</h3>
        <div class="">
            <a href="{{ route('admin.tests.index') }}"
               class="btn btn-primary">@lang('labels.backend.tests.view')</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 col-lg-6 form-group">
                   <label for="course_id" class="control-label">
                    {{ trans('labels.backend.lessons.fields.course') }}
                </label>
                <div class=" custom-select-wrapper">
                    <select name="course_id" id="course_id" class="form-control custom-select-box" required>
                        <option value="">Select Course</option>
                        @foreach($courses as $id => $course)
                        <option value="{{ $id }}"
                            @if(request('course_id')==$id || old('course_id')==$id) selected @endif>
                            {{ $course }}
                        </option>
                        @endforeach
                    </select>
                    <span class="custom-select-icon">
                        <i class="fa fa-chevron-down"></i>
                    </span>
                </div>
                </div>

                <div class="col-md-12 col-lg-6 form-group">
                    <label for="title" class="control-label">{{ trans('labels.backend.tests.fields.title') }}</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $test->title ?? '') }}" class="form-control" placeholder="{{ trans('labels.backend.tests.fields.title') }}">
                </div>
            </div>
            <div class="row">
                <div class="col-12 form-group">
                    <label for="description" class="control-label">{{ trans('labels.backend.tests.fields.description') }}</label>
                    <textarea name="description" id="description" class="form-control" placeholder="{{ trans('labels.backend.tests.fields.description') }}">{{ old('description', $test->description ?? '') }}</textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-12 form-group">
                    <label for="passing_score" class="control-label">{{ trans('labels.backend.tests.fields.score_field') }}</label>
                    <input type="text" name="passing_score" id="passing_score" value="{{ old('passing_score', $test->passing_score ?? '') }}" class="form-control" placeholder="{{ trans('labels.backend.tests.fields.score_field_placeholder') }}">
                </div>
                {{-- <div class="col-4 form-group">
                    {!! Form::hidden('published', 0) !!}
                    {!! Form::checkbox('published', 1, old('published'), []) !!}
                    {!! Form::label('published', trans('labels.backend.tests.fields.published'), ['class' => 'control-label font-weight-bold']) !!}

                </div> --}}
            </div>

        </div>
        <div class="col-12 mb-3 text-right">
            <button type="submit" class="btn add-btn">{{ trans('strings.backend.general.app_update') }}</button>
        </form>
        </div>
    </div>

@stop

