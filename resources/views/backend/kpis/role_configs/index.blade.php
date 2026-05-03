@extends('backend.layouts.app')

@section('title', __('kpi.titles.role_configurations') . ' | ' . app_name())

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <h4 class="mb-0">@lang('kpi.titles.role_configurations')</h4>
        <a href="{{ route('admin.kpis.index') }}" class="btn btn-secondary">&larr; @lang('kpi.actions.back_to_kpis')</a>
    </div>

    @if(!$canManage)
        <div class="alert alert-secondary">
            @lang('kpi.messages.read_only_role_configs')
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <strong>@lang('kpi.help.role_config_about')</strong>
        </div>
        <div class="card-body text-muted small">
            @lang('kpi.help.role_config_description')
        </div>
    </div>

    @foreach($roles as $role)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>{{ ucfirst($role->name) }}</strong>
                <span class="text-muted small">@lang('kpi.labels.role_id'): {{ $role->id }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>@lang('kpi.labels.kpi')</th>
                            <th>@lang('kpi.labels.type')</th>
                            <th>@lang('kpi.labels.global_weight')</th>
                            <th>@lang('kpi.labels.global_active')</th>
                            <th style="min-width:130px">@lang('kpi.labels.override_weight')</th>
                            <th style="min-width:130px">@lang('kpi.labels.override_active')</th>
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
                                        <span class="badge badge-success">@lang('kpi.states.active')</span>
                                    @else
                                        <span class="badge badge-secondary">@lang('kpi.states.inactive')</span>
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
                                                placeholder="{{ __('kpi.placeholders.default_weight', ['weight' => $kpi->weight]) }}"
                                                value="{{ $override?->weight_override }}"
                                            >
                                    @else
                                        {{ $override?->weight_override !== null ? number_format((float) $override->weight_override, 2) : __('kpi.states.inherit_default') }}
                                    @endif
                                </td>
                                <td>
                                    @if($canManage)
                                        <select name="is_active_override" class="form-control form-control-sm">
                                            <option value="" @if($override === null || $override->is_active_override === null) selected @endif>
                                                @lang('kpi.states.inherit_default_option')
                                            </option>
                                            <option value="1" @if($override !== null && $override->is_active_override === true) selected @endif>
                                                @lang('kpi.states.active')
                                            </option>
                                            <option value="0" @if($override !== null && $override->is_active_override === false) selected @endif>
                                                @lang('kpi.states.inactive')
                                            </option>
                                        </select>
                                    @else
                                        @if($override === null || $override->is_active_override === null)
                                            @lang('kpi.states.inherit_default')
                                        @elseif($override->is_active_override)
                                            @lang('kpi.states.active')
                                        @else
                                            @lang('kpi.states.inactive')
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if($canManage)
                                        <button type="submit" class="btn btn-sm btn-primary" title="{{ __('kpi.actions.save') }}">
                                            @lang('kpi.actions.save')
                                        </button>
                                        </form>

                                        @if($override)
                                            <form method="POST"
                                                  action="{{ route('admin.kpi-role-configs.destroy', $override->id) }}"
                                                  style="display:inline-block;"
                                                  onsubmit="return confirm(@json(__('kpi.confirm.remove_override')))">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('kpi.confirm.remove_override') }}">
                                                    ✕
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="text-muted small">@lang('kpi.states.read_only')</span>
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
        <div class="alert alert-warning">@lang('kpi.messages.no_roles')</div>
    @endif

    @if($kpis->isEmpty())
        <div class="alert alert-warning">@lang('kpi.messages.no_active_kpis')</div>
    @endif
@endsection
