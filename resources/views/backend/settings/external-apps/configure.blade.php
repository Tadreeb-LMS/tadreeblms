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

                    <div class="alert alert-info mb-4">
                        <h5><i class="fas fa-info-circle mr-2"></i>Module Information</h5>
                        <table class="table table-sm mb-0">
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
                                <td><strong>Installed:</strong></td>
                                <td>{{ $app->installed_at ? $app->installed_at->format('M d, Y H:i') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>

                    <form action="{{ route('admin.external-apps.update-config', $app->slug) }}" method="POST">
                        @csrf

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
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                No configuration options available for this module.
                            </div>
                        @endif

                        <div class="form-group mt-4">
                            <a href="{{ route('admin.external-apps.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i>Back
                            </a>
                            @if ($app->configuration && count($app->configuration) > 0)
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i>Save Configuration
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
