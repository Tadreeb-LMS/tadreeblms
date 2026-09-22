<div class="row">
    <div class="col-12 mt-2">
        <label>Question <span class="text-danger">*</span></label>
        <textarea class="form-control editor" rows="3" name="question" id="question" required="required">{{ @$feedbackQuestion->question }}</textarea>
    </div>
</div>
<div class="row">
    <div class="col-6 mt-3">
        <label>Option</label>
        <textarea class="form-control editor" rows="3" name="option" id="option" required="required"></textarea>
        <button type="button" id="add_option" class="btn add-btn pull-right mt-3">Add Option</button>
    </div>
    <div class="col-6" style="margin-top: 20px;">
        <div id="option-area" class="pt-4"></div>
    </div>
</div>
<!-- <div class="row">
    <div class="col-12">
        <label>Solution</label>
        <textarea class="form-control textarea-col editor" rows="3" name="solution" id="solution"></textarea>
    </div>
</div> -->

<script src="{{ asset('ckeditor/ckeditor.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    CKEDITOR.replace('question');
    CKEDITOR.replace('option');
    CKEDITOR.replace('solution');
    CKEDITOR.replace('comment')
</script>
