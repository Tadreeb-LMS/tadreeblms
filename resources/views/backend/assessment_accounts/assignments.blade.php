@extends('backend.layouts.app')
@section('title', __('Assessments').' | '.app_name())
@push('after-styles')
<link rel="stylesheet" href="{{asset('assets/css/colors/switch.css')}}">
  <style>
.select2-container--default .select2-selection--single {
        border: 1px solid #ccc !important;
        border-radius: 5px !important;
        /* padding: 4px; */
    }
    .select2-container .select2-selection--single {
    box-sizing: border-box;
    cursor: pointer;
    display: block;
    height: 34px;
    user-select: none;
    -webkit-user-select: none;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #444;
    line-height: 30px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 26px;
    position: absolute;
    top: 4px;
    right: 1px;
    width: 20px;
}


.select2-container--default .select2-selection--single .select2-selection__arrow {
    display: none;
    visibility: hidden;
}

/* Add custom arrow icon */
.select2-container--default .select2-selection--single {
    position: relative;
    padding-right: 30px; /* adjust if needed */
}

.select2-container--default .select2-selection--single::after {
    content: '\f078'; /* FontAwesome unicode for caret-down */
    font-family: "Font Awesome 5 Free"; /* Adjust based on your Font Awesome version */
    font-weight: 900;
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #333; /* Icon color */
    font-size: 14px;
}

.select2-container--default.select2-container--focus .select2-selection--single {
     outline: none;
  box-shadow: 0 0 5px rgba(0, 123, 255, 0.5) !important;
  border-color: #007bff !important;
}

    </style>
@endpush
@section('content')
@php
use App\Models\Course;
@endphp
<div class="pb-3 d-flex justify-content-between align-items-center">
    <h4 class="page-title float-left mb-0">@lang('Course Assessment') ({{$count}})</h4>
    @can('course_create')
    <div class="">
        @if ($user_id != NULL)
        <a href="{{ route('admin.assessment_accounts.account_assignment_create', $user_id) }}" class="btn btn-primary">@lang('Assignment')</a>
        @else
        @can('course_create')
        <div class="">
            <!-- <a href="{{ route('admin.assessment_accounts.create') }}" class="btn btn-success">@lang('strings.backend.general.app_add_new')</a> -->
            <a href="{{ route('admin.assessment_accounts.assignment_create') }}" class="btn add-btn">@lang('Add Course Assessment')</a>
        </div>
        @endcan
        @endif
    </div>

    @endcan
