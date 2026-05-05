@extends('backend.layouts.app')

@section('title', $template->name . ' ' . __('kpi.sidebar.templates') . ' | ' . app_name())

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <h4 class="mb-0">{{ $template->name }}</h4>
        <a href="{{ route('admin.kpi-templates.index') }}" class="btn btn-secondary">&larr; @lang('kpi.actions.back_to_templates')</a>
    </div>

    <!-- Header Info -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong>@lang('kpi.labels.category'):</strong> {{ ucfirst(str_replace('_', ' ', $template->category)) }}</p>
                    <p class="mb-0"><strong>@lang('kpi.labels.description'):</strong></p>
                    <p class="text-muted">{{ $template->description ?: __('kpi.messages.no_description') }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>@lang('kpi.labels.use_case'):</strong></p>
                    <p class="text-muted">{{ $template->use_case ?: __('kpi.messages.general_configuration') }}</p>
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
                    <small class="text-muted">@lang('kpi.labels.total_kpis')</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($stats['total_weight'], 2) }}</h3>
                    <small class="text-muted">@lang('kpi.labels.total_weight')</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($stats['average_weight'], 2) }}</h3>
                    <small class="text-muted">@lang('kpi.labels.avg_weight')</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ count($stats['types']) }}</h3>
                    <small class="text-muted">@lang('kpi.labels.kpi_types')</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Validation Messages -->
    @if(!$validation['valid'])
        <div class="alert alert-danger">
            <strong>@lang('kpi.messages.validation_issues')</strong>
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
            <strong>{{ __('kpi.messages.existing_kpis_found', ['count' => $preview['conflicts_count']]) }}</strong>
            <p class="mb-0 mt-2 small">@lang('kpi.messages.existing_kpis_skipped_help')</p>
        </div>
    @endif

    <div class="alert alert-info small">
        <strong>@lang('kpi.messages.what_happens_on_apply'):</strong> @lang('kpi.help.apply_explanation')
    </div>

    <!-- New KPIs Preview -->
    <div class="card mb-4">
        <div class="card-header">
            <strong>{{ __('kpi.messages.kpis_to_create', ['count' => count($preview['items'])]) }}</strong>
        </div>
        <div class="card-body p-0">
            @if(count($preview['items']) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>@lang('kpi.labels.code')</th>
                                <th>@lang('kpi.labels.name')</th>
                                <th>@lang('kpi.labels.type')</th>
                                <th>@lang('kpi.labels.weight')</th>
                                <th>@lang('kpi.labels.active')</th>
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
                                            <span class="badge badge-success">@lang('kpi.states.yes')</span>
                                        @else
                                            <span class="badge badge-secondary">@lang('kpi.states.no')</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-3 text-muted text-center">@lang('kpi.messages.no_new_kpis')</div>
            @endif
        </div>
    </div>

    <!-- Conflicts Preview -->
    @if($preview['conflicts_count'] > 0)
        <div class="card mb-4">
            <div class="card-header">
                <strong>{{ __('kpi.messages.existing_kpis_skipped', ['count' => $preview['conflicts_count']]) }}</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>@lang('kpi.labels.code')</th>
                                <th>@lang('kpi.labels.template_name')</th>
                                <th>@lang('kpi.labels.existing_name')</th>
                                <th>@lang('kpi.labels.template_weight')</th>
                                <th>@lang('kpi.labels.existing_weight')</th>
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
                    <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm(@json(__('kpi.confirm.apply_template', ['count' => count($preview['items'])])));">
                        @lang('kpi.actions.apply_template')
                    </button>
                </form>
                <a href="{{ route('admin.kpi-templates.index') }}" class="btn btn-secondary btn-lg">
                    @lang('kpi.actions.cancel')
                </a>
            @else
                @if(!$canApply)
                    <div class="alert alert-info mb-0">
                        @lang('kpi.messages.cannot_apply_permission')
                    </div>
                @elseif(count($preview['items']) === 0)
                    <div class="alert alert-info mb-0">
                        @lang('kpi.messages.all_kpis_exist')
                    </div>
                @else
                    <div class="alert alert-danger mb-0">
                        @lang('kpi.messages.template_invalid')
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
