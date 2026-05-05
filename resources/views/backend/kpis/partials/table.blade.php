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
                <th>@lang('kpi.labels.id')</th>
                <th>@lang('kpi.labels.name')</th>
                <th>@lang('kpi.labels.code')</th>
                <th>
                    <button type="button" class="btn btn-link btn-sm p-0 text-dark js-kpi-sort" data-sort-column="type">
                        @lang('kpi.labels.type') {!! $typeSort['icon'] !!}
                    </button>
                    <i class="fa fa-question-circle text-muted ml-1" title="{{ __('kpi.tooltips.type') }}"></i>
                </th>
                <th>
                    <button type="button" class="btn btn-link btn-sm p-0 text-dark js-kpi-sort" data-sort-column="weight">
                        @lang('kpi.labels.weight') {!! $weightSort['icon'] !!}
                    </button>
                    <i class="fa fa-question-circle text-muted ml-1" title="{{ __('kpi.tooltips.weight') }}"></i>
                </th>
                <th>
                    <button type="button" class="btn btn-link btn-sm p-0 text-dark js-kpi-sort" data-sort-column="is_active">
                        @lang('kpi.labels.status') {!! $statusSort['icon'] !!}
                    </button>
                    <i class="fa fa-question-circle text-muted ml-1" title="{{ __('kpi.tooltips.status') }}"></i>
                </th>
                <th>
                    <button type="button" class="btn btn-link btn-sm p-0 text-dark js-kpi-sort" data-sort-column="current_value">
                        @lang('kpi.labels.current_value') {!! $currentValueSort['icon'] !!}
                    </button>
                    <i class="fa fa-question-circle text-muted ml-1" title="{{ __('kpi.tooltips.current_value') }}"></i>
                </th>
                <th>
                    <button type="button" class="btn btn-link btn-sm p-0 text-dark js-kpi-sort" data-sort-column="weighted_score">
                        @lang('kpi.labels.weighted_score') {!! $weightedScoreSort['icon'] !!}
                    </button>
                    <i class="fa fa-question-circle text-muted ml-1" title="{{ __('kpi.tooltips.weighted_score') }}"></i>
                </th>
                <th>@lang('kpi.labels.mapped_categories')</th>
                <th>@lang('kpi.labels.target')</th>
                <th>@lang('kpi.labels.deviation')</th>
                <th>@lang('kpi.labels.updated')</th>
                <th class="text-center">@lang('kpi.labels.actions')</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kpis as $kpi)
                <tr>
                    <td>{{ $kpi->id }}</td>
                    <td>{{ $kpi->name }}</td>
                    <td><code>{{ $kpi->code }}</code></td>
                    <td title="{{ $kpiTypes[$kpi->type]['description'] ?? '' }}">
                        {{ $kpiTypes[$kpi->type]['label'] ?? ucfirst($kpi->type) }}
                    </td>
                    <td>{{ number_format((float) $kpi->weight, 2) }}</td>
                    <td>
                        @if($kpi->is_active)
                            <span class="badge badge-success">@lang('kpi.states.active')</span>
                        @else
                            <span class="badge badge-secondary">@lang('kpi.states.inactive')</span>
                        @endif
                    </td>
                    <td>
                        @if($kpi->calculation['excluded'])
                            <span class="text-muted">@lang('kpi.states.excluded')</span>
                        @else
                            {{ number_format((float) $kpi->calculation['value'], 2) }}
                        @endif
                    </td>
                    <td>
                        @if($kpi->calculation['excluded'])
                            <span class="text-muted">@lang('kpi.states.excluded')</span>
                        @else
                            {{ number_format((float) $kpi->calculation['weighted_score'], 2) }}
                        @endif
                    </td>
                    <td>
                        @forelse($kpi->categories as $category)
                            <span class="badge badge-light border mr-1 mb-1">{{ $category->name }}</span>
                        @empty
                            <span class="text-muted">@lang('kpi.states.uncategorized')</span>
                        @endforelse
                    </td>
                    <td>
                        @if(($kpi->calculation['target'] ?? null) === null)
                            <span class="text-muted">@lang('kpi.states.not_set')</span>
                        @else
                            @php
                                $targetScopeKey = 'kpi.states.scope_' . ($kpi->calculation['target_scope'] ?? 'global');
                                $targetScopeLabel = __($targetScopeKey);
                            @endphp
                            {{ number_format((float) $kpi->calculation['target'], 2) }}
                            <br>
                            <small class="text-muted">{{ $targetScopeLabel === $targetScopeKey ? ucfirst(str_replace('_', ' ', (string) ($kpi->calculation['target_scope'] ?? 'global'))) : $targetScopeLabel }}</small>
                        @endif
                    </td>
                    <td>
                        @if(($kpi->calculation['deviation_direction'] ?? null) === null)
                            <span class="text-muted">@lang('kpi.states.not_applicable')</span>
                        @else
                            @if($kpi->calculation['deviation_direction'] === 'on_target')
                                <span class="badge badge-success">@lang('kpi.states.on_target')</span>
                            @elseif($kpi->calculation['deviation_direction'] === 'over')
                                <span class="badge badge-info">@lang('kpi.states.over')</span>
                            @else
                                <span class="badge badge-warning">@lang('kpi.states.under')</span>
                            @endif
                            <br>
                            <small>
                                {{ number_format((float) ($kpi->calculation['deviation_value'] ?? 0), 2) }}
                                @if(($kpi->calculation['deviation_percentage'] ?? null) !== null)
                                    ({{ number_format((float) $kpi->calculation['deviation_percentage'], 2) }}%)
                                @endif
                            </small>
                        @endif
                    </td>
                    <td>{{ optional($kpi->updated_at)->diffForHumans() }}</td>
                    <td class="text-center">
                        @can('kpi_edit')
                            <a href="{{ route('admin.kpis.edit', $kpi->id) }}" class="btn btn-sm btn-info">@lang('kpi.actions.edit')</a>

                            <form method="POST" action="{{ route('admin.kpis.toggle-status', $kpi->id) }}" class="d-inline-block">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning">
                                    {{ $kpi->is_active ? __('kpi.actions.deactivate') : __('kpi.actions.activate') }}
                                </button>
                            </form>
                        @endcan

                        @can('kpi_delete')
                            <form method="POST" action="{{ route('admin.kpis.destroy', $kpi->id) }}" class="d-inline-block" onsubmit="return confirm(@json(__('kpi.confirm.archive')));">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">@lang('kpi.actions.archive')</button>
                            </form>
                        @endcan

                        @cannot('kpi_edit')
                            <span class="text-muted small">@lang('kpi.states.read_only')</span>
                        @endcannot
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center">@lang('kpi.messages.no_kpis_found')</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $kpis->links() }}
