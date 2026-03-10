<div class="modal fade" id="assignCourseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="assignCourseForm" action="{{ route('course.assign.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Course</h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select User / User Group</label>
                        <select name="user_id" class="form-control" required>
                            <!-- Populate via JS or existing blade loop -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Select Course</label>
                        <select name="course_id" class="form-control" required>
                            <!-- Populate via JS or existing blade loop -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
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
            swal('Success', 'Course assigned successfully!', 'success');
            $('#assignCourseModal').modal('hide');
            location.reload(); // Refresh to update list
        })
        .catch(err => swal('Error', 'Failed to assign course', 'error'));
});
</script>