@extends('backend.layouts.app')

@section('title', __('s3_storage.page_title') . ' | ' . config('app.name'))

@push('after-styles')
<style>
    .s3-fields { display: none; }
    .s3-fields.active { display: block; }
    .test-result {
        display: none;
        margin-top: 10px;
        padding: 10px 15px;
        border-radius: 4px;
        font-size: 14px;
    }
    .test-result.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .test-result.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-cloud mr-2"></i>{{ __('s3_storage.page_title') }}
                    </h4>
                    <span class="badge badge-{{ ($settings['STORAGE_DRIVER'] ?? 'local') === 's3' ? 'success' : 'secondary' }}">
                        {{ ($settings['STORAGE_DRIVER'] ?? 'local') === 's3' ? __('s3_storage.s3_active') : __('s3_storage.local_storage') }}
                    </span>
                </div>
            </div>
            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif

                <p class="text-muted mb-4">{{ __('s3_storage.description') }}</p>

                <form action="{{ route('admin.s3-storage-settings.store') }}" method="POST" id="storageSettingsForm">
                    @csrf

                    {{-- Storage Driver --}}
                    <div class="form-group row">
                        <label for="storage_driver" class="col-md-3 col-form-label font-weight-bold">
                            {{ __('s3_storage.storage_driver') }} <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-6">
                            <select name="storage_driver" id="storage_driver" class="form-control @error('storage_driver') is-invalid @enderror">
                                <option value="local" {{ ($settings['STORAGE_DRIVER'] ?? 'local') === 'local' ? 'selected' : '' }}>
                                    {{ __('s3_storage.local_storage') }}
                                </option>
                                <option value="s3" {{ ($settings['STORAGE_DRIVER'] ?? '') === 's3' ? 'selected' : '' }}>
                                    {{ __('s3_storage.s3_compatible_storage') }}
                                </option>
                            </select>
                            @error('storage_driver')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- S3 Configuration Fields --}}
                    <div class="s3-fields {{ ($settings['STORAGE_DRIVER'] ?? 'local') === 's3' ? 'active' : '' }}">
                        <hr/>
                        <h5 class="mb-3"><i class="fab fa-aws mr-1"></i> {{ __('s3_storage.s3_configuration') }}</h5>

                        {{-- Access Key --}}
                        <div class="form-group row">
                            <label for="s3_access_key_id" class="col-md-3 col-form-label font-weight-bold">
                                Access Key ID <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-6">
                                <input type="text" name="s3_access_key_id" id="s3_access_key_id"
                                       class="form-control @error('s3_access_key_id') is-invalid @enderror"
                                       value="{{ old('s3_access_key_id', $settings['S3_ACCESS_KEY_ID'] ?? '') }}"
                                       placeholder="Enter your access key ID">
                                @error('s3_access_key_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Secret Key --}}
                        <div class="form-group row">
                            <label for="s3_secret_access_key" class="col-md-3 col-form-label font-weight-bold">
                                Secret Access Key <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="password" name="s3_secret_access_key" id="s3_secret_access_key"
                                           class="form-control password-field @error('s3_secret_access_key') is-invalid @enderror"
                                           value="{{ old('s3_secret_access_key', $settings['S3_SECRET_ACCESS_KEY'] ?? '') }}"
                                           placeholder="Enter your secret access key">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                @error('s3_secret_access_key')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Region --}}
                        <div class="form-group row">
                            <label for="s3_default_region" class="col-md-3 col-form-label font-weight-bold">
                                Region <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-6">
                                <input type="text" name="s3_default_region" id="s3_default_region"
                                       class="form-control @error('s3_default_region') is-invalid @enderror"
                                       value="{{ old('s3_default_region', $settings['S3_DEFAULT_REGION'] ?? 'us-east-1') }}"
                                       placeholder="e.g. us-east-1">
                                @error('s3_default_region')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Bucket --}}
                        <div class="form-group row">
                            <label for="s3_bucket" class="col-md-3 col-form-label font-weight-bold">
                                {{ __('s3_storage.bucket_name') }} <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-6">
                                <input type="text" name="s3_bucket" id="s3_bucket"
                                       class="form-control @error('s3_bucket') is-invalid @enderror"
                                       value="{{ old('s3_bucket', $settings['S3_BUCKET'] ?? '') }}"
                                       placeholder="e.g. my-bucket">
                                @error('s3_bucket')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Endpoint --}}
                        <div class="form-group row">
                            <label for="s3_endpoint" class="col-md-3 col-form-label font-weight-bold">
                                {{ __('s3_storage.endpoint_url') }}
                            </label>
                            <div class="col-md-6">
                                <input type="text" name="s3_endpoint" id="s3_endpoint"
                                       class="form-control @error('s3_endpoint') is-invalid @enderror"
                                       value="{{ old('s3_endpoint', $settings['S3_ENDPOINT'] ?? '') }}"
                                       placeholder="e.g. https://s3.example.com (optional)">
                                @error('s3_endpoint')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">{{ __('s3_storage.endpoint_help') }}</small>
                            </div>
                        </div>

                        {{-- Custom URL --}}
                        <div class="form-group row">
                            <label for="s3_url" class="col-md-3 col-form-label font-weight-bold">
                                {{ __('s3_storage.custom_url') }}
                            </label>
                            <div class="col-md-6">
                                <input type="text" name="s3_url" id="s3_url"
                                       class="form-control @error('s3_url') is-invalid @enderror"
                                       value="{{ old('s3_url', $settings['S3_URL'] ?? '') }}"
                                       placeholder="e.g. https://cdn.example.com (optional)">
                                @error('s3_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">{{ __('s3_storage.custom_url_help') }}</small>
                            </div>
                        </div>

                        {{-- Root Path --}}
                        <div class="form-group row">
                            <label for="s3_root" class="col-md-3 col-form-label font-weight-bold">
                                {{ __('s3_storage.root_path') }}
                            </label>
                            <div class="col-md-6">
                                <input type="text" name="s3_root" id="s3_root"
                                       class="form-control @error('s3_root') is-invalid @enderror"
                                       value="{{ old('s3_root', $settings['S3_ROOT'] ?? '') }}"
                                       placeholder="e.g. uploads (optional)">
                                @error('s3_root')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">{{ __('s3_storage.root_path_help') }}</small>
                            </div>
                        </div>

                        {{-- Test Connection --}}
                        <div class="form-group row">
                            <div class="col-md-6 offset-md-3">
                                <button type="button" id="testConnectionBtn" class="btn btn-outline-info">
                                    <i class="fas fa-plug mr-1"></i> {{ __('s3_storage.test_connection') }}
                                </button>
                                <div id="testResult" class="test-result"></div>
                            </div>
                        </div>
                    </div>

                    <hr/>

                    {{-- Save Button --}}
                    <div class="form-group row">
                        <div class="col-md-6 offset-md-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save mr-1"></i> {{ __('s3_storage.save_settings') }}
                            </button>
                            <a href="{{ route('admin.external-apps.index') }}" class="btn btn-secondary ml-2">
                                {{ __('s3_storage.back') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after-scripts')
<script>
$(document).ready(function() {
    // Toggle S3 fields visibility
    $('#storage_driver').on('change', function() {
        if ($(this).val() === 's3') {
            $('.s3-fields').addClass('active');
        } else {
            $('.s3-fields').removeClass('active');
        }
    });

    // Toggle password visibility
    $('.toggle-password').on('click', function() {
        var input = $(this).closest('.input-group').find('.password-field');
        var icon  = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Test Connection
    $('#testConnectionBtn').on('click', function() {
        var btn = $(this);
        var resultDiv = $('#testResult');
        var originalText = btn.html();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> {{ __('s3_storage.testing') }}');
        resultDiv.hide().removeClass('success error');

        $.ajax({
            url: '{{ route("admin.s3-storage-settings.test-connection") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                storage_driver: 's3',
                s3_access_key_id: $('#s3_access_key_id').val(),
                s3_secret_access_key: $('#s3_secret_access_key').val(),
                s3_default_region: $('#s3_default_region').val(),
                s3_bucket: $('#s3_bucket').val(),
                s3_endpoint: $('#s3_endpoint').val(),
                s3_url: $('#s3_url').val(),
                s3_root: $('#s3_root').val()
            },
            success: function(response) {
                resultDiv
                    .addClass(response.success ? 'success' : 'error')
                    .text(response.message)
                    .show();
            },
            error: function(xhr) {
                var message = '{{ __('s3_storage.error_occurred') }}';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                resultDiv.addClass('error').text(message).show();
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endpush
