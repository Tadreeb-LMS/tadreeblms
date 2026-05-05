@if($kpiCategoryGroups->isEmpty())
    <div class="alert alert-light border mb-0">
        @lang('kpi.messages.no_category_groups')
    </div>
@else
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">@lang('kpi.titles.category_groups')</h6>
                <small class="text-muted">@lang('kpi.help.category_groups')</small>
            </div>

            <div class="row">
                @foreach($kpiCategoryGroups as $group)
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="border rounded p-3 h-100 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>{{ $group['name'] }}</strong>
                                <span class="badge badge-info">{{ __('kpi.messages.kpi_count', ['count' => $group['kpi_count']]) }}</span>
                            </div>
                            <div class="small text-muted mb-2">{{ __('kpi.messages.active_kpis_count', ['count' => $group['active_count']]) }}</div>
                            <div class="small">
                                @lang('kpi.messages.average_current_value')
                                <strong>
                                    {{ $group['average_current_value'] === null ? __('kpi.states.not_applicable') : number_format((float) $group['average_current_value'], 2) }}
                                </strong>
                            </div>
                            <div class="small">
                                @lang('kpi.messages.average_weighted_score')
                                <strong>
                                    {{ $group['average_weighted_score'] === null ? __('kpi.states.not_applicable') : number_format((float) $group['average_weighted_score'], 2) }}
                                </strong>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
