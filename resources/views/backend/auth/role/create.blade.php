@extends('backend.layouts.app')

@section('title', __('labels.backend.access.roles.management') . ' | ' . __('labels.backend.access.roles.create'))

@section('content')
{{ html()->form('POST', route('admin.auth.role.store'))->class('form-horizontal')->open() }}
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-5">
                    <h4 class="card-title mb-0">
                        @lang('labels.backend.access.roles.management')
                        <small class="text-muted">@lang('labels.backend.access.roles.create')</small>
                    </h4>
                </div><!--col-->
            </div><!--row-->

            <hr>

            <div class="row mt-4">
                <div class="col">
                    <div class="form-group row">
                        {{ html()->label(__('validation.attributes.backend.access.roles.name'))
                            ->class('col-md-2 form-control-label')
                            ->for('name') }}

                        <div class="col-md-10">
                            {{ html()->text('name')
                                ->class('form-control')
                                ->placeholder(__('validation.attributes.backend.access.roles.name'))
                                ->attribute('maxlength', 191)
                                ->required()
                                ->autofocus() }}
                        </div><!--col-->
                    </div><!--form-group-->

                    <!-- Global Controls -->
                    <div class="form-group row">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <button type="button" class="btn btn-primary" id="select-all">Select All Permissions</button>
                                    <button type="button" class="btn btn-secondary" id="unselect-all">Unselect All Permissions</button>
                                    <button type="button" class="btn btn-warning" id="reset-default">Reset to Default</button>
                                </div>
                                <div>
                                    <input type="text" class="form-control" id="permission-search" placeholder="Search permissions...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Cards -->
                    <div class="form-group row">
                        {{ html()->label(__('validation.attributes.backend.access.roles.associated_permissions'))
                            ->class('col-md-2 form-control-label')
                            ->for('permissions') }}

                        <div class="col-md-10">
                            @if($groupedPermissions->count())
                                @foreach($groupedPermissions as $module => $permissions)
                                    <div class="card permission-card mb-3" data-module="{{ $module }}">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link text-decoration-none" type="button" data-toggle="collapse" data-target="#collapse-{{ $module }}" aria-expanded="true" aria-controls="collapse-{{ $module }}">
                                                    {{ ucwords(str_replace('_', ' ', $module)) }}
                                                </button>
                                            </h5>
                                            <div class="form-check">
                                                <input class="form-check-input module-select-all" type="checkbox" id="select-all-{{ $module }}">
                                                <label class="form-check-label" for="select-all-{{ $module }}">
                                                    Select All
                                                </label>
                                            </div>
                                        </div>
                                        <div id="collapse-{{ $module }}" class="collapse show">
                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach($permissions as $permission)
                                                        @php
                                                            $parts = explode('_', $permission->name);
                                                            $action = end($parts);
                                                        @endphp
                                                        <div class="col-md-2 mb-2">
                                                            <div class="form-check">
                                                                {{ html()->checkbox('permissions[]', old('permissions') && in_array($permission->name, old('permissions')) ? true : false, $permission->name)
                                                                    ->class('form-check-input permission-checkbox')
                                                                    ->id('permission-'.$permission->id) }}
                                                                <label class="form-check-label" for="permission-{{$permission->id}}">
                                                                    {{ ucwords($action) }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div><!--col-->
                    </div><!--form-group-->
                </div><!--col-->
            </div><!--row-->
        </div><!--card-body-->

        <div class="card-footer">
            <div class="row">
                <div class="col">
                    {{ form_cancel(route('admin.auth.role.index'), __('buttons.general.cancel')) }}
                </div><!--col-->

                <div class="col text-right">
                    {{ form_submit(__('buttons.general.crud.create')) }}
                </div><!--col-->
            </div><!--row-->
        </div><!--card-footer-->
    </div><!--card-->
{{ html()->form()->close() }}
@endsection

@section('pagescript')
<script>
$(document).ready(function() {
    // Select All Permissions
    $('#select-all').click(function() {
        $('.permission-checkbox').prop('checked', true);
        updateModuleCheckboxes();
    });

    // Unselect All Permissions
    $('#unselect-all').click(function() {
        $('.permission-checkbox').prop('checked', false);
        updateModuleCheckboxes();
    });

    // Reset to Default (uncheck all for now, can be customized)
    $('#reset-default').click(function() {
        $('.permission-checkbox').prop('checked', false);
        updateModuleCheckboxes();
    });

    // Module Select All
    $('.module-select-all').change(function() {
        var module = $(this).closest('.permission-card').data('module');
        var isChecked = $(this).is(':checked');
        $(this).closest('.permission-card').find('.permission-checkbox').prop('checked', isChecked);
    });

    // Individual permission change
    $('.permission-checkbox').change(function() {
        updateModuleCheckboxes();
    });

    function updateModuleCheckboxes() {
        $('.permission-card').each(function() {
            var moduleCard = $(this);
            var totalCheckboxes = moduleCard.find('.permission-checkbox').length;
            var checkedCheckboxes = moduleCard.find('.permission-checkbox:checked').length;
            var moduleCheckbox = moduleCard.find('.module-select-all');
            
            if (checkedCheckboxes === 0) {
                moduleCheckbox.prop('checked', false);
                moduleCheckbox.prop('indeterminate', false);
            } else if (checkedCheckboxes === totalCheckboxes) {
                moduleCheckbox.prop('checked', true);
                moduleCheckbox.prop('indeterminate', false);
            } else {
                moduleCheckbox.prop('checked', false);
                moduleCheckbox.prop('indeterminate', true);
            }
        });
    }

    // Search functionality
    $('#permission-search').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('.permission-card').each(function() {
            var module = $(this).data('module').toLowerCase();
            if (module.indexOf(value) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Initialize
    updateModuleCheckboxes();
});
</script>
@endsection
