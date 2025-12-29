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

    
    .control-btns {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
    }
    .dropdown-item{
        border-bottom: none;
   
    }

     .select2-container--default .select2-selection--single .select2-selection__arrow {
    display: none !important;
}
   .select2-container .select2-search--inline .select2-search__field {
    box-sizing: border-box;
    border: none;
    font-size: 100%;
    margin-top: 5px;
    padding-left: 8px;
}

.select2-container--default .select2-selection--multiple:focus {
    outline: none !important;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5) !important;
    border-color: #007bff !important;
}
.select2-container--default.select2-container--focus .select2-selection--multiple {
     outline: none !important;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5) !important;
    border-color: #007bff !important;
}
.select2-container--default .select2-selection--multiple{
    border: 1px solid #ccc !important;
}

.select2-container--default .select2-selection--single {
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow b{
    display: none;
}
.select2-container .select2-selection--single .select2-selection__rendered {
    display: block;
    padding-left: 10px;
    padding-right: 20px;
    padding-top: 3px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.select2-container .select2-selection--single {
    box-sizing: border-box;
    cursor: pointer;
    display: block;
    height: 34px;
    user-select: none;
    -webkit-user-select: none;
}
.buttons-colvis{
top: 7px !important;
}

.dt-buttons a:hover svg {
    color: #007bff !important;
}

#advance-search-btn {
    background: linear-gradient(45deg, #233e74 0%, #c1902d 100%);
    border: none;
    color: #fff;
    transition: all 0.3s ease;
}

#advance-search-btn:hover {
    background: linear-gradient(45deg, #c1902d 0%, #233e74 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>
@endpush
@php
$local_lang = App::getLocale() ?? 'en';
@endphp
@section('content')

<div class="row">
@if (auth()->user()->hasRole('student'))
<div class="col-12 text-left heading-text userheading">


  <!--  <h4> @lang('strings.backend.dashboard.welcome') <span>{{ $logged_in_user->name }} {{ $logged_in_user->id }}</span> </h4> -->

 <h4> <span> @lang('labels.backend.dashboard.my_courses')</span></h4>

</div>
@endif
</div>



<div class="">



  
    <div class="">

        <div class="">

            <div class="">

                
               @if (auth()->user()->hasRole('student')) 

                
                
                    <form id="advace_filter" method="GET">
                        <div class="row">
                            <div class="col-lg-4 col-sm-6 col-xs-12 mt-3">
                                <div for="">
                                    @lang('labels.backend.dashboard.Course-Name') 
                                </div>
                                <div class="custom-select-wrapper mt-2">
                                <input type="text" class="form-control" name="course_name" id="course_name" value="{{ request('course_name') }}" placeholder="@lang('labels.backend.dashboard.type-your-course') " />
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-xs-12 mt-3">
                                <div for="">
                                    @lang('labels.backend.dashboard.course-status') 
                                </div>
                                <div class="custom-select-wrapper mt-2">
                                <select class="form-control custom-select-box select2 js-example-placeholder-single" name="course_status" id="course_status" >
                                    <option value=""> @lang('labels.backend.dashboard.select-one') </option>
                                    <option @if('InProgress' == request()->course_status) selected @endif value="InProgress">@lang('labels.backend.dashboard.in-progress')</option>
                                    <option @if('NotStarted' == request()->course_status) selected @endif value="NotStarted">@lang('labels.backend.dashboard.not-stated')</option>
                                    <option @if('Completed' == request()->course_status) selected @endif value="Completed">@lang('labels.backend.dashboard.completed')</option>
                                </select>
                                <span class="custom-select-icon" style="right: 10px;">
                                            <i class="fa fa-chevron-down"></i>
                                        </span>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-xs-12 mt-3" id="email-block">
                                @lang('labels.backend.dashboard.by-due-date')
                                <div class="custom-select-wrapper mt-2">
                                <select class="form-control custom-select-box select2 js-example-placeholder-single" name="by_due_date" id="by_due_date" >
                                    <option value=""> @lang('labels.backend.dashboard.select-one')</option>
                                    <option value="late"> @lang('labels.backend.dashboard.late')</option>
                                    <option value="soon"> @lang('labels.backend.dashboard.soon')</option>
                                    
                                </select>
                                <span class="custom-select-icon" style="right: 10px;">
                                            <i class="fa fa-chevron-down"></i>
                                        </span>
                            </div>
                            </div>
                            

                            <div class="col-lg-4 col-md-12 col-sm-6 col-xs-12 d-flex align-items-center mt-4">

                            <div class="d-flex justify-content-between mt-3">
                                <div>
                                    <button class="btn btn-primary" id="advance-search-btn" type="submit">@lang('labels.backend.dashboard.advance-search')</button>
                                </div>
                                <div>
                                    <button class="btn btn-danger ml-3" id="reset" type="button">@lang('labels.backend.dashboard.reset')</button>

                                </div>
                                
                            </div>
                            </div>
                        </div>
                    </form>
                    
 <!--  <div class="row">
                    {{-- add comment -anup --}}
                    <div class="col-12 text-left heading-text mab10">
                        <h4>@lang('labels.backend.dashboard.my_courses')</h4>
                    </div>
</div> -->
                    {{-- add comment -anup ends --}}
                    {{-- add comment -anup --}}
                    
                <div class="row">

                    @if (isset($subscribe_courses) && count($subscribe_courses) > 0)


                        @php
                        $local_lang = App::getLocale() ?? 'en';
                        @endphp
                        @foreach ($subscribe_courses as $item)
                        <?php
                        $cat_slug = '';
                        $cat_name = '';

                        //$category_details = CustomHelper::getCategoryName($item->course->category_id);
                        $category_details = $item->course->category;

                        if (isset($category_details)) {
                            $cat_slug = $category_details->slug;
                            $cat_name = $category_details->name;
                        }
                        ?>
                        <div class="col-md-4 col-lg-4 col-sm-6 col-xs-12 ">

                            <div class="user-course-card position-relative border"> 
                                <div class="best-course-pic position-relative overflow-hidden"
                                    @if ($item->course->course_image != '') style="background-image: url({{  $item->course->course_image }}); border-radius: 5px;" @endif>

                                    @if ($item->trending == 1)
                                    <div class="trend-badge-2 text-center text-uppercase">
                                        <i class="fas fa-bolt"></i>
                                        <span>@lang('labels.backend.dashboard.trending') </span>
                                    </div>
                                    @endif

                                    <div class="course-rate ul-li">
                                        <ul>
                                            @for ($i = 1; $i <= (int) $item->rating; $i++)
                                                <li><i class="fas fa-star"></i></li>
                                                @endfor
                                        </ul>
                                    </div>
                                </div>
                                <div class="user-clist">
                                    <div class="course-title mb20 headline relative-position">
                                        <h5>
                                            <a
                                                class="course-head"
                                                href="{{ route('courses.show', [$item->course->slug]) }}">

                                                {{
                                                                            $local_lang == 'ar' 
                                                                            ? $item->course->arabic_title ??  $item->course->title
                                                                            : $item->course->title
                                                                        }}

                                            </a>
                                        </h5>
                                    </div>
                                    <span class="course-category coursetag">
                                                <a href="{{ route('courses.category', ['category' => $cat_slug]) }}"
                                                    class="">{{ $cat_name }}</a>
                                            </span>
                                    <div class="course-meta d-inline-block w-100 ">
                                        <div class="d-inline-block w-100 0 mt-2 main-info">
                                            
                                            <div class="addi-info">
                                                <div class="course-author">
                                                
                                                <div> {{ trans('course.total_lessons') }}</div> 
                            <div class="flex"><i class="ri-git-repository-line"></i> {{ $item->course->publishedCourseLessons()->count() }}</div>

                                                </div>  
                                                <div class="course-author">
                                                    <div>{{ trans('course.duration') }}</div> 
                                                <div class="flex"><i class="ri-history-line"></i> {{ $item->course->courseAllLessonDuration 
                                                                            }}</div>  
                                                
                                                                        </div>
                                            </div>
                                        </div>

                                        <div class="progress">
                                            <div class="progress-bar"
                                                style="width:{{ 
                                                                            $item->assignment_progress ?? 0
                                                                        }}%">
                                                                        <span> {{
                                                                            $item->assignment_progress
                                                                        }}% </span> 
                                                                        <span>
                                                @lang('labels.backend.dashboard.completed')</span>
                                            
                                            </div>
                                        </div>
                                        <div class="duedate">
                                        <?php

                                        /*
                                                                $ass = DB::table('course_assignment')
                                                                    ->where('course_id', $item->course->id)
                                                                    ->where('assign_to', Auth::user()->id)
                                                                    ->first();
                                                                if (!empty($ass) && !empty($ass->due_date)) {
                                                                    echo 'Due Date: ' . date('d/m/Y', strtotime($ass->due_date));
                                                                } else {
                                                                    echo !empty($item->due_date) ? 'Due Date: ' . date('d/m/Y', strtotime($item->due_date)) : null;
                                                                }
                                                                */
                                        if ($item->due_date) {
                                            echo '<i class="ri-calendar-schedule-line"></i> <span style="color: #64748b;"> ' . __('labels.backend.dashboard.due_date') . ': ' . date('d/m/Y', strtotime($item->due_date)) . '</span>';
                                        }

                                        ?>
                                        </div>
                                        <div class="dcertificate">
                                        @if ($item->is_completed && $item->grant_certificate == 1)
                                        <a class="btn btn-success"
                                            href="{{ route('admin.certificates.generate', ['course_id' => $item->course->id, 'user_id' => auth()->id()]) }}"> {{ trans('course.btn.download_certificate') }}
                                        </a>
                                        @endif
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>

                    @endforeach
                    @else
                        <div class="col-12 text-center">
                            <h4 class="text-center">@lang('labels.backend.dashboard.no_data')</h4>
                            {{-- <a class="btn btn-primary" href="{{ route('courses.all') }}">Subscribe Course
                            <i class="fa fa-arrow-right"></i></a> --}}
                        </div>
                    @endif
                    
                    </div>
                    {{-- add comment -anup ends --}}    
                    

                
                   
                @if (isset($learning_pathways) && count($learning_pathways) > 0)
                    <div class="col-12 pt-5 heading-text">
                        <h4 class="mypaths">@lang('My Learning Paths')</h4>
                    </div>

                    @foreach ($learning_pathways as $learning_pathway)
                        @php
                        $prevCourseProgress = 100;
                        @endphp
                        <h5 class="col-md-12 mt-2 heading-text subtitle">{{ $learning_pathway->learningPathway->title }}</h5>


                        <div class="row">
                        @foreach ($learning_pathway->learningPathwayCoursesOrdered as $item)
                            <?php
                            $cat_slug = '';
                            $cat_name = '';

                            $category_details = $item->course->category;

                            if (isset($category_details)) {
                                $cat_slug = $category_details->slug;
                                $cat_name = $category_details->name;
                            }
                            ?>
                            <div class="col-md-4 col-lg-4 col-sm-6 col-xs-12 @if($prevCourseProgress<100 && $learning_pathway->learningPathway->in_sequence) disabled-course @endif">
                                @if(
                                    $prevCourseProgress<100 && $learning_pathway->learningPathway->in_sequence
                                    )
                                    <img src="/assets/img/lock-icon.png" class="lock-image" alt="">
                                @endif
                                <div class="levelrow"> <div> Level</div> <span class="leveitem"> {{ $item->position }}</span></div>
                                <div class="user-course-card best-course-pic-text position-relative mb-4">
                                    <div class="best-course-pic position-relative overflow-hidden"
                                        @if ($item->course->course_image != '') style="background-image: url({{ asset('storage/uploads/' . $item->course->course_image) }});border-radius: 5px;" @endif>

                                        @if ($item->trending == 1)
                                        <div class="trend-badge-2 text-center text-uppercase">
                                            <i class="fas fa-bolt"></i>
                                            <span>@lang('labels.backend.dashboard.trending') </span>
                                        </div>
                                        @endif

                                        <div class="course-rate ul-li">
                                            <ul>
                                                @for ($i = 1; $i <= (int) $item->rating; $i++)
                                                    <li><i class="fas fa-star"></i></li>
                                                    @endfor
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="user-clist">
                                        <div class="course-title mb20 headline relative-position">
                                            <h5 class="heading-text">
                                                @if($learning_pathway->learningPathway->in_sequence)
                                                <a
                                                    href="@if($prevCourseProgress==100){{ route('courses.show', [$item->course->slug]) }}@endif"
                                                    class="w-100">

                                                    {{
                                                                            $local_lang == 'ar' 
                                                                            ? $item->course->arabic_title ??  $item->course->title
                                                                            : $item->course->title
                                                                            }}
                                                </a>
                                                @else
                                                <a
                                                    href="{{ route('courses.show', [$item->course->slug]) }}">
                                                    {{
                                                                            $local_lang == 'ar' 
                                                                            ? $item->course->arabic_title ??  $item->course->title
                                                                            : $item->course->title
                                                                            }}
                                                </a>
                                                @endif
                                            </h5>
                                        </div>
                                        <span class="course-category coursetag">
                                                    @if($learning_pathway->learningPathway->in_sequence)
                                                    <a href="@if($prevCourseProgress==100){{ route('courses.category', ['category' => $cat_slug]) }} @endif"
                                                        class="pill-publish">{{ $cat_name }}
                                                    </a>
                                                    @else
                                                    <a href="{{ route('courses.category', ['category' => $cat_slug]) }}"
                                                        class="pill-publish">{{ $cat_name }}
                                                    </a>
                                                    @endif
                                                </span>
                                        <div class="course-meta d-inline-block w-100 ">
                                            <div class="w-100 0 mt-2">
                                                
                                                <div>
                                    <div class="addi-info">
                                                    <div class="course-author ">
                                                    <div> {{ trans('course.total_lessons') }}</div>
                                                        <div class="flex"><i class="ri-git-repository-line"></i>{{
                                                                                $item->course->lessons->count()
                                                                                }}  </div>
                                                        
                                                    </div>  
                                                    <div class="course-author ">
                                                        {{ trans('course.duration') }}
                                                    <div class="flex"><i class="ri-history-line"></i> {{ $item->course->courseAllLessonDuration }}
                                                    </div>
                                                    </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="progress">
                                                <div class="progress-bar"
                                                    style="width:{{ 
                                                                        $item->subscribedCourse->assignment_progress ?? 0
                                                                        }}%">
                                                                    <span>{{
                                                                        $item->subscribedCourse->assignment_progress
                                                                        
                                                                            }} %</span> 
                                                                            <span class="ctext">
                                                    @lang('labels.backend.dashboard.completed')
                                                    </span>
                                                    

                                                </div>
                                            </div>
                                            <div class="duedate">
                                            <?php

                                            if ($item->subscribedCourse->due_date) {
                                                echo '<i class="ri-calendar-schedule-line"></i> Due Date: ' . date('d/m/Y', strtotime($item->subscribedCourse->due_date));
                                            }

                                            ?>
                                            </div>
                                            <div class="dcertificate">
                                            @if ($item->subscribedCourse->is_completed)
                                            <a class="btn btn-success"
                                                href="{{ route('admin.certificates.generate', ['course_id' => $item->course->id, 'user_id' => auth()->id()]) }}"> {{ trans('course.btn.download_certificate') }}</a>
                                            @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php
                            $prevCourseProgress = $item->subscribedCourse->assignment_progress ?? 0;
                            @endphp

                        @endforeach
                        </div>
                    @endforeach
                @endif
                                        

                @elseif(auth()->user()->hasRole('teacher'))
                    

            </div>
        </div>
    </div>


    
    @endif

    <!--card-body-->
    <!--card-->
</div><!--col-->
@endsection

@push('after-scripts')


<script>
    
        $('#advace_filter').submit(function (e) {
            e.preventDefault();
            $('#advance-search-btn').prop('disabled', true);
            loadDataTable(); // 👉 filter submission
        });

        
        $('#reset').click(function (e) {
            window.location.href = `${window.location.pathname}`;
        });

        function loadDataTable()
        {
            let course_status = $('#course_status').val();
            let by_due_date = $('#by_due_date').val();
            let course_name = $('#course_name').val();

            window.location.href = `${window.location.pathname}?course_name=${encodeURIComponent(course_name)}&course_status=${encodeURIComponent(course_status)}&by_due_date=${encodeURIComponent(by_due_date)}`;

        }
</script>
@endpush