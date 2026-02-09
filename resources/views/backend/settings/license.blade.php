@extends('backend.layouts.app')
@section('title', __('labels.backend.license_settings.title').' | '.app_name())

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- Alert Container --}}
            <div id="licenseAlertContainer"></div>

            {{-- Configuration Warning --}}
            @if(!$isConfigured)
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>{{ __('labels.backend.license_settings.not_configured') }}</strong>
                    <p class="mb-0 mt-2">{{ __('labels.backend.license_settings.not_configured_note') }}</p>
                    <code class="d-block mt-2">
                        KEYGEN_ACCOUNT_ID=your-account-id<br>
                        KEYGEN_PRODUCT_ID=your-product-id
                    </code>
                </div>
            @endif

            {{-- User Limit Warning --}}
            @if($stats['is_exceeded'])
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <strong>{{ __('labels.backend.license_settings.limit_exceeded') }}</strong>
                    <p class="mb-0">{{ __('labels.backend.license_settings.limit_exceeded_note', ['current' => $stats['active_users'], 'max' => $stats['max_users']]) }}</p>
                </div>
            @elseif($stats['is_warning'])
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>{{ __('labels.backend.license_settings.limit_warning') }}</strong>
                    <p class="mb-0">{{ __('labels.backend.license_settings.limit_warning_note', ['current' => $stats['active_users'], 'max' => $stats['max_users']]) }}</p>
                </div>
            @endif

            {{-- License Status Card --}}
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-key mr-2"></i>
                                {{ __('labels.backend.license_settings.title') }}
                            </h4>
                        </div>
                        <!-- <div class="col-sm-6 text-right">
                            @if($stats['has_license'])
                                <button type="button" class="btn btn-outline-success btn-sm mr-2" id="btnSyncUsers">
                                    <i class="fas fa-users mr-1"></i> Sync Users
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnRefreshLicense">
                                    <i class="fas fa-sync-alt mr-1"></i> {{ __('labels.backend.license_settings.validate_now') }}
                                </button>
                            @endif
                        </div> -->
                    </div>

                    <hr/>

                    @if($stats['has_license'])
                        @php $license = $stats['license']; @endphp

                        <div class="row">
                            {{-- License Details --}}
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-muted" width="40%">{{ __('labels.backend.license_settings.license_key') }}</td>
                                        <td>
                                            <code id="maskedKey">{{ $license->masked_key }}</code>
                                            <button type="button" class="btn btn-link btn-sm p-0 ml-2" id="toggleKeyVisibility" title="Show/Hide">
                                                <i class="fas fa-eye" id="keyVisibilityIcon"></i>
                                            </button>
                                            <span id="fullKey" class="d-none">{{ $license->license_key }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('labels.backend.license_settings.status') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $license->status_badge_class }} px-3 py-2">
                                                {{ $license->status_label }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('labels.backend.license_settings.license_type') }}</td>
                                        <td>{{ ucfirst($license->license_type ?? 'Standard') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('labels.backend.license_settings.licensed_to') }}</td>
                                        <td>{{ $license->licensed_to ?? '-' }}</td>
                                    </tr>
                                    @if($license->licensee_email)
                                    <tr>
                                        <td class="text-muted">{{ __('labels.backend.license_settings.licensee_email') }}</td>
                                        <td>{{ $license->licensee_email }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>

                            {{-- Usage & Dates --}}
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-muted" width="40%">{{ __('labels.backend.license_settings.max_users') }}</td>
                                        <td>{{ $stats['max_users'] ?? __('labels.backend.license_settings.unlimited') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('labels.backend.license_settings.active_users') }}</td>
                                        <td>
                                            <span class="{{ $stats['is_exceeded'] ? 'text-danger font-weight-bold' : '' }}">
                                                {{ $stats['active_users'] }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('labels.backend.license_settings.remaining_users') }}</td>
                                        <td>
                                            @if($stats['remaining_users'] !== null)
                                                <span class="{{ $stats['remaining_users'] <= 0 ? 'text-danger' : ($stats['remaining_users'] <= 5 ? 'text-warning' : 'text-success') }}">
                                                    {{ max(0, $stats['remaining_users']) }}
                                                </span>
                                            @else
                                                {{ __('labels.backend.license_settings.unlimited') }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('labels.backend.license_settings.expiry_date') }}</td>
                                        <td>
                                            @if($license->expiry_date)
                                                {{ $license->expiry_date->format('M d, Y') }}
                                                @if($license->expiry_date->isPast())
                                                    <span class="badge badge-danger ml-2">{{ __('labels.backend.license_settings.expired') }}</span>
                                                @elseif($license->expiry_date->diffInDays(now()) <= 30)
                                                    <span class="badge badge-warning ml-2">{{ $license->expiry_date->diffForHumans() }}</span>
                                                @endif
                                            @else
                                                {{ __('labels.backend.license_settings.never') }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('labels.backend.license_settings.support_until') }}</td>
                                        <td>
                                            @if($license->support_valid_until)
                                                {{ $license->support_valid_until->format('M d, Y') }}
                                                @if($license->support_valid_until->isPast())
                                                    <span class="badge badge-secondary ml-2">{{ __('labels.backend.license_settings.expired') }}</span>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">{{ __('labels.backend.license_settings.last_validated') }}</td>
                                        <td>
                                            {{ $license->last_validated_at ? $license->last_validated_at->diffForHumans() : '-' }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        {{-- Usage Progress Bar --}}
                        @if($stats['max_users'])
                            <div class="mt-3">
                                <label class="text-muted">{{ __('labels.backend.license_settings.user_usage') }}</label>
                                <div class="progress" style="height: 25px;">
                                    @php
                                        $progressClass = 'bg-success';
                                        if ($stats['usage_percentage'] >= 100) {
                                            $progressClass = 'bg-danger';
                                        } elseif ($stats['usage_percentage'] >= 90) {
                                            $progressClass = 'bg-warning';
                                        } elseif ($stats['usage_percentage'] >= 75) {
                                            $progressClass = 'bg-info';
                                        }
                                    @endphp
                                    <div class="progress-bar {{ $progressClass }}" role="progressbar"
                                         style="width: {{ min(100, $stats['usage_percentage']) }}%"
                                         aria-valuenow="{{ $stats['active_users'] }}"
                                         aria-valuemin="0"
                                         aria-valuemax="{{ $stats['max_users'] }}">
                                        {{ $stats['active_users'] }} / {{ $stats['max_users'] }} {{ __('labels.backend.license_settings.users') }}
                                    </div>
                                </div>
                            </div>
                        @endif

                    @else
                        {{-- No License --}}
                        <div class="text-center py-5">
                            <i class="fas fa-key fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('labels.backend.license_settings.no_license') }}</h5>
                            <p class="text-muted">{!! __('labels.backend.license_settings.no_license_note') !!}</p>
                        </div>
                    @endif
                </div>

                @if($stats['has_license'])
                    <div class="card-footer">
                        <button type="button" class="btn btn-danger btn-sm" id="btnRemoveLicense">
                            <i class="fas fa-trash mr-1"></i> {{ __('labels.backend.license_settings.remove_license') }}
                        </button>
                    </div>
                @endif
            </div>

            {{-- Activate/Update License Card --}}
            <div class="card mt-4">
                <div class="card-body">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-plus-circle mr-2"></i>
                        {{ $stats['has_license'] ? __('labels.backend.license_settings.update_license') : __('labels.backend.license_settings.activate_license') }}
                    </h4>

                    <hr/>

                    <form id="licenseForm">
                        @csrf
                        <div class="form-group">
                            <label for="license_key">{{ __('labels.backend.license_settings.enter_license_key') }}</label>
                            <div class="input-group">
                                <input type="text" name="license_key" id="license_key" class="form-control"
                                       placeholder="{{ __('labels.backend.license_settings.license_key_placeholder') }}" required>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary" id="btnActivate">
                                        <i class="fas fa-check mr-1"></i> {{ __('labels.backend.license_settings.activate') }}
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">{{ __('labels.backend.license_settings.license_key_note') }}</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Remove License Confirmation Modal --}}
    <div class="modal fade" id="removeLicenseModal" tabindex="-1" aria-labelledby="removeLicenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10" style="width: 80px; height: 80px; background-color: rgba(220, 53, 69, 0.1);">
                            <i class="fas fa-exclamation-triangle text-danger fa-2x"></i>
                        </div>
                    </div>
                    <h5 class="mb-2">{{ __('labels.backend.license_settings.remove_license_title') }}</h5>
                    <p class="text-muted mb-4">{{ __('labels.backend.license_settings.remove_confirm') }}</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-secondary mr-2" data-dismiss="modal">
                            {{ __('labels.general.buttons.cancel') }}
                        </button>
                        <button type="button" class="btn btn-danger" id="btnConfirmRemoveLicense">
                            <i class="fas fa-trash mr-1"></i> {{ __('labels.backend.license_settings.remove_license') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
<script>
$(document).ready(function() {
    // Show alert function
    function showAlert(type, message) {
        var alertClass = (type === 'success') ? 'alert-success' : 'alert-danger';
        var iconClass = (type === 'success') ? 'fa-check-circle' : 'fa-exclamation-triangle';
        var html = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
            '<i class="fas ' + iconClass + ' mr-2"></i>' + message +
            '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' +
            '</div>';
        $('#licenseAlertContainer').html(html);
        window.scrollTo(0, 0);
    }

    // Toggle license key visibility
    var keyVisible = false;
    $('#toggleKeyVisibility').on('click', function() {
        keyVisible = !keyVisible;
        if (keyVisible) {
            $('#maskedKey').text($('#fullKey').text());
            $('#keyVisibilityIcon').removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $('#maskedKey').text('{{ $stats["has_license"] ? $stats["license"]->masked_key : "" }}');
            $('#keyVisibilityIcon').removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Activate License
    $('#licenseForm').on('submit', function(e) {
        e.preventDefault();

        var btn = $('#btnActivate');
        var btnHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> {{ __("labels.backend.license_settings.activating") }}');

        $.ajax({
            url: '{{ route("admin.license.activate") }}',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                showAlert('success', response.message);
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                var msg = '{{ __("labels.backend.license_settings.activation_failed") }}';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showAlert('error', msg);
                btn.prop('disabled', false).html(btnHtml);
            }
        });
    });

    // Sync Users to Keygen
    $('#btnSyncUsers').on('click', function() {
        var btn = $(this);
        var btnHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Syncing...');

        $.ajax({
            url: '{{ route("admin.license.sync-users") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            dataType: 'json',
            success: function(response) {
                var details = 'Total: ' + (response.total || 0) +
                    ', Created: ' + (response.created || 0) +
                    ', Attached: ' + (response.attached || 0) +
                    ', Failed: ' + (response.failed || 0);
                showAlert('success', response.message + ' (' + details + ')');
                btn.prop('disabled', false).html(btnHtml);
                setTimeout(function() {
                    location.reload();
                }, 2000);
            },
            error: function(xhr) {
                var msg = 'Failed to sync users';
                var details = '';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    details = ' (Total: ' + (xhr.responseJSON.total || 0) +
                        ', Created: ' + (xhr.responseJSON.created || 0) +
                        ', Failed: ' + (xhr.responseJSON.failed || 0) + ')';
                    if (xhr.responseJSON.errors && xhr.responseJSON.errors.length > 0) {
                        details += '<br><small>' + xhr.responseJSON.errors.join('<br>') + '</small>';
                    }
                }
                showAlert('error', msg + details);
                btn.prop('disabled', false).html(btnHtml);
            }
        });
    });

    // Refresh/Validate License
    $('#btnRefreshLicense').on('click', function() {
        var btn = $(this);
        var btnHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>');

        $.ajax({
            url: '{{ route("admin.license.validate") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            dataType: 'json',
            success: function(response) {
                showAlert('success', response.message);
                if (!response.cached) {
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                }
                btn.prop('disabled', false).html(btnHtml);
            },
            error: function(xhr) {
                var msg = '{{ __("labels.backend.license_settings.validation_failed") }}';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showAlert('error', msg);
                btn.prop('disabled', false).html(btnHtml);
            }
        });
    });

    // Remove License - Show confirmation modal
    $('#btnRemoveLicense').on('click', function() {
        $('#removeLicenseModal').modal('show');
    });

    // Confirm Remove License
    $('#btnConfirmRemoveLicense').on('click', function() {
        var btn = $(this);
        var btnHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> {{ __("labels.backend.license_settings.removing") }}');

        $.ajax({
            url: '{{ route("admin.license.remove") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            dataType: 'json',
            success: function(response) {
                $('#removeLicenseModal').modal('hide');
                showAlert('success', response.message);
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                var msg = '{{ __("labels.backend.license_settings.remove_failed") }}';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                $('#removeLicenseModal').modal('hide');
                showAlert('error', msg);
                btn.prop('disabled', false).html(btnHtml);
            }
        });
    });
});
</script>
@endpush