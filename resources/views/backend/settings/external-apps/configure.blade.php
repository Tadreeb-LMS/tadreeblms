@extends('backend.layouts.app')

@section('title', 'Configure ' . $app->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-cog mr-2"></i>Configure {{ $app->name }}
                        </h4>
                        <span class="badge badge-{{ $app->getStatusBadge() }}">{{ ucfirst($app->status) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                    @endif

                    @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                    @endif

                    <div class="alert alert-info mb-4">
                        <h5><i class="fas fa-info-circle mr-2"></i>Module Information</h5>
                        <table class="table table-sm mb-0">
<<<<<<< HEAD
                            <tr><td><strong>Name:</strong></td><td>{{ $app->name }}</td></tr>
                            <tr><td><strong>Version:</strong></td><td>{{ $app->version ?? 'N/A' }}</td></tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge badge-{{ $app->getStatusBadge() }}">{{ ucfirst($app->status) }}</span></td>
=======
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $app->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Version:</strong></td>
                                <td>{{ $app->version ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge badge-{{ $app->getStatusBadge() }}">{{ ucfirst($app->status) }}</span>
                                </td>
>>>>>>> MarketPlace
                            </tr>
                            <tr>
                                <td><strong>Enabled:</strong></td>
                                <td>
                                    <span class="badge {{ $app->is_enabled ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $app->is_enabled ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                            </tr>
<<<<<<< HEAD
=======
                            <tr>
                                <td><strong>Installed:</strong></td>
                                <td>{{ $app->installed_at ? $app->installed_at->format('M d, Y H:i') : 'N/A' }}</td>
                            </tr>
>>>>>>> MarketPlace
                        </table>
                    </div>

                    <form action="{{ route('admin.external-apps.update-config', $app->slug) }}" method="POST">
                        @csrf

<<<<<<< HEAD
                        {{-- ===== ZOOM: read from .env only ===== --}}
                        @if (isset($zoomConfig))
                            <h5 class="mb-4 d-flex align-items-center">
                                <i class="fas fa-sliders-h mr-2 text-primary"></i>
                                Configuration Settings
                                <small class="ml-2 text-muted" style="font-size:.75rem;">(stored in .env)</small>
                            </h5>

                            <div class="form-group row mb-3">
                                <label for="ZOOM_ACCOUNT_ID" class="col-md-3 col-form-label font-weight-bold">
                                    ZOOM_ACCOUNT_ID <span class="text-danger">*</span>
                                </label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control"
                                           id="ZOOM_ACCOUNT_ID" name="ZOOM_ACCOUNT_ID"
                                           value="{{ old('ZOOM_ACCOUNT_ID', $zoomConfig['ZOOM_ACCOUNT_ID']) }}"
                                           placeholder="e.g. aBcDeFgH1234" required>
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="ZOOM_CLIENT_ID" class="col-md-3 col-form-label font-weight-bold">
                                    ZOOM_CLIENT_ID <span class="text-danger">*</span>
                                </label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control"
                                           id="ZOOM_CLIENT_ID" name="ZOOM_CLIENT_ID"
                                           value="{{ old('ZOOM_CLIENT_ID', $zoomConfig['ZOOM_CLIENT_ID']) }}"
                                           placeholder="e.g. xYzAbC123456" required>
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label for="ZOOM_CLIENT_SECRET" class="col-md-3 col-form-label font-weight-bold">
                                    ZOOM_CLIENT_SECRET <span class="text-danger">*</span>
                                </label>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <input type="password" class="form-control password-field"
                                               id="ZOOM_CLIENT_SECRET" name="ZOOM_CLIENT_SECRET"
                                               value="{{ old('ZOOM_CLIENT_SECRET', $zoomConfig['ZOOM_CLIENT_SECRET']) }}"
                                               placeholder="Enter your Zoom Client Secret"
                                               autocomplete="new-password" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        {{-- ===== Other modules: dynamic fields from DB config ===== --}}
                        @elseif ($app->configuration && count($app->configuration) > 0)
                            <h5 class="mb-4 d-flex align-items-center">
                                <i class="fas fa-sliders-h mr-2 text-primary"></i>
                                Configuration Settings
                            </h5>

                            @php
                                $metadata = $app->configuration['metadata'] ?? [];
                                $fields = $metadata['fields'] ?? [];
                            @endphp

                            @foreach ($app->configuration as $key => $value)
                                @if (!in_array($key, ['name', 'description', 'version', 'metadata']))
                                    @php
                                        $fieldMeta   = $fields[$key] ?? [];
                                        $label       = $fieldMeta['label']       ?? ucwords(str_replace('_', ' ', $key));
                                        $type        = $fieldMeta['type']        ?? 'text';
                                        $required    = $fieldMeta['required']    ?? false;
                                        $placeholder = $fieldMeta['placeholder'] ?? '';
                                    @endphp

                                    <div class="form-group row mb-3">
                                        <label for="config_{{ $key }}" class="col-md-3 form-control-label">
                                            {{ $label }}{{ $required ? '*' : '' }}
                                        </label>
                                        <div class="col-md-9">
                                            @if (is_array($value))
                                                <textarea class="form-control" id="config_{{ $key }}" name="{{ $key }}" rows="4" {{ $required ? 'required' : '' }}>{{ json_encode($value, JSON_PRETTY_PRINT) }}</textarea>
                                            @elseif (is_bool($value) || $type === 'switch')
                                                <div class="custom-control custom-switch mt-2">
                                                    <input type="checkbox" class="custom-control-input"
                                                           id="config_{{ $key }}" name="{{ $key }}" value="1"
                                                           {{ $value ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="config_{{ $key }}">Enabled</label>
                                                </div>
                                            @elseif ($type === 'password')
                                                <div class="input-group">
                                                    <input type="password" class="form-control password-field"
                                                           id="config_{{ $key }}" name="{{ $key }}"
                                                           value="{{ $value }}" placeholder="{{ $placeholder }}"
                                                           {{ $required ? 'required' : '' }}>
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                <input type="{{ $type }}" class="form-control"
                                                       id="config_{{ $key }}" name="{{ $key }}"
                                                       value="{{ $value }}" placeholder="{{ $placeholder }}"
                                                       {{ $required ? 'required' : '' }}>
                                            @endif
                                        </div>
=======
                        @if ($app->configuration && count($app->configuration) > 0)
                            <h5 class="mb-3">Configuration Settings</h5>

                            @foreach ($app->configuration as $key => $value)
                                @if (!in_array($key, ['name', 'description', 'version']))
                                    <div class="form-group">
                                        <label for="config_{{ $key }}">{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                                        
                                        @if (is_array($value))
                                            <textarea class="form-control" id="config_{{ $key }}" name="{{ $key }}" rows="4">{{ json_encode($value, JSON_PRETTY_PRINT) }}</textarea>
                                        @elseif (is_bool($value))
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" 
                                                       id="config_{{ $key }}" name="{{ $key }}" value="1"
                                                       {{ $value ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="config_{{ $key }}">Enabled</label>
                                            </div>
                                        @else
                                            <input type="text" class="form-control" id="config_{{ $key }}" name="{{ $key }}" value="{{ $value }}">
                                        @endif
>>>>>>> MarketPlace
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                No configuration options available for this module.
                            </div>
                        @endif

<<<<<<< HEAD
                        <hr class="mt-4 mb-4">

                        <div class="row">
                            <div class="col text-left">
                                <a href="{{ route('admin.external-apps.index') }}" class="btn btn-secondary">
                                    Back
                                </a>
                            </div>
                            <div class="col text-right">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save mr-1"></i>Save Configuration
                                </button>
                            </div>
=======
                        <div class="form-group mt-4">
                            <a href="{{ route('admin.external-apps.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i>Back
                            </a>
                            @if ($app->configuration && count($app->configuration) > 0)
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i>Save Configuration
                                </button>
                            @endif
>>>>>>> MarketPlace
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<<<<<<< HEAD

@push('after-scripts')
<script>
$(document).ready(function () {
    $('.toggle-password').on('click', function () {
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
});
</script>
@endpush
=======
>>>>>>> MarketPlace
