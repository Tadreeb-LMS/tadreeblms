@extends('backend.layouts.app')

@section('title', __('strings.backend.dashboard.title') . ' | ' . app_name())

@push('after-styles')
<style>
    .addi-info {

        /* width: 50%;
            justify-content: center; */

    }

    .course-card {
        transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
    }

    .course-card:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        transform: translateY(-3px);
    }

    .text-wrapper {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .trend-badge-2 {
        top: -10px;
        left: -52px;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        position: absolute;
        padding: 40px 40px 12px;
        -webkit-transform: rotate(-45deg);
        transform: rotate(-45deg);
        background-color: #ff5a00;
    }

    .progress {
    background-color: #8690a1;
    font-weight: 300;margin-top: 10px;
    font-size: 10px;
    border: 0px solid #a0a5db;
    height: 18px;
    } .progress-bar span:nth-child(1) {
        font-size: 13px;
    font-weight: 600;}
    .progress-bar{flex-direction: unset;
    justify-content: start;
    gap: 13px;
    margin: 0 0 0px;
    align-items: center;background: linear-gradient(267deg, #5da10a, #3b4188);
    border-radius: 30px }
    .progress-bar span {
        padding: 0 10px;
        color: #ffffff;
    }
    .user-clist i{background: linear-gradient(90deg, #ca0063, #042bd0) !important; 
    background-clip: text !important;
        -webkit-background-clip: text !important; 
        
        -webkit-text-fill-color: transparent; font-size: 20px !important;
        }

    .best-course-pic {
        background-color: #333333;
        background-position: center;
        background-size: cover;
        height: 150px;
        width: 100%;
        background-repeat: no-repeat;
    }

    .disabled-course {
        pointer-events: none;
    }

     .lock-image {
        position: absolute;
        top: 110px;
        left: 50%;
        transform: translate(-50%, 0);
        z-index: 1;
        width: 100px; 
    background: #ffffff;
    border-radius: 20px;
    height: 100px;
    object-fit: contain;
    padding: 17px;
    }

    .certificate {
        padding: 27px 84px;
        font-size: 30px;
        display: flex; gap:20px;
        text-align: center;
        align-items: center; font-weight: 400;
    }  
</style>
@endpush
@php
$local_lang = App::getLocale() ?? 'en';
@endphp
@section('content')


@if(auth()->user()->hasRole('administrator') || !auth()->user()->hasRole('student'))
<div class="row">

    <div class="col-12 mb-3">
        <h4 class="heading-text">
            @lang('strings.backend.dashboard.welcome') {{ $logged_in_user->name }} {{ $logged_in_user->id }}
        </h4>

    </div>
    <div class="col-12 pl-0 d-flex justify-content-between">

        <div class="col-lg-2 dash-card mb-3 ml-3 leftBorder">
            <div class="d-flex justify-content-center">
                <h5>
                    {{ $students_count }}
                </h5>
                <div>
                    <i class="fa fa-users ml-3" aria-hidden="true" style="font-size: 18px;"></i>
                </div>
            </div>
            <div class="text-center">
                <div class="" style="display: flex;">
                    <h5>

                        @lang('strings.backend.dashboard.Active-Users')
                    </h5>
                    <a href="{{ route('admin.employee.index',['status'=>'active']) }}"> <i class="fa fa-arrow-right ml-3" aria-hidden="true" style="font-size:15px;"></i>  </a>
                </div>
            </div>


        </div>
        <div class="col-lg-2 dash-card mb-3 ml-3 leftBorder1">
            <div class="d-flex justify-content-center">
                <h5>
                    {{ $courses_count }}
                </h5>
                <div>
                    <i class="fa fa-graduation-cap ml-3" aria-hidden="true" style="font-size: 18px;"></i>
                </div>
            </div>
            <div class="text-center">
                <div class="" style="display: flex;">
                    <h5>

                        @lang('strings.backend.dashboard.Added-Courses')
                    </h5>
                    <a href="{{ route('admin.courses.index',['status'=>'active']) }}"> <i class="fa fa-arrow-right ml-3" aria-hidden="true" style="font-size:15px;"></i>  </a>
                </div>
            </div>


        </div>
        <div class="col-lg-2 dash-card mb-3 ml-3 leftBorder2">
            <div class="d-flex justify-content-center">
                <h5>
                    {{ $assigned_users_count }}
                </h5>
                <div>
                    <i class="fa fa-users ml-3" aria-hidden="true" style="font-size: 18px;"></i>
                </div>
            </div>
            <div class="text-center">
                <div class=""  style="display: flex; ">
                    <h5>

                        @lang('strings.backend.dashboard.Assigned-Users')
                    </h5>
                    <a href="{{ route('admin.employee.index') }}"> <i class="fa fa-arrow-right ml-3" aria-hidden="true" style="font-size:15px;"></i>  </a>
                </div>
            </div>


        </div>
        <div class="col-lg-2 dash-card mb-3 ml-3 leftBorder3">
            <div class="d-flex justify-content-center">
                <h5>
                    {{ $total_assignments }}
                </h5>
                <div>
                    <i class="fa fa-briefcase ml-3" aria-hidden="true" style="font-size: 18px;"></i>
                </div>
            </div>
            <div class="text-center">
                <div class="" style="display: flex; ">
                    <h5>

                        @lang('strings.backend.dashboard.Assignments')
                    </h5>
                    <a href="{{ route('admin.assessment_accounts.course-assign-list') }}"> <i class="fa fa-arrow-right ml-3" aria-hidden="true" style="font-size:15px;"></i>  </a>
                </div>
            </div>


        </div>
        <div class="col-lg-2 dash-card mb-3 ml-3 leftBorder4">
            <div class="d-flex justify-content-center">
                <h5>
                    {{ $total_certificate_issued }}
                </h5>
                <div>
                    <i class="fa fa-trophy ml-3" aria-hidden="true" style="font-size: 18px;"></i>
                </div>
            </div>
            <div class="text-center">
                <div class="" style="display: flex; ">
                    <h5>

                        @lang('strings.backend.dashboard.Certificate-Issued')
                    </h5>

                </div>
            </div>


        </div>
    </div>

</div>
<div class="row">
    <div class="col-12">

        <div class="accordion" id="accordionExample">
            <div class="card" style="border-radius: 5px;">
                <div class="card-header d-flex justify-content-between" id="headingOne" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                    <h5 class="mb-0" style="color: #3c4085;">
                        <a class="" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                            <i class="fa fa-search-plus" aria-hidden="true" style="font-size: 16px;"></i>
                            <span class="ml-4" style="font-size: 16px;">
                                @lang('strings.backend.dashboard.Filter-Your-Progress')
                            </span>
                        </a>
                    </h5>
                    <div>

                        <i class="fa fa-chevron-down" aria-hidden="true" style="font-size: 16px;color:#3c4085"></i>
                    </div>
                </div>

                <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                    <div class="card-body">
                        <div class="col-12">
                            <form id="dashboard-form-filter" action="{{ route('admin.dashboard.stats') }}" method="POST">
                                @csrf
                                <div class="row">

                                    <div class="col-lg-4 mt-2">
                                        <label for="user_id" class="control-label">
                                            @lang('strings.backend.dashboard.Employee')
                                        </label>
                                        <div class=" custom-select-wrapper">
                                            <select name="user_id" id="user_id" class="form-control custom-select-box select2" >
                                                <option value="">@lang('strings.backend.dashboard.select-one')</option>

                                                @foreach($internal_users as $user)
                                                    <option value="{{ $user->id }}">{{  $user->email }}</option>
                                                @endforeach


                                            </select>
                                            <span class="custom-select-icon">
                                                <i class="fa fa-chevron-down"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 mt-2">
                                        <label for="department_id" class="control-label">
                                            @lang('strings.backend.dashboard.Department')
                                        </label>
                                        <div class=" custom-select-wrapper">
                                            <select name="department_id" id="department_id" class="form-control custom-select-box select2">
                                                <option value="">@lang('strings.backend.dashboard.select-one')</option>

                                                    
                                                @foreach($departments as $row)
                                                    <option value="{{ $row->id }}">{{  $row->title }}</option>
                                                @endforeach
                                            </select>
                                            <span class="custom-select-icon">
                                                <i class="fa fa-chevron-down"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 mt-2">
                                        <label for="course_id" class="control-label">
                                            @lang('strings.backend.dashboard.Course')
                                        </label>
                                        <div class=" custom-select-wrapper">
                                            <select name="course_id" id="course_id" class="form-control custom-select-box select2" >
                                                <option value="">@lang('strings.backend.dashboard.select-one')</option>

                                                @foreach($published_courses as $row)
                                                    <option value="{{ $row->id }}">{{  $row->title }}</option>
                                                @endforeach

                                            </select>
                                            <span class="custom-select-icon">
                                                <i class="fa fa-chevron-down"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 mt-3">
                                        <div class="">
                                            <div class="mb-2">
                                                @lang('strings.backend.dashboard.Period')
                                            </div>
                                            <input type="date" name="from" value="{{ request()->from }}" id="assign_from_date" class="w-100" style="border: 1px solid #c8ced3;border-radius:4px;padding-left:8px;padding-right:8px;padding-top:4px;padding-bottom:5px">
                                        </div>
                                    </div>
                                    <div class="col-lg-4 mt-3">
                                        <label for="category_id" class="control-label">
                                            @lang('strings.backend.dashboard.Course-Categeory')
                                        </label>
                                        <div class=" custom-select-wrapper">
                                            <select name="category_id" id="category_id" class="form-control custom-select-box select2" >
                                                <option value="">@lang('strings.backend.dashboard.select-one')</option>

                                                @foreach($categories as $row)
                                                    <option value="{{ $row->id }}">{{  $row->name }}</option>
                                                @endforeach

                                            </select>
                                            <span class="custom-select-icon">
                                                <i class="fa fa-chevron-down"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-lg-2" style="margin-top:28px">
                                        <div class="d-flex justify-content-between mt-3">
                                            <div>
                                                <button class="btn btn-primary pl-4 pr-4" id="advance-search-btn" type="submit"> @lang('strings.backend.dashboard.search')</button>
                                            </div>
                                            <div>
                                                <button class="btn btn-danger ml-3" id="reset" type="button"> @lang('strings.backend.dashboard.reset')</button>

                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>



<div class="row mb-3">
    <div class="col-lg-4 col-md-12 col-sm-12">
        <div class="avg-card leftBorder4">
            <div class="avg-card-head">

                <h5>
                    @lang('strings.backend.dashboard.average-completion-rate')
                </h5>
            </div>
            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <!-- <i class="fa fa-pie-chart" aria-hidden="true"></i> -->
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-around align-items-center">
                <div>

                    <div>
                        <i class="fa fa-graduation-cap p-card-i" aria-hidden="true"></i>
                        <span class="ml-3 font-weight-bold p-card-i">
                            @lang('strings.backend.dashboard.Completed') -
                            <span class="p-card-i">
                                <span id="av-comp_rate">{{ $total_completed }}</span>
                            </span>
                        </span>
                    </div>
                    <div class="mt-3" style="color: #344050;font-weight:bold">
                        <i class="fa fa-hourglass-end" aria-hidden="true"></i>
                        <span class="ml-4">
                            @lang('strings.backend.dashboard.Remaining') - <span id="av-rem-comp_rate">{{ $total_pending }}</span>
                        </span>
                    </div>
                </div>

                <div class="circular-progress-container">
                    <svg class="circular-progress" width="120" height="120">
                        <circle class="bg" cx="60" cy="60" r="50"></circle>
                        <circle class="progress" cx="60" cy="60" r="50" id="progress1"></circle>
                    </svg>
                    <div class="percentage" id="value1">0%</div>
                </div>
            </div>

        </div>
    </div>
    <div class="col-lg-4 col-md-12 col-sm-12">
        <div class="avg-card leftBorder1">
            <div class="avg-card-head">
                <h5>
                    @lang('strings.backend.dashboard.average-assessment-score')
                </h5>

            </div>
            <div class="mt-3 d-flex justify-content-around align-items-center">
                <div>

                    <div class="p-card-i font-weight-bold">
                        <i class="fa fa-graduation-cap" aria-hidden="true"></i>
                        <span class="ml-3">
                            @lang('strings.backend.dashboard.Completed') -
                            <span id="av-comp-score">{{ $completed_assesment }}</span>
                        </span>
                    </div>
                    <div class="mt-3" style="color: #344050;font-weight:bold">
                        <i class="fa fa-hourglass-end" aria-hidden="true"></i>
                        <span class="ml-4">
                            @lang('strings.backend.dashboard.Remaining') - <span id="av-not-comp-score"> {{ $not_completed_assesment }} </span>
                        </span>
                    </div>
                </div>

                <div class="circular-progress-container">
                    <svg class="circular-progress" width="120" height="120">
                        <circle class="bg" cx="60" cy="60" r="50"></circle>
                        <circle class="progress" cx="60" cy="60" r="50" id="progress2"></circle>
                    </svg>
                    <div class="percentage" id="value2">0%</div>
                </div>
            </div>

        </div>
    </div>
</div>
@endif
<div class="row">
@if (auth()->user()->hasRole('student'))
<div class="col-12 text-left heading-text userheading">
    <h4> @lang('strings.backend.dashboard.welcome') <span>{{ $logged_in_user->name }} {{ $logged_in_user->id }}</span> </h4>
</div>
@endif
</div>



<div class=" ">



  
    <div class="">

        <div class="pt20">

            <div class="row">

                
                @if (auth()->user()->hasRole('student'))

                
                
                    <div class="col-12">
                        <div class="row mb-3">
                            <div class="col-lg-4 col-md-12 col-sm-12">
                                <div class="avg-card leftBorder4">
                                    <div class="avg-card-head">

                                        <h5>
                                            @lang('strings.backend.dashboard.average-completion-rate')
                                        </h5>
                                    </div>
                                    
                                    <div class="mt-3 d-flex justify-content-around align-items-center">
                                        <div>

                                            <div>
                                                <i class="fa fa-graduation-cap p-card-i" aria-hidden="true"></i>
                                                <span class="ml-3 font-weight-bold p-card-i">
                                                    @lang('strings.backend.dashboard.Completed') -
                                                    <span class="p-card-i">

                                                        {{ $total_completed }}
                                                    </span>
                                                </span>
                                            </div>
                                            <div class="mt-3" style="color: #344050;font-weight:bold">
                                                <i class="fa fa-hourglass-end" aria-hidden="true"></i>
                                                <span class="ml-4">
                                                    @lang('strings.backend.dashboard.Remaining') - {{ $total_pending }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="circular-progress-container">
                                            <svg class="circular-progress" width="120" height="120">
                                                <circle class="bg" cx="60" cy="60" r="50"></circle>
                                                <circle class="progress" cx="60" cy="60" r="50" id="progress1"></circle>
                                            </svg>
                                            <div class="percentage" id="value1">0%</div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12 col-sm-12">
                                <div class="avg-card leftBorder1">
                                    <div class="avg-card-head">
                                        <h5>
                                             @lang('strings.backend.dashboard.average-assessment-score')
                                        </h5>

                                    </div>
                                    <div class="mt-3 d-flex justify-content-around align-items-center">
                                        <div>

                                            <div class="p-card-i font-weight-bold">
                                                <i class="fa fa-graduation-cap" aria-hidden="true"></i>
                                                <span class="ml-3">
                                                     @lang('strings.backend.dashboard.Completed') -
                                                    <span id="completed_assesment"> {{ $completed_assesment }}</span>
                                                </span>
                                            </div>
                                            <div class="mt-3" style="color: #344050;font-weight:bold">
                                                <i class="fa fa-hourglass-end" aria-hidden="true"></i>
                                                <span class="ml-4">
                                                    @lang('strings.backend.dashboard.Remaining') - <span id="not_completed_assesment">{{ $not_completed_assesment }}</span>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="circular-progress-container">
                                            <svg class="circular-progress" width="120" height="120">
                                                <circle class="bg" cx="60" cy="60" r="50"></circle>
                                                <circle class="progress" cx="60" cy="60" r="50" id="progress2"></circle>
                                            </svg>
                                            <div class="percentage" id="value2">0%</div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12 col-sm-12">
                                <div class="avg-card leftBorder1">
                                    <div class="avg-card-head">
                                        <h5>
                                            @lang('strings.backend.dashboard.Total-Certificates')
                                        </h5>

                                    </div>
                                    <div class="mt-3 d-flex justify-content-around align-items-center">
                                        <div>

                                            <div class="p-card-i font-weight-bold">
                                              
                                                <div class="text-center">
                                                    @lang('strings.backend.dashboard.Achieved-certificates')
                                                    {{-- <span id="completed_assesment"></span>
                                                    </div> --}}
                                                <div class="certificate">  <i class="fa fa-trophy" aria-hidden="true"></i>  {{ $total_completed }}</div>
                                            </div>
                                            {{-- <div class="mt-3" style="color: #344050;font-weight:bold">
                                                <i class="fa fa-hourglass-end" aria-hidden="true"></i>
                                                <span class="ml-4">
                                                    Remaining - <span id="not_completed_assesment">{{ $total_pending }}</span>
                                                </span>
                                            </div> --}}
                                        </div>

                                        {{-- <div class="circular-progress-container">
                                            <svg class="circular-progress" width="120" height="120">
                                                <circle class="bg" cx="60" cy="60" r="50"></circle>
                                                <circle class="progress" cx="60" cy="60" r="50" id="progress3"></circle>
                                            </svg>
                                            <div class="percentage" id="value3">0%</div>
                                        </div> --}}
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    

                   
               
                

                @elseif(auth()->user()->hasRole('teacher'))
                    <div class="col-12">
                        <div class="row">
                            <div class="col-md-3 col-12 border-right">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card text-white bg-primary text-center">
                                            <div class="card-body" style="border: none;">
                                                <h2 class="">
                                                    {{ count(auth()->user()->courses) + count(auth()->user()->bundles) }}
                                                </h2>
                                                <h5>@lang('labels.backend.dashboard.your_courses_and_bundles')</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card text-white bg-success text-center">
                                            <div class="card-body">
                                                <h2 class="">{{ $students_count }}</h2>
                                                <h5>@lang('labels.backend.dashboard.students_enrolled')</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5 col-12 border-right">
                                <div class="d-inline-block form-group w-100">
                                    <h4 class="mb-0">@lang('labels.backend.dashboard.recent_reviews') <a class="btn btn-primary float-right"
                                            href="{{ route('admin.reviews.index') }}">@lang('labels.backend.dashboard.view_all')</a>
                                    </h4>

                                </div>
                                <table class="table table-responsive-sm table-striped">
                                    <thead>
                                        <tr>
                                            <td>@lang('labels.backend.dashboard.course')</td>
                                            <td>@lang('labels.backend.dashboard.review')</td>
                                            <td>@lang('labels.backend.dashboard.time')</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($recent_reviews) > 0)
                                        @foreach ($recent_reviews as $item)
                                        <tr>
                                            <td>
                                                <a target="_blank"
                                                    href="{{ route('courses.show', [$item->reviewable->slug]) }}">{{ $item->reviewable->title }}</a>
                                            </td>
                                            <td>{{ $item->content }}</td>
                                            <td>{{ $item->created_at->diffforhumans() }}</td>
                                        </tr>
                                        @endforeach
                                        @else
                                        <tr>
                                            <td colspan="3">@lang('labels.backend.dashboard.no_data')</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="d-inline-block form-group w-100">
                                    <h4 class="mb-0">@lang('labels.backend.dashboard.recent_messages') <a class="btn btn-primary float-right"
                                            href="{{ route('admin.messages') }}">@lang('labels.backend.dashboard.view_all')</a>
                                    </h4>
                                </div>


                                <table class="table table-responsive-sm table-striped">
                                    <thead>
                                        <tr>
                                            <td>@lang('labels.backend.dashboard.message_by')</td>
                                            <td>@lang('labels.backend.dashboard.message')</td>
                                            <td>@lang('labels.backend.dashboard.time')</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($threads) > 0)
                                        @foreach ($threads as $item)
                                        <tr>
                                            <td>
                                                <a target="_blank"
                                                    href="{{ asset('/user/messages/?thread=' . $item->id) }}">{{ $item->participants()->with('user')->where('user_id', '<>', auth()->user()->id)->first()->user->name }}</a>
                                            </td>
                                            <td>{{ $item->messages()->orderBy('id', 'desc')->first()->body }}
                                            </td>
                                            <td>{{ $item->messages()->orderBy('id', 'desc')->first()->created_at->diffForHumans() }}
                                            </td>
                                        </tr>
                                        @endforeach
                                        @else
                                        <tr>
                                            <td colspan="3">@lang('labels.backend.dashboard.no_data')</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

            </div>
        </div>
    </div>


    @elseif(auth()->user()->hasRole('administrator'))
    
    @else
    
    @endif

    <!--card-body-->
    <!--card-->
</div><!--col-->
@endsection

@push('after-scripts')


<script>
    function animateProgress(targetId, valueId, targetValue, speed = 10) {
        const progressCircle = document.getElementById(targetId);
        const valueDisplay = document.getElementById(valueId);

        if (!progressCircle || !valueDisplay) return; // prevent errors

        const circumference = 314;
        progressCircle.style.strokeDasharray = circumference;
        progressCircle.style.strokeDashoffset = circumference;

        let currentValue = 0;

        const interval = setInterval(() => {
            currentValue = Math.min(currentValue + 1, targetValue); // ensure it never exceeds target
            const offset = circumference - (circumference * currentValue) / 100;
            progressCircle.style.strokeDashoffset = offset;
            valueDisplay.textContent = `${currentValue}%`;

            if (currentValue >= targetValue) clearInterval(interval);
        }, speed);

        console.log("Animating:", targetId, valueId, targetValue);
    }


    // Run on DOM ready
    document.addEventListener("DOMContentLoaded", function() {
        animateProgress("progress1", "value1", {{ $av_completion_rate }});
        animateProgress("progress2", "value2", {{ $av_completed_score }});
        animateProgress("progress3", "value3", {{ '100' }});
    });

    $(document).ready(function () {
        $('#dashboard-form-filter').on('submit', function (e) {
            e.preventDefault();

            let form = $(this);
            let formData = form.serialize();

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                beforeSend: function () {
                    $('#dashboard-result').html('<p>Loading...</p>');
                },
                success: function (response) {
                    console.log("AJAX Response dev:", response);
                    //alert("hi")
                    // force update
                    $('#av-comp_rate').text(response.total_completed);
                    $('#av-rem-comp_rate').text(response.total_pending);

                    // handle 0 correctly
                    //alert(response.av_completion_rate)
                    animateProgress("progress1", "value1", Number(response.av_completion_rate) || 0);
                    animateProgress("progress2", "value2", Number(response.av_completed_score) || 0);

                    $('#av-comp-score').text(response.completed_assesment);
                    $('#av-not-comp-score').text(response.not_completed_assesment);

                    //alert(response.av_completed_score)
                    

                    $('#advance-search-btn').prop('disabled', false);
                },

                error: function (xhr) {
                    let message = 'Something went wrong.';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        message = '';
                        $.each(xhr.responseJSON.errors, function (key, errors) {
                            message += `<p>${errors.join('<br>')}</p>`;
                        });
                    }

                    $('#advance-search-btn').prop('disabled', false);

                    $('#dashboard-result').html('<div class="alert alert-danger">' + message + '</div>');
                }
            });
        });
    });
</script>
@endpush