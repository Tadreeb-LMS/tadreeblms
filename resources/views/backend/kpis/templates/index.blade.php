@extends('backend.layouts.app')

@section('title', 'KPI Templates | ' . app_name())

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <h4 class="mb-0">KPI Templates</h4>
        <a href="{{ route('admin.kpis.index') }}" class="btn btn-secondary">&larr; Back to KPIs</a>
    </div>

    <div class="card mb-4 template-intro-card">
        <div class="card-body small">
            <strong>Quick Setup with Templates</strong> — Select a predefined template matching your use case. Templates are blueprints: you can safely configure and review first, and live KPIs are created only when you click <strong>Apply This Template</strong>.
        </div>
    </div>

    @if($canCreate)
        <div class="card mb-4 template-create-section">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Create Custom Template</strong>
                <a href="{{ route('admin.kpi-templates.create') }}" class="btn btn-primary btn-sm" target="_blank" rel="noopener">Open Blueprint Editor</a>
            </div>
            <div class="card-body">
                <p class="mb-0 text-muted small">Template creation is in a dedicated editor window/tab for easier setup. Saving there stores a blueprint only; live KPIs are created later on apply.</p>
            </div>
        </div>
    @endif

    @php
        $allTemplates = $templates->flatten(1);
    @endphp

    @if($allTemplates->isNotEmpty())
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Available Templates</h5>
            <small class="text-muted">{{ $allTemplates->count() }} template{{ $allTemplates->count() !== 1 ? 's' : '' }}</small>
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
                            <small class="template-meta">{{ $template->item_count }} KPI{{ $template->item_count !== 1 ? 's' : '' }}</small>
                        </div>
                        <div class="card-body">
                            <p class="template-description small">{{ $template->description }}</p>
                            <p class="template-use-case" style="font-size: 0.9rem;">
                                <strong>Use case:</strong> {{ $template->use_case ?: 'N/A' }}
                            </p>
                        </div>
                        <div class="card-footer bg-white border-top">
                            <a href="{{ route('admin.kpi-templates.show', $template->id) }}" class="btn btn-sm btn-outline-primary w-100">
                                Preview & Apply
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info">
            No templates available at this time. Check back soon or contact your administrator.
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
