@extends('backend.layouts.app')

@section('title', 'KPI Role Configurations | ' . app_name())

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <h4 class="mb-0">KPI Role Configurations</h4>
        <a href="{{ route('admin.kpis.index') }}" class="btn btn-secondary">← Back to KPIs</a>
    </div>

    @if(!$canManage)
        <div class="alert alert-secondary">
            Read-only mode: only authorized users can save or remove role KPI overrides.
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <strong>About Role Configurations</strong>
        </div>
        <div class="card-body text-muted small">
            Override a KPI's <strong>weight</strong> or <strong>active status</strong> per role.
            Leave a field blank to inherit the KPI's global default.
            Removing an override (trash icon) restores the global default for that role.
        </div>
    </div>

    @foreach($roles as $role)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>{{ ucfirst($role->name) }}</strong>
                <span class="text-muted small">Role ID: {{ $role->id }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>KPI</th>
                            <th>Type</th>
                            <th>Global Weight</th>
                            <th>Global Active</th>
                            <th style="min-width:130px">Override Weight</th>
                            <th style="min-width:130px">Override Active</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kpis as $kpi)
                            @php
                                /** @var \App\Models\KpiRoleConfig|null $override */
                                $override = $overrides->get($role->id)?->get($kpi->id) ?? null;
                            @endphp
                            <tr>
                                <td>{{ $kpi->name }}<br><small class="text-muted">{{ $kpi->code }}</small></td>
                                <td>{{ $kpi->type_label }}</td>
                                <td>{{ $kpi->weight }}</td>
                                <td>
                                    @if($kpi->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if($canManage)
                                        <form method="POST" action="{{ route('admin.kpi-role-configs.store') }}">
                                            @csrf
                                            <input type="hidden" name="role_id" value="{{ $role->id }}">
                                            <input type="hidden" name="kpi_id" value="{{ $kpi->id }}">
                                            <input
                                                type="number"
                                                name="weight_override"
                                                class="form-control form-control-sm"
                                                min="0" max="100" step="0.01"
                                                placeholder="(default: {{ $kpi->weight }})"
                                                value="{{ $override?->weight_override }}"
                                            >
                                    @else
                                        {{ $override?->weight_override !== null ? number_format((float) $override->weight_override, 2) : 'Inherit default' }}
                                    @endif
                                </td>
                                <td>
                                    @if($canManage)
                                        <select name="is_active_override" class="form-control form-control-sm">
                                            <option value="" @if($override === null || $override->is_active_override === null) selected @endif>
                                                — inherit default —
                                            </option>
                                            <option value="1" @if($override !== null && $override->is_active_override === true) selected @endif>
                                                Active
                                            </option>
                                            <option value="0" @if($override !== null && $override->is_active_override === false) selected @endif>
                                                Inactive
                                            </option>
                                        </select>
                                    @else
                                        @if($override === null || $override->is_active_override === null)
                                            Inherit default
                                        @elseif($override->is_active_override)
                                            Active
                                        @else
                                            Inactive
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if($canManage)
                                        <button type="submit" class="btn btn-sm btn-primary" title="Save override">
                                            Save
                                        </button>
                                        </form>

                                        @if($override)
                                            <form method="POST"
                                                  action="{{ route('admin.kpi-role-configs.destroy', $override->id) }}"
                                                  style="display:inline-block;"
                                                  onsubmit="return confirm('Remove this override and revert to global default?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove override">
                                                    ✕
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="text-muted small">Read-only</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    @if($roles->isEmpty())
        <div class="alert alert-warning">No roles found. Create roles first.</div>
    @endif

    @if($kpis->isEmpty())
        <div class="alert alert-warning">No active KPIs found. Activate at least one KPI first.</div>
    @endif
@endsection