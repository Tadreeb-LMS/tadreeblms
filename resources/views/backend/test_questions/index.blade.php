@extends('backend.layouts.app')

@section('title', __('labels.backend.questions.title') . ' | ' . app_name())
@push('after-styles') 
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
.dropdown-item{
    border-bottom: none;
}

#add_new_question_btn.disabled {
    pointer-events: none;
    opacity: 0.65;
}

    
    </style>

@endpush

@section('content')
    @php
        use App\Models\Course;
    @endphp
    <div class="pb-3 d-flex justify-content-between align-items-center">
   <h4>
      @lang('labels.backend.questions.title')
   </h4>
   <div >
       @php
           $test_id = app('request')->input('test_id');
           $course_id = app('request')->input('course_id');
           $add_new_href = 'javascript:void(0)';
           $add_new_disabled = true;

           if ($test_id) {
               $add_new_href = route('admin.test_questions.create') . '?test_id=' . $test_id . ($course_id ? '&course_id=' . $course_id : '');
               $add_new_disabled = false;
           } elseif ($course_id) {
               $add_new_href = route('admin.test_questions.create') . '?course_id=' . $course_id;
               $add_new_disabled = false;
           }
       @endphp
       <a
           id="add_new_question_btn"
           href="{{ $add_new_href }}"
           data-base-url="{{ route('admin.test_questions.create') }}"
           data-test-id="{{ $test_id }}"
           class="btn add-btn {{ $add_new_disabled ? 'disabled' : '' }}"
           aria-disabled="{{ $add_new_disabled ? 'true' : 'false' }}"
           title="{{ $add_new_disabled ? 'Select a course first' : '' }}"
       >@lang('strings.backend.general.app_add_new')</a>
   </div>
 
</div>
    <div class="card">
        <!-- <div class="card-header">

            <h3 class="page-title d-inline">@lang('labels.backend.questions.title')</h3>
            <div class="float-right">
                @if (app('request')->input('test_id'))
                    @php
                        $test_id = app('request')->input('test_id');
                    @endphp
                    <a href="{{ route('admin.test_questions.create') }}?test_id={{ $test_id }}"
                        class="btn btn-success">@lang('strings.backend.general.app_add_new')</a>
                @else
                    <a href="{{ route('admin.test_questions.create') }}" class="btn btn-success">@lang('strings.backend.general.app_add_new')</a>
                @endif
            </div>

        </div> -->



        <div class="card-body">
            <div class="row mb-3">
                @php
                    $courses = Course::has('category')
                        ->ofTeacher()
                        ->pluck('title', 'id');
                @endphp
                <div class="col-md-12 col-lg-6 form-group mb-3">

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
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table id="myTable"
                            class="table dt-select custom-teacher-table table-striped @can('category_delete') @if (request('show_deleted') != 1) dt-select @endif @endcan">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">@lang('Id')</th>
                                    <th style="width: 80px;">@lang('Test')</th>
                                    <th style="width: 140px;">@lang('Course')</th>
                                    <th style="width: 130px;">@lang('Question Type')</th>
                                    <th style="width: 80px;">@lang('Question Text')</th>
                                    <th style="width: 80px;">@lang('Marks')</th>
                                    <th style="width: 80px;text-align:center">@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($test_questions as $key => $value)
                                    <tr>
                                        <td>{{ $value->id }}</td>
                                        <td>{{ $value->title }}</td>
                                        <td>{{ $value->course_title ?? '-' }}</td>
                                        <td>
                                            @if ($value->question_type == 1)
                                                Single Choice
                                            @elseif ($value->question_type == 2)
                                                Multiple Choice
                                            @else
                                                Short Answer
                                            @endif
                                        </td>
                                        <td><?= $value->question_text ?></td>
                                        <td>{{ $value->marks }}</td>
                                        <!-- <td class="d-flex"><a href="{{ route('admin.test_questions.edit', $value->id) }}"
                                                class="btn btn-xs btn-info mb-1 mr-2"><i class="icon-pencil"></i></a>
                                            <a onclick="return confirm('Are you sure you want to delete?')"
                                                href="{{ route('admin.test_questions_delete', ['id' => $value->id]) }}"
                                                class="btn btn-xs btn-danger mb-1"><i class="icon-trash"></i></a>

                                        </td> -->
                                        <td class="text-center">
    <div class="dropdown">
        <a class="dropdown-toggle" href="javascript:void(0);" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-ellipsis-v action-icon" ></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <a href="{{ route('admin.test_questions.edit', $value->id) }}" class="dropdown-item">
                 Edit
            </a>

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

                                                            <form action="{{ route('admin.test_questions_delete', ['id' => $value->id]) }}" 
                                                                method="POST" 
                                                                name="delete_item" 
                                                                style="display:none">
                                                                @csrf
                                                                @method('GET')
                                                            </form>
                                                    </a>
                                                </div>
                                            </div>
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

@stop

@push('after-scripts')
    <script>
        $(document).ready(function() {
            $('#myTable').dataTable({
                "paginate": true,
                "sort": true,
                "language": {
                    "emptyTable": "No Data Is Available.",
    
                },
                "order": [
                    [0, "desc"]
                ],
                dom: "<'table-controls'lfB>" +
                     "<'table-responsive't>" +
                     "<'d-flex justify-content-between align-items-center mt-3'ip><'actions'>",
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
                // buttons: [{
                //         extend: 'csv',
                //         exportOptions: {
                //             columns: [1, 2, 3, 4]
                //         }
                //     },
                //     {
                //         extend: 'pdf',
                //         exportOptions: {
                //             columns: [1, 2, 3, 4]
                //         }
                //     },
                //     'colvis'
                // ],
                language:{
                    search:"",
                },
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
            var indexUrl = "{{ route('admin.test_questions.index') }}";
            if (course_id) {
                window.location.href = indexUrl + "?course_id=" + course_id;
            } else {
                window.location.href = indexUrl;
            }
        });

        $(document).on('click', '#add_new_question_btn.disabled', function(e) {
            e.preventDefault();
            alert('Please select a course before adding a new question.');
        });
    </script>
@endpush
