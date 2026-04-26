@if($kpiCategoryGroups->isEmpty())
    <div class="alert alert-light border mb-0">
        No KPI category groups match the current filters.
    </div>
@else
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">KPI Category Groups</h6>
                <small class="text-muted">Flat grouping for reporting and organization</small>
            </div>

            <div class="row">
                @foreach($kpiCategoryGroups as $group)
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="border rounded p-3 h-100 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>{{ $group['name'] }}</strong>
                                <span class="badge badge-info">{{ $group['kpi_count'] }} KPI(s)</span>
                            </div>
                            <div class="small text-muted mb-2">Active KPIs: {{ $group['active_count'] }}</div>
                            <div class="small">
                                Average current value:
                                <strong>
                                    {{ $group['average_current_value'] === null ? 'N/A' : number_format((float) $group['average_current_value'], 2) }}
                                </strong>
                            </div>
                            <div class="small">
                                Average weighted score:
                                <strong>
                                    {{ $group['average_weighted_score'] === null ? 'N/A' : number_format((float) $group['average_weighted_score'], 2) }}
                                </strong>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
