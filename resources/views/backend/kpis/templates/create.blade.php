@extends('backend.layouts.app')

@section('title', __('kpi.titles.create_template') . ' | ' . app_name())

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <h4 class="mb-0">@lang('kpi.titles.create_template')</h4>
        <a href="{{ route('admin.kpi-templates.index') }}" class="btn btn-secondary">&larr; @lang('kpi.actions.back_to_templates')</a>
    </div>

    <div class="card mb-4 template-create-section">
        <div class="card-header">
            <strong>@lang('kpi.messages.blueprint_details')</strong>
        </div>
        <div class="card-body">
            <div class="alert alert-info small mb-3">
                <strong>@lang('kpi.messages.important'):</strong> @lang('kpi.help.blueprint_important')
            </div>
            <p class="mb-3 text-muted small">@lang('kpi.help.blueprint_define')</p>

            <form method="POST" action="{{ route('admin.kpi-templates.store') }}" id="template-create-form">
                @csrf
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>@lang('kpi.labels.template_name') *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>@lang('kpi.labels.category') *</label>
                        <input type="text" name="category" class="form-control" value="{{ old('category', 'general') }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>@lang('kpi.labels.slug') (@lang('kpi.labels.optional'))</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="{{ __('kpi.placeholders.slug_auto') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>@lang('kpi.labels.description')</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>@lang('kpi.labels.use_case')</label>
                        <textarea name="use_case" class="form-control" rows="2">{{ old('use_case') }}</textarea>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>@lang('kpi.messages.include_existing_kpis')</strong>
                    <small class="text-muted">@lang('kpi.help.existing_kpis_source')</small>
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
                                            <small class="text-muted">{{ $kpi->code }} | {{ $kpi->type_label }} | @lang('kpi.labels.weight'): {{ number_format((float) $kpi->weight, 2) }}</small>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted small mb-0">@lang('kpi.messages.no_existing_kpis')</div>
                    @endif
                </div>

                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>@lang('kpi.messages.add_new_blueprint_items')</strong>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="add-template-item">@lang('kpi.actions.add_blueprint_item')</button>
                </div>

                <p class="text-muted small mb-2">@lang('kpi.help.blueprint_sources')</p>

                <div id="template-items-container">
                    <div class="template-item-row border rounded p-3 mb-2">
                        <div class="row">
                            <div class="col-md-3 form-group mb-2">
                                <label class="small mb-1">@lang('kpi.labels.name') *</label>
                                    <input type="text" name="items[0][name]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small mb-1">@lang('kpi.labels.code') *</label>
                                    <input type="text" name="items[0][code]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small mb-1">@lang('kpi.labels.type') *</label>
                                    <input type="text" name="items[0][type]" class="form-control form-control-sm" value="percentage">
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small mb-1">@lang('kpi.labels.weight') *</label>
                                    <input type="number" name="items[0][weight]" class="form-control form-control-sm" min="0" max="100" step="0.01" value="25">
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small mb-1">@lang('kpi.labels.active')</label>
                                <select name="items[0][is_active]" class="form-control form-control-sm">
                                    <option value="1" selected>@lang('kpi.states.yes')</option>
                                    <option value="0">@lang('kpi.states.no')</option>
                                </select>
                            </div>
                            <div class="col-md-1 d-flex align-items-end mb-2">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-template-item">&times;</button>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small mb-1">@lang('kpi.labels.description')</label>
                            <input type="text" name="items[0][description]" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-primary">@lang('kpi.actions.save_template_blueprint')</button>
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
                            '<label class="small mb-1">' + @json(__('kpi.labels.name') . ' *') + '</label>' +
                            '<input type="text" name="items[' + idx + '][name]" class="form-control form-control-sm">' +
                        '</div>' +
                        '<div class="col-md-2 form-group mb-2">' +
                            '<label class="small mb-1">' + @json(__('kpi.labels.code') . ' *') + '</label>' +
                            '<input type="text" name="items[' + idx + '][code]" class="form-control form-control-sm">' +
                        '</div>' +
                        '<div class="col-md-2 form-group mb-2">' +
                            '<label class="small mb-1">' + @json(__('kpi.labels.type') . ' *') + '</label>' +
                            '<input type="text" name="items[' + idx + '][type]" class="form-control form-control-sm" value="percentage">' +
                        '</div>' +
                        '<div class="col-md-2 form-group mb-2">' +
                            '<label class="small mb-1">' + @json(__('kpi.labels.weight') . ' *') + '</label>' +
                            '<input type="number" name="items[' + idx + '][weight]" class="form-control form-control-sm" min="0" max="100" step="0.01" value="25">' +
                        '</div>' +
                        '<div class="col-md-2 form-group mb-2">' +
                            '<label class="small mb-1">' + @json(__('kpi.labels.active')) + '</label>' +
                            '<select name="items[' + idx + '][is_active]" class="form-control form-control-sm">' +
                                '<option value="1" selected>' + @json(__('kpi.states.yes')) + '</option>' +
                                '<option value="0">' + @json(__('kpi.states.no')) + '</option>' +
                            '</select>' +
                        '</div>' +
                        '<div class="col-md-1 d-flex align-items-end mb-2">' +
                            '<button type="button" class="btn btn-sm btn-outline-danger remove-template-item">&times;</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="form-group mb-0">' +
                        '<label class="small mb-1">' + @json(__('kpi.labels.description')) + '</label>' +
                        '<input type="text" name="items[' + idx + '][description]" class="form-control form-control-sm">' +
                    '</div>';

                container.appendChild(template);
                bindRemoveButtons();
            });

            bindRemoveButtons();
        })();
    </script>
@endsection
