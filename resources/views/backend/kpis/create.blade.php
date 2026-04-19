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
                        <div class="mt-2 small text-muted">
                            Current active total: <strong id="kpi-current-active-total">{{ number_format($activeTotalWeight, 2) }}</strong>
                            <br>
                            Projected active total after save: <strong id="kpi-projected-active-total">{{ number_format($activeTotalWeight + (float) old('weight', $defaultWeight), 2) }}</strong>
                        </div>
                        <div id="kpi-weight-warning" class="small text-warning mt-1" style="display: none;"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 form-group">
                        <label for="category_ids">Mapped Course Categories *</label>
                        <select id="category_ids" name="category_ids[]" class="form-control" multiple required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ in_array($category->id, old('category_ids', []), true) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Select one or more learning domains. KPI calculations will include only courses in these categories with KPI inclusion enabled.</small>
                    </div>

                    <div class="col-12 form-group">
                        <label for="course_ids">Legacy Explicit Courses (Optional)</label>
                        <select id="course_ids" name="course_ids[]" class="form-control" multiple>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ in_array($course->id, old('course_ids', []), true) ? 'selected' : '' }}>
                                    {{ $course->title }}{{ $course->course_code ? ' (' . $course->course_code . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Only used for backward compatibility. Category mapping is the primary KPI scope.</small>
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

    <script>
        (function () {
            var weightInput = document.getElementById('weight');
            var projectedTotalEl = document.getElementById('kpi-projected-active-total');
            var warningEl = document.getElementById('kpi-weight-warning');

            if (!weightInput || !projectedTotalEl || !warningEl) {
                return;
            }

            var baseActiveTotal = {{ (float) $activeTotalWeight }};
            var extremeThreshold = {{ (float) $extremeWeightThreshold }};
            var validationEnabled = {{ !empty($totalWeightValidation['enabled']) ? 'true' : 'false' }};
            var validationTarget = {{ (float) ($totalWeightValidation['target'] ?? 100) }};
            var validationTolerance = {{ (float) ($totalWeightValidation['tolerance'] ?? 0.01) }};

            function roundTwo(value) {
                return Math.round(value * 100) / 100;
            }

            function updateWeightSummary() {
                var weight = parseFloat(weightInput.value);
                if (isNaN(weight) || weight < 0) {
                    weight = 0;
                }

                var projectedTotal = roundTwo(baseActiveTotal + weight);
                projectedTotalEl.textContent = projectedTotal.toFixed(2);

                var warnings = [];
                if (weight >= extremeThreshold) {
                    warnings.push('This weight is in the extreme range and may dominate final KPI scoring.');
                }

                if (!validationEnabled && projectedTotal <= 0) {
                    warnings.push('Projected active total is 0, so weighted scores will all be 0.');
                }

                if (validationEnabled && Math.abs(projectedTotal - validationTarget) > validationTolerance) {
                    warnings.push('Projected total is outside the strict validation target range.');
                }

                if (warnings.length === 0) {
                    warningEl.style.display = 'none';
                    warningEl.textContent = '';
                    return;
                }

                warningEl.style.display = 'block';
                warningEl.textContent = warnings.join(' ');
            }

            weightInput.addEventListener('input', updateWeightSummary);
            updateWeightSummary();
        })();
    </script>
@endsection
