@extends('backend.layouts.app')
@section('title', __('labels.backend.questions.title').' | '.app_name())

@section('content')
<!-- {!! Form::open(['method' => 'POST', 'route' => ['admin.questions.store'], 'files' => true,]) !!} -->
@push('after-styles')
<style>
    :root {
        --primary-color: #4e73df;
        --secondary-color: #858796;
        --success-color: #1cc88a;
        --info-color: #36b9cc;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --light-color: #f8f9fc;
        --dark-color: #5a5c69;
        --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .question-builder-container {
        padding: 20px 0;
    }

    .card {
        border-radius: 12px;
        border: none;
        box-shadow: var(--card-shadow);
        transition: transform 0.2s ease-in-out;
        margin-bottom: 24px;
    }

    .card-header {
        background-color: #fff !important;
        border-bottom: 1px solid #f2f4f9 !important;
        border-radius: 12px 12px 0 0 !important;
        padding: 1.25rem !important;
    }

    .card-header h5 {
        color: #4e73df;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-group label {
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .form-control {
        border-radius: 8px;
        padding: 12px 15px;
        border: 1px solid #e2e8f0;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
    }

    /* Options Builder Styling */
    .option-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 15px;
        transition: all 0.2s;
        position: relative;
    }

    .option-item:hover {
        border-color: #cbd5e0;
        background: #f1f5f9;
        transform: translateY(-2px);
    }

    .option-item .drag-handle {
        cursor: grab;
        color: #cbd5e0;
    }

    .option-item .option-content {
        flex-grow: 1;
        font-size: 0.95rem;
    }

    .option-item .option-actions {
        display: flex;
        gap: 8px;
    }

    .option-item.correct {
        border-left: 4px solid #1cc88a;
        background: #f0fff4;
    }

    /* Floating Preview Styling */
    .preview-panel {
        position: sticky;
        top: 20px;
        z-index: 100;
    }

    .preview-card {
        background: #fff;
        border-top: 4px solid #4e73df;
    }

    .preview-header {
        background: #f8fafc;
        padding: 15px;
        border-bottom: 1px solid #e2e8f0;
        font-weight: 700;
        color: #2d3748;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.05em;
    }

    .preview-body {
        padding: 24px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .badge-difficulty {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge-easy { background: #def7ec; color: #03543f; }
    .badge-medium { background: #fef3c7; color: #92400e; }
    .badge-hard { background: #fde8e8; color: #9b1c1c; }

    /* Sticky Footer */
    .sticky-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        padding: 15px 30px;
        box-shadow: 0 -5px 20px rgba(0,0,0,0.05);
        z-index: 1000;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #e2e8f0;
    }

    .main {
        padding-bottom: 80px !important; /* Space for footer */
    }

    /* Custom Radio/Checkbox */
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #4e73df;
        border-color: #4e73df;
    }

    /* Helper spacing */
    .gap-10 { gap: 10px; }
    .mt-auto { margin-top: auto; }
</style>
@endpush




@include('backend.includes.partials.course-steps', ['step' => 3, 'course_id' => $course_id, 'course' => $course ?? null ])

<div class="question-builder-container">
    <div class="row">
        <!-- Left Panel: Question Builder -->
        <div class="col-lg-8">
            <input type="hidden" id="temp_id" name="temp_id" value="{{ $temp_id }}">
            <input type="hidden" id="action_btn" name="action_btn" value="">
            <input type="hidden" id="course_id" name="course_id" value="{{ $course_id }}">

<div class="pb-3 d-flex justify-content-between align-items-center addcourseheader">
       <h4>
           @lang('labels.backend.questions.create')
       </h4>
         <div class="">
       <a href="{{ route('admin.test_questions.index') }}" class="btn btn-primary">@lang('labels.backend.questions.view')</a>
   </div>
     
   </div>
<div class="card">
    <!-- <div class="card-header">
        <h3 class="page-title float-left mb-0">@lang('labels.backend.questions.create')</h3>
        <div class="float-right">
            <a href="{{ route('admin.test_questions.index') }}" class="btn btn-success">@lang('labels.backend.questions.view')</a>
        </div>
    </div> -->
    <div class="card-body">
        <input type="hidden" id="temp_id" name="temp_id" value="{{ $temp_id }}">
        <input type="hidden" id="action_btn" name="action_btn" value="">
        <input type="hidden" id="course_id" name="course_id" value="{{ $course_id }}">
        <div class="row mt-3">
            <div class="col-12 col-md-6">
                <label>Test</label>
                <div class="custom-select-wrapper">

                    <select @if($auto_test_id) disabled @endif class="form-control custom-select-box" name="test_id" id="test_id" required>
                        <option value="">Select Test</option>
                        @foreach($tests as $key=> $value)
                        <option @if((request()->get('test_id') == $value->id) || ($auto_test_id == $value->id)) selected @endif value="{{$value->id}}">{{$value->title}}</option>
                        @endforeach
                    </select>
                     <span class="custom-select-icon">
        <i class="fa fa-chevron-down"></i>
    </span>
                </div>
            </div>
         
            <div class="col-12 col-md-6">
                <label>Question Type</label>
                <div class="custom-select-wrapper">

                    <select class="form-control custom-select-box" name="question_type" id="question_type">
                        <option value="1"> Single Choice </option>
                        {{-- <option value="2"> Multiple Choice </option>
                        <option value="3"> Short Answer </option> --}}
                    </select>
                     <span class="custom-select-icon">
        <i class="fa fa-chevron-down"></i>
    </span>
                </div>
            </div>
        </div>



        <div class="row">
          <div class="col-12 col-md-6 mt-3 notextarea"> 
                <label>Question</label>
                <textarea class="form-control editor" rows="3" name="question" id="question" required="required"></textarea>
            </div>
         
                <div class="col-12 col-md-6"> 
                     <div class="mt-3 notextarea">
                    <label>Option</label>
                    <textarea class="form-control editor" rows="3" name="option" id="option" required="required"></textarea>
                    <div class="addoptbtn">
                    <button type="button" id="add_option" class="btn btn-primary mt-2">Add Option</button>
                    </div>
                    <div class="addoptiontable mt-1">
                    <div id="option-area" class="pt-1"></div>
    </div>
                </div>
              
            <!-- Metadata Card -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fa fa-info-circle"></i> Question Metadata</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Test</label>
                            <div class="custom-select-wrapper">
                                <select @if($auto_test_id) disabled @endif class="form-control custom-select-box" name="test_id" id="test_id" required>
                                    <option value="">Select Test</option>
                                    @foreach($tests as $key=> $value)
                                    <option @if((request()->get('test_id') == $value->id) || ($auto_test_id == $value->id)) selected @endif value="{{$value->id}}">{{$value->title}}</option>
                                    @endforeach
                                </select>
                                <span class="custom-select-icon"><i class="fa fa-chevron-down"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Question Type</label>
                            <div class="custom-select-wrapper">
                                <select class="form-control custom-select-box" name="question_type" id="question_type">
                                    <option value="1">Single Choice</option>
                                    <option value="2">Multiple Choice</option>
                                    <option value="3">Descriptive / Short Answer</option>
                                </select>
                                <span class="custom-select-icon"><i class="fa fa-chevron-down"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4 form-group">
                            <label>Marks</label>
                            <input type="number" class="form-control" name="marks" id="marks" placeholder="E.g. 5" required />
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Difficulty Level</label>
                            <div class="custom-select-wrapper">
                                <select class="form-control custom-select-box" name="difficulty" id="difficulty">
                                    <option value="easy">Easy</option>
                                    <option value="medium">Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                                <span class="custom-select-icon"><i class="fa fa-chevron-down"></i></span>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Tags (Optional)</label>
                            <input type="text" class="form-control" name="tags" id="tags" placeholder="E.g. Math, Algebra" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Question Content Card -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fa fa-edit"></i> Question Content</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Question Text</label>
                        <textarea class="form-control editor" rows="4" name="question" id="question" required="required"></textarea>
                    </div>
                    <div class="form-group mt-3">
                        <label>Hint (Optional)</label>
                        <textarea class="form-control" rows="2" name="hint" id="hint" placeholder="Provide a hint for learners..."></textarea>
                    </div>
                </div>
               
            </div>
            <div class="cb_question_setup">
            <div class="row">
                 <div class="col-12 col-md-5 notextarea">
                    <label>Solution</label>
                    <textarea class="form-control textarea-col editor" rows="3" name="solution" id="solution"></textarea>
                </div>
             
             <div class="col-12 col-md-2">
                    <label>Marks</label>
                    <input type="number" class="form-control" name="marks" id="marks" placeholder="Enter Marks" required />
                </div>
             
                <div class="col-12 col-md-5 notextarea">
                    <label>Comment</label>
                    <textarea class="form-control textarea-col editor" rows="3" name="comment" id="comment"></textarea>

            <!-- Options Builder Card -->
            <div class="card" id="options-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fa fa-list-ul"></i> Options Builder</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add_option_btn">
                        <i class="fa fa-plus"></i> Add Option
                    </button>
                </div>
                <div class="card-body">
                    <!-- New Option Input (Floating) -->
                    <div id="option-input-wrapper" class="mb-4 bg-light p-3 rounded shadow-sm" style="display:none;">
                        <label>New Option Content</label>
                        <textarea class="form-control editor" name="option_editor" id="option_editor"></textarea>
                        <div class="mt-2 text-right">
                            <button type="button" class="btn btn-secondary btn-sm" id="cancel_option">Cancel</button>
                            <button type="button" class="btn btn-primary btn-sm" id="confirm_add_option">Add this Option</button>
                        </div>
                    </div>

                    <div id="option-area" class="mt-3">
                        <div class="text-center py-4 text-muted">
                            <i class="fa fa-info-circle fa-2x mb-2"></i>
                            <p>No options added yet. Click "Add Option" to begin.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Solution & Comments Card -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fa fa-check-circle"></i> Explanation & Feedback</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Correct Solution / Explanation</label>
                        <textarea class="form-control editor" rows="3" name="solution" id="solution"></textarea>
                    </div>
                    <div class="form-group mt-3">
                        <label>Admin Comments (Internal)</label>
                        <textarea class="form-control editor" rows="3" name="comment" id="comment"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Live Preview -->
        <div class="col-lg-4">
            <div class="preview-panel">
                <div class="card preview-card">
                    <div class="preview-header">
                        <i class="fa fa-eye"></i> Learner Preview
                    </div>
                    <div class="preview-body" id="live-preview-content">
                        <div class="preview-meta mb-3 d-flex justify-content-between align-items-center">
                            <span id="preview-marks" class="badge badge-info">0 Marks</span>
                            <span id="preview-difficulty" class="badge-difficulty badge-easy">Easy</span>
                        </div>
                        <div id="preview-tags" class="mb-3"></div>
                        <div id="preview-question" class="question-text mb-4" style="font-size: 1.1rem; font-weight: 500;">
                            <span class="text-muted italic">Question content will appear here...</span>
                        </div>
                        <div id="preview-options" class="options-preview">
                            <!-- Preview options will appear here -->
                        </div>
                        <div id="preview-hint" class="mt-3 d-none">
                            <div class="alert alert-warning py-2 small">
                                <strong>Hint:</strong> <span id="preview-hint-text"></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white py-3">
                        <small class="text-muted"><i class="fa fa-mobile-alt"></i> Optimized for all devices</small>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
     <div class="btmbtns">
        <div class="row">
    <div class="col-12 mt-5 buttons">
     {!! Form::button('Save & Add More', ['class' => 'frm_submit add-btn', 'id'=>'save_and_add_more', 'value'=>'save_and_add_more']) !!}
     <div class="text-right">
        <button
            type="button"
            class="frm_submit cancel-btn"
            id="save_as_draft"
            value="Save As Draft">
            Save As Draft
        </button>

        <button
            type="button"
            class="frm_submit add-btn"
            id="save"
            value="Next">
            Next
        </button>
    </div>
    </div></div>
     </div>
</div>

<!-- Sticky Footer -->
<div class="sticky-footer">
    <div class="footer-left">
        <a href="{{ route('admin.test_questions.index') }}" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
    </div>
    <div class="footer-right gap-10 d-flex">
        <button type="button" class="btn btn-outline-primary frm_submit" id="save_as_draft" value="Save As Draft">
            <i class="fa fa-save"></i> Save Draft
        </button>
        <button type="button" class="btn btn-primary frm_submit" id="save_and_add_more" value="save_and_add_more">
             <i class="fa fa-plus-circle"></i> Save & Add Next
        </button>
        <button type="button" class="btn btn-success frm_submit px-4" id="save" value="Next">
            Save & Finish <i class="fa fa-check"></i>
        </button>
    </div>
</div>



<!-- {!! Form::close() !!} -->
<script src="{{asset('ckeditor/ckeditor.js')}}" type="text/javascript"></script>
<script type="text/javascript">

    $('.frm_submit').on('click', function (){
        //alert($(this).val())
        $('#action_btn').val($(this).val());
    });

    CKEDITOR.replace('question');
    
    CKEDITOR.replace('option');
    
    CKEDITOR.replace('solution');
    
    CKEDITOR.replace('comment')
    
</script>
@stop
@push('after-scripts')
<script type="text/javascript">
    var options = [];
    var currentOptionId = null;

    $(document).ready(function() {
        // Initialize CKEditors
        const editorIds = ['question', 'solution', 'comment', 'option_editor'];
        editorIds.forEach(id => {
            if (CKEDITOR.instances[id]) {
                CKEDITOR.instances[id].on('change', function() {
                    updatePreview();
                });
            }
        });

        // Event listeners for preview updates
        $('#marks, #difficulty, #tags, #hint, #question_type').on('input change', function() {
            updatePreview();
        });

        // Initial preview
        updatePreview();
    });

    function updatePreview() {
        // Update Marks
        $('#preview-marks').text(($('#marks').val() || 0) + ' Marks');

        // Update Difficulty
        const diff = $('#difficulty').val();
        $('#preview-difficulty').text(diff.charAt(0).toUpperCase() + diff.slice(1))
            .removeClass('badge-easy badge-medium badge-hard')
            .addClass('badge-' + diff);

        // Update Tags
        const tags = $('#tags').val();
        if (tags) {
            let tagHtml = tags.split(',').map(t => `<span class="badge badge-secondary mr-1">${t.trim()}</span>`).join('');
            $('#preview-tags').html(tagHtml);
        } else {
            $('#preview-tags').empty();
        }

        // Update Question Text
        const questionText = CKEDITOR.instances['question'].getData();
        if (questionText) {
            $('#preview-question').html(questionText);
        } else {
            $('#preview-question').html('<span class="text-muted italic">Question content will appear here...</span>');
        }

        // Update Hint
        const hint = $('#hint').val();
        if (hint) {
            $('#preview-hint').removeClass('d-none');
            $('#preview-hint-text').text(hint);
        } else {
            $('#preview-hint').addClass('d-none');
        }

        renderOptionsPreview();
    }

    function renderOptionsPreview() {
        const type = $('#question_type').val();
        let html = '';
        
        if (type == 3) {
            html = '<div class="alert alert-light border text-muted px-4 py-3">Learners will provide a descriptive answer here.</div>';
        } else {
            options.forEach((opt, index) => {
                const inputType = type == 1 ? 'radio' : 'checkbox';
                html += `
                    <div class="custom-control custom-${inputType} mb-2">
                        <input type="${inputType}" class="custom-control-input" id="prev_opt_${index}" name="prev_opt" value="${index}">
                        <label class="custom-control-label" for="prev_opt_${index}">${opt[0]}</label>
                    </div>
                `;
            });
        }
        $('#preview-options').html(html || '<span class="text-muted small italic">Options will appear here...</span>');
    }

    // Options Management
    $(document).on('click', '#add_option_btn', function() {
        $('#option-input-wrapper').slideDown();
        CKEDITOR.instances['option_editor'].setData('');
        $(this).hide();
    });

    $(document).on('click', '#cancel_option', function() {
        $('#option-input-wrapper').slideUp();
        $('#add_option_btn').show();
    });

    $(document).on('click', '#confirm_add_option', function() {
        const content = CKEDITOR.instances['option_editor'].getData().trim();
        if (!content) {
            alert('Please enter option content');
            return;
        }

        options.push([content, 0]); // [content, is_right]
        renderOptionsList();
        updatePreview();
        
        $('#option-input-wrapper').slideUp();
        $('#add_option_btn').show();
    });

    function removeOption(index) {
        options.splice(index, 1);
        renderOptionsList();
        updatePreview();
    }

    function markCorrect(index) {
        const type = $('#question_type').val();
        if (type == 1) {
            // Single choice: only one can be correct
            options.forEach((opt, i) => opt[1] = (i === index ? 1 : 0));
        } else {
            // Multiple choice: toggle
            options[index][1] = options[index][1] === 1 ? 0 : 1;
        }
        renderOptionsList();
        updatePreview();
    }

    function renderOptionsList() {
        const container = $('#option-area');
        const type = $('#question_type').val();

        if (options.length === 0) {
            container.html(`
                <div class="text-center py-4 text-muted">
                    <i class="fa fa-info-circle fa-2x mb-2"></i>
                    <p>No options added yet. Click "Add Option" to begin.</p>
                </div>
            `);
            return;
        }

        let html = '';
        options.forEach((opt, index) => {
            const isCorrect = opt[1] === 1;
            const inputType = type == 1 ? 'radio' : 'checkbox';
            
            html += `
                <div class="option-item ${isCorrect ? 'correct' : ''}">
                    <div class="drag-handle"><i class="fa fa-grip-vertical"></i></div>
                    <div class="option-check">
                        <div class="custom-control custom-${inputType}">
                            <input type="${inputType}" class="custom-control-input" id="opt_check_${index}" 
                                ${isCorrect ? 'checked' : ''} onclick="markCorrect(${index})">
                            <label class="custom-control-label" for="opt_check_${index}"></label>
                        </div>
                    </div>
                    <div class="option-content">${opt[0]}</div>
                    <div class="option-actions">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeOption(${index})">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        container.html(html);
    }

    // Submit Handling
    $(document).on('click', '.frm_submit', function() {
        $('#action_btn').val($(this).val());
        sendData();
    });

    function sendData() {
        const type = $('#question_type').val();
        
        // Basic Validation
        if (!CKEDITOR.instances['question'].getData().trim()) {
            alert('Question text is required');
            return;
        }
        if (!$('#marks').val()) {
            alert('Marks field is required');
            return;
        }
        if (type != 3 && options.length < 2) {
            alert('Please add at least 2 options');
            return;
        }
        if (type != 3 && !options.some(opt => opt[1] === 1)) {
            alert('Please select at least one correct answer');
            return;
        }

        const data = {
            _token: "{{ csrf_token() }}",
            temp_id: $("#temp_id").val(),
            test_id: $("#test_id").val(),
            course_id: $('#course_id').val(),
            action_btn: $('#action_btn').val(),
            question_type: type,
            question: CKEDITOR.instances["question"].getData(),
            options: JSON.stringify(options),
            solution: CKEDITOR.instances["solution"].getData(),
            comment: CKEDITOR.instances["comment"].getData(),
            marks: $("#marks").val(),
            difficulty: $('#difficulty').val(),
            tags: $('#tags').val(),
            hint: $('#hint').val()
        };

        $.ajax({
            url: "{{route('admin.test_questions.store')}}",
            type: 'post',
            data: data,
            success: function(response) {
                const res = JSON.parse(response);
                if (res.code == 200) {
                    window.location.replace(res.redirect_url);
                } else {
                    alert(res.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const res = xhr.responseJSON;
                    alert(res.message);
                } else {
                    alert('An error occurred while saving.');
                }
            }
        });
    }

    // Handle Type change
    $('#question_type').on('change', function() {
        const type = $(this).val();
        if (type == 3) {
            $('#options-card').fadeOut();
        } else {
            $('#options-card').fadeIn();
        }
        options = []; // Reset options when type changes for simplicity or we can convert them
        renderOptionsList();
        updatePreview();
    });

</script>
@endpush
