@extends('backend.layouts.app')

@section('title', __('Final Submit').' | '.app_name())

@section('content')
<form method="POST"
      action="{{ route('admin.assessment_accounts.final-submit-store') }}"
      enctype="multipart/form-data"
      class="form-horizontal">
    @csrf
    <input type="hidden" name="course_id" value="{{ $course_id }}" />
    <div class="card">

    <div class="card shadow-sm">

        {{-- Header --}}
        <div class="card-header">
            <h4 class="mb-0">Final Submission</h4>
        </div>



        <div class="card-body">

    <!-- Course Weightage -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h5 class="mb-3">Course Module Weightage</h5>
            <p class="text-muted mb-4">Total weightage must be exactly <strong>100%</strong></p>
        </div>

        <div class="col-md-8">

            @if($course->is_online == 'Online')
            <div class="form-group row align-items-center mb-3">
                <label class="col-md-4 col-form-label">
                    Lesson Module
                </label>
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="number"
                               class="form-control @if($course->is_online == 'Online') module-weight @endif"
                               name="course_module_weight[LessonModule]"
                               min="0"
                               max="100"
                               placeholder="e.g. 50">
                        <div class="input-group-append">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="form-group row align-items-center mb-3">
                <label class="col-md-4 col-form-label">
                    Question Module
                </label>
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="number"
                               class="form-control module-weight"
                               name="course_module_weight[QuestionModule]"
                               min="0"
                               max="100"
                               placeholder="e.g. 30">
                        <div class="input-group-append">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group row align-items-center">
                <label class="col-md-4 col-form-label">
                    Feedback Module
                </label>
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="number"
                               class="form-control module-weight"
                               name="course_module_weight[FeedbackModule]"
                               min="0"
                               max="100"
                               placeholder="e.g. 20">
                        <div class="input-group-append">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Display -->
            <div class="mt-3">
                <strong>Total:</strong>
                <span id="totalWeight" class="ml-2 badge badge-secondary">0%</span>
            </div>

            <div class="text-danger mt-2 d-none" id="weightError">
                Total weightage must be exactly 100%
            </div>

            <hr>

            {{-- Confirmation --}}
            <p class="mb-0">
                Are you sure you want to submit this final page?
            </p>

        </div>
    </div>

    <hr>

    <!-- Confirmation -->
    

</div>


        {{-- Footer --}}
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.courses.index') }}"
               class="btn btn-outline-danger">
                Cancel
            </a>

            <button type="submit" class="btn btn-success">
                Final Submit
            </button>
        </div>

    </div>
</form>

<script>
    document.querySelectorAll('.module-weight').forEach(input => {
        input.addEventListener('input', calculateTotal);
    });

    function calculateTotal() {
        let total = 0;

        document.querySelectorAll('.module-weight').forEach(input => {
            total += Number(input.value) || 0;
        });

        document.getElementById('totalWeight').innerText = total + '%';

        if (total === 100) {
            document.getElementById('totalWeight').className = 'ml-2 badge badge-success';
            document.getElementById('weightError').classList.add('d-none');
        } else {
            document.getElementById('totalWeight').className = 'ml-2 badge badge-danger';
            document.getElementById('weightError').classList.remove('d-none');
        }
    }
</script>


@stop
