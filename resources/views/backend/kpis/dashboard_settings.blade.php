@extends('backend.layouts.app')

@section('title', 'KPI Dashboard Settings | ' . app_name())

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <div>
            <h4 class="mb-1">KPI Dashboard Settings</h4>
            <p class="text-muted mb-0">Choose up to 8 active KPIs and a simple presentation mode for the dashboard.</p>
        </div>

        <a href="{{ route('admin.kpis.index') }}" class="btn btn-secondary">Back to KPIs</a>
    </div>

    <div class="alert alert-info">
        A default dashboard configuration is created automatically from active KPIs if none exists yet.
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.kpis.dashboard-settings.update') }}">
                @csrf

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th style="width: 90px;">Visible</th>
                                <th>KPI</th>
                                <th style="width: 140px;">Code</th>
                                <th style="width: 140px;">Weight</th>
                                <th style="width: 180px;">Presentation</th>
                                <th style="width: 160px;">Display Order</th>
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
                                        <div class="text-muted small">{{ ucfirst($kpi->type) }}</div>
                                    </td>
                                    <td>{{ $kpi->code }}</td>
                                    <td>{{ number_format((float) $kpi->weight, 2) }}</td>
                                    <td>
                                        <select name="presentation[{{ $kpi->id }}]" class="form-control">
                                            <option value="compact" {{ ($widget['presentation'] ?? 'compact') === 'compact' ? 'selected' : '' }}>Compact card</option>
                                            <option value="detail" {{ ($widget['presentation'] ?? '') === 'detail' ? 'selected' : '' }}>Detailed card</option>
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
                                    <td colspan="6" class="text-center text-muted">No active KPIs are available for dashboard display yet.</td>
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
                    <small class="text-muted">Detailed cards show more KPI context. Compact cards keep the dashboard denser.</small>
                    <button type="submit" class="btn btn-primary">Save Dashboard Settings</button>
                </div>
            </form>
        </div>
    </div>
@endsection
