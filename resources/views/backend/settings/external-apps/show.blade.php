@extends('backend.layouts.app')

@section('title', $app->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-puzzle-piece mr-2"></i>{{ $app->name }}
                        </h4>
                        <span class="badge badge-{{ $app->getStatusBadge() }}">{{ ucfirst($app->status) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if ($message = Session::get('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>{{ $message }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-3">{{ __('external_apps.external_apps.module_details') }}</h5>
                            
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>{{ __('labels.backend.courses.fields.description') }}:</strong></td>
                                    <td>{{ $app->description ?? __('external_apps.external_apps.no_description_provided') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('external_apps.external_apps.version') }}:</strong></td>
                                    <td>{{ $app->version ?? '1.0.0' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('external_apps.external_apps.status') }}:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $app->getStatusBadge() }}">{{ ucfirst($app->status) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('external_apps.external_apps.installation_path') }}:</strong></td>
                                    <td>
                                        <code class="text-muted" style="font-size: 0.85em;">{{ $app->installed_path ?? __('labels.general.not_available') }}</code>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('external_apps.external_apps.installed') }}:</strong></td>
                                    <td>{{ $app->installed_at ? $app->installed_at->format('M d, Y H:i A') : __('external_apps.external_apps.not_installed') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('external_apps.external_apps.last_updated') }}:</strong></td>
                                    <td>{{ $app->last_updated_at ? $app->last_updated_at->format('M d, Y H:i A') : __('external_apps.external_apps.never') }}</td>
                                </tr>
                            </table>

                            @if ($app->error_message)
                            <div class="alert alert-danger mt-3">
                                <h5><i class="fas fa-exclamation-circle mr-2"></i>{{ __('external_apps.external_apps.error') }}</h5>
                                <p class="mb-0">{{ $app->error_message }}</p>
                            </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <h5 class="mb-3">{{ __('external_apps.external_apps.actions') }}</h5>
                            
                            <div class="list-group">
                                <div class="list-group-item">
                                    <strong class="d-block mb-2">{{ __('external_apps.external_apps.status') }}</strong>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="toggleStatus"
                                               {{ $app->is_enabled ? 'checked' : '' }}
                                               {{ $app->status !== 'active' ? 'disabled' : '' }}>
                                        <label class="custom-control-label" for="toggleStatus">
                                            {{ $app->is_enabled ? __('external_apps.external_apps.enabled') : __('external_apps.external_apps.disabled') }}
                                        </label>
                                    </div>
                                </div>

                                @if ($app->status === 'active')
                                <a href="{{ route('admin.external-apps.edit-config', $app->slug) }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-cog mr-2"></i>{{ __('external_apps.external_apps.configure_module') }}
                                </a>
                                @endif

                                <button type="button" class="list-group-item list-group-item-action text-danger" id="uninstallBtn">
                                    <i class="fas fa-trash mr-2"></i>{{ __('external_apps.external_apps.uninstall_module') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    @if ($app->configuration && count($app->configuration) > 0)
                    <hr class="my-4">
                    <h5 class="mb-3">{{ __('external_apps.external_apps.current_configuration') }}</h5>
                    <div class="card bg-light">
                        <div class="card-body">
                            <pre class="mb-0">{{ json_encode($app->configuration, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('admin.external-apps.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>{{ __('external_apps.external_apps.back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Uninstall Modal -->
<div class="modal fade" id="uninstallModal" tabindex="-1" role="dialog" aria-labelledby="uninstallModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="uninstallModalLabel">
                    <i class="fas fa-warning mr-2"></i>{{ __('external_apps.external_apps.confirm_uninstall') }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ __('external_apps.external_apps.sure_uninstall_generic') }} <strong>{{ $app->name }}</strong>?</p>
                <p class="text-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ __('external_apps.external_apps.uninstall_warning') }}
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('external_apps.external_apps.cancel') }}</button>
                <form id="uninstallForm" action="{{ route('admin.external-apps.destroy', $app->slug) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-1"></i>{{ __('external_apps.external_apps.uninstall_module') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('after-scripts')
<script>
$(document).ready(function() {
    // Toggle status
    $('#toggleStatus').on('change', function() {
        const enabled = $(this).is(':checked');
        const $toggle = $(this);

        $.ajax({
            url: '{{ route("admin.external-apps.toggle-status", $app->slug) }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                enabled: enabled
            },
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    $toggle.prop('checked', enabled);
                    location.reload();
                } else {
                    showAlert(response.message, 'error');
                    $toggle.prop('checked', !enabled);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showAlert(response.message || '{{ __('external_apps.external_apps.an_error_occurred') }}', 'error');
                $toggle.prop('checked', !enabled);
            }
        });
    });

    // Uninstall button
    $('#uninstallBtn').on('click', function() {
        $('#uninstallModal').modal('show');
    });
});

function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${icon} mr-2"></i>${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;

    $('.card-body').prepend(alertHtml);

    setTimeout(() => {
        $('.card-body .alert').fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}
</script>
@endpush
