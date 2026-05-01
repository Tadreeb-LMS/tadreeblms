@extends('backend.layouts.app')

@section('title', $template->name . ' Template | ' . app_name())

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <h4 class="mb-0">{{ $template->name }}</h4>
        <a href="{{ route('admin.kpi-templates.index') }}" class="btn btn-secondary">&larr; Back to Templates</a>
    </div>

    <!-- Header Info -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Category:</strong> {{ ucfirst(str_replace('_', ' ', $template->category)) }}</p>
                    <p class="mb-0"><strong>Description:</strong></p>
                    <p class="text-muted">{{ $template->description ?: 'No description provided.' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>Use Case:</strong></p>
                    <p class="text-muted">{{ $template->use_case ?: 'General KPI configuration.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['total_items'] }}</h3>
                    <small class="text-muted">Total KPIs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($stats['total_weight'], 2) }}</h3>
                    <small class="text-muted">Total Weight</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($stats['average_weight'], 2) }}</h3>
                    <small class="text-muted">Avg Weight</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ count($stats['types']) }}</h3>
                    <small class="text-muted">KPI Types</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Validation Messages -->
    @if(!$validation['valid'])
        <div class="alert alert-danger">
            <strong>Template Validation Issues:</strong>
            <ul class="mb-0 mt-2">
                @foreach($validation['errors'] as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Conflicts Warning -->
    @if($preview['conflicts_count'] > 0)
        <div class="alert alert-warning">
            <strong>⚠️ {{ $preview['conflicts_count'] }} Existing KPI(s) Found</strong>
            <p class="mb-0 mt-2 small">The following KPI codes already exist. They will be skipped during application to prevent overwriting your data.</p>
        </div>
    @endif

    <div class="alert alert-info small">
        <strong>What happens on apply:</strong> this is the step that creates live KPI records in the KPI table. If you leave this page without applying, no live KPI is created.
    </div>

    <!-- New KPIs Preview -->
    <div class="card mb-4">
        <div class="card-header">
            <strong>KPIs to be Created ({{ count($preview['items']) }})</strong>
        </div>
        <div class="card-body p-0">
            @if(count($preview['items']) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Weight</th>
                                <th>Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($preview['items'] as $item)
                                <tr>
                                    <td><code>{{ $item['code'] }}</code></td>
                                    <td>{{ $item['name'] }}</td>
                                    <td><span class="badge badge-info">{{ $item['type'] }}</span></td>
                                    <td>{{ number_format($item['weight'], 2) }}</td>
                                    <td>
                                        @if($item['is_active'])
                                            <span class="badge badge-success">Yes</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-3 text-muted text-center">No new KPIs to create (all codes may already exist).</div>
            @endif
        </div>
    </div>

    <!-- Conflicts Preview -->
    @if($preview['conflicts_count'] > 0)
        <div class="card mb-4">
            <div class="card-header">
                <strong>Existing KPIs ({{ $preview['conflicts_count'] }}) — Will be Skipped</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Code</th>
                                <th>Template Name</th>
                                <th>Existing Name</th>
                                <th>Template Weight</th>
                                <th>Existing Weight</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($preview['conflicts'] as $item)
                                <tr>
                                    <td><code>{{ $item['code'] }}</code></td>
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $item['conflict_with']['name'] }}</td>
                                    <td>{{ number_format($item['weight'], 2) }}</td>
                                    <td>{{ number_format($item['conflict_with']['weight'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="card">
        <div class="card-body">
            @if($canApply && count($preview['items']) > 0 && $validation['valid'])
                <form method="POST" action="{{ route('admin.kpi-templates.apply', $template->id) }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="skip_existing" value="1">
                    <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Are you sure? This will create {{ count($preview['items']) }} new KPI(s). Existing KPIs will not be modified.');">
                        ✓ Apply This Template
                    </button>
                </form>
                <a href="{{ route('admin.kpi-templates.index') }}" class="btn btn-secondary btn-lg">
                    ✕ Cancel
                </a>
            @else
                @if(!$canApply)
                    <div class="alert alert-info mb-0">
                        You don't have permission to apply templates. Contact your administrator.
                    </div>
                @elseif(count($preview['items']) === 0)
                    <div class="alert alert-info mb-0">
                        All KPIs in this template already exist. Nothing to create.
                    </div>
                @else
                    <div class="alert alert-danger mb-0">
                        This template has validation issues and cannot be applied.
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
