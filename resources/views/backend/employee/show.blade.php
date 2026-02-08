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
<div>
    <div
        class="d-flex justify-content-between align-items-center pb-3">
        <div class="">
            <h4 class="">Trainee</h4>
        </div>
        <div>
            <a href="{{ route('admin.employee.index') }}">

                <button
                    type="button"
                    class="add-btn">
                    View Trainees
                </button>

            </a>

        </div>
        
       
    </div>

    <div class="card">
        
            <div class="" >
           
    
        
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @if($teacher->employee_type == 'external')
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
                            <th>Id Number</th>
                            <td>{{ $teacher->id_number }}</td>
                        </tr>
                        <tr>
                            <th>Classification Number</th>
                            <td>{{ $teacher->classfi_number }}</td>
                        </tr>
                        <tr>
                            <th>Nationality</th>
                            <td>{{ $teacher->nationality }}</td>
                        </tr>
                        <tr>
                            <th>Date of birth</th>
                            <td>{{ $teacher->dob }}</td>
                        </tr>
                        <tr>
                            <th>Mobile phone</th>
                            <td>{{ $teacher->phone }}</td>
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
                            // $teacherProfile = $teacher->teacherProfile?:'';
                            // $payment_details = $teacher->teacherProfile?json_decode($teacher->teacherProfile->payment_details):new stdClass();
                        @endphp
                    </table>
                    @else
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
                            <th>Department</th>
                            @if(isset($teacher->employee->department_details->title))
                            <td>{{ $teacher->employee->department_details->title }}</td>
                            @else
                            <td></td>
                            @endif
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
                            // $teacherProfile = $teacher->teacherProfile?:'';
                            // $payment_details = $teacher->teacherProfile?json_decode($teacher->teacherProfile->payment_details):new stdClass();
                        @endphp
                    </table>
                    @endif
                </div>
            </div><!-- Nav tabs -->
        </div>
    </div>
</div>

@stop
