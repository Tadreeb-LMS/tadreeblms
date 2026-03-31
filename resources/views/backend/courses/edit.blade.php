@extends('backend.layouts.app')
@section('title', __('labels.backend.courses.title') . ' | ' . app_name())
@push('after-styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet" />
@endpush
@section('content')
    <style>
.payment-options{
    display:flex;
    gap:15px;
}

.payment-card{
    border:2px solid #ddd;
    padding:15px 20px;
    border-radius:8px;
    cursor:pointer;
    width:180px;
    transition:0.2s;
    display:flex;
    flex-direction:column;
}

.payment-card input{
    margin-bottom:8px;
}

.payment-card:hover{
    border-color:#007bff;
    background:#f8f9fa;
}

.free-option{
    border-color:#28a745;
}

.paid-option{
    border-color:#dc3545;
}

.payment-title{
    font-weight:600;
    font-size:16px;
}

.payment-desc{
    font-size:12px;
    color:#666;
}

.price-box{
    max-width:250px;
}

#price_field{
    margin-top:25px;
}
        .float-right.gap-20 {
            gap: 20px;
            justify-content: right;
        }

        span.course-type-desc {
            padding: 0 0 0 20px;
            font-size: 12px;
            font-weight: bold;
            font-style: italic;
        }
        .create_done {
            padding: 10px 40px;
            font-size: 16px;
            font-weight: 500;
            background: #20a8d8;
            border: none;
            outline: none;
            float: right;
            margin: 0 15px 0 0;
        }

        .create_done.next {
            background: #4dbd74;
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
    padding-top: 1px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.select2-container .select2-selection--single {
    box-sizing: border-box;
    cursor: pointer;
    display: block;
    height: 32px;
    user-select: none;
    -webkit-user-select: none;
}
    </style>

    

    @include('backend.includes.partials.course-steps', ['step' => 1, 'course_id' => $course->id, 'course' => $course ])

    <form method="POST" action="{{ route('admin.courses.update', $course->id) }}" enctype="multipart/form-data" id="updateCourse">
    @csrf
    @method('PUT')

    <div>
        <div class="pb-3 d-flex justify-content-between addcourseheader">

            
             <h5 >
                 @lang('labels.backend.courses.create')
             </h5>
            
                 <div class="">
                     <a href="{{ route('admin.courses.index') }}" class="btn btn-primary">@lang('labels.backend.courses.view')</a>
        
                 </div>
         
        </div>
        <div class="card coursesteps">
        <!-- <div class="card-header">
            <h3 class="page-title float-left">@lang('labels.backend.courses.create')</h3>
            <div class="float-right">
                <a href="{{ route('admin.courses.index') }}" class="btn btn-success">@lang('labels.backend.courses.view')</a>
            </div>
        </div> -->

        <div class="card-body">
            @if (Auth::user()->isAdmin())

            <div class="row">
             <div class="col-md-6 col-12 form-group frmbm10">
                     <div class="row">
                    <div class="col-md-8 col-12 form-group">
                        <div>
                            Teachers
                        </div>
                        <div class="custom-select-wrapper mt-2">
                            <select name="teachers[]" class="form-control custom-select-box select2 js-example-placeholder-multiple" multiple required>
                                @foreach($teachers as $id => $name)
                                    <option value="{{ $id }}" {{ in_array($id, old('teachers', $course->teachers->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <span class="custom-select-icon">
                                <i class="fa fa-chevron-down"></i>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-1 col-12 d-flex form-group flex-column"><span class="ortext">
                        OR
                       </span></div>
                    <div class="col-md-3 col-12 d-flex form-group flex-column">
                        <a target="_blank" class="btn btn-primary mt-auto"
                            href="{{ url('user/teachers/create?teacher') }}">{{ trans('labels.backend.courses.add_teachers') }}</a>
                    </div>
                </div>
            @endif


              <div class="row">
                <div class="col-md-8 col-12 form-group">
                    <div>Category</div>
                   <div class="custom-select-wrapper mt-2">
                    <select name="category_id" class="form-control custom-select-box select2 js-example-placeholder-single" required>
                        <option value="">{{ trans('labels.backend.courses.select_category') }}</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}" {{ old('category_id', $course->category_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
    <span class="custom-select-icon">
        <i class="fa fa-chevron-down"></i>
    </span>
</div>
                </div><div class="col-md-1 col-12 d-flex form-group flex-column"><span class="ortext">
                        OR
                       </span></div>
                <div class="col-md-3 col-12 d-flex form-group flex-column">
                    <a target="_blank" class="btn btn-primary mt-auto"
                        href="{{ route('admin.categories.create') . '?create' }}">{{ trans('labels.backend.courses.add_categories') }}</a>
                </div>
            </div>

                <div class="form-group">
                    <label for="course_code" class="control-label">Course Code *</label>
                    <input class="form-control" placeholder="Course code" name="course_code" type="text" value="{{ old('course_code', $course->course_code) }}">
                </div>
<div class="form-group">
                    <div>

                        <label for="slug" class="control-label">{{ trans('Course Language') }}</label>
                    </div>
                    <div class="custom-select-wrapper">

                        <select name="course_lang" class="form-control custom-select-box">
                            <option @if($course->course_lang == 'english') selected @endif value="english">English</option>
                            <option @if($course->course_lang == 'arabic') selected @endif value="arabic">Arabic</option>
                        </select>
                        <span class="custom-select-icon">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="title" class="control-label">{{ trans('labels.backend.courses.fields.title') }} *</label>
                    <input class="form-control" placeholder="{{ trans('labels.backend.courses.fields.title') }}" name="title" type="text" value="{{ old('title', $course->title) }}">
                </div>


                </div>
              <div class="col-md-6 col-12 form-group">
                    <div class="form-group">
                    <label for="description" class="control-label">{{ trans('labels.backend.courses.fields.description') }}</label>
                    <textarea class="form-control editor" placeholder="{{ trans('labels.backend.courses.fields.description') }}" name="description">{{ old('description', $course->description) }}</textarea>

                </div>
                </div>
            </div>
               



            

            @if (Auth::user()->isAdmin())
                {{-- <div class="row">
                        <div class="col-10 form-group">
                            {!! Form::label('internal_students', trans('labels.backend.courses.fields.internal_students'), [
                                'class' => 'control-label',
                            ]) !!}
                            {!! Form::select('internalStudents[]', $internalStudents, old('internalStudents'), [
                                'class' => 'form-control select2 js-example-internal-student-placeholder-multiple',
                                'multiple' => 'multiple',
                                'required' => false,
                            ]) !!}
                        </div>
                    </div> --}}
            @endif

            @if (Auth::user()->isAdmin())
                {{-- <div class="row">
            <div class="col-10 form-group">
                {!! Form::label('external_students',trans('labels.backend.courses.fields.external_students'), ['class' => 'control-label']) !!}
                {!! Form::select('externalStudents[]', $externalStudents, old('externalStudents'), ['class' => 'form-control select2 js-example-external-student-placeholder-multiple', 'multiple' => 'multiple', 'required' => false]) !!}
            </div>
        </div> --}}
            @endif

          

            <div class="row">

                
                
                
                
                {{-- <div class="col-sm-12 col-lg-4 col-md-12 form-group">
                    {!! Form::label('slug', trans('Title In Arabic') . ' *', ['class' => 'control-label']) !!}
                    {!! Form::text('arabic_title', old('arabic_title'), [
                        'class' => 'form-control',
                        'placeholder' => trans('Arabic Title'),
                    ]) !!}

                </div> --}}
                {{-- <div class="col-md-12 col-lg-6 form-group">
                    {!! Form::label('slug', trans('labels.backend.courses.fields.slug'), ['class' => 'control-label']) !!}
                    {!! Form::text('slug', old('slug'), [
                        'class' => 'form-control',
                        'placeholder' => trans('labels.backend.courses.slug_placeholder'),
                    ]) !!}

                </div> --}}
                
            </div>
             
            <div class="row">
                {{-- <div class="col-sm-12 col-lg-2 col-md-12 form-group">
                    <label for="price" class="control-label">{{ trans('labels.backend.courses.fields.price') }}</label>
                    <input class="form-control" placeholder="{{ trans('labels.backend.courses.fields.price') }}" step="any" pattern="[0-9]" name="price" type="number" value="{{ old('price', $course->price) }}">
                </div> --}}
                {{-- <div class="col-sm-12 col-lg-4 col-md-12">
                    <label for="control-label">@lang('Minimum percentage required to qualify')</label>
                    <input type="number" name="marks_required" class="form-control"
                        oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value > 100) this.value = 100; if(this.value < 1 && this.value != '') this.value = 1;">
                </div> --}}
                {{-- <div class="col-12 col-lg-4 form-group">
                                {!! Form::label(
                                    'strike',
                                    trans('labels.backend.courses.fields.strike') . ' (in ' . $appCurrency['symbol'] . ')',
                                    ['class' => 'control-label'],
                                ) !!}
                                {!! Form::number('strike', old('strike'), [
                                    'class' => 'form-control',
                                    'placeholder' => trans('labels.backend.courses.fields.strike'),
                                    'step' => 'any',
                                    'pattern' => '[0-9]',
                                ]) !!}
                            </div> --}}
                <div class="col-sm-12 col-lg-4 col-md-12 form-group">
                    <div style="margin-bottom: 8px;">
                        Course Image
                    </div>

                   <div class="custom-file-upload-wrapper">
                        <input type="file" name="course_image" id="customFileInput" class="custom-file-input">
                        <label for="customFileInput" class="custom-file-label">
                            <i class="fa fa-upload mr-1"></i> Choose a file
                        </label>
                    </div>

                </div>
                
               <div id="date-fields" class="row">
    <div class="col-sm-12 col-lg-4 col-md-12 form-group">
        <label for="start_date" class="control-label">{{ trans('labels.backend.courses.fields.start_date') }} (yyyy-mm-dd) *</label>
        <input class="form-control" id="start_date" autocomplete="off" name="start_date" type="text" value="{{ old('start_date', $course->start_date) }}">
    </div>

    @if (Auth::user()->isAdmin())
    <div class="col-sm-12 col-lg-4 col-md-12 form-group">
        <label for="expire_at" class="control-label">{{ trans('labels.backend.courses.fields.expire_at') }} (yyyy-mm-dd) *</label>
        <input class="form-control" id="expire_at" autocomplete="off" name="expire_at" type="text" value="{{ old('expire_at', $course->expire_at) }}">
    </div>
    @endif
</div>
                    </div>

            <!-- <div class="row">
                        <label class="col-md-2 form-control-label" for="first_name">Select Department</label>

                        <div class="col-md-10">
                            <select name="department_id" class="form-control">
                                <option value=""> Select One </option>
                                @foreach ($departments as $row)
    <option value="{{ $row->id }}"> {{ $row->title }} </option>
    @endforeach
                            </select>
                        </div> -->
            <!--col-->
            <!-- </div> -->

            <div class="row">
                <div class="col-md-12 form-group">
                    <input class="course-type mr-2 " type="radio" @if($course->is_online == 'Online') checked @endif name="course_type" value="Online" /> E-Learning
                    <input class="course-type ml-2 mr-2" type="radio" @if($course->is_online == 'Offline') checked @endif name="course_type" value="Offline" /> Live-Online
                    <input class="course-type ml-2 mr-2" type="radio" @if($course->is_online == 'Live-Classroom') checked @endif name="course_type" value="Live-Classroom" /> Live-Classroom
                </div>
                <span class="course-type-desc">
                    <span id="e-learning">
                        E-Learning type course is a course which can be taken online.
                    </span>
                    <span id="live-online" style="display: none;">
                        Live-Online type course is a course can be done on goole meet/Zoom link.
                        @if(count($enabledMeetingProviders ?? []))
                            <div class="card mt-3" id="meeting-provider-section">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fa fa-video-camera mr-2"></i> Meeting Configuration</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label for="meeting_provider">Meeting Provider *</label>
                                            <select name="meeting_provider" id="meeting_provider" class="form-control">
                                                @foreach($enabledMeetingProviders as $key => $label)
                                                    <option value="{{ $key }}" {{ $course->meeting_provider == $key ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="meeting_timezone">Timezone</label>
                                            <input type="text" name="meeting_timezone" id="meeting_timezone" class="form-control" value="{{ $course->meeting_timezone ?? 'Asia/Riyadh' }}">
                                        </div>
                                    </div>
                                    {{-- Single meeting fields (only shown when NO schedule type selected) --}}
                                    <div class="row" id="single-meeting-fields" @if(in_array($course->schedule_type ?? '', ['daily','weekly','custom'])) style="display:none;" @endif>
                                        <div class="col-md-4 form-group">
                                            <label for="meeting_start_date">Start Date *</label>
                                            <input type="date" name="meeting_start_date" id="meeting_start_date" class="form-control" value="{{ $course->meeting_start_at ? \Carbon\Carbon::parse($course->meeting_start_at)->format('Y-m-d') : '' }}" min="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label for="meeting_start_time">Start Time *</label>
                                            <input type="time" name="meeting_start_time" id="meeting_start_time" class="form-control" value="{{ $course->meeting_start_at ? \Carbon\Carbon::parse($course->meeting_start_at)->format('H:i') : '' }}">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label for="meeting_duration">Duration (mins) *</label>
                                            <input type="number" name="meeting_duration" id="meeting_duration" class="form-control" value="{{ $course->meeting_duration ?? 60 }}">
                                            <input type="hidden" name="meeting_start_at" id="meeting_start_at">
                                        </div>
                                    </div>
                                    <small class="text-muted" id="single-meeting-hint" @if(in_array($course->schedule_type ?? '', ['daily','weekly','custom'])) style="display:none;" @endif>For a single meeting. Or choose a schedule type below for recurring sessions.</small>
                                </div>
                            </div>
                        @endif

                        {{-- Live Session Scheduling Section --}}
                        <div class="card mt-3" id="schedule-section">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fa fa-calendar mr-2"></i> Live Session Scheduling</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="font-weight-bold">Schedule Type *</label>
                                    <div class="d-flex gap-3 mt-2">
                                        <label class="mr-4"><input type="radio" name="schedule_type" value="daily" class="mr-1 schedule-type-radio" {{ ($course->schedule_type ?? '') == 'daily' ? 'checked' : '' }}> Daily</label>
                                        <label class="mr-4"><input type="radio" name="schedule_type" value="weekly" class="mr-1 schedule-type-radio" {{ ($course->schedule_type ?? '') == 'weekly' ? 'checked' : '' }}> Weekly</label>
                                        <label class="mr-4"><input type="radio" name="schedule_type" value="custom" class="mr-1 schedule-type-radio" {{ ($course->schedule_type ?? '') == 'custom' ? 'checked' : '' }}> Custom</label>
                                    </div>
                                </div>

                                {{-- Daily Options --}}
                                <div id="schedule-daily" class="schedule-panel" style="display:none;">
                                    <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label>Session Time *</label>
                                            <input type="time" name="daily_time" id="daily_time" class="form-control">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Duration (mins) *</label>
                                            <input type="number" name="daily_duration" id="daily_duration" class="form-control" value="60" min="1">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Repeat *</label>
                                            <select name="daily_repeat" id="daily_repeat" class="form-control">
                                                <option value="every_day">Every Day</option>
                                                <option value="weekdays">Weekdays Only (Mon-Fri)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <small class="text-muted">Sessions will be auto-generated between course Start Date and End Date.</small>
                                </div>

                                {{-- Weekly Options --}}
                                <div id="schedule-weekly" class="schedule-panel" style="display:none;">
                                    <div class="row">
                                        <div class="col-md-12 form-group">
                                            <label>Select Days *</label>
                                            @php $savedDays = $course->schedule_days ?? []; @endphp
                                            <div class="d-flex flex-wrap gap-2 mt-1">
                                                <label class="mr-3"><input type="checkbox" name="weekly_days[]" value="1" class="mr-1" {{ in_array(1, $savedDays) ? 'checked' : '' }}> Monday</label>
                                                <label class="mr-3"><input type="checkbox" name="weekly_days[]" value="2" class="mr-1" {{ in_array(2, $savedDays) ? 'checked' : '' }}> Tuesday</label>
                                                <label class="mr-3"><input type="checkbox" name="weekly_days[]" value="3" class="mr-1" {{ in_array(3, $savedDays) ? 'checked' : '' }}> Wednesday</label>
                                                <label class="mr-3"><input type="checkbox" name="weekly_days[]" value="4" class="mr-1" {{ in_array(4, $savedDays) ? 'checked' : '' }}> Thursday</label>
                                                <label class="mr-3"><input type="checkbox" name="weekly_days[]" value="5" class="mr-1" {{ in_array(5, $savedDays) ? 'checked' : '' }}> Friday</label>
                                                <label class="mr-3"><input type="checkbox" name="weekly_days[]" value="6" class="mr-1" {{ in_array(6, $savedDays) ? 'checked' : '' }}> Saturday</label>
                                                <label class="mr-3"><input type="checkbox" name="weekly_days[]" value="0" class="mr-1" {{ in_array(0, $savedDays) ? 'checked' : '' }}> Sunday</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label>Session Time *</label>
                                            <input type="time" name="weekly_time" id="weekly_time" class="form-control">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Duration (mins) *</label>
                                            <input type="number" name="weekly_duration" id="weekly_duration" class="form-control" value="60" min="1">
                                        </div>
                                    </div>
                                    <small class="text-muted">Sessions will repeat on selected days between course Start Date and End Date.</small>
                                </div>

                                {{-- Custom Options --}}
                                <div id="schedule-custom" class="schedule-panel" style="display:none;">
                                    <div id="custom-sessions-container">
                                        <div class="row custom-session-row mb-2">
                                            <div class="col-md-4 form-group">
                                                <label>Date *</label>
                                                <input type="date" name="custom_dates[]" class="form-control custom-session-date">
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <label>Time *</label>
                                                <input type="time" name="custom_times[]" class="form-control">
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <label>Duration (mins) *</label>
                                                <input type="number" name="custom_durations[]" class="form-control" value="60" min="1">
                                            </div>
                                            <div class="col-md-2 form-group d-flex align-items-end">
                                                <button type="button" class="btn btn-danger btn-sm remove-session-btn" style="display:none;">&times; Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-session-btn">
                                        <i class="fa fa-plus mr-1"></i> Add Session
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Existing Live Sessions Table --}}
                        @if($course->liveSessions && $course->liveSessions->count() > 0)
                        <div class="card mt-3">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fa fa-list mr-2"></i> Scheduled Sessions ({{ $course->liveSessions->count() }})</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Date</th>
                                                <th>Day</th>
                                                <th>Time</th>
                                                <th>Duration</th>
                                                <th>Meeting Link</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($course->liveSessions as $index => $session)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $session->session_date->format('Y-m-d') }}</td>
                                                <td>{{ $session->session_date->format('l') }}</td>
                                                <td>{{ \Carbon\Carbon::parse($session->session_time)->format('h:i A') }}</td>
                                                <td>{{ $session->duration }} mins</td>
                                                <td>
                                                    @if($session->meeting_link)
                                                        <a href="{{ $session->meeting_link }}" target="_blank" class="btn btn-sm btn-primary">Join</a>
                                                    @else
                                                        <span class="text-muted">Pending</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($session->session_date->isPast())
                                                        <span class="badge badge-secondary">Completed</span>
                                                    @elseif($session->session_date->isToday())
                                                        <span class="badge badge-success">Today</span>
                                                    @else
                                                        <span class="badge badge-primary">Upcoming</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex align-items-center justify-content-between">
                                    @if($course->liveSessions->whereNull('meeting_link')->count() > 0)
                                        <span class="text-danger">
                                            <i class="fa fa-exclamation-triangle mr-1"></i>
                                            {{ $course->liveSessions->whereNull('meeting_link')->count() }} session(s) have missing meeting links.
                                        </span>
                                    @else
                                        <span class="text-success">
                                            <i class="fa fa-check-circle mr-1"></i> All sessions have meeting links.
                                        </span>
                                    @endif
                                    <form method="POST" action="{{ route('admin.courses.regenerate_meeting_links', $course->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-{{ $course->liveSessions->whereNull('meeting_link')->count() > 0 ? 'warning' : 'outline-secondary' }} btn-sm">
                                            <i class="fa fa-refresh mr-1"></i> Regenerate Meeting Links
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </span>
                    <span id="live-classroom" style="display: none;">
                        Live-Classroom type course is a course can be happen on a specific classroom location.
                    </span>
                </span>
            </div>
 





<div class="row mt-3">
    <div class="col-md-6">

        <label class="font-weight-bold mb-2">Course Payment Type</label>

        <div class="payment-options">

            <label class="payment-card free-option">
                <input type="radio" name="course_payment_type" value="Free" checked>
                <span class="payment-title">Free</span>
                <small class="payment-desc">Students can access this course for free</small>
            </label>

            <label class="payment-card paid-option">
                <input type="radio" name="course_payment_type" value="Paid">
                <span class="payment-title">Paid</span>
                <small class="payment-desc">Students must pay to enroll</small>
            </label>

        </div>

    </div>

    <div class="col-md-6 d-none" id="price_field">

        <label for="price" class="font-weight-bold">Course Price</label>

        <div class="input-group price-box">
            <div class="input-group-prepend">
                <span class="input-group-text">$</span>
            </div>

            <input type="number"
                   name="price"
                   id="price"
                   class="form-control"
                   placeholder="Enter course price"
                   min="1"
                   step="0.01">
        </div>

    </div>
</div>
            <div class="row mt-4">
            {{-- <div class="col-sm-12 col-lg-4 col-md-12">
                <label>@lang('Minimum percentage required to qualify')</label>
                <input readonly disabled type="number"
                    name="marks_required"
                    value="{{ $course?->latestModuleWeightage?->minimun_qualify_marks ?? '' }}"
                    class="form-control"
                    oninput="this.value=this.value.replace(/[^0-9]/g,''); if(this.value>100)this.value=100;">
            </div> --}}

            <span>
                @if($course->latestModuleWeightage?->normalized_weightage['LessonModule'] != 0)
                Lesson Weightage: {{ $course->latestModuleWeightage?->normalized_weightage['LessonModule'] }} <br />
                @endif
                @if($course->latestModuleWeightage?->normalized_weightage['QuestionModule'] != 0)
                Question Weightage: {{ $course->latestModuleWeightage?->normalized_weightage['QuestionModule'] }} <br />
                @endif
                @if($course->latestModuleWeightage?->normalized_weightage['FeedbackModule'] != 0)
                Feedback Weightage: {{ $course->latestModuleWeightage?->normalized_weightage['FeedbackModule'] }}
                @endif
            </span>

    {{-- <div class="col-md-12 col-lg-8 form-group">
        <div class="row">

            <div class="col-md-12 d-flex mt-3">
                <div class="col-md-6"><strong>Need to Included</strong></div>
                <div class="col-md-6"><strong>Module Weightage (total 100%)</strong></div>
            </div>

           
            <div class="col-md-12 mt-3" id="lesson-module-block">
                <div class="d-flex">
                    <div class="col-md-6">
                        <input type="checkbox"
                               checked
                               disabled
                               class="course-module-inc"
                               value="LessonModule">
                        Lesson Module
                    </div>
                    <div class="col-md-6">
                        <input type="text"
                               class="sm-input text-end"
                               name="course_module_weight[LessonModule]"
                               value="{{ $weights['LessonModule'] ?? '' }}">
                    </div>
                </div>
            </div>

            
            <div class="col-md-12 d-flex mt-3">
                <div class="col-md-6">
                    <input type="checkbox"
                           checked
                           disabled
                           value="QuestionModule">
                    Question Assessment Module
                </div>
                <div class="col-md-6">
                    <input type="text"
                           class="sm-input text-end"
                           name="course_module_weight[QuestionModule]"
                           value="{{ $weights['QuestionModule'] ?? '' }}">
                </div>
            </div>

            
            <div class="col-md-12 d-flex mt-3">
                <div class="col-md-6">
                    <input type="checkbox"
                           disabled
                           value="FeedbackModule">
                    Feedback Module
                </div>
                <div class="col-md-6">
                    <input type="text"
                           class="sm-input text-end"
                           name="course_module_weight[FeedbackModule]"
                           value="{{ $weights['FeedbackModule'] ?? '' }}">
                </div>
            </div>

        </div>
    </div> --}}
</div>


            

            {{-- <div class="row" id="online-course-material">
                <div class="col-md-12 form-group">

                   <div class="mt-2 custom-select-wrapper">
                    <select name="media_type" class="form-control custom-select-box" id="media_type">
                        <option value="">Select One</option>
                        <option value="youtube" @if(old('media_type') == 'youtube') selected @endif>Youtube</option>
                        <option value="vimeo" @if(old('media_type') == 'vimeo') selected @endif>Video</option>
                        <option value="upload" @if(old('media_type') == 'upload') selected @endif>Upload</option>
                        <option value="embed" @if(old('media_type') == 'embed') selected @endif>Embed</option>
                    </select>
                    <span class="custom-select-icon">
                        <i class="fa fa-chevron-down"></i>
                    </span>
                </div>

                <!-- Video URL Input (YouTube, Vimeo, Embed) -->
                <input type="text" name="video" id="video"
                    value="{{ old('video') }}"
                    class="form-control mt-3 d-none"
                    placeholder="{{ trans('labels.backend.lessons.enter_video_url') }}">

                    <!-- Video Upload Input -->
                    <input type="file" name="video_file" id="video_file"
                        class="form-control mt-3 d-none"
                        accept="video/mp4"
                        placeholder="{{ trans('labels.backend.lessons.enter_video_url') }}">
            </div> --}}
                {{-- <div class="col-md-12 form-group d-none" id="video_subtitle_box"> --}}

                {{-- {!! Form::label('add_subtitle', trans('labels.backend.lessons.fields.add_subtitle'), ['class' => 'control-label']) !!} --}}

                {{-- {!! Form::file('video_subtitle', ['class' => 'form-control', 'placeholder' => trans('labels.backend.lessons.video_subtitle'),'id'=>'video_subtitle'  ]) !!} --}}

                {{-- </div> --}}
                {{-- <div class="col-md-12 form-group">

                    @lang('labels.backend.lessons.video_guide')
                </div> --}}

                <div class="col-md-12 form-group text-center">
                    @if( $course->course_image )
                    <img src="{{ $course->course_image }}" height="150px"
                                    width="150px">
                    @endif
                </div>
                
            </div><div class="btmbtns">
            <div class="row">
                
                <div class="col-12 d-flex float-right gap-20">
                            <!-- <div class="col-12 text-center form-group">
                                {{-- {!! Form::submit(trans('strings.backend.general.app_save'), ['class' => 'btn btn-lg btn-danger']) !!} --}}
                            </div> -->

                            @if($course->published == 0)

                            <div class=" ">
                                <button class="btn add-btn frm_submit" id="doneBtn" type="submit">{{ trans('Save As Draft') }}</button>
                            </div>
                            <div class=" ">
                                <button class="btn cancel-btn frm_submit" id="nextBtn" type="submit">{{ trans('Next') }}</button>
                            </div>
                            @else
                            <div class="form-group">
                                <button class="btn add-btn frm_submit" id="doneBtn" type="submit">{{ trans('Update') }}</button>
                            </div>
                            @endif
    
                </div>
            </div> 
        </div>
        <input type="hidden" name="submit_btn" id="submit-btn" value="">
        
    </div>
    </div></div>
    
    </form>
@stop

@push('after-scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <script src="{{ asset('/vendor/laravel-filemanager/js/lfm.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="/js/helpers/form-submit.js"></script>
    <script>
$(document).ready(function(){

    $('input[name="course_payment_type"]').on('change', function(){

        if($(this).val() === 'Paid'){
            $('#price_field').removeClass('d-none');
        }else{
            $('#price_field').addClass('d-none');
            $('#price').val('');
        }

    });

});
</script>
    <script>
        $(document).ready(function() {

            var dateToday = new Date();

           $('#start_date').datepicker({
    autoclose: true,
    startDate: new Date(),
    format: "yyyy-mm-dd"
});

$('#expire_at').datepicker({
    autoclose: true,
    startDate: new Date(),
    format: "yyyy-mm-dd"
});

            $(".js-example-placeholder-single").select2({
                placeholder: "{{ trans('labels.backend.courses.select_category') }}",
            });

            $(".js-example-placeholder-multiple").select2({
                placeholder: "{{ trans('labels.backend.courses.select_teachers') }}",
            });

            $(".js-example-internal-student-placeholder-multiple").select2({
                placeholder: "{{ trans('labels.backend.courses.select_internal_students') }}",
            });

            $(".js-example-external-student-placeholder-multiple").select2({
                placeholder: "{{ trans('labels.backend.courses.select_external_students') }}",
            });

            $('#meeting_start_date').on('change', function() {
                var selectedDate = $(this).val();
                var today = new Date().toISOString().split('T')[0];
                if (selectedDate === today) {
                    var now = new Date();
                    var hours = String(now.getHours()).padStart(2, '0');
                    var minutes = String(now.getMinutes()).padStart(2, '0');
                    $('#meeting_start_time').attr('min', hours + ':' + minutes);
                } else {
                    $('#meeting_start_time').removeAttr('min');
                }
            });

            $('#meeting_start_date').trigger('change');
            
            $('#meeting_start_time').on('change', function() {
                var selectedDate = $('#meeting_start_date').val();
                var today = new Date().toISOString().split('T')[0];
                if (selectedDate === today) {
                    var selectedTime = $(this).val();
                    var minTime = $(this).attr('min');
                    if (selectedTime && minTime && selectedTime < minTime) {
                        alert('Meeting start time cannot be in the past for today.');
                        $(this).val('');
                    }
                }
            });
        });

        var uploadField = $('input[type="file"]');

        $(document).on('change', 'input[type="file"]', function() {
            var $this = $(this);
            $(this.files).each(function(key, value) {
                // if (value.size > 100000000) {
                //     alert('"' + value.name + '"' + 'exceeds limit of maximum file upload size')
                //     $this.val("");
                // }
            })
        })

        function toggleCourseType(type) {
    if (type === 'Online') {
        // E-Learning
        $('#e-learning').show();
        $('#live-online, #live-classroom').hide();

        $('#date-fields').show();
        $('#start_date, #expire_at').prop('required', false);

        // Meeting Config NOT REQUIRED
        $('#meeting_start_date').prop('required', false);
        $('#meeting_start_time').prop('required', false);
        $('#meeting_duration').prop('required', false);
    } else {
        // Live Courses
        $('#e-learning').hide();
        type === 'Offline' ? $('#live-online').show() : $('#live-classroom').show();

        $('#date-fields').show();
        $('#start_date, #expire_at').prop('required', true);

        if (type === 'Offline') {
            // Single meeting fields NOT required (scheduling section handles this)
            $('#meeting_start_date').prop('required', false);
            $('#meeting_start_time').prop('required', false);
            $('#meeting_duration').prop('required', false);
        } else {
            $('#meeting_start_date').prop('required', false);
            $('#meeting_start_time').prop('required', false);
            $('#meeting_duration').prop('required', false);
        }
    }
}

        $(document).on('change', '#media_type', function() {
            if ($(this).val()) {
                if ($(this).val() != 'upload') {
                    $('#video').removeClass('d-none').attr('required', true)
                    $('#video_file').addClass('d-none').attr('required', false)
                    //                    $('#video_subtitle_box').addClass('d-none').attr('required', false)

                } else if ($(this).val() == 'upload') {
                    $('#video').addClass('d-none').attr('required', false)
                    $('#video_file').removeClass('d-none').attr('required', true)
                    //                    $('#video_subtitle_box').removeClass('d-none').attr('required', true)
                }
            } else {
                $('#video_file').addClass('d-none').attr('required', false)
                //                $('#video_subtitle_box').addClass('d-none').attr('required', false)
                $('#video').addClass('d-none').attr('required', false)
            }
        })
    </script>
<script>
function toggleCourseWeightage(type) {

    // lesson module logic
    if (type === 'Online') {
        $('#lesson-module-block')
            .show()
            .find('input')
            .prop('disabled', false);
    } else {
        $('#lesson-module-block')
            .hide()
            .find('input')
            .prop('disabled', true);
    }

    // course description
    $('#e-learning').toggle(type === 'Online');
    $('#live-online').toggle(type === 'Offline');
    $('#live-classroom').toggle(type === 'Live-Classroom');
}

// change event
$(document).on('change', '.course-type', function () {
    var type = $(this).val();
    toggleCourseWeightage(type);

    // Toggle date required asterisks
    if (type === 'Online') {
        $('.date-required-star').hide();
    } else {
        $('.date-required-star').show();
    }
});

// EDIT PAGE LOAD FIX (IMPORTANT)
$(document).ready(function () {
    let selectedType = $('input[name="course_type"]:checked').val();
    toggleCourseWeightage(selectedType);

    // Toggle date required asterisks on load
    if (selectedType === 'Online') {
        $('.date-required-star').hide();
    } else {
        $('.date-required-star').show();
    }
});
</script>

<script>
    $('#updateCourse').on('submit', function(e) {
        let courseType = $('.course-type:checked').val();
        // Populate meeting_start_at if offline course
        if (courseType === 'Offline' && $('#meeting_start_date').val() && $('#meeting_start_time').val()) {
            $('#meeting_start_at').val($('#meeting_start_date').val() + ' ' + $('#meeting_start_time').val() + ':00');
        }
    });
</script>

    <script>
        var nxt_url_val = '';
        $('.frm_submit').on('click', function() {
            nxt_url_val = $(this).val();
            $('#submit-btn').val(nxt_url_val)
        });
        $('#editCourse').on('submit', function(e) {
            var $form = $(this);

            function enableButtons() {
                $form.find('input[type=submit], button[type=submit]').removeAttr('disabled').prop('disabled', false);
            }

            function clearInlineErrors() {
                $form.find('.inline-error').remove();
                $form.find('.is-invalid').removeClass('is-invalid');
            }

            function showInlineError(field, message) {
                var $field = $form.find(field);
                $field.addClass('is-invalid');
                $field.closest('.form-group').find('.inline-error').remove();
                $field.after('<span class="text-danger inline-error w-100 d-block mt-1">' + message + '</span>');
            }

            clearInlineErrors();

            var startDateVal = $('input[name="start_date"]').val();
            var expireDateVal = $('input[name="expire_at"]').val();
            var courseType = $('input[name="course_type"]:checked').val();
            var hasError = false;

            if (courseType !== 'Online') {
                if (!startDateVal) {
                    showInlineError('#start_date', 'Start Date is required.');
                    hasError = true;
                }
                if (!expireDateVal) {
                    showInlineError('#expire_at', 'Expire Date is required.');
                    hasError = true;
                }
            }

            if (startDateVal && expireDateVal && expireDateVal < startDateVal) {
                showInlineError('#expire_at', 'Expire Date cannot be earlier than Start Date.');
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
                enableButtons();
                setTimeout(enableButtons, 0);
                scrollToClass('inline-error');
                return false;
            }
        });
    </script>
    <script>
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const label = input.nextElementSibling;
            const fileName = e.target.files.length > 0 ? e.target.files[0].name : 'Choose a file';
            label.innerHTML = '<i class="fa fa-upload mr-1"></i> ' + fileName;
        });
    });
