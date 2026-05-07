@extends('backend.layouts.app')

@section('title', __('kpi.titles.templates') . ' | ' . app_name())

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <h4 class="mb-0">@lang('kpi.titles.templates')</h4>
        <a href="{{ route('admin.kpis.index') }}" class="btn btn-secondary">&larr; @lang('kpi.actions.back_to_kpis')</a>
    </div>

    <div class="card mb-4 template-intro-card">
        <div class="card-body small">
            <strong>@lang('kpi.messages.quick_setup_templates')</strong> &mdash; @lang('kpi.help.template_quick_setup')
        </div>
    </div>

    @if($canCreate)
        <div class="card mb-4 template-create-section">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>@lang('kpi.messages.create_custom_template')</strong>
                <a href="{{ route('admin.kpi-templates.create') }}" class="btn btn-primary btn-sm" target="_blank" rel="noopener">@lang('kpi.actions.open_blueprint_editor')</a>
            </div>
            <div class="card-body">
                <p class="mb-0 text-muted small">@lang('kpi.help.template_creation_note')</p>
            </div>
        </div>
    @endif

    @php
        $allTemplates = $templates->flatten(1);
    @endphp

    @if($allTemplates->isNotEmpty())
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">@lang('kpi.messages.available_templates')</h5>
            <small class="text-muted">{{ __('kpi.messages.template_count', ['count' => $allTemplates->count()]) }}</small>
        </div>
        <div class="row">
            @foreach($allTemplates as $template)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm hover-lift" style="transition: transform 0.2s; cursor: pointer;">
                        <div class="card-header template-card-header border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="mb-1 template-title">{{ $template->name }}</h6>
                                <span class="badge template-category-badge text-uppercase">{{ str_replace('_', ' ', $template->category) }}</span>
                            </div>
                                <small class="template-meta">{{ __('kpi.messages.kpi_count', ['count' => $template->item_count]) }}</small>
                        </div>
                        <div class="card-body">
                            <p class="template-description small">{{ $template->description }}</p>
                            <p class="template-use-case" style="font-size: 0.9rem;">
                                <strong>@lang('kpi.labels.use_case'):</strong> {{ $template->use_case ?: __('kpi.states.not_applicable') }}
                            </p>
                        </div>
                        <div class="card-footer bg-white border-top">
                            <a href="{{ route('admin.kpi-templates.show', $template->id) }}" class="btn btn-sm btn-outline-primary w-100">
                                @lang('kpi.actions.preview_apply')
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info">
            @lang('kpi.messages.no_templates')
        </div>
    @endif

    <style>
        .template-intro-card .card-body {
            color: #2f3b52;
            background: #f8fafc;
        }

        .template-card-header {
            background: #f3f6fa;
        }

        .template-title {
            color: #1f2937;
            font-weight: 600;
        }

        .template-meta {
            color: #4b5563;
            font-weight: 500;
        }

        .template-description {
            color: #374151;
            line-height: 1.5;
        }

        .template-use-case {
            color: #1f2937;
            line-height: 1.45;
            margin-bottom: 0;
        }

        .template-category-badge {
            background: #dbeafe;
            color: #1e3a8a;
            border: 1px solid #bfdbfe;
            font-size: 0.7rem;
            letter-spacing: 0.02em;
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
        }

        .template-create-section .card-header {
            background: #eef4ff;
            color: #1e3a8a;
        }
    </style>
@endsection
