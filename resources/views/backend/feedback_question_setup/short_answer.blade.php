<div class="row">
    <div class="col-12 mt-2">
        <label>Question <span class="text-danger">*</span></label>
        <textarea class="form-control editor" rows="3" name="question" id="question" required="required">{{ @$feedbackQuestion->question }}</textarea>
    </div>
</div>
<div class="row">
    <div class="col-12 mt-3">
        <label>Answer</label>
        <textarea class="form-control textarea-col editor" rows="3" name="solution" id="solution">{{ @$feedbackQuestion->solution }}</textarea>
    </div>
</div>

<script src="{{ asset('ckeditor/ckeditor.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    CKEDITOR.replace('question');
    CKEDITOR.replace('solution');
    CKEDITOR.replace('comment')
</script>
