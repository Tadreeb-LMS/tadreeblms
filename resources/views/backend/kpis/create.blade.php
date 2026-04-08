@extends('backend.layouts.app')

@section('title', 'Create KPI | ' . app_name())

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <h4 class="mb-0">Create KPI</h4>
        <a href="{{ route('admin.kpis.index') }}" class="btn btn-primary">View KPIs</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.kpis.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="name">KPI Name *</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="col-md-6 form-group">
                        <label for="code">KPI Code *</label>
                        <input
                            type="text"
                            id="code"
                            name="code"
                            class="form-control"
                            value="{{ old('code') }}"
                            placeholder="Example: COURSE_COMPLETION_RATE"
                            required
                        >
                        <small class="form-text text-muted">Use uppercase letters, numbers, and underscores only.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="type">KPI Type *</label>
                        <select id="type" name="type" class="form-control" required>
                            <option value="">Select a KPI type</option>
                            @foreach($kpiTypes as $typeKey => $typeConfig)
                                <option value="{{ $typeKey }}" {{ old('type') === $typeKey ? 'selected' : '' }}>
                                    {{ $typeConfig['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Formulas are system-managed. Admin selects only supported KPI types.</small>
                    </div>

                    <div class="col-md-6 form-group">
                        <label for="weight">Weight *</label>
                        <input
                            type="number"
                            id="weight"
                            name="weight"
                            class="form-control"
                            min="0"
                            max="{{ $maxWeight }}"
                            step="0.01"
                            value="{{ old('weight', $defaultWeight) }}"
                            required
                        >
                        <small class="form-text text-muted">Set relative importance. Allowed range: 0 to {{ $maxWeight }}.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 form-group">
                        <label for="course_ids">Associated Courses</label>
                        <select id="course_ids" name="course_ids[]" class="form-control" multiple>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ in_array($course->id, old('course_ids', []), true) ? 'selected' : '' }}>
                                    {{ $course->title }}{{ $course->course_code ? ' (' . $course->course_code . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Leave empty to apply this KPI to all courses. Use Ctrl/Cmd + click to select specific courses.</small>
                    </div>

                    <div class="col-12 form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" rows="4" class="form-control" required>{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="add-btn">Save KPI</button>
                </div>
            </form>

            <div class="mt-3">
                <h6 class="mb-2">KPI Type Guide</h6>
                <ul class="mb-0 pl-3">
                    @foreach($kpiTypes as $typeConfig)
                        <li><strong>{{ $typeConfig['label'] }}:</strong> {{ $typeConfig['description'] }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection
