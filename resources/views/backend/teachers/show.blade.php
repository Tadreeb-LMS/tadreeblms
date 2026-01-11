@extends('backend.layouts.app')

@section('title', __('labels.backend.teachers.title').' | '.app_name())
@push('after-styles')
<style>
    table th {
        width: 20%;
    }
</style>
@endpush
@section('content')
<div class="">
    <div
        class="d-flex justify-content-between align-items-center pb-3">
        <div class="">
            <h4 class="">@lang('labels.backend.teachers.title')</h4>
        </div>
      
        <div>
          

                <a href="{{ route('admin.teachers.index') }}"
                >
                <button
                    type="button"
                    class="add-btn">
                    @lang('labels.backend.teachers.view')
                </button>

            </a>

        </div>
        
        
    </div>

    <div class="card">
    
        <div class="" >
              
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>@lang('labels.backend.access.users.tabs.content.overview.avatar')</th>
                            <td><img height="100px" src="{{ $teacher->picture }}" class="user-profile-image" /></td>
                        </tr>
    
                        <tr>
                            <th>@lang('labels.backend.access.users.tabs.content.overview.name')</th>
                            <td>{{ $teacher->name }}</td>
                        </tr>
    
                        <tr>
                            <th>@lang('labels.backend.access.users.tabs.content.overview.email')</th>
                            <td>{{ $teacher->email }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.access.users.tabs.content.overview.status')</th>
                            <td>{!! $teacher->status_label !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.general_settings.user_registration_settings.fields.gender')</th>
                            <td>{!! $teacher->gender !!}</td>
                        </tr>
                        {{-- @php
                            $teacherProfile = $teacher->teacherProfile?:'';
                            $payment_details = $teacher->teacherProfile?json_decode($teacher->teacherProfile->payment_details):new stdClass();
                        @endphp --}}
                        {{-- <tr>
                            <th>@lang('labels.teacher.facebook_link')</th>
                            <td>{!! $teacherProfile->facebook_link !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.teacher.twitter_link')</th>
                            <td>{!! $teacherProfile->twitter_link !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.teacher.linkedin_link')</th>
                            <td>{!! $teacherProfile->linkedin_link !!}</td>
                        </tr> --}}
                        
                       
                    </table>
                </div>
            </div><!-- Nav tabs -->
        </div>
    </div>
</div>

@stop
