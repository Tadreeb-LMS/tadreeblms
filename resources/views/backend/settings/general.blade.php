@extends('backend.layouts.app')
@section('title', __('labels.backend.general_settings.title') . ' | ' . app_name())

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap-iconpicker/css/bootstrap-iconpicker.min.css') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('assets/css/colors/switch.css') }}">
    <style>
        .color-list li {
            float: left;
            width: 8%;
        }

        @media screen and (max-width: 768px) {
            .color-list li {
                width: 20%;
                padding-bottom: 20px;
            }

            .color-list li:first-child {
                padding-bottom: 0px;
            }
        }

        .options {
            line-height: 35px;
        }

        .color-list li a {
            font-size: 20px;
        }

        .color-list li a.active {
            border: 4px solid grey;
        }

        .color-default {
            font-size: 18px !important;
            background: #101010;
            border-radius: 100%;
        }

        .form-control-label {
            line-height: 35px;
        }

        .switch.switch-3d {
            margin-bottom: 0px;
            vertical-align: middle;

        }

        .color-default i {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .preview {
            background-color: #dcd8d8;
            background-image: url(https://www.transparenttextures.com/patterns/carbon-fibre-v2.png);
        }

        #logos img {
            height: auto;
            width: 100%;
        }

        .language-workflow .btn {
            border-radius: 6px;
            font-weight: 600;
        }

        .language-action-group {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .language-action-group .btn {
            min-width: 110px;
        }

        .workflow-publish-controls {
            width: 100%;
        }

        .workflow-publish-controls .form-control,
        .workflow-publish-controls .btn {
            height: 38px;
            border-radius: 6px;
        }

        .workflow-publish-controls .btn {
            white-space: nowrap;
        }

        .review-action-stack {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .review-action-stack .btn {
            width: 100%;
        }

        @media screen and (max-width: 768px) {
            .workflow-publish-controls .btn {
                width: 100%;
            }

            .language-action-group {
                width: 100%;
            }

            .language-action-group .btn {
                flex: 1 1 auto;
                min-width: 0;
            }
        }
    </style>
@endpush
@section('content')
    <form method="POST" action="{{ route('admin.general-settings') }}" id="general-settings-form" class="form-horizontal" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="active_tab" id="active_tab" value="general">

    <div class="card">
        <div class="card-body">
            <div class="col-md-3 mb-4 pl-0 custom-select-wrapper">
                <select name="lang" id="change-lang" class="form-control custom-select-box">
                    <option value="en" @if (request()->lang == 'en') selected @endif>{{ locale_label('en') }}</option>
                    <option value="ar" @if (request()->lang == 'ar') selected @endif>{{ locale_label('ar') }}</option>
                </select>
                <span class="custom-select-icon" style="right: 23px;">
        <i class="fa fa-chevron-down"></i>
        </span>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <ul class="nav main-nav-tabs nav-tabs">
                        <li class="nav-item"><a data-toggle="tab" class="nav-link active " href="#general">
                                {{ __('labels.backend.general_settings.title') }}
                            </a>
                        </li>
                        <li class="nav-item"><a data-toggle="tab" class="nav-link" href="#layout">
                                {{ __('labels.backend.general_settings.layout_type') }}
                            </a>
                        </li>
                        <li class="nav-item"><a data-toggle="tab" class="nav-link" href="#email">
                                {{ __('labels.backend.general_settings.email.mail_from_name') }}
                            </a>
                        </li>
                        <li class="nav-item"><a data-toggle="tab" class="nav-link" href="#payment_settings">
                                {{ __('labels.backend.general_settings.payment_settings.stripe') }}
                            </a>
                        </li>
                        <li class="nav-item"><a data-toggle="tab" class="nav-link" href="#language_settings">
                                Translations
                            </a>
                        </li>
                       
                    </ul>
                    <h4 class="card-title mb-0">
                        {{-- {{ __('labels.backend.general_settings.management') }} --}}
                    </h4>
                </div><!--col-->
            </div><!--row-->

            <div class="tab-content">
                <!---General Tab--->
                <div id="general" class="tab-pane container active">
                    <div class="row mt-4 mb-4">
                        <div class="col">

                            <!-- App Name -->
                            <div class="form-group row">
                                <label for="app_name" class="col-md-2 form-control-label">
                                    {{ __('labels.backend.general_settings.app_name') }}
                                </label>
                                <div class="col-md-10">
                                    <input type="text" name="app_name" id="app_name" class="form-control"
                                        value="{{ old('app_name', $settings['app_name'] ?? '') }}"
                                        placeholder="{{ __('labels.backend.general_settings.app_name') }}"
                                        maxlength="191" value="{{ config('app.name') }}" autofocus>
                                </div>
                            </div>

                            <!-- Site Logo -->
                            <div class="form-group row">
                                <label for="site_logo" class="col-md-2 form-control-label">{{ __('labels.backend.general_settings.site_logo') }}</label>
                                <div class="col-md-10">
                                    <label for="site_logo" class="control-label">
                                        {{ __('labels.backend.general_settings.site_logo') . ' ' . trans('labels.backend.pages.max_file_size') }}
                                    </label>
                                    <input type="file" name="site_logo" class="form-control">
                                    <input type="hidden" name="site_logo_max_size" value="8">
                                    <input type="hidden" name="site_logo_max_width" value="4000">
                                    <input type="hidden" name="site_logo_max_height" value="4000">
                                </div>
                            </div>

                            <!-- Current Logo Preview -->
                            <div class="form-group row">
                                <div class="col-lg-1 col-12 form-group">
                                    @if(isset($logo_data->value))
                                        <a href="{{ asset($logo_data->value) }}" target="_blank">
                                            <img src="{{ asset($logo_data->value) }}" height="65" width="65">
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <!-- App URL -->
                            <div class="form-group row">
                                <label for="app_url" class="col-md-2 form-control-label">
                                    {{ __('labels.backend.general_settings.app_url') }}
                                </label>
                                <div class="col-md-10">
                                    <input type="text" name="app_url" id="app_url" class="form-control"
                                        value="{{ old('app_name', $settings['app_name'] ?? '') }}"
                                        placeholder="{{ __('labels.backend.general_settings.app_url') }}"
                                        maxlength="191" value="{{ config('app.url') }}">
                                </div>
                            </div>

                            <!-- Our Vision -->
                            <!-- <div class="form-group row">
                                <label for="our_vision" class="col-md-2 form-control-label">Our Vision</label>
                                <div class="col-md-10">
                                    <textarea name="our_vision" id="our_vision" class="form-control"
                                        placeholder="Our Vision">{{ $our_vision->value ?? '' }}</textarea>
                                </div>
                            </div> -->

                            <!-- Our Mission -->
                            <!-- <div class="form-group row">
                                <label for="our_mission" class="col-md-2 form-control-label">Our Mission</label>
                                <div class="col-md-10">
                                    <textarea name="our_mission" id="our_mission" class="form-control"
                                        placeholder="Our Mission">{{ $our_mission->value ?? '' }}</textarea>
                                </div>
                            </div> -->

                            <div class="text-end mt-3">
    <button type="submit" class="btn btn-primary">
        {{ __('labels.backend.general_settings.save_settings') }}
    </button>
</div>

                        </div>
                    </div>
                </div>


                

                <!---Layout Tab--->
                <div id="layout" class="tab-pane container fade">
                    <div class="row mt-4 mb-4">
                        <div class="col">

                            <input type="hidden" id="section_data" name="layout_{{ config('theme_layout') }}">

                            <!-- Layout Type -->
                            <div class="form-group row">
                                <label for="layout_type" class="col-md-2 form-control-label">
                                    {{ __('labels.backend.general_settings.layout_type') }}
                                </label>
                                <div class="col-md-10">
                                    <select class="form-control" id="layout_type" name="layout_type">
                                        <option value="wide-layout" selected>{{ __('labels.backend.general_settings.wide') }}</option>
                                        <option value="box-layout">{{ __('labels.backend.general_settings.box') }}</option>
                                    </select>
                                    <span class="help-text font-italic">
                                        {{ __('labels.backend.general_settings.layout_type_note') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Theme Layout -->
                            <div class="form-group row">
                                <label for="theme_layout" class="col-md-2 form-control-label">
                                    {{ __('labels.backend.general_settings.theme_layout') }}
                                </label>
                                <div class="col-md-10">
                                    <select class="form-control" id="theme_layout" name="theme_layout">
                                        @php
                                            $themeLayouts = (array) config('theme_layouts.layouts', []);
                                        @endphp
                                        @foreach($themeLayouts as $layoutId => $layoutMeta)
                                            <option value="{{ $layoutId }}" {{ theme_layout_id(config('theme_layout')) === (string) $layoutId ? 'selected' : '' }}>
                                                {{ __('labels.backend.general_settings.layout_label') }} {{ $layoutId }} ({{ $layoutMeta['name'] ?? ucfirst($layoutMeta['slug'] ?? $layoutId) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="help-text font-italic">
                                        {{ __('labels.backend.general_settings.layout_note') }}
                                    </span>
                                    <p id="sections_note" class="d-none font-weight-bold">
                                        {{ __('labels.backend.general_settings.list_update_note') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Sections -->
                            <div class="form-group row" id="sections">
                                <div class="col-md-10 offset-md-2">
                                    <div class="row">
                                        @foreach ($sections as $key => $item)
                                            <p style="line-height: 35px" class="col-md-4 col-12">
                                                <label class="switch switch-sm switch-3d switch-primary">
                                                    <input type="checkbox" 
                                                        id="{{ $key }}" 
                                                        name="sections[{{ $key }}]" 
                                                        class="switch-input" 
                                                        value="1" 
                                                        {{ $item->status == 1 ? 'checked' : '' }}>
                                                    <span class="switch-label"></span>
                                                    <span class="switch-handle"></span>
                                                </label>
                                                <span class="ml-2 title">{{ $item->title }}</span>
                                            </p>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    Save Layout
                                </button>
                            </div>

                        </div>
                    </div>
                </div>


                <!---SMTP Tab--->
                <div id="email" class="tab-pane container fade">
                    <div class="row mt-4 mb-4">
                        <div class="col">

                            <!-- Mail From Name -->
                            <div class="form-group row">
                                <label for="mail_from_name" class="col-md-2 form-control-label">
                                    {{ __('labels.backend.general_settings.email.mail_from_name') }}
                                </label>
                                <div class="col-md-10">
                                    <input type="text" name="mail__from__name" id="mail_from_name"
                                        class="form-control"
                                        placeholder="{{ __('labels.backend.general_settings.email.mail_from_name') }}"
                                        maxlength="191"
                                        value="{{ config('mail.from.name') }}">
                                    <span class="help-text font-italic">{{ __('labels.backend.general_settings.email.mail_from_name_note') }}</span>
                                </div>
                            </div>

                            <!-- Mail From Address -->
                            <div class="form-group row">
                                <label for="mail_from_address" class="col-md-2 form-control-label">
                                    {{ __('labels.backend.general_settings.email.mail_from_address') }}
                                </label>
                                <div class="col-md-10">
                                    <input type="text" name="mail__from__address" id="mail_from_address"
                                        class="form-control"
                                        placeholder="{{ __('labels.backend.general_settings.email.mail_from_address') }}"
                                        maxlength="191"
                                        value="{{ config('mail.from.address') }}">
                                    <span class="help-text font-italic">{{ __('labels.backend.general_settings.email.mail_from_address_note') }}</span>
                                </div>
                            </div>

                            <!-- Mail Driver -->
                            <div class="form-group row">
                                <label for="mail_driver" class="col-md-2 form-control-label">
                                    {{ __('labels.backend.general_settings.email.mail_driver') }}
                                </label>
                                <div class="col-md-10">
                                    <input type="text" name="mail__driver" id="mail_driver"
                                        class="form-control"
                                        placeholder="{{ __('labels.backend.general_settings.email.mail_driver') }}"
                                        maxlength="191"
                                        value="{{ config('mail.driver') }}">
                                    <span class="help-text font-italic">{!! __('labels.backend.general_settings.email.mail_driver_note') !!}</span>
                                </div>
                            </div>

                            <!-- Mail Host -->
                            <div class="form-group row">
                                <label for="mail_host" class="col-md-2 form-control-label">
                                    {{ __('labels.backend.general_settings.email.mail_host') }}
                                </label>
                                <div class="col-md-10">
                                    <input type="text" name="mail__host" id="mail_host"
                                        class="form-control"
                                        placeholder="Ex. smtp.gmail.com"
                                        maxlength="191"
                                        value="{{ config('mail.host') }}">
                                </div>
                            </div>

                            <!-- Mail Port -->
                            <div class="form-group row">
                                <label for="mail_port" class="col-md-2 form-control-label">
                                    {{ __('labels.backend.general_settings.email.mail_port') }}
                                </label>
                                <div class="col-md-10">
                                    <input type="text" name="mail__port" id="mail_port"
                                        class="form-control"
                                        placeholder="Ex. 465"
                                        maxlength="191"
                                        value="{{ config('mail.port') }}">
                                </div>
                            </div>

                            <!-- Mail Username -->
                            <div class="form-group row">
                                <label for="mail_username" class="col-md-2 form-control-label">
                                    {{ __('labels.backend.general_settings.email.mail_username') }}
                                </label>
                                <div class="col-md-10">
                                    <input type="text" name="mail__username" id="mail_username"
                                        class="form-control"
                                        placeholder="Ex. myemail@email.com"
                                        maxlength="191"
                                        value="{{ config('mail.username') }}">
                                    <span class="help-text font-italic">{!! __('labels.backend.general_settings.email.mail_username_note') !!}</span>
                                </div>
                            </div>

                            <!-- Mail Password -->
                            <div class="form-group row">
                                <label for="mail_password" class="col-md-2 form-control-label">
                                    {{ __('labels.backend.general_settings.email.mail_password') }}
                                </label>
                                <div class="col-md-10">
                                    <input type="password" name="mail__password" id="mail_password"
                                        class="form-control"
                                        placeholder="{{ __('labels.backend.general_settings.email.mail_password') }}"
                                        maxlength="191"
                                        value="{{ config('mail.password') }}">
                                    <span class="help-text font-italic">{!! __('labels.backend.general_settings.email.mail_password_note') !!}</span>
                                </div>
                            </div>

                            <!-- Mail Encryption -->
                            <div class="form-group row">
                                <label for="mail_encryption" class="col-md-2 form-control-label">
                                    {{ __('labels.backend.general_settings.email.mail_encryption') }}
                                </label>
                                <div class="col-md-10">
                                    <select name="mail__encryption" id="mail_encryption" class="form-control">
                                        <option value="tls" {{ config('mail.encryption') == 'tls' ? 'selected' : '' }}>tls</option>
                                        <option value="ssl" {{ config('mail.encryption') == 'ssl' ? 'selected' : '' }}>ssl</option>
                                    </select>
                                    <span class="help-text font-italic">{!! __('labels.backend.general_settings.email.mail_encryption_note') !!}</span>
                                </div>
                            </div>

                            <hr>
                            <p class="help-text mb-0">{!! __('labels.backend.general_settings.email.note') !!}</p>

                        </div>
                    </div>
                </div>

                <!---Payment Configuration Tab--->
                <div id="payment_settings" class="tab-pane container fade">
    <div class="row mt-4 mb-4">
        <div class="col">

            <!-- Currency -->
            <div class="form-group row">
                <label class="col-md-3 form-control-label">
                    {{ __('labels.backend.general_settings.payment_settings.select_currency') }}
                </label>
                <div class="col-md-9">
                    <select class="form-control" id="app__currency" name="app__currency">
                        @foreach (config('currencies') as $currency)
                            <option value="{{ $currency['short_code'] }}"
                                {{ config('app.currency') == $currency['short_code'] ? 'selected' : '' }}>
                                {{ $currency['symbol'] }} - {{ $currency['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Stripe Activation -->
            <div class="form-group row">
                <label class="col-md-3 form-control-label">
                    {{ __('labels.backend.general_settings.payment_settings.stripe') }}
                </label>
                <div class="col-md-9">
                    <label class="switch switch-sm switch-3d switch-primary">
                        <input type="checkbox" name="services__stripe__active" class="switch-input" value="1"
                            {{ config('services.stripe.active') ? 'checked' : '' }}>
                        <span class="switch-label"></span>
                        <span class="switch-handle"></span>
                    </label>

                    <a class="float-right font-weight-bold font-italic" 
                       href="https://stripe.com/docs/keys" target="_blank">
                        {{ __('labels.backend.general_settings.payment_settings.how_to_stripe') }}
                    </a>

                    <small><i>{{ __('labels.backend.general_settings.payment_settings.stripe_note') }}</i></small>
                </div>
            </div>

            <!-- Stripe Keys -->
            <div class="switch-content {{ config('services.stripe.active') ? '' : 'd-none' }}">
                <div class="form-group row">
                    <label class="col-md-2 form-control-label">
                        {{ __('labels.backend.general_settings.payment_settings.key') }}
                    </label>
                    <div class="col-md-8">
                        <input type="text" name="services__stripe__key" class="form-control"
                               value="{{ config('services.stripe.key') }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-2 form-control-label">
                        {{ __('labels.backend.general_settings.payment_settings.secret') }}
                    </label>
                    <div class="col-md-8">
                        <input type="text" name="services__stripe__secret" class="form-control"
                               value="{{ config('services.stripe.secret') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="language_settings" class="tab-pane container fade language-workflow">
    <div class="row mt-4 mb-4">
        <div class="col">

            <div class="alert alert-primary d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4" role="alert">
                <div class="mb-2 mb-md-0 mr-md-3">
                    <strong>Language Marketplace Manual</strong><br>
                    <span class="small">Download the full English guide for contributor flow, approval rules, and GitHub sync policy.</span>
                </div>
                <a class="btn btn-primary btn-lg"
                   href="{{ route('admin.settings.language-marketplace.manual.download') }}">
                    Download Manual (PDF)
                </a>
            </div>

            <div class="form-group row">
                <div class="col-md-12 mb-3">
                    <a href="{{ route('admin.settings.language.download-base') }}" class="btn btn-secondary">
                        <i class="fa fa-download mr-1"></i> {{ __('labels.backend.general_settings.language_settings.download_base_language_file') }}
                    </a>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2 form-control-label" for="default_language">
                    {{ __('labels.backend.general_settings.language_settings.default_language') }}
                </label>
                <div class="col-md-10">
                    <select class="form-control" id="app_locale" name="app__locale">
                        @foreach ($app_locales as $lang)
                            <option data-display-type="{{ $lang->display_type }}"
                                value="{{ $lang->short_name }}"
                                @if ($lang->is_default) selected @endif>
                                {{ locale_label($lang->short_name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2 form-control-label" for="display_type">
                    {{ __('labels.backend.general_settings.language_settings.display_type') }}
                </label>
                <div class="col-md-10">
                    <select class="form-control" id="app_display_type" name="app__display_type">
                        <option value="ltr" @if (config('app.display_type') == 'ltr') selected @endif>
                            @lang('labels.backend.general_settings.language_settings.left_to_right')
                        </option>
                        <option value="rtl" @if (config('app.display_type') == 'rtl') selected @endif>
                            @lang('labels.backend.general_settings.language_settings.right_to_left')
                        </option>
                    </select>
                </div>
            </div>

            <hr>

            <h5 class="mb-3">Language Library Management</h5>
            <p class="text-muted">
                Upload translated strings for a language, enable it for frontend selection, and download existing language packs.
            </p>

            <div class="form-group row">
                <label class="col-md-2 form-control-label" for="language_target_locale">Target language</label>
                <div class="col-md-10">
                    <select class="form-control" id="language_target_locale" name="language_target_locale">
                        @foreach ($app_locales as $lang)
                            <option value="{{ $lang->short_name }}">{{ locale_label($lang->short_name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2 form-control-label" for="language_payload_file">Upload JSON file</label>
                <div class="col-md-10">
                    <input type="file" class="form-control" id="language_payload_file" name="language_payload_file" accept=".json,.txt">
                    <small class="form-text text-muted">Supported formats: {"modules": {"module_name": {...}}} or {"module": "module_name", "translations": {...}}</small>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2 form-control-label" for="language_payload_json">Or paste JSON</label>
                <div class="col-md-10">
                    <textarea class="form-control" id="language_payload_json" name="language_payload_json" rows="5" placeholder='{"modules": {"messages": {"welcome": "Bienvenue"}}}'></textarea>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-10 offset-md-2">
                    <button type="submit" class="btn btn-info" name="language_action" value="upload">Upload language strings</button>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Language</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Last upload</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($app_locales as $lang)
                            @php
                                $isEnabled = isset($lang->is_enabled) ? (int) $lang->is_enabled : 1;
                                $toggleTo = $isEnabled ? 0 : 1;
                                $toggleLabel = $isEnabled ? 'Disable' : 'Enable';
                                $isDefault = (int) $lang->is_default === 1;
                            @endphp
                            <tr>
                                <td>{{ $lang->name ?: locale_label($lang->short_name) }}</td>
                                <td>{{ $lang->short_name }}</td>
                                <td>
                                    @if ($isEnabled)
                                        <span class="badge badge-success">Enabled</span>
                                    @else
                                        <span class="badge badge-secondary">Disabled</span>
                                    @endif
                                    @if ($isDefault)
                                        <span class="badge badge-primary">Default</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $lang->library_uploaded_at ? $lang->library_uploaded_at->format('Y-m-d H:i') : 'Never' }}
                                </td>
                                <td>
                                    <div class="language-action-group">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="{{ route('admin.settings.language-library.download', ['locale' => $lang->short_name]) }}">
                                            Download
                                        </a>
                                        <button type="submit"
                                                class="btn btn-sm btn-{{ $isEnabled ? 'outline-warning' : 'success' }}"
                                                name="language_action"
                                                value="toggle:{{ $lang->short_name }}:{{ $toggleTo }}"
                                                @if ($isDefault && $isEnabled) disabled @endif>
                                            {{ $toggleLabel }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <hr class="my-5">

            <h5 class="mb-3">Language Marketplace Workflow</h5>
            <p class="text-muted">
                Publish a source package to docs, invite contributors, collect translated returns, review submissions, and publish approved language packs for download.
            </p>

            <div class="card border-0 bg-light mb-4">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-lg-8">
                            <h6 class="mb-1">1. Publish source package</h6>
                            <div class="text-muted small">
                                Generates a canonical source JSON package for the selected locale, stores it in the internal marketplace, and writes a git-trackable docs copy under <strong>docs/language-library/&lt;locale&gt;.json</strong>.
                            </div>
                        </div>
                        <div class="col-lg-4 mt-3 mt-lg-0">
                            <div class="form-row workflow-publish-controls">
                                <div class="col-5">
                                    <select class="form-control" name="source_locale">
                                        @foreach ($app_locales as $lang)
                                            <option value="{{ $lang->short_name }}" @if ($lang->short_name === 'en') selected @endif>
                                                {{ strtoupper($lang->short_name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-7">
                                    <button type="submit"
                                            class="btn btn-primary btn-block"
                                            formaction="{{ route('admin.settings.language-marketplace.publish-source') }}"
                                            formmethod="POST">
                                        Publish source package
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if ($sourcePackage)
                        <div class="small text-muted mt-3">
                            Latest source package: version {{ $sourcePackage->version }} published {{ optional($sourcePackage->published_at)->format('Y-m-d H:i') }}
                            <a href="{{ route('admin.settings.language-marketplace.packages.download', ['package' => $sourcePackage->id]) }}" class="ml-2">Download</a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 bg-light mb-4">
                <div class="card-body">
                    <h6 class="mb-3">2. Invite contributor</h6>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="invite_locale_code">Target language</label>
                            <select class="form-control" id="invite_locale_code" name="invite_locale_code">
                                @foreach ($app_locales as $lang)
                                    <option value="{{ $lang->short_name }}">{{ locale_label($lang->short_name) }}</option>
                                @endforeach
                                <option value="de">DE</option>
                                <option value="pt">PT</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="contributor_name">Contributor name</label>
                            <input type="text" class="form-control" id="contributor_name" name="contributor_name" placeholder="Translator name">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="contributor_email">Contributor email</label>
                            <input type="email" class="form-control" id="contributor_email" name="contributor_email" placeholder="translator@example.com">
                        </div>
                        <div class="form-group col-md-2 d-flex align-items-end">
                                <button type="submit"
                                    class="btn btn-success btn-block"
                                    formaction="{{ route('admin.settings.language-marketplace.invite') }}"
                                    formmethod="POST">
                                Create invite
                            </button>
                        </div>
                    </div>
                    @if ($sourcePackage)
                        <input type="hidden" name="source_package_id" value="{{ $sourcePackage->id }}">
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <h6 class="mb-3">3. Contributor invitations</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white">
                            <thead>
                                <tr>
                                    <th>Locale</th>
                                    <th>Contributor</th>
                                    <th>Status</th>
                                    <th>Invite link</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($translationInvitations as $invitation)
                                    <tr>
                                        <td>{{ strtoupper($invitation->locale_code) }}</td>
                                        <td>
                                            {{ $invitation->contributor_name ?: 'Contributor' }}<br>
                                            <small class="text-muted">{{ $invitation->contributor_email }}</small>
                                        </td>
                                        <td>{{ ucfirst($invitation->status) }}</td>
                                        <td>
                                            <input type="text" readonly class="form-control form-control-sm"
                                                   value="{{ route('language-marketplace.contribute', ['token' => $invitation->invite_token]) }}">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-muted text-center">No contributor invitations yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <h6 class="mb-3">4. Published marketplace packages</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Locale</th>
                                    <th>Version</th>
                                    <th>GitHub sync</th>
                                    <th>Download</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($publishedLanguagePackages as $package)
                                    <tr>
                                        <td>{{ ucfirst($package->package_type) }}</td>
                                        <td>{{ strtoupper($package->target_locale) }}</td>
                                        <td>{{ $package->version }}</td>
                                        <td>
                                            @php
                                                $syncStatus = $package->github_sync_status ?: 'not_synced';
                                            @endphp
                                            <span class="badge {{ $syncStatus === 'synced' ? 'badge-success' : ($syncStatus === 'failed' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ str_replace('_', ' ', ucfirst($syncStatus)) }}
                                            </span>
                                            @if (!empty($package->github_sync_url))
                                                <a href="{{ $package->github_sync_url }}" target="_blank" rel="noopener" class="d-block mt-1">View file</a>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="language-action-group">
                                                <a class="btn btn-sm btn-outline-primary"
                                                   href="{{ route('admin.settings.language-marketplace.packages.download', ['package' => $package->id]) }}">
                                                    Download
                                                </a>
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-dark"
                                                        formaction="{{ route('admin.settings.language-marketplace.packages.sync-github', ['package' => $package->id]) }}"
                                                        formmethod="POST">
                                                    Sync GitHub
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted text-center">No published packages yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h6 class="mb-3">5. Review queue</h6>
                <div class="table-responsive">
                    <table class="table table-bordered bg-white">
                        <thead>
                            <tr>
                                <th>Locale</th>
                                <th>Submitted</th>
                                <th>Package</th>
                                <th>Reviewer notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingLanguageSubmissions as $package)
                                <tr>
                                    <td>{{ strtoupper($package->target_locale) }}</td>
                                    <td>{{ optional($package->submitted_at)->format('Y-m-d H:i') ?: '-' }}</td>
                                    <td>
                                        <a href="{{ route('admin.settings.language-marketplace.packages.download', ['package' => $package->id]) }}">Download submission</a>
                                    </td>
                                    <td style="min-width:220px;">
                                        <textarea class="form-control form-control-sm" rows="2" name="submission_review_notes[{{ $package->id }}]" placeholder="Optional rejection note"></textarea>
                                    </td>
                                    <td style="min-width:190px;">
                                        <div class="review-action-stack">
                                            <button type="submit"
                                                    class="btn btn-sm btn-success"
                                                    formaction="{{ route('admin.settings.language-marketplace.submissions.approve', ['package' => $package->id]) }}"
                                                    formmethod="POST">
                                                Approve & publish
                                            </button>
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    formaction="{{ route('admin.settings.language-marketplace.submissions.reject', ['package' => $package->id]) }}"
                                                    formmethod="POST">
                                                Reject
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted text-center">No pending translation submissions.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

            </div>
        </div>
    </div>
    </form>
@endsection


@push('after-scripts')
    <script src="{{ asset('plugins/bootstrap-iconpicker/js/bootstrap-iconpicker.bundle.min.js') }}"></script>
    <script>
        $(document).ready(function() {

            @if (request()->has('tab'))
                var tab = "{{ request('tab') }}";
                $('.nav-tabs a[href="#' + tab + '"]').tab('show');
                $('#active_tab').val(tab);
            @endif

            $('.main-nav-tabs a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                var href = $(e.target).attr('href') || '#general';
                $('#active_tab').val(href.replace('#', ''));
            });

            //========= Initialisation for Iconpicker ===========//
            $('#icon').iconpicker({
                cols: 10,
                icon: 'fab fa-facebook-f',
                iconset: 'fontawesome5',
                labelHeader: '{0} of {1} pages',
                labelFooter: '{0} - {1} of {2} icons',
                placement: 'bottom', // Only in button tag
                rows: 5,
                search: true,
                searchText: 'Search',
                selectedClass: 'btn-success',
                unselectedClass: ''
            });


            //========== Preset theme layout ==============//
            @if (config('theme_layout') != '')
                $('#theme_layout').find('option').removeAttr('selected')
                $('#theme_layout').find('option[value="{{ config('theme_layout') }}"]').attr('selected',
                    'selected');
            @endif


            //============ Preset font color ===============//
            @if (config('font_color') != '')
                $('.color-list').find('li a').removeClass('active');
                $('.color-list').find('li a[data-color="{{ config('font_color') }}"]').addClass('active');
                $('#font_color').val("{{ config('font_color') }}");
            @endif


            //========= Preset Layout type =================//
            @if (config('layout_type') != '')
                $('#layout_type').find('option').removeAttr('selected')
                $('#layout_type').find('option[value="{{ config('layout_type') }}"]').attr('selected',
                    'selected');
            @endif


            //=========== Preset Counter data =============//
            @if (config('counter') != '')
                @if ((int) config('counter') == 1)
                    $('.counter-container').removeClass('d-none')
                    $('#total_students').val("{{ config('total_students') }}");
                    $('#total_teachers').val("{{ config('total_teachers') }}");
                    $('#total_courses').val("{{ config('total_courses') }}");
                @else
                    $('#counter-container').empty();
                @endif

                @if (config('counter') != '')
                    $('.counter-container').removeClass('d-none');
                @endif

                $('#counter').find('option').removeAttr('selected')
                $('#counter').find('option[value="{{ config('counter') }}"]').attr('selected', 'selected');
            @endif


            //======== Preset PaymentMode for Paypal =======>
            @if (config('paypal.settings.mode') != '')
                $('#paypal_settings_mode').find('option').removeAttr('selected')
                $('#paypal_settings_mode').find('option[value="{{ config('paypal.settings.mode') }}"]').attr(
                    'selected', 'selected');
            @endif

            //======== Preset PaymentMode for Instamojo =======>
            @if (config('services.instamojo.mode') != '')
                $('#instamojo_settings_mode').find('option').removeAttr('selected')
                $('#instamojo_settings_mode').find('option[value="{{ config('services.instamojo.mode') }}"]')
                    .attr('selected', 'selected');
            @endif

            //======== Preset PaymentMode for Cashfree =======>
            @if (config('services.cashfree.mode') != '')
                $('#cashfree_settings_mode').find('option').removeAttr('selected')
                $('#cashfree_settings_mode').find('option[value="{{ config('services.cashfree.mode') }}"]').attr(
                    'selected', 'selected');
            @endif

            //======== Preset PaymentMode for PayUMoney =======>
            @if (config('services.payu.mode') != '')
                $('#cashfree_settings_mode').find('option').removeAttr('selected')
                $('#cashfree_settings_mode').find('option[value="{{ config('services.payu.mode') }}"]').attr(
                    'selected', 'selected');
            @endif

            //======== Preset PaymentMode for Flutter =======>
            @if (config('rave.env') != '')
                $('#rave_env').find('option').removeAttr('selected')
                $('#rave_env').find('option[value="{{ config('rave.env') }}"]').attr('selected', 'selected');
            @endif


            //============= Font Color selection =================//
            $(document).on('click', '.color-list li', function() {
                $(this).siblings('li').find('a').removeClass('active')
                $(this).find('a').addClass('active');
                $('#font_color').val($(this).find('a').data('color'));
            });


            //============== Captcha status =============//
            $(document).on('click', '#captcha_status', function(e) {
                //              e.preventDefault();
                if ($('#captcha-credentials').hasClass('d-none')) {
                    $('#captcha_status').attr('checked', 'checked');
                    $('#captcha-credentials').find('input').attr('required', true);
                    $('#captcha-credentials').removeClass('d-none');
                } else {
                    $('#captcha-credentials').addClass('d-none');
                    $('#captcha-credentials').find('input').attr('required', false);
                }
            });

            //============== One Signal status =============//
            $(document).on('click', '#onesignal_status', function(e) {
                //              e.preventDefault();
                if ($('#onesignal-configuration').hasClass('d-none')) {
                    console.log('here')
                    $('#onesignal_status').attr('checked', 'checked');
                    $('#onesignal-configuration').removeClass('d-none').find('textarea').attr('required',
                        true);
                } else {
                    $('#onesignal-configuration').addClass('d-none').find('textarea').attr('required',
                        false);
                }
            });


            //===== Counter value on change ==========//
            $(document).on('change', '#counter', function() {
                if ($(this).val() == 1) {
                    $('.counter-container').empty().removeClass('d-none');
                    var html =
                        "<input class='form-control my-2' type='text' id='total_students' name='total_students' placeholder='" +
                        "{{ __('labels.backend.general_settings.total_students') }}" +
                        "'><input type='text' id='total_courses' class='form-control mb-2' name='total_courses' placeholder='" +
                        "{{ __('labels.backend.general_settings.total_courses') }}" +
                        "'><input type='text' class='form-control mb-2' id='total_teachers' name='total_teachers' placeholder='" +
                        "{{ __('labels.backend.general_settings.total_teachers') }}" + "'>";

                    $('.counter-container').append(html);
                } else {
                    $('.counter-container').addClass('d-none');
                }
            });


            //========== Preview image function on upload =============//
            var previewImage = function(input, block) {
                var fileTypes = ['jpg', 'jpeg', 'png', 'gif'];
                var extension = input.files[0].name.split('.').pop().toLowerCase();
                var isSuccess = fileTypes.indexOf(extension) > -1;

                if (isSuccess) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $(block).find('img').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(input.files[0]);
                } else {
                    alert('Please input valid file!');
                }

            };
            $(document).on('change', 'input[type="file"]', function() {
                previewImage(this, $(this).data('preview'));
            });


            //========== Registration fields status =========//
            @if (config('registration_fields') != null)
                var fields = "{{ config('registration_fields') }}";

                fields = JSON.parse(fields.replace(/&quot;/g, '"'));

                $(fields).each(function(key, element) {
                    appendElement(element.type, element.name);
                    $('.input-list').find('[data-name="' + element.name + '"]').attr('checked', true)

                });
            @endif


            //======= Saving settings for All tabs =================//
            $(document).on('submit', '#general-settings-form', function(e) {
                //                e.preventDefault();

                //======Saving Layout sections details=====//
                var sections = $('#sections').find('input[type="checkbox"]');
                var title, name, status;
                var sections_data = {};
                $(sections).each(function() {
                    if ($(this).is(':checked')) {
                        status = 1
                    } else {
                        status = 0
                    }
                    name = $(this).attr('id');
                    title = $(this).parent('label').siblings('.title').html();
                    sections_data[name] = {
                        title: title,
                        status: status
                    }
                });
                $('#section_data').val(JSON.stringify(sections_data));

                //=========Saving Registration fields ===============//
                var inputName, inputType;
                var fieldsData = [];
                var registrationFields = $('.input-list').find('.option:checked');
                $(registrationFields).each(function(key, value) {
                    inputName = $(value).attr('data-name');
                    inputType = $(value).attr('data-type');
                    fieldsData.push({
                        name: inputName,
                        type: inputType
                    });
                });
                $('#registration_fields').val(JSON.stringify(fieldsData));

            });


            //==========Hiding sections on Theme layout option changed ==========//
            $(document).on('change', '#theme_layout', function() {
                var theme_layout = "{{ config('theme_layout') }}";
                if ($(this).val() != theme_layout) {
                    $('#sections').addClass('d-none');
                    $('#sections_note').removeClass('d-none')
                } else {
                    $('#sections').removeClass('d-none');
                    $('#sections_note').addClass('d-none')
                }
            });

            @if (request()->has('tab'))
                var tab = "{{ request('tab') }}";
                $('.nav-tabs a[href="#' + tab + '"]').tab('show');
            @endif

        });

        $(document).on('click', '.switch-input', function(e) {
            //              e.preventDefault();
            var content = $(this).parents('.checkbox').siblings('.switch-content');
            if (content.hasClass('d-none')) {
                $(this).attr('checked', 'checked');
                content.find('input').attr('required', true);
                content.removeClass('d-none');
            } else {
                content.addClass('d-none');
                content.find('input').attr('required', false);
            }
        })


        //On Default language change update Display type RTL/LTR
        $(document).on('change', '#app_locale', function() {
            var display_type = $(this).find(":selected").data('display-type');
            $('#app_display_type').val(display_type)
        });


        //On click add input list
        $(document).on('click', '.input-list input[type="checkbox"]', function() {

            var html;
            var type = $(this).data('type');
            var name = $(this).data('name');
            var textInputs = ['text', 'date', 'number'];
            if ($(this).is(':checked')) {
                appendElement(type, name)
            } else {
                if ((textInputs.includes(type)) || (type == 'textarea')) {
                    $('.input-boxes').find('[data-name="' + name + '"]').parents('.form-group').remove();
                } else if (type == 'radio') {
                    $('.input-boxes').find('.radiogroup').remove();
                }
            }
        });


        //Revoke App Client Secret
        $(document).on('click', '.revoke-api-client', function() {
            var api_id = $(this).data('id');
            $.ajax({
                url: '{{ route('admin.api-client.status') }}',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    'api_id': api_id,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href =
                            '{{ route('admin.general-settings', ['tab' => 'api_client_settings']) }}'

                    } else {
                        alert(
                            "{{ __('labels.backend.general_settings.api_clients.something_went_wrong') }}"
                        );
                    }

                }
            })
        });

        $(document).on('click', '.generate-client', function() {
            var api_client_name = $('#api_client_name').val();

            if ($.trim(api_client_name).length > 0) { // zero-length string AFTER a trim
                $.ajax({
                    url: '{{ route('admin.api-client.generate') }}',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        'api_client_name': api_client_name,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            window.location.href =
                                '{{ route('admin.general-settings', ['tab' => 'api_client_settings']) }}'

                        } else {
                            alert(
                                "{{ __('labels.backend.general_settings.api_clients.something_went_wrong') }}"
                            );
                        }

                    }
                })
            } else {
                $('#api_client_name_error').text(
                    "{{ __('labels.backend.general_settings.api_clients.please_input_api_client_name') }}");
            }

        });

        function appendElement(type, name) {
            var values =
                "{{ json_encode(Lang::get('labels.backend.general_settings.user_registration_settings.fields')) }}";
            values = JSON.parse(values.replace(/&quot;/g, '"'));
            var textInputs = ['text', 'date', 'number'];
            var html;
            if (textInputs.includes(type)) {
                html = "<div class='form-group'>" +
                    "<input type='" + type + "' readonly data-name='" + name + "' placeholder='" + values[name] +
                    "' class='form-control'>" +
                    "</div>";
            } else if (type == 'radio') {
                html = "<div class='form-group radiogroup'>" +
                    "<label class='radio-inline mr-3'><input type='radio' data-name='optradio'> {{ __('labels.backend.general_settings.user_registration_settings.fields.male') }} </label>" +
                    "<label class='radio-inline mr-3'><input type='radio' data-name='optradio'> {{ __('labels.backend.general_settings.user_registration_settings.fields.female') }}</label>" +
                    "<label class='radio-inline mr-3'><input type='radio' data-name='optradio'> {{ __('labels.backend.general_settings.user_registration_settings.fields.other') }}</label>" +
                    "</div>";
            } else if (type == 'textarea') {
                html = "<div class='form-group'>" +
                    "<textarea  readonly data-name='" + name + "' placeholder='" + values[name] +
                    "' class='form-control'></textarea>" +
                    "</div>";
            }
            $('.input-boxes').append(html)
        }

        $('#change-lang').change(function(e) {
            e.preventDefault();
            let params = new URLSearchParams(window.location.search);
            const slug = params.get('slug');
            window.location.href = window.location.origin + window.location.pathname +
                `?&lang=${$(this).val()}`
        });
    </script>
@endpush
