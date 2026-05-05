@extends('backend.layouts.app')

@section('title', __('kpi.titles.team_insights') . ' | ' . app_name())

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <h4 class="mb-0">@lang('kpi.titles.team_insights')</h4>
        <a href="{{ route('admin.kpis.index') }}" class="btn btn-outline-secondary">@lang('kpi.actions.back_to_management')</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.kpis.team-insights') }}">
                <div class="form-row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label for="team_id">@lang('kpi.labels.team')</label>
                        <select id="team_id" name="team_id" class="form-control" required>
                            <option value="">@lang('kpi.placeholders.select_team')</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ (int) $selectedTeamId === (int) $team->id ? 'selected' : '' }}>
                                    {{ $team->title ?: __('kpi.labels.team_number', ['id' => $team->id]) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-2">
                        <label for="date_from">@lang('kpi.labels.from')</label>
                        <input type="date" id="date_from" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>

                    <div class="col-md-3 mb-2">
                        <label for="date_to">@lang('kpi.labels.to')</label>
                        <input type="date" id="date_to" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>

                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-primary btn-block">@lang('kpi.actions.apply')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($teams->isEmpty())
        <div class="alert alert-warning">
            @lang('kpi.messages.no_teams')
        </div>
    @else
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card border-left-primary h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase">@lang('kpi.labels.team_members')</small>
                        <div class="h4 mb-0">{{ $insights['team_member_count'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-success h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase">@lang('kpi.labels.evaluated_members')</small>
                        <div class="h4 mb-0">{{ $insights['evaluated_member_count'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-info h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase">@lang('kpi.labels.team_average_score')</small>
                        <div class="h4 mb-0">
                            @if($insights['team_score_average'] === null)
                                <span class="text-muted">@lang('kpi.states.not_applicable')</span>
                            @else
                                {{ number_format((float) $insights['team_score_average'], 2) }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">@lang('kpi.messages.team_level_metrics')</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>@lang('kpi.labels.kpi')</th>
                                <th>@lang('kpi.labels.type')</th>
                                <th>@lang('kpi.labels.weight')</th>
                                <th>@lang('kpi.labels.team_average')</th>
                                <th>@lang('kpi.labels.members_evaluated')</th>
                                <th>@lang('kpi.labels.top_performer')</th>
                                <th>@lang('kpi.labels.bottom_performer')</th>
                                <th>@lang('kpi.labels.spread')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($insights['kpi_summaries'] as $summary)
                                <tr>
                                    <td>
                                        <strong>{{ $summary['name'] }}</strong>
                                        <div class="text-muted small">{{ $summary['code'] }}</div>
                                    </td>
                                    <td>{{ $summary['type_label'] }}</td>
                                    <td>{{ number_format((float) $summary['weight'], 2) }}</td>
                                    <td>
                                        @if($summary['team_average'] === null)
                                            <span class="text-muted">@lang('kpi.states.not_applicable')</span>
                                        @else
                                            {{ number_format((float) $summary['team_average'], 2) }}
                                        @endif
                                    </td>
                                    <td>{{ $summary['members_evaluated'] }}</td>
                                    <td>
                                        @if($summary['top_performer'])
                                            {{ $summary['top_performer']['name'] }}
                                            <div class="text-success small">{{ number_format((float) $summary['top_performer']['value'], 2) }}</div>
                                        @else
                                            <span class="text-muted">@lang('kpi.states.not_applicable')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($summary['bottom_performer'])
                                            {{ $summary['bottom_performer']['name'] }}
                                            <div class="text-danger small">{{ number_format((float) $summary['bottom_performer']['value'], 2) }}</div>
                                        @else
                                            <span class="text-muted">@lang('kpi.states.not_applicable')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($summary['spread'] === null)
                                            <span class="text-muted">@lang('kpi.states.not_applicable')</span>
                                        @else
                                            {{ number_format((float) $summary['spread'], 2) }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">@lang('kpi.messages.no_team_data')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">@lang('kpi.messages.top_performers')</div>
                    <div class="card-body">
                        @if(!empty($insights['top_performers']))
                            <ul class="list-group list-group-flush">
                                @foreach($insights['top_performers'] as $performer)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span>{{ $performer['name'] }}</span>
                                        <span class="badge badge-success">{{ number_format((float) $performer['overall_score'], 2) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-0">@lang('kpi.messages.no_ranking')</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">@lang('kpi.messages.bottom_performers')</div>
                    <div class="card-body">
                        @if(!empty($insights['bottom_performers']))
                            <ul class="list-group list-group-flush">
                                @foreach($insights['bottom_performers'] as $performer)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span>{{ $performer['name'] }}</span>
                                        <span class="badge badge-warning">{{ number_format((float) $performer['overall_score'], 2) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-0">@lang('kpi.messages.no_ranking')</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
