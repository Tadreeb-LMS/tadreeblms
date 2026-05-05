@extends('backend.layouts.app')

@section('title', __('kpi.titles.targets') . ' | ' . app_name())

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <h4 class="mb-0">@lang('kpi.titles.targets')</h4>
        <a href="{{ route('admin.kpis.index') }}" class="btn btn-secondary">&larr; @lang('kpi.actions.back_to_kpis')</a>
    </div>

    @if(!$canManage)
        <div class="alert alert-secondary">
            @lang('kpi.messages.read_only_targets')
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <strong>@lang('kpi.help.target_definition')</strong>
        </div>
        <div class="card-body text-muted small">
            @lang('kpi.help.target_description')
        </div>
    </div>

    @if($canManage)
        <div class="card mb-4">
            <div class="card-header"><strong>@lang('kpi.messages.create_update_target')</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.kpi-targets.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>@lang('kpi.labels.kpi') *</label>
                            <select name="kpi_id" class="form-control" required>
                                <option value="">@lang('kpi.placeholders.select_kpi')</option>
                                @foreach($kpis as $kpi)
                                    <option value="{{ $kpi->id }}">{{ $kpi->name }} ({{ $kpi->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>@lang('kpi.labels.role') (@lang('kpi.labels.optional'))</label>
                            <select name="role_id" class="form-control">
                                <option value="">@lang('kpi.states.all_roles')</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>@lang('kpi.labels.course') (@lang('kpi.labels.optional'))</label>
                            <select name="course_id" class="form-control">
                                <option value="">@lang('kpi.states.all_courses')</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->title }}{{ $course->course_code ? ' (' . $course->course_code . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>@lang('kpi.labels.target_value_percent') *</label>
                            <input type="number" name="target_value" class="form-control" min="0" max="100" step="0.01" required>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="add-btn">@lang('kpi.messages.save_target')</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><strong>@lang('kpi.messages.configured_targets')</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>@lang('kpi.labels.kpi')</th>
                            <th>@lang('kpi.labels.role_scope')</th>
                            <th>@lang('kpi.labels.course_scope')</th>
                            <th>@lang('kpi.labels.target_percent')</th>
                            <th>@lang('kpi.labels.updated')</th>
                            <th class="text-center">@lang('kpi.labels.actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($targets as $target)
                            <tr>
                                <td>{{ $target->kpi->name }}<br><small class="text-muted">{{ $target->kpi->code }}</small></td>
                                <td>{{ $target->role ? ucfirst($target->role->name) : __('kpi.states.all_roles') }}</td>
                                <td>
                                    @if($target->course)
                                        {{ $target->course->title }}{{ $target->course->course_code ? ' (' . $target->course->course_code . ')' : '' }}
                                    @else
                                        @lang('kpi.states.all_courses')
                                    @endif
                                </td>
                                <td>{{ number_format((float) $target->target_value, 2) }}</td>
                                <td>{{ optional($target->updated_at)->diffForHumans() }}</td>
                                <td class="text-center">
                                    @if($canManage)
                                        <form method="POST" action="{{ route('admin.kpi-targets.destroy', $target->id) }}" style="display:inline-block;" onsubmit="return confirm(@json(__('kpi.confirm.delete_target')));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">@lang('kpi.actions.delete')</button>
                                        </form>
                                    @else
                                        <span class="text-muted small">@lang('kpi.states.read_only')</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">@lang('kpi.messages.no_targets')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