</script>

{{-- Live Session Scheduling JS --}}
<script>
$(document).ready(function() {
    // Toggle schedule panels and hide single-meeting fields
    $(document).on('change', '.schedule-type-radio', function() {
        $('.schedule-panel').hide();
        var type = $(this).val();
        if (type === 'daily') $('#schedule-daily').show();
        else if (type === 'weekly') $('#schedule-weekly').show();
        else if (type === 'custom') $('#schedule-custom').show();

        // Hide single-meeting date/time/duration when schedule type is selected
        $('#single-meeting-fields').hide();
        $('#single-meeting-hint').hide();
        $('#meeting_start_date, #meeting_start_time, #meeting_duration').prop('required', false);
    });

    // Show active panel on page load
    var activeSchedule = $('input[name="schedule_type"]:checked').val();
    if (activeSchedule) {
        $('#schedule-' + activeSchedule).show();
    }

    // Add custom session row
    $(document).on('click', '#add-session-btn', function() {
        var row = `<div class="row custom-session-row mb-2">
            <div class="col-md-4 form-group">
                <label>Date *</label>
                <input type="date" name="custom_dates[]" class="form-control custom-session-date">
            </div>
            <div class="col-md-3 form-group">
                <label>Time *</label>
                <input type="time" name="custom_times[]" class="form-control">
            </div>
            <div class="col-md-3 form-group">
                <label>Duration (mins) *</label>
                <input type="number" name="custom_durations[]" class="form-control" value="60" min="1">
            </div>
            <div class="col-md-2 form-group d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm remove-session-btn">&times; Remove</button>
            </div>
        </div>`;
        $('#custom-sessions-container').append(row);
        updateRemoveButtons();
    });

    // Remove custom session row
    $(document).on('click', '.remove-session-btn', function() {
        $(this).closest('.custom-session-row').remove();
        updateRemoveButtons();
    });

    function updateRemoveButtons() {
        var rows = $('.custom-session-row');
        if (rows.length > 1) {
            rows.find('.remove-session-btn').show();
        } else {
            rows.find('.remove-session-btn').hide();
        }
    }
});
</script>
@endpush