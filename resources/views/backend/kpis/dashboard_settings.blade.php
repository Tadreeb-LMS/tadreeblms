@extends('backend.layouts.app')

@section('title', __('kpi.titles.dashboard_settings') . ' | ' . app_name())

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <div>
            <h4 class="mb-1">@lang('kpi.titles.dashboard_settings')</h4>
            <p class="text-muted mb-0">@lang('kpi.help.dashboard_settings')</p>
        </div>

        <a href="{{ route('admin.kpis.index') }}" class="btn btn-secondary">@lang('kpi.actions.back_to_kpis')</a>
    </div>

    <div class="alert alert-info">
        @lang('kpi.help.dashboard_default')
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.kpis.dashboard-settings.update') }}">
                @csrf

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th style="width: 90px;">@lang('kpi.labels.visible')</th>
                                <th>@lang('kpi.labels.kpi')</th>
                                <th style="width: 140px;">@lang('kpi.labels.code')</th>
                                <th style="width: 140px;">@lang('kpi.labels.weight')</th>
                                <th style="width: 180px;">@lang('kpi.labels.presentation')</th>
                                <th style="width: 160px;">@lang('kpi.labels.display_order')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($active_kpis as $kpi)
                                @php
                                    $widget = $widget_map->get($kpi->id);
                                    $isVisible = in_array($kpi->id, $selected_ids, true);
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <input
                                            type="checkbox"
                                            name="visible_kpis[]"
                                            value="{{ $kpi->id }}"
                                            {{ $isVisible ? 'checked' : '' }}
                                        >
                                    </td>
                                    <td>
                                        <strong>{{ $kpi->name }}</strong>
                                        <div class="text-muted small">{{ $kpi->type_label }}</div>
                                    </td>
                                    <td>{{ $kpi->code }}</td>
                                    <td>{{ number_format((float) $kpi->weight, 2) }}</td>
                                    <td>
                                        <select name="presentation[{{ $kpi->id }}]" class="form-control">
                                            <option value="compact" {{ ($widget['presentation'] ?? 'compact') === 'compact' ? 'selected' : '' }}>@lang('kpi.states.compact_card')</option>
                                            <option value="detail" {{ ($widget['presentation'] ?? '') === 'detail' ? 'selected' : '' }}>@lang('kpi.states.detailed_card')</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            min="1"
                                            max="99"
                                            name="display_order[{{ $kpi->id }}]"
                                            value="{{ old('display_order.' . $kpi->id, $widget['display_order'] ?? 99) }}"
                                            class="form-control"
                                        >
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">@lang('kpi.messages.no_active_dashboard_kpis')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger mt-3 mb-0">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <small class="text-muted">@lang('kpi.help.dashboard_cards')</small>
                    <button type="submit" class="btn btn-primary">@lang('kpi.actions.save_dashboard_settings')</button>
                </div>
            </form>
        </div>
    </div>
@endsection
