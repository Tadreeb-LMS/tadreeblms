@extends('backend.layouts.app')

@section('title', 'Team KPI Insights | ' . app_name())

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <h4 class="mb-0">Team KPI Insights</h4>
        <a href="{{ route('admin.kpis.index') }}" class="btn btn-outline-secondary">Back to KPI Management</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.kpis.team-insights') }}">
                <div class="form-row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label for="team_id">Team</label>
                        <select id="team_id" name="team_id" class="form-control" required>
                            <option value="">Select team</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ (int) $selectedTeamId === (int) $team->id ? 'selected' : '' }}>
                                    {{ $team->title ?: ('Team #' . $team->id) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-2">
                        <label for="date_from">From</label>
                        <input type="date" id="date_from" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>

                    <div class="col-md-3 mb-2">
                        <label for="date_to">To</label>
                        <input type="date" id="date_to" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>

                    <div class="col-md-2 mb-2">
                        <button type="submit" class="btn btn-primary btn-block">Apply</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($teams->isEmpty())
        <div class="alert alert-warning">
            No teams are available for your account.
        </div>
    @else
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card border-left-primary h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase">Team Members</small>
                        <div class="h4 mb-0">{{ $insights['team_member_count'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-success h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase">Evaluated Members</small>
                        <div class="h4 mb-0">{{ $insights['evaluated_member_count'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-left-info h-100">
                    <div class="card-body">
                        <small class="text-muted text-uppercase">Team Average Score</small>
                        <div class="h4 mb-0">
                            @if($insights['team_score_average'] === null)
                                <span class="text-muted">N/A</span>
                            @else
                                {{ number_format((float) $insights['team_score_average'], 2) }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Team-Level KPI Metrics</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>KPI</th>
                                <th>Type</th>
                                <th>Weight</th>
                                <th>Team Average</th>
                                <th>Members Evaluated</th>
                                <th>Top Performer</th>
                                <th>Bottom Performer</th>
                                <th>Spread</th>
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
                                            <span class="text-muted">N/A</span>
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
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($summary['bottom_performer'])
                                            {{ $summary['bottom_performer']['name'] }}
                                            <div class="text-danger small">{{ number_format((float) $summary['bottom_performer']['value'], 2) }}</div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($summary['spread'] === null)
                                            <span class="text-muted">N/A</span>
                                        @else
                                            {{ number_format((float) $summary['spread'], 2) }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No active KPI data found for the selected filters.</td>
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
                    <div class="card-header">Top Performers</div>
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
                            <p class="text-muted mb-0">No performer ranking available for the selected filters.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">Bottom Performers</div>
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
                            <p class="text-muted mb-0">No performer ranking available for the selected filters.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
