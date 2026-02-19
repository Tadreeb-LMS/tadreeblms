@extends('backend.layouts.app')

@section('title', 'External Apps')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-puzzle-piece mr-2"></i>External Apps Management
                        </h4>
                        <a href="{{ route('admin.external-apps.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i>Upload New Module
                        </a>
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

                    @if ($message = Session::get('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    @if (count($apps) > 0)
                    <div class="row">
                        @foreach ($apps as $app)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-left-{{ $app->getStatusBadge() }}">
                                <div class="card-header bg-light d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1">{{ $app->name }}</h5>
                                        <small class="text-muted">v{{ $app->version ?? '1.0.0' }}</small>
                                    </div>
                                    <span class="badge badge-{{ $app->getStatusBadge() }}">
                                        {{ ucfirst($app->status) }}
                                    </span>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">{{ $app->description ?? 'No description provided' }}</p>
                                    
                                    <div class="mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input toggle-app-status" 
                                                   id="toggle-{{ $app->slug }}" 
                                                   data-slug="{{ $app->slug }}"
                                                   {{ $app->is_enabled ? 'checked' : '' }}
                                                   {{ $app->status !== 'active' ? 'disabled' : '' }}>
                                            <label class="custom-control-label" for="toggle-{{ $app->slug }}">
                                                {{ $app->is_enabled ? 'Enabled' : 'Disabled' }}
                                            </label>
                                        </div>
                                    </div>

                                    @if ($app->installed_at)
                                    <small class="text-muted d-block mb-2">
                                        <i class="fas fa-calendar-check mr-1"></i>
                                        Installed: {{ $app->installed_at->format('M d, Y H:i') }}
                                    </small>
                                    @endif

                                    @if ($app->error_message)
                                    <div class="alert alert-danger alert-sm mb-3">
                                        <small>{{ $app->error_message }}</small>
                                    </div>
                                    @endif
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <a href="{{ route('admin.external-apps.edit-config', $app->slug) }}" 
                                           class="btn btn-outline-primary" 
                                           {{ $app->status !== 'active' ? 'disabled' : '' }}>
                                            <i class="fas fa-cog mr-1"></i>Configure
                                        </a>
                                        <button type="button" class="btn btn-outline-danger delete-app" 
                                                data-slug="{{ $app->slug }}" 
                                                data-name="{{ $app->name }}">
                                            <i class="fas fa-trash mr-1"></i>Uninstall
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
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
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Uninstall</button>
                </form>
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
    // Toggle app status
    $('.toggle-app-status').on('change', function() {
        const slug = $(this).data('slug');
        const enabled = $(this).is(':checked');
        const $toggle = $(this);

        $.ajax({
            url: '/admin/external-apps/' + slug + '/toggle-status',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                enabled: enabled
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showAlert(response.message, 'success');
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

    // Delete app
    $('.delete-app').on('click', function() {
        const slug = $(this).data('slug');
        const name = $(this).data('name');

        $('#appNameDisplay').text(name);
        $('#deleteForm').attr('action', '/admin/external-apps/' + slug);
        $('#deleteModal').modal('show');
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
