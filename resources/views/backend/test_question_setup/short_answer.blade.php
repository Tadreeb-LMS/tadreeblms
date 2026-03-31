
<div class="row">
    <div class="col-12 child">
        <label>Solution</label>
        <textarea class="form-control textarea-col editor" rows="3" name="solution" id="solution"></textarea>
    </div>
</div>
<div class="row">
    <div class="col-12 child">
        <label>Marks <span style="color:red">*</span></label>
        <input type="number" class="form-control" name="marks" id="marks" placeholder="Enter Marks" required oninvalid="this.setCustomValidity('Marks is required')" oninput="this.setCustomValidity('')"/>
    </div>
</div>
<div class="row">
    <div class="col-12 child">
        <label>Comment</label>
        <textarea class="form-control textarea-col editor" rows="3" name="comment" id="comment"></textarea>
    </div>
</div>
<script src="{{asset('ckeditor/ckeditor.js')}}" type="text/javascript"></script>
<script type="text/javascript">
    CKEDITOR.replace('question');
    CKEDITOR.replace('solution');
    CKEDITOR.replace('comment')
</script>