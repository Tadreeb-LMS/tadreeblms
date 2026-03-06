@extends('backend.layouts.app')

@section('title', 'Employee'.' | '.app_name())
@push('after-styles')
<style>
    table th {
        width: 20%;
    }
</style>
@endpush
@section('content')

    <div class="card">

        <div class="card-header">
            <h3 class="page-title d-inline mb-0">Trainee</h3>
            <div class="float-right">
                <a href="{{ route('admin.employee.index') }}"
                   class="btn btn-success">View Trainee</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>@lang('labels.backend.access.users.tabs.content.overview.avatar')</th>
                            <td><img height="100px" src="{{ asset('public/uploads/employee/'.$teacher->avatar_location) }}" class="user-profile-image" /></td>
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
                            <th>@lang('Department')</th>
                            <td>{{ $teacher->employee->department_details->title }}</td>
                        </tr>

                        <tr>
                            <th>@lang('labels.backend.access.users.tabs.content.overview.status')</th>
                            <td>{!! $teacher->status_label !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.general_settings.user_registration_settings.fields.gender')</th>
                            <td>{!! $teacher->gender !!}</td>
                        </tr>
                        @php
                            $teacherProfile = $teacher->teacherProfile?:'';
                            $payment_details = $teacher->teacherProfile?json_decode($teacher->teacherProfile->payment_details):new stdClass();
                        @endphp
                    
                        
                    </table>
                </div>
            </div><!-- Nav tabs -->
        </div>
    </div>
@stop
