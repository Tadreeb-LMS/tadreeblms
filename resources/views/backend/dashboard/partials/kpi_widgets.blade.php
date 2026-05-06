@php
    $presentationClassMap = [
        'compact' => 'col-lg-3 col-md-6',
        'detail' => 'col-lg-6 col-md-12',
    ];
@endphp

<div class="row">
    <div class="col-12 mb-2 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">@lang('kpi.titles.dashboard')</h5>
            <p class="text-muted mb-0">@lang('kpi.help.dashboard_controlled')</p>
        </div>

        @if(auth()->user()->hasRole('administrator') && auth()->user()->can('kpi_edit'))
            <a href="{{ route('admin.kpis.dashboard-settings.edit') }}" class="btn btn-outline-primary btn-sm">@lang('kpi.actions.configure_cards')</a>
        @endif
    </div>

    @foreach($dashboardKpiWidgets as $kpi)
        @php
            $presentation = $kpi->dashboard_presentation ?? 'compact';
            $cardColumnClass = $presentationClassMap[$presentation] ?? $presentationClassMap['compact'];
            $value = $kpi->calculation['value'] ?? null;
            $weightedScore = $kpi->calculation['weighted_score'] ?? null;
            $target = $kpi->calculation['target'] ?? null;
            $deviationDirection = $kpi->calculation['deviation_direction'] ?? null;
            $targetBadgeClass = $deviationDirection === 'over'
                ? 'badge-success'
                : ($deviationDirection === 'under' ? 'badge-warning' : 'badge-secondary');
            $categoryNames = $kpi->categories->pluck('name')->filter()->take(3)->implode(', ');
        @endphp

        <div class="{{ $cardColumnClass }} mb-3">
            <div class="card h-100 kpi-dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="text-muted small text-uppercase">{{ $kpi->code }}</div>
                            <h5 class="mb-1">{{ $kpi->name }}</h5>
                            <div class="text-muted small">{{ $kpi->type_label }}</div>
                        </div>
                        <span class="badge badge-light">@lang('kpi.labels.weight') {{ number_format((float) $kpi->weight, 2) }}</span>
                    </div>

                    <div class="kpi-dashboard-value mb-2">
                        @if($value === null || ($kpi->calculation['excluded'] ?? false))
                            <span class="text-muted">@lang('kpi.states.no_data')</span>
                        @else
                            {{ number_format((float) $value, 2) }}
                        @endif
                    </div>

                    @if($presentation === 'detail')
                        <div class="row text-muted small">
                            <div class="col-sm-6 mb-2">
                                <strong class="d-block text-dark">@lang('kpi.labels.weighted_score')</strong>
                                {{ $weightedScore === null ? __('kpi.states.not_applicable') : number_format((float) $weightedScore, 2) }}
                            </div>
                            <div class="col-sm-6 mb-2">
                                <strong class="d-block text-dark">@lang('kpi.labels.target')</strong>
                                {{ $target === null ? __('kpi.states.no_target') : number_format((float) $target, 2) }}
                            </div>
                            <div class="col-sm-6 mb-2">
                                <strong class="d-block text-dark">@lang('kpi.labels.target_status')</strong>
                                <span class="badge {{ $targetBadgeClass }}">
                                    {{ $deviationDirection ? __('kpi.states.' . $deviationDirection) : __('kpi.states.not_set') }}
                                </span>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <strong class="d-block text-dark">@lang('kpi.labels.categories')</strong>
                                {{ $categoryNames !== '' ? $categoryNames : __('kpi.states.all_unmapped') }}
                            </div>
                        </div>
                    @else
                        <div class="d-flex justify-content-between text-muted small">
                            <span>@lang('kpi.labels.weighted_score'): {{ $weightedScore === null ? __('kpi.states.not_applicable') : number_format((float) $weightedScore, 2) }}</span>
                            <span>
                                @if($target === null)
                                    @lang('kpi.states.no_target')
                                @else
                                    @lang('kpi.labels.target') {{ number_format((float) $target, 2) }}
                                @endif
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
