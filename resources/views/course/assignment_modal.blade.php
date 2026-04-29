<div class="modal fade" id="assignCourseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="assignCourseForm" action="{{ route('course.assign.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('modals_pages.assignment_modal.title') }}</h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('modals_pages.assignment_modal.select_user') }}</label>
                        <select name="user_id" class="form-control" required>
                            <!-- Populate via JS or existing blade loop -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('modals_pages.assignment_modal.select_course') }}</label>
                        <select name="course_id" class="form-control" required>
                            <!-- Populate via JS or existing blade loop -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('modals_pages.assignment_modal.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('modals_pages.assignment_modal.assign_button') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('#assignCourseForm').on('submit', function(e) {
    e.preventDefault();
    axios.post($(this).attr('action'), $(this).serialize())
        .then(res => {
            swal('Success', '{{ __('modals_pages.assignment_modal.success_message') }}', 'success');
            $('#assignCourseModal').modal('hide');
            location.reload(); // Refresh to update list
        })
        .catch(err => swal('Error', '{{ __('modals_pages.assignment_modal.error_message') }}', 'error'));
});
</script>