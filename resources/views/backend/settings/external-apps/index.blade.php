@extends('backend.layouts.app')

@section('title', 'External Apps')

@section('content')

@php
    $installedSlugs = collect($apps ?? [])
        ->pluck('slug')
        ->map(function ($slug) {
            return str_replace('_', '-', strtolower(trim($slug)));
        })
        ->toArray();
@endphp

<div class="container-fluid">
    <div id="externalAppsAlerts"></div>
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-end">
            <a href="{{ route('admin.external-apps.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i>Upload New Module
            </a>
        </div>
    </div>
    <!-- <div class="alert alert-info d-flex justify-content-between align-items-center mb-3" role="alert">
        <span>
            <i class="fas fa-info-circle mr-2"></i>
            Explore additional external applications from the Marketplace.
        </span>
        <a href="https://tadreeblms.com/marketplaces"
        target="_blank"
        rel="noopener noreferrer"
        class="btn btn-sm btn-outline-primary">
            View Marketplace
        </a>
    </div> -->
    <div class="card mt-4">
        <div class="alert alert-info d-flex justify-content-between align-items-center"
            data-toggle="collapse"
            data-target="#marketplaceCollapse"
            aria-expanded="false"
            aria-controls="marketplaceCollapse"
            id="marketplaceHeader"
            style="cursor:pointer;">
            <div class="d-flex align-items-center">
                <!-- <strong>Marketplace</strong> -->
                <i class="fas fa-info-circle text-info mr-2"></i>
                <div class="text-inherit large">
                    Explore external applications available in the marketplace.
                </div>
            </div>

            <!-- <button class="btn btn-sm btn-outline-primary"
                    type="button"
                    data-toggle="collapse"
                    data-target="#marketplaceCollapse"
                    aria-expanded="false"
                    aria-controls="marketplaceCollapse"
                    id="marketplaceToggleBtn">
                Show Marketplace ▼
            </button> -->
            <i class="fas fa-chevron-down" id="marketplaceArrow"></i>
        </div>

        <!-- <div class="collapse" id="marketplaceCollapse">
            <div class="card-body p-0">
                <iframe
                    src="https://tadreeblms.com/marketplaces/"
                    width="100%"
                    height="700"
                    style="border: 1px solid #bee5eb;"
                    loading="lazy">
                </iframe>
            </div>
        </div> -->
        <div class="collapse" id="marketplaceCollapse">
            <div class="card-body">
                <div class="row">
                    @forelse($marketplaceApps ?? [] as $marketplaceApp)
                    @php
                        $marketplaceSlug = basename($marketplaceApp['slug'] ?? '');
                        $marketplaceSlug = str_replace('_', '-', strtolower(trim($marketplaceSlug)));
                        $isInstalled = in_array($marketplaceSlug, $installedSlugs);
                    @endphp
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body d-flex flex-column text-center">
                                    @if(!empty($marketplaceApp['icon']))
                                        <img
                                            src="{{ 'http://localhost:3000' . $marketplaceApp['icon'] }}"
                                            alt="{{ $marketplaceApp['name'] }}"
                                            style="height:80px; width:auto; object-fit:contain; margin: 0 auto 15px;"
                                        >
                                    @endif

                                    <h5 class="mb-2">{{ $marketplaceApp['name'] }}</h5>

                                    @if(!empty($marketplaceApp['summary']))
                                        <p class="text-muted flex-grow-1">
                                            {{ $marketplaceApp['summary'] }}
                                        </p>
                                    @endif

                                    @if(!empty($marketplaceApp['version']))
                                        <p class="small text-muted mb-2">
                                            Version: {{ $marketplaceApp['version'] }}
                                        </p>
                                    @endif

                                    <div class="d-flex justify-content-center mt-auto mb-3">
                                        @if(!empty($marketplaceApp['details_url']))
                                            <a href="{{ 'http://localhost:3000' . $marketplaceApp['details_url'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="btn btn-sm btn-primary mr-2">
                                                View Details
                                            </a>
                                        @endif

                                        <!-- Installed State -->
                                        @if($isInstalled)
                                            <span class="badge badge-success align-self-center px-3 py-2">
                                                Installed
                                            </span>
                                        @else
                                            <button type="button"
                                                    class="btn btn-sm btn-primary install-marketplace-app"
                                                    data-name="{{ $marketplaceApp['name'] }}"
                                                    data-download-url="{{ $marketplaceApp['download_url'] }}">
                                                Install
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-light mb-0">
                                No marketplace applications are available right now.
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
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

