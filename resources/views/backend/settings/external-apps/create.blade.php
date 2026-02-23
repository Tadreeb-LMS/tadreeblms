@extends('backend.layouts.app')

@section('title', 'Upload External App')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-cloud-upload-alt mr-2"></i>Upload External Module
                    </h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <h4><i class="icon fa fa-ban"></i> Alert!</h4>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('admin.external-apps.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf


                        <div class="form-group">
                            <label for="zip_file">Upload Zip File <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('zip_file') is-invalid @enderror" 
                                       id="zip_file" name="zip_file" accept=".zip" required>
                                <label class="custom-file-label" for="zip_file">Choose zip file...</label>
                                @error('zip_file')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted d-block mt-2">
                                <i class="fas fa-info-circle mr-1"></i>Maximum file size: 100MB
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <h5 class="mb-3"><i class="fas fa-lightbulb mr-2"></i>Module Requirements</h5>
                            <p>Your zip file must contain the following structure:</p>
                            <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">module-name/
├── config.json          (Required)
├── install.php          (Optional)
├── uninstall.php        (Optional)
├── validate-config.php  (Optional)
└── ... other files</pre>
                            <p class="mb-0"><strong>config.json example:</strong></p>
                            <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; margin-top: 8px;">{
  "name": "Google Meet Integration",
  "description": "Integrate Google Meet with LMS",
  "version": "1.0.0"
}</pre>
                        </div>

                        <div class="form-group mt-4">
                            <a href="{{ route('admin.external-apps.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-upload mr-1"></i>Upload & Install
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after-scripts')
<script>
$(document).ready(function() {
    // Update custom file input label
    $('#zip_file').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });


    // Form submission
    $('#uploadForm').on('submit', function() {
        $('#submitBtn').prop('disabled', true);
        $('#submitBtn').html('<i class="fas fa-spinner fa-spin mr-1"></i>Uploading...');
    });
});
</script>
@endpush