</div>
<div class="card">
    <div class="">
        <!-- <h3 class="page-title d-inline">@lang('Course Assessment') ({{$count}})</h3>
        @can('course_create')
        <div class="float-right">
            @if ($user_id != NULL)
            <a href="{{ route('admin.assessment_accounts.account_assignment_create', $user_id) }}" class="btn btn-success">@lang('Assignment')</a>
            @else
            @can('course_create')
            <div class="float-right mr-3">
               
                <a href="{{ route('admin.assessment_accounts.assignment_create') }}" class="btn btn-success">@lang('Add Course Assessment')</a>
            </div>
            @endcan
            @endif
        </div>

        @endcan -->


        <div class="card-body">

            <div class="row mb-3">
                @php
                $courses = $courses = Course::has('category')->ofTeacher()->pluck('title', 'id')->prepend('Please select', '');
                @endphp
                <div class="col-12 col-lg-6 form-group">
                    <label for="course_id" class="control-label">{{ trans('labels.backend.lessons.fields.course') }}</label>
                    <select name="course_id" id="course_id" class="form-control js-example-placeholder-single select2">
                        @foreach($courses as $key => $course)
                            <option value="{{ $key }}" @if((request('course_id') ? request('course_id') : old('course_id')) == $key) selected @endif>{{ $course }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table id="myTable" class="table dt-select custom-teacher-table table-striped @if(auth()->user()->isAdmin()) @if ( request('show_deleted') != 1 ) dt-select @endif @endcan">
                        <thead>
                            <tr>
                                <th>@lang('Test')</th>
                                <th>@lang('Duration(Minutes)')</th>
                                <th>@lang('Start Time')</th>
                                <th>@lang('End Time')</th>
                                <th>@lang('Buffer Time(Minutes)')</th>
                                <th>@lang('Verification Code')</th>
                                <th>@lang('URL')</th>
                                <th>@lang('Total Marks')</th>
                                <th>@lang('Secured Marks')</th>
                                @if ($type == 1)
                                <th>@lang('Details')</th>
                                @endif
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($assignments as $key=> $value)
                            <tr>
                                <td> {{$value->title}} </td>
                                <td> {{$value->duration}} </td>
                                <td> {{$value->start_time}} </td>
                                <td> {{$value->end_time}} </td>
                                <td> {{$value->buffer_time}} </td>
                                <td> {{$value->verify_code}} </td>
                                <td> {{ route('online_assessment', ['assignment' => $value->url_code , 'verify_code' => $value->verify_code ]) }} </td>
                                <td> {{$value->total_marks}} </td>
                                <td> {{$value->secured_marks}} </td>
                                @if ($type == 1)
                                <td>
                                    <a href="{{ route('admin.assessment_accounts.assignment_question_answers', ['user_id'=>$user_id, 'assignment_id'=>$value->id]) }}" class="btn btn-xs btn-primary mb-1"><i class="fa fa-tasks"></i></a>
                                </td>
                                @endif
                                <td>
                                    <a data-method="delete" 
                                        data-trans-button-cancel="{{ __('buttons.general.cancel') }}" 
                                        data-trans-button-confirm="{{ __('buttons.general.crud.delete') }}" 
                                        data-trans-title="{{ __('strings.backend.general.are_you_sure') }}" 
                                        class="btn btn-xs btn-danger text-white mb-1" 
                                        style="cursor:pointer;" 
                                        onclick="if(confirm('{{ __('strings.backend.general.are_you_sure') }}')) { $(this).find('form').submit(); }">
                                        
                                            <i class="fa fa-trash" 
                                            data-toggle="tooltip" 
                                            data-placement="top" 
                                            title="{{ __('buttons.general.crud.delete') }}"></i>

                                            <form action="{{ route('admin.assessment_accounts.assignments_delete', ['id' => $value->id]) }}" 
                                                method="POST" 
                                                name="delete_item" 
                                                style="display:none">
                                                @csrf
                                                @method('GET')
                                            </form>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        </div>


        
   
</div>

@endsection

@push('after-scripts')
<script>
    $(document).ready(function() {
        $('#myTable').dataTable({
            "paginate": true,
            "sort": true,
            "language": {
                "emptyTable": "No Data Is Available.",
                search:"",
   
            },
            "order": [
                [0, "desc"]
            ],
            dom:  "<'table-controls'lfB>" +
                     "<'table-responsive't>" +
                     "<'d-flex justify-content-between align-items-center mt-3'ip><'actions'>",
            // buttons: [{
            //         extend: 'csv',
            //         exportOptions: {
            //             columns: [1, 2, 3, 4]
            //         }
            //     },
            //     {
            //         extend: 'pdf',
            //         exportOptions: {
            //             columns: [1, 2, 3, 4],
            //         }
            //     },
            //     'colvis'
            // ],
             buttons: [
    {
        extend: 'collection',
        text: '<i class="fa fa-download icon-styles"></i>',
        className: '',
        buttons: [
            {
                extend: 'csv',
                text: 'CSV',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5]
                }
            },
            {
                extend: 'pdf',
                text: 'PDF',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5]
                }
            }
        ]
    },
      {extend: 'colvis',
    text: '<i class="fa fa-eye icon-styles" aria-hidden="true"></i>',
    className: ''},
],
             initComplete: function () {
                    let $searchInput = $('#myTable_filter input[type="search"]');
    $searchInput
        .addClass('custom-search')
        .wrap('<div class="search-wrapper position-relative d-inline-block"></div>')
        .after('<i class="fa fa-search search-icon"></i>');

    $('#myTable_length select').addClass('form-select form-select-sm custom-entries');
                },
                  
        });
    });




    $(document).on('change', '#course_id', function(e) {
        var course_id = $(this).val();
        window.location.href = "{{ url('user/assignments') }}" + "?course_id=" + course_id
    });
</script>

@endpush