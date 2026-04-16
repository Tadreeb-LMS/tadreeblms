@php
    $currentSortBy = request()->input('sort_by', []);
    $currentSortDir = request()->input('sort_dir', []);

    if (!is_array($currentSortBy)) {
        $currentSortBy = [$currentSortBy];
    }

    if (!is_array($currentSortDir)) {
        $currentSortDir = [$currentSortDir];
    }

    $sortMetaFor = function ($column) use ($currentSortBy, $currentSortDir) {
        $index = array_search($column, $currentSortBy, true);
        if ($index === false) {
            return [
                'icon' => '<i class="fa fa-sort text-muted ml-1" aria-hidden="true"></i>',
                'priority' => null,
                'next_dir' => 'asc',
            ];
        }

        $dir = strtolower($currentSortDir[$index] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return [
            'icon' => $dir === 'asc'
                ? '<i class="fa fa-sort-amount-up ml-1" aria-hidden="true"></i>'
                : '<i class="fa fa-sort-amount-down ml-1" aria-hidden="true"></i>',
            'priority' => $index + 1,
            'next_dir' => $dir === 'asc' ? 'desc' : 'asc',
        ];
    };

    $typeSort = $sortMetaFor('type');
    $weightSort = $sortMetaFor('weight');
    $statusSort = $sortMetaFor('is_active');
    $currentValueSort = $sortMetaFor('current_value');
    $weightedScoreSort = $sortMetaFor('weighted_score');
@endphp

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Code</th>
                <th>
                    <button type="button" class="btn btn-link btn-sm p-0 text-dark js-kpi-sort" data-sort-column="type">
                        Type {!! $typeSort['icon'] !!}
                    </button>
                    <i class="fa fa-question-circle text-muted ml-1" title="KPI category that maps to a predefined system calculation logic."></i>
                </th>
                <th>
                    <button type="button" class="btn btn-link btn-sm p-0 text-dark js-kpi-sort" data-sort-column="weight">
                        Weight {!! $weightSort['icon'] !!}
                    </button>
                    <i class="fa fa-question-circle text-muted ml-1" title="Relative importance of this KPI in weighted scoring."></i>
                </th>
                <th>
                    <button type="button" class="btn btn-link btn-sm p-0 text-dark js-kpi-sort" data-sort-column="is_active">
                        Status {!! $statusSort['icon'] !!}
                    </button>
                    <i class="fa fa-question-circle text-muted ml-1" title="Active KPIs are included in calculations; inactive KPIs are excluded."></i>
                </th>
                <th>
                    <button type="button" class="btn btn-link btn-sm p-0 text-dark js-kpi-sort" data-sort-column="current_value">
                        Current Value {!! $currentValueSort['icon'] !!}
                    </button>
                    <i class="fa fa-question-circle text-muted ml-1" title="Latest computed KPI value produced by the mapped KPI type logic."></i>
                </th>
                <th>
                    <button type="button" class="btn btn-link btn-sm p-0 text-dark js-kpi-sort" data-sort-column="weighted_score">
                        Weighted Score {!! $weightedScoreSort['icon'] !!}
                    </button>
                    <i class="fa fa-question-circle text-muted ml-1" title="KPI contribution after applying its weight relative to total active weight."></i>
                </th>
                <th>Updated</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kpis as $kpi)
                <tr>
                    <td>{{ $kpi->id }}</td>
                    <td>{{ $kpi->name }}</td>
                    <td><code>{{ $kpi->code }}</code></td>
                    <td title="{{ $kpiTypes[$kpi->type]['description'] ?? '' }}">{{ $kpiTypes[$kpi->type]['label'] ?? ucfirst($kpi->type) }}</td>
                    <td>{{ number_format((float) $kpi->weight, 2) }}</td>
                    <td>
                        @if($kpi->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @if($kpi->calculation['excluded'])
                            <span class="text-muted">Excluded</span>
                        @else
                            {{ number_format((float) $kpi->calculation['value'], 2) }}
                        @endif
                    </td>
                    <td>
                        @if($kpi->calculation['excluded'])
                            <span class="text-muted">Excluded</span>
                        @else
                            {{ number_format((float) $kpi->calculation['weighted_score'], 2) }}
                        @endif
                    </td>
                    <td>{{ optional($kpi->updated_at)->diffForHumans() }}</td>
                    <td class="text-center">
                        <a href="{{ route('admin.kpis.edit', $kpi->id) }}" class="btn btn-sm btn-info">Edit</a>

                        <form method="POST" action="{{ route('admin.kpis.toggle-status', $kpi->id) }}" class="d-inline-block">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning">
                                {{ $kpi->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.kpis.destroy', $kpi->id) }}" class="d-inline-block" onsubmit="return confirm('Archive this KPI? It will be soft-deleted and historical records stay intact.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Archive</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">No KPIs found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $kpis->links() }}
