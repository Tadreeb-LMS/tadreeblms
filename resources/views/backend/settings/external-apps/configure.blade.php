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

                    {{-- Module info summary --}}
                    <div class="alert alert-info mb-4">
                        <h5><i class="fas fa-info-circle mr-2"></i>Module Information</h5>
                        <table class="table table-sm mb-0">
                            <tr><td><strong>Name:</strong></td><td>{{ $app->name }}</td></tr>
                            <tr><td><strong>Version:</strong></td><td>{{ $app->version ?? 'N/A' }}</td></tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge badge-{{ $app->getStatusBadge() }}">{{ ucfirst($app->status) }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Enabled:</strong></td>
                                <td>
                                    <span class="badge {{ $app->is_enabled ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $app->is_enabled ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Credentials stored in:</strong></td>
                                <td>
                                    <code class="text-muted" style="font-size:.85em;">
                                        {{ $app->installed_path }}/.env
                                    </code>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <form action="{{ route('admin.external-apps.update-config', $app->slug) }}" method="POST">
                        @csrf

                        @php
                            // Field metadata from config.json (stored in DB configuration column)
                            $fields = $app->configuration['metadata']['fields'] ?? [];
                        @endphp

                        @if (!empty($fields))
                            <h5 class="mb-4 d-flex align-items-center">
                                <i class="fas fa-sliders-h mr-2 text-primary"></i>
                                Configuration Settings
                                <small class="ml-2 text-muted" style="font-size:.75rem;">
                                    (stored in module .env)
                                </small>
                            </h5>

                            @foreach ($fields as $key => $meta)
                                @php
                                    $label       = $meta['label']       ?? ucwords(str_replace('_', ' ', $key));
                                    $type        = $meta['type']        ?? 'text';
                                    $required    = $meta['required']    ?? false;
                                    $placeholder = $meta['placeholder'] ?? '';
                                    // Value comes from the module .env, falling back to empty string
                                    $currentVal  = $moduleEnv[$key] ?? '';
                                @endphp

                                <div class="form-group row mb-3">
                                    <label for="field_{{ $key }}" class="col-md-3 col-form-label font-weight-bold">
                                        {{ $label }}{{ $required ? ' *' : '' }}
                                    </label>
                                    <div class="col-md-9">
                                        @if ($type === 'password')
                                            <div class="input-group">
                                                <input type="password"
                                                       class="form-control password-field"
                                                       id="field_{{ $key }}"
                                                       name="{{ $key }}"
                                                       value="{{ old($key, $currentVal) }}"
                                                       placeholder="{{ $placeholder }}"
                                                       autocomplete="new-password"
                                                       {{ $required ? 'required' : '' }}>
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @elseif ($type === 'switch' || is_bool($currentVal))
                                            <div class="custom-control custom-switch mt-2">
                                                <input type="checkbox"
                                                       class="custom-control-input"
                                                       id="field_{{ $key }}"
                                                       name="{{ $key }}"
                                                       value="1"
                                                       {{ $currentVal ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="field_{{ $key }}">Enabled</label>
                                            </div>
                                        @elseif ($type === 'textarea')
                                            <textarea class="form-control"
                                                      id="field_{{ $key }}"
                                                      name="{{ $key }}"
                                                      rows="4"
                                                      placeholder="{{ $placeholder }}"
                                                      {{ $required ? 'required' : '' }}>{{ old($key, $currentVal) }}</textarea>
                                        @else
                                            <input type="{{ $type }}"
                                                   class="form-control"
                                                   id="field_{{ $key }}"
                                                   name="{{ $key }}"
                                                   value="{{ old($key, $currentVal) }}"
                                                   placeholder="{{ $placeholder }}"
                                                   {{ $required ? 'required' : '' }}>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                No configuration fields defined for this module.
                            </div>
                        @endif

                        @if ($app->slug === 'zoom')
                            <hr class="mt-4 mb-4">
                            <h5 class="mb-4 d-flex align-items-center">
                                <i class="fas fa-plug mr-2 text-primary"></i>
                                Test Connection
                            </h5>
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle mr-2"></i>
                                Click the button below to test if the Zoom API credentials provided above are working correctly. You do not need to save the configuration first.
                            </div>
                            <div id="testConnectionAlertContainer"></div>
                            <div class="row">
                                <div class="col text-left">
                                    <button type="button" class="btn btn-info" id="btnTestConnection">
                                        <i class="fas fa-link mr-1"></i> Test Zoom Connection
                                    </button>
                                </div>
                            </div>
                        @endif

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
$(document).ready(function () {
    // Toggle password visibility
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

    @if ($app->slug === 'zoom')
    $('#btnTestConnection').on('click', function() {
        var btn = $(this);
        var btnHtml = btn.html();
        var alertContainer = $('#testConnectionAlertContainer');

        // Clear previous alerts
        alertContainer.empty();

        // Get values from the form
        var accountId = $('#field_ZOOM_ACCOUNT_ID').val();
        var clientId = $('#field_ZOOM_CLIENT_ID').val();
        var clientSecret = $('#field_ZOOM_CLIENT_SECRET').val();

        if (!accountId || !clientId || !clientSecret) {
            alertContainer.html('<div class="alert alert-warning alert-dismissible fade show"><i class="fas fa-exclamation-triangle mr-2"></i>Please fill in Account ID, Client ID, and Client Secret before testing.<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>');
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Testing...');

        $.ajax({
            url: '{{ url("external-apps/zoom/test-connection") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                ZOOM_ACCOUNT_ID: accountId,
                ZOOM_CLIENT_ID: clientId,
                ZOOM_CLIENT_SECRET: clientSecret
            },
            success: function(response) {
                alertContainer.html('<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle mr-2"></i>' + response.message + '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>');
                btn.prop('disabled', false).html(btnHtml);
            },
            error: function(xhr) {
                var msg = 'Failed to connect to Zoom API.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alertContainer.html('<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle mr-2"></i>' + msg + '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>');
                btn.prop('disabled', false).html(btnHtml);
            }
        });
    });
    @endif
});
</script>
@endpush
