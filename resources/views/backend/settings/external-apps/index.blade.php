@extends('backend.layouts.app')

@section('title', 'External Apps')

@section('content')

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-end">
            <a href="{{ route('admin.external-apps.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i>Upload New Module
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle mr-2"></i>{{ $message }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if ($message = Session::get('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (count($apps) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Name</th>
                                    <th class="text-center">Enable/Disable</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($apps as $app)
                                <tr>
                                    <td>
                                        <strong>{{ $app->name }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-switch d-inline-block">
                                            <input type="checkbox" class="custom-control-input toggle-app-status" id="toggle-{{ $app->slug }}" data-slug="{{ $app->slug }}" {{ $app->is_enabled ? 'checked' : '' }} {{ $app->status !== 'active' ? 'disabled' : '' }}>
                                            <label class="custom-control-label" for="toggle-{{ $app->slug }}"></label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if(!$app->is_enabled)
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-app" data-slug="{{ $app->slug }}" data-name="{{ $app->name }}">
                                            <i class="fas fa-trash mr-1"></i>Uninstall
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No external modules installed yet.</p>
                        <a href="{{ route('admin.external-apps.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i>Upload First Module
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Uninstall</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to uninstall <strong id="appNameDisplay"></strong>?</p>
                <p class="text-warning"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmUninstallBtn">Uninstall</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('after-scripts')
<style>
.border-left-success {
    border-left: 4px solid #28a745;
}

.border-left-danger {
    border-left: 4px solid #dc3545;
}

.border-left-secondary {
    border-left: 4px solid #6c757d;
}

.alert-sm {
    padding: 0.5rem;
    margin: 0;
}
</style>

<script>
$(document).ready(function() {
    var pendingDeleteSlug = null;

    // Toggle app status
    $('.toggle-app-status').on('change', function() {
        const slug = $(this).data('slug');
        const enabled = $(this).is(':checked');
        const $toggle = $(this);

        $.ajax({
            url: '/user/external-apps/' + slug + '/toggle-status',
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
                    // Delay reload to allow .env changes to settle
                    setTimeout(function() {
                        location.reload();
                    }, 2500);
                } else {
                    showAlert(response.message, 'error');
                    $toggle.prop('checked', !enabled);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showAlert(response.message || 'An error occurred', 'error');
                $toggle.prop('checked', !enabled);
            }
        });
    });

    // Delete app - show modal
    $('.delete-app').on('click', function() {
        pendingDeleteSlug = $(this).data('slug');
        const name = $(this).data('name');

        $('#appNameDisplay').text(name);
        $('#deleteModal').modal('show');
    });

    // Confirm uninstall via AJAX
    $('#confirmUninstallBtn').on('click', function() {
        if (!pendingDeleteSlug) return;

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Uninstalling...');

        $.ajax({
            url: '/user/external-apps/' + pendingDeleteSlug,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#deleteModal').modal('hide');
                if (response.success) {
                    showAlert(response.message, 'success');
                    // Delay reload to allow .env changes to settle
                    setTimeout(function() {
                        location.reload();
                    }, 2500);
                } else {
                    showAlert(response.message, 'error');
                    $btn.prop('disabled', false).html('Uninstall');
                }
            },
            error: function(xhr) {
                $('#deleteModal').modal('hide');
                const response = xhr.responseJSON || {};
                showAlert(response.message || 'An error occurred', 'error');
                $btn.prop('disabled', false).html('Uninstall');
            }
        });
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