<!-- Sync Info Modal -->
<div class="modal fade" id="syncInfoModal" tabindex="-1" role="dialog" aria-labelledby="syncInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" id="syncInfoModalHeader">
                <h5 class="modal-title" id="syncInfoModalLabel">
                    <i class="fas fa-sync-alt mr-2"></i><span id="syncInfoModalTitle">Sync Started</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center py-4">
                <div id="syncInfoIcon" class="mb-3" style="font-size: 48px;"></div>
                <p id="syncInfoMessage" class="mb-2" style="font-size: 15px;"></p>
                <p id="syncInfoDetail" class="text-muted small mb-0"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary px-4" data-dismiss="modal">OK</button>
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
    var toggleInFlight = {};

    $('.toggle-app-status').on('change', function() {
        const slug    = $(this).data('slug');
        const enabled = $(this).is(':checked');
        const $toggle = $(this);

        // Prevent duplicate requests for the same module
        if (toggleInFlight[slug]) {
            $toggle.prop('checked', !enabled);
            return;
        }
        toggleInFlight[slug] = true;

        $.ajax({
            url: '/user/external-apps/' + slug + '/toggle-status',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { enabled: enabled },
            success: function(response) {
                toggleInFlight[slug] = false;

                if (response.success) {
                    var sync = response.sync;

                    if (sync) {
                        if (sync.direction === 'local_to_s3') {
                            $('#syncInfoModalHeader').removeClass('bg-danger bg-warning text-white text-dark').addClass('bg-success text-white');
                            $('#syncInfoModalTitle').text('Module Enabled — Syncing to S3');
                            $('#syncInfoIcon').html('<i class="fas fa-cloud-upload-alt text-success"></i>');
                            $('#syncInfoMessage').html('<strong>' + sync.file_count + ' file(s)</strong> queued for upload to S3.');
                        } else {
                            $('#syncInfoModalHeader').removeClass('bg-success bg-danger text-white').addClass('bg-warning text-dark');
                            $('#syncInfoModalTitle').text('Module Disabled — Syncing from S3');
                            $('#syncInfoIcon').html('<i class="fas fa-cloud-download-alt text-warning"></i>');
                            $('#syncInfoMessage').html('<strong>' + sync.file_count + ' file(s)</strong> queued for download from S3.');
                        }
                        $('#syncInfoDetail').text('Sync runs in the background. Page will reload when you close this dialog.');
                        $('#syncInfoModal').off('hidden.bs.modal').one('hidden.bs.modal', function () {
                            location.reload();
                        });
                        $('#syncInfoModal').modal('show');
                    } else {
                        showAlert(response.message, 'success');
                        setTimeout(function() { location.reload(); }, 1500);
                    }
                } else {
                    $toggle.prop('checked', !enabled);
                    showAlert(response.message, 'error');
                }
            },
            error: function(xhr) {
                toggleInFlight[slug] = false;
                const response = xhr.responseJSON || {};
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

    $('#marketplaceCollapse').on('show.bs.collapse', function () {
    $('#marketplaceArrow')
        .removeClass('fa-chevron-down')
        .addClass('fa-chevron-up');
    });

    $('#marketplaceCollapse').on('hide.bs.collapse', function () {
        $('#marketplaceArrow')
            .removeClass('fa-chevron-up')
            .addClass('fa-chevron-down');
    });

    $('.install-marketplace-app').on('click', function() {
        const $btn = $(this);
        const downloadUrl = $btn.data('download-url');
        const name = $btn.data('name');

        $btn.prop('disabled', true).text('Installing...');

        $.ajax({
            url: '/user/external-apps/install-from-marketplace',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                download_url: downloadUrl,
                name: name
            },
            success: function(response) {
                if (response.success) {
                    showAlert(response.message || 'Module installed successfully.', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert(response.message || 'Installation failed.', 'error');
                    $btn.prop('disabled', false).text('Install');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON || {};
                showAlert(response.message || 'An error occurred during installation.', 'error');
                $btn.prop('disabled', false).text('Install');
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

    // $('.card-body').prepend(alertHtml);
    $('#externalAppsAlerts').prepend(alertHtml);

    setTimeout(() => {
        $('.card-body .alert').fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}
</script>
@endpush
