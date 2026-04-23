@extends('backend.layouts.app')

@section('title', 'Create KPI Template Blueprint | ' . app_name())

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <h4 class="mb-0">Create KPI Template Blueprint</h4>
        <a href="{{ route('admin.kpi-templates.index') }}" class="btn btn-secondary">&larr; Back to Templates</a>
    </div>

    <div class="card mb-4 template-create-section">
        <div class="card-header">
            <strong>Blueprint Details</strong>
        </div>
        <div class="card-body">
            <div class="alert alert-info small mb-3">
                <strong>Important:</strong> Saving this page creates a <strong>template blueprint only</strong>. No live KPI is created until you open the template and click <strong>Apply This Template</strong>.
            </div>
            <p class="mb-3 text-muted small">Define the blueprint information and add KPI items below. After saving, you can preview and apply this template from the templates list.</p>

            <form method="POST" action="{{ route('admin.kpi-templates.store') }}" id="template-create-form">
                @csrf
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Template Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Category *</label>
                        <input type="text" name="category" class="form-control" value="{{ old('category', 'general') }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Slug (Optional)</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="auto-generated if empty">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Use Case</label>
                        <textarea name="use_case" class="form-control" rows="2">{{ old('use_case') }}</textarea>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Include Existing KPIs in Blueprint</strong>
                    <small class="text-muted">Optional source</small>
                </div>

                <div class="existing-kpi-list border rounded p-3 mb-3">
                    @if($kpis->isNotEmpty())
                        <div class="row">
                            @foreach($kpis as $kpi)
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <label class="existing-kpi-item d-flex align-items-start p-2 mb-0 rounded">
                                        <input type="checkbox" name="existing_kpi_ids[]" value="{{ $kpi->id }}" class="mt-1 mr-2" {{ in_array((string) $kpi->id, old('existing_kpi_ids', []), true) ? 'checked' : '' }}>
                                        <span>
                                            <strong>{{ $kpi->name }}</strong><br>
                                            <small class="text-muted">{{ $kpi->code }} | {{ $kpi->type }} | W: {{ number_format((float) $kpi->weight, 2) }}</small>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted small mb-0">No existing KPIs found.</div>
                    @endif
                </div>

                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Add New Blueprint Items</strong>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="add-template-item">Add Blueprint Item</button>
                </div>

                <p class="text-muted small mb-2">Use either source (existing KPIs, new blueprint items) or both.</p>

                <div id="template-items-container">
                    <div class="template-item-row border rounded p-3 mb-2">
                        <div class="row">
                            <div class="col-md-3 form-group mb-2">
                                <label class="small mb-1">Name *</label>
                                    <input type="text" name="items[0][name]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small mb-1">Code *</label>
                                    <input type="text" name="items[0][code]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small mb-1">Type *</label>
                                    <input type="text" name="items[0][type]" class="form-control form-control-sm" value="percentage">
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small mb-1">Weight *</label>
                                    <input type="number" name="items[0][weight]" class="form-control form-control-sm" min="0" max="100" step="0.01" value="25">
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small mb-1">Active</label>
                                <select name="items[0][is_active]" class="form-control form-control-sm">
                                    <option value="1" selected>Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="col-md-1 d-flex align-items-end mb-2">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-template-item">&times;</button>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small mb-1">Description</label>
                            <input type="text" name="items[0][description]" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-primary">Save Template Blueprint</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .template-item-row {
            background: #fafbfc;
        }

        .existing-kpi-list {
            max-height: 260px;
            overflow-y: auto;
            background: #f9fbff;
        }

        .existing-kpi-item {
            cursor: pointer;
            border: 1px solid #e5e7eb;
            background: #fff;
        }

        .existing-kpi-item:hover {
            border-color: #bfdbfe;
            background: #eff6ff;
        }

        .template-create-section .card-header {
            background: #eef4ff;
            color: #1e3a8a;
        }
    </style>

    <script>
        (function () {
            var addButton = document.getElementById('add-template-item');
            var container = document.getElementById('template-items-container');

            if (!addButton || !container) {
                return;
            }

            function nextIndex() {
                return container.querySelectorAll('.template-item-row').length;
            }

            function bindRemoveButtons() {
                var removeButtons = container.querySelectorAll('.remove-template-item');
                removeButtons.forEach(function (button) {
                    button.onclick = function () {
                        if (container.querySelectorAll('.template-item-row').length <= 1) {
                            return;
                        }

                        var row = button.closest('.template-item-row');
                        if (row) {
                            row.remove();
                        }
                    };
                });
            }

            addButton.addEventListener('click', function () {
                var idx = nextIndex();
                var template = document.createElement('div');
                template.className = 'template-item-row border rounded p-3 mb-2';
                template.innerHTML =
                    '<div class="row">' +
                        '<div class="col-md-3 form-group mb-2">' +
                            '<label class="small mb-1">Name *</label>' +
                            '<input type="text" name="items[' + idx + '][name]" class="form-control form-control-sm">' +
                        '</div>' +
                        '<div class="col-md-2 form-group mb-2">' +
                            '<label class="small mb-1">Code *</label>' +
                            '<input type="text" name="items[' + idx + '][code]" class="form-control form-control-sm">' +
                        '</div>' +
                        '<div class="col-md-2 form-group mb-2">' +
                            '<label class="small mb-1">Type *</label>' +
                            '<input type="text" name="items[' + idx + '][type]" class="form-control form-control-sm" value="percentage">' +
                        '</div>' +
                        '<div class="col-md-2 form-group mb-2">' +
                            '<label class="small mb-1">Weight *</label>' +
                            '<input type="number" name="items[' + idx + '][weight]" class="form-control form-control-sm" min="0" max="100" step="0.01" value="25">' +
                        '</div>' +
                        '<div class="col-md-2 form-group mb-2">' +
                            '<label class="small mb-1">Active</label>' +
                            '<select name="items[' + idx + '][is_active]" class="form-control form-control-sm">' +
                                '<option value="1" selected>Yes</option>' +
                                '<option value="0">No</option>' +
                            '</select>' +
                        '</div>' +
                        '<div class="col-md-1 d-flex align-items-end mb-2">' +
                            '<button type="button" class="btn btn-sm btn-outline-danger remove-template-item">&times;</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="form-group mb-0">' +
                        '<label class="small mb-1">Description</label>' +
                        '<input type="text" name="items[' + idx + '][description]" class="form-control form-control-sm">' +
                    '</div>';

                container.appendChild(template);
                bindRemoveButtons();
            });

            bindRemoveButtons();
        })();
    </script>
@endsection
