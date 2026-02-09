@if(isset($licenseWarning) && $licenseWarning)
    <div class="alert alert-{{ $licenseWarningType ?? 'warning' }} alert-dismissible fade show mb-3" role="alert">
        <i class="fas {{ $licenseWarningType === 'danger' ? 'fa-exclamation-circle' : 'fa-exclamation-triangle' }} mr-2"></i>
        <strong>{{ __('labels.backend.license_settings.license_notice') }}</strong>
        <p class="mb-0 mt-1">{{ $licenseWarning }}</p>
        <a href="{{ route('admin.license-settings') }}" class="btn btn-sm btn-outline-{{ $licenseWarningType ?? 'warning' }} mt-2">
            <i class="fas fa-key mr-1"></i> {{ __('labels.backend.license_settings.view_license') }}
        </a>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
