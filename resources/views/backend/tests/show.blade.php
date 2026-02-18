@extends('backend.layouts.app')
@section('title', __('labels.backend.tests.title').' | '.app_name())

@section('content')

<div class="pb-3">
    <h4 class="">@lang('labels.backend.tests.title')</h4>
</div>
    <div class="card">
        
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>@lang('labels.backend.tests.fields.course')</th>
                            <td>{{ ($test->course) ? $test->course->title : 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th>@lang('labels.backend.tests.fields.title')</th>
                            <td>{{ $test->title }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.tests.fields.description')</th>
                            <td>{!! $test->description !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.tests.fields.questions')</th>
                            <td>
                                <ol class="pl-3 mb-0">
                                @foreach ($test->questions as $singleQuestions)
                                    <li class="label label-info label-many">{{ $singleQuestions->question }}</li>
                                @endforeach
                                </ol>
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.tests.fields.published')</th>
                            <td><input type=\"checkbox\" value=\"1\" @if($test->published == 1) checked @endif disabled></td>
                        </tr>
                    </table>
                </div>
            </div>

            <a href="{{ route('admin.tests.index') }}" class="cancel-btn border">@lang('strings.backend.general.app_back_to_list')</a>
        </div>
    </div>
@stop