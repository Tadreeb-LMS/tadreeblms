@extends('backend.layouts.app')

@section('title', 'KPI Targets | ' . app_name())

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <h4 class="mb-0">KPI Targets</h4>
        <a href="{{ route('admin.kpis.index') }}" class="btn btn-secondary">&larr; Back to KPIs</a>
    </div>

    @if(!$canManage)
        <div class="alert alert-secondary">
            Read-only mode: only authorized users can save or remove KPI targets.
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <strong>Target Definition</strong>
        </div>
        <div class="card-body text-muted small">
            Targets are optional. You can set a global target per KPI, or narrow the target by role, course, or role+course.
            Deviation is computed as <strong>(actual - target)</strong> and percentage as <strong>((actual - target) / target) * 100</strong> when target > 0.
        </div>
    </div>

    @if($canManage)
        <div class="card mb-4">
            <div class="card-header"><strong>Create or Update Target</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.kpi-targets.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>KPI *</label>
                            <select name="kpi_id" class="form-control" required>
                                <option value="">Select KPI</option>
                                @foreach($kpis as $kpi)
                                    <option value="{{ $kpi->id }}">{{ $kpi->name }} ({{ $kpi->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Role (Optional)</label>
                            <select name="role_id" class="form-control">
                                <option value="">All roles</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Course (Optional)</label>
                            <select name="course_id" class="form-control">
                                <option value="">All courses</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->title }}{{ $course->course_code ? ' (' . $course->course_code . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Target Value (%) *</label>
                            <input type="number" name="target_value" class="form-control" min="0" max="100" step="0.01" required>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="add-btn">Save Target</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><strong>Configured Targets</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>KPI</th>
                            <th>Role Scope</th>
                            <th>Course Scope</th>
                            <th>Target (%)</th>
                            <th>Updated</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($targets as $target)
                            <tr>
                                <td>{{ $target->kpi->name }}<br><small class="text-muted">{{ $target->kpi->code }}</small></td>
                                <td>{{ $target->role ? ucfirst($target->role->name) : 'All roles' }}</td>
                                <td>
                                    @if($target->course)
                                        {{ $target->course->title }}{{ $target->course->course_code ? ' (' . $target->course->course_code . ')' : '' }}
                                    @else
                                        All courses
                                    @endif
                                </td>
                                <td>{{ number_format((float) $target->target_value, 2) }}</td>
                                <td>{{ optional($target->updated_at)->diffForHumans() }}</td>
                                <td class="text-center">
                                    @if($canManage)
                                        <form method="POST" action="{{ route('admin.kpi-targets.destroy', $target->id) }}" style="display:inline-block;" onsubmit="return confirm('Delete this target configuration?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @else
                                        <span class="text-muted small">Read-only</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No KPI targets configured yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
