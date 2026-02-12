@extends('backend.layouts.app')

@section('title', __('Assessment Accounts').' | '.app_name())

@section('content')

@include('backend.includes.license-warning')

{{ html()->form('POST', route('admin.assessment_accounts.store'))->acceptsFiles()->class('form-horizontal')->open() }}
<div class="pb-3 d-flex justify-content-between align-items-center">
    <h4 class="page-title d-inline">@lang('Create Assessment Account')</h4>
    <div class="">
        <a href="{{ route('admin.assessment_accounts.index') }}" class="add-btn">@lang('View Assessment Accounts')</a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <div class="form-group row">
                    {{ html()->label(__('First Name'))->class('col-md-2 form-control-label')->for('first_name') }}

                    <div class="col-md-10">
                        {{ html()->text('first_name')
                                ->class('form-control')
                                ->placeholder(__('First Name'))
                                ->attribute('maxlength', 191)
                                ->required()
                                ->autofocus() }}
                    </div>
                    <!--col-->
                </div>
                <!--form-group-->

                <div class="form-group row">
                    {{ html()->label(__('Last Name'))->class('col-md-2 form-control-label')->for('last_name') }}

                    <div class="col-md-10">
                        {{ html()->text('last_name')
                                ->class('form-control')
                                ->placeholder(__('Last Name'))
                                ->attribute('maxlength', 191)
                                ->required() }}
                    </div>
                    <!--col-->
                </div>
                <!--form-group-->

                <div class="form-group row">
                    {{ html()->label(__('Email'))->class('col-md-2 form-control-label')->for('email') }}

                    <div class="col-md-10">
                        {{ html()->email('email')
                                ->class('form-control')
                                ->placeholder(__('Email'))
                                ->attribute('maxlength', 191)
                                ->required() }}
                    </div>
                    <!--col-->
                </div>
                <!--form-group-->

                <div class="form-group row">
                    {{ html()->label(__('Phone'))->class('col-md-2 form-control-label')->for('phone') }}

                    <div class="col-md-10">
                        {{ html()->text('phone')
                                ->class('form-control')
                                ->placeholder(__('Phone'))
                                ->attribute('maxlength', 15)
                                ->required() }}
                    </div>
                    <!--col-->
                </div>
                <!--form-group-->

                <div class="form-group row">
                    {{ html()->label(__('Status'))->class('col-md-2 form-control-label')->for('active') }}
                    <div class="col-md-10">
                        {{ html()->label(html()->checkbox('')->name('active')
                                        ->checked(true)->class('switch-input')->value(1)

                                    . '<span class="switch-label"></span><span class="switch-handle"></span>')
                                ->class('switch switch-lg switch-3d switch-primary')
                            }}
                    </div>

                </div>

                <div class="form-group row justify-content-center">
                    <div class="col-4">
                        {{ form_cancel(route('admin.assessment_accounts.index'), __('buttons.general.cancel')) }}
                        {{ form_submit(__('buttons.general.crud.create')) }}
                    </div>
                </div>
                <!--col-->
            </div>
        </div>
    </div>
</div>
{{ html()->form()->close() }}
@endsection
@push('after-scripts')
@endpush