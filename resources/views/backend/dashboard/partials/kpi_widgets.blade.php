@php
    $presentationClassMap = [
        'compact' => 'col-lg-3 col-md-6',
        'detail' => 'col-lg-6 col-md-12',
    ];
@endphp

<div class="row">
    <div class="col-12 mb-2 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">KPI Dashboard</h5>
            <p class="text-muted mb-0">Visible KPI cards are controlled from KPI dashboard settings.</p>
        </div>

        @if(auth()->user()->hasRole('administrator') && auth()->user()->can('kpi_edit'))
            <a href="{{ route('admin.kpis.dashboard-settings.edit') }}" class="btn btn-outline-primary btn-sm">Configure KPI Cards</a>
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
                        <span class="badge badge-light">Weight {{ number_format((float) $kpi->weight, 2) }}</span>
                    </div>

                    <div class="kpi-dashboard-value mb-2">
                        @if($value === null || ($kpi->calculation['excluded'] ?? false))
                            <span class="text-muted">No data</span>
                        @else
                            {{ number_format((float) $value, 2) }}
                        @endif
                    </div>

                    @if($presentation === 'detail')
                        <div class="row text-muted small">
                            <div class="col-sm-6 mb-2">
                                <strong class="d-block text-dark">Weighted Score</strong>
                                {{ $weightedScore === null ? 'N/A' : number_format((float) $weightedScore, 2) }}
                            </div>
                            <div class="col-sm-6 mb-2">
                                <strong class="d-block text-dark">Target</strong>
                                {{ $target === null ? 'No target' : number_format((float) $target, 2) }}
                            </div>
                            <div class="col-sm-6 mb-2">
                                <strong class="d-block text-dark">Target Status</strong>
                                <span class="badge {{ $targetBadgeClass }}">
                                    {{ $deviationDirection ? ucfirst(str_replace('_', ' ', $deviationDirection)) : 'Not set' }}
                                </span>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <strong class="d-block text-dark">Categories</strong>
                                {{ $categoryNames !== '' ? $categoryNames : 'All / unmapped' }}
                            </div>
                        </div>
                    @else
                        <div class="d-flex justify-content-between text-muted small">
                            <span>Weighted: {{ $weightedScore === null ? 'N/A' : number_format((float) $weightedScore, 2) }}</span>
                            <span>
                                @if($target === null)
                                    No target
                                @else
                                    Target {{ number_format((float) $target, 2) }}
                                @endif
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
