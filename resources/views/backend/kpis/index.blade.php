@extends('backend.layouts.app')

@section('title', 'KPI Management | ' . app_name())

@push('after-styles')
    <style>
        .kpi-action-toolbar {
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .kpi-action-btn {
            background-color: #ffffff;
            border: 1px solid #b8c3ce;
            color: #2f4052;
            font-weight: 600;
        }

        .kpi-action-btn:hover,
        .kpi-action-btn:focus {
            background-color: #f1f5f9;
            border-color: #8fa4b8;
            color: #1f2f3f;
            box-shadow: 0 0 0 0.2rem rgba(143, 164, 184, 0.2);
        }

        .kpi-action-btn:focus {
            outline: 0;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <h4 class="mb-0">KPI Management</h4>

          <div class="d-flex align-items-center kpi-action-toolbar">
            @can('category_access')
                <a href="{{ route('admin.categories.index') }}" class="btn kpi-action-btn">Manage Categories</a>
            @endcan

            @can('kpi_template_access')
                <a href="{{ route('admin.kpi-templates.index') }}" class="btn kpi-action-btn">Templates</a>
            @endcan

            <a href="{{ route('admin.kpis.team-insights') }}" class="btn kpi-action-btn">
                Team Insights
            </a>

            <button type="button" class="btn kpi-action-btn" data-toggle="modal" data-target="#kpiExportModal">
                Export KPI Data
            </button>

            @can('kpi_target_access')
                <a href="{{ route('admin.kpi-targets.index') }}" class="btn kpi-action-btn">Manage Targets</a>
            @endcan

            @can('kpi_create')
                <a href="{{ route('admin.kpis.create') }}" class="add-btn">Add KPI</a>
            @endcan
        </div>
    </div>

    @cannot('kpi_edit')
        <div class="alert alert-secondary">
            Read-only mode: you can view KPI data, but only authorized users can create, edit, activate, or archive KPIs.
        </div>
    @endcannot

    <div class="alert alert-info">
        Active KPI total weight: <strong id="kpi-active-total-weight">{{ number_format($totalActiveWeight, 2) }}</strong>
    </div>

    @if($weightInsights['zero_weight_count'] > 0)
        <div class="alert alert-warning">
            {{ $weightInsights['zero_weight_count'] }} active KPI(s) currently have zero weight and will contribute 0 to weighted scoring.
        </div>
    @endif

    @if($weightInsights['extreme_weight_count'] > 0)
        <div class="alert alert-warning">
            {{ $weightInsights['extreme_weight_count'] }} active KPI(s) are at or above the extreme-weight threshold. Review distribution to avoid over-concentration.
        </div>
    @endif

    @if(!$weightInsights['validation_enabled'] && !$weightInsights['is_total_on_target'])
        <div class="alert alert-secondary">
            Optional guidance: active total weight differs from target {{ number_format($weightInsights['target'], 2) }}. Enable strict total-weight validation in KPI config if required.
        </div>
    @endif

    <div id="kpi-category-groups" class="mb-3">
        @include('backend.kpis.partials.category_groups', ['kpiCategoryGroups' => $kpiCategoryGroups])
    </div>

    <p class="text-muted small">Click a sortable header to cycle through ascending, descending, and off. Multiple active sorts are applied automatically.</p>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.kpis.index') }}" class="mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <input
                            type="text"
                            id="kpi-search"
                            name="search"
                            class="form-control"
                            placeholder="Search by name, code, or type"
                            value="{{ request('search') }}"
                            autocomplete="off"
                        >
                        <small class="form-text text-muted">Instant search enabled</small>
                    </div>
                    <div class="col-md-4">
                        <select id="kpi-category-filter" name="category_id" class="form-control">
                            <option value="">All mapped categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Filter KPIs by mapped category</small>
                    </div>
                </div>
            </form>

            <div id="kpi-results">
                @include('backend.kpis.partials.table', ['kpis' => $kpis, 'kpiTypes' => $kpiTypes])
            </div>
        </div>
    </div>

    <div class="modal fade" id="kpiExportModal" tabindex="-1" role="dialog" aria-labelledby="kpiExportModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="kpiExportModalLabel">Export KPI Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="kpi-export-form">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="kpi-export-role">Role</label>
                            <select id="kpi-export-role" name="role" class="form-control">
                                <option value="">All roles</option>
                                @foreach($exportRoles as $roleName)
                                    <option value="{{ $roleName }}">{{ $roleName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="kpi-export-date-from">Date From</label>
                                <input id="kpi-export-date-from" type="date" name="date_from" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="kpi-export-date-to">Date To</label>
                                <input id="kpi-export-date-to" type="date" name="date_to" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="kpi-export-kpis">KPI Filter</label>
                            <select id="kpi-export-kpis" name="kpi_ids[]" class="form-control" multiple size="6">
                                @foreach($exportKpis as $exportKpi)
                                    <option value="{{ $exportKpi->id }}">{{ $exportKpi->name }} ({{ $exportKpi->code }})</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Leave empty to export all active KPIs.</small>
                        </div>

                        <div class="form-group mb-0">
                            <label for="kpi-export-format">Format</label>
                            <select id="kpi-export-format" name="format" class="form-control" required>
                                <option value="csv" selected>CSV</option>
                                <option value="xlsx">Excel (XLSX)</option>
                            </select>
                        </div>

                        <div id="kpi-export-status" class="alert alert-info mt-3 d-none mb-0"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" id="kpi-export-submit" class="btn btn-primary">Start Export</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var form = document.querySelector('form[action="{{ route('admin.kpis.index') }}"]');
            var searchInput = document.getElementById('kpi-search');
            var categoryInput = document.getElementById('kpi-category-filter');
            var resultsContainer = document.getElementById('kpi-results');
            var groupsContainer = document.getElementById('kpi-category-groups');
            var totalWeightEl = document.getElementById('kpi-active-total-weight');
            var timer = null;
            var activeRequest = null;
            var sorts = [];

            if (!form || !searchInput || !resultsContainer) {
                return;
            }

            function readSortsFromUrl() {
                var url = new URL(window.location.href);
                var by = url.searchParams.getAll('sort_by[]');
                var dir = url.searchParams.getAll('sort_dir[]');

                sorts = by.map(function (column, index) {
                    return {
                        by: column,
                        dir: (dir[index] || 'asc') === 'desc' ? 'desc' : 'asc'
                    };
                });
            }

            readSortsFromUrl();

            function requestAndRender(url) {
                if (activeRequest) {
                    activeRequest.abort();
                }

                activeRequest = new XMLHttpRequest();
                activeRequest.open('GET', url, true);
                activeRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                activeRequest.onreadystatechange = function () {
                    if (activeRequest.readyState !== 4) {
                        return;
                    }

                    if (activeRequest.status < 200 || activeRequest.status >= 300) {
                        return;
                    }

                    var response = JSON.parse(activeRequest.responseText);
                    resultsContainer.innerHTML = response.html;

                    if (groupsContainer && response.groupedHtml) {
                        groupsContainer.innerHTML = response.groupedHtml;
                    }

                    if (totalWeightEl && response.totalActiveWeight) {
                        totalWeightEl.textContent = response.totalActiveWeight;
                    }

                    if (window.history && window.history.replaceState) {
                        window.history.replaceState({}, '', url);
                    }
                };
                activeRequest.send();
            }

            function buildUrl(pageUrl) {
                if (pageUrl) {
                    var parsed = new URL(pageUrl, window.location.origin);
                    var currentSearch = searchInput.value.trim();

                    if (currentSearch.length > 0) {
                        parsed.searchParams.set('search', currentSearch);
                    } else {
                        parsed.searchParams.delete('search');
                    }

                    if (categoryInput && categoryInput.value) {
                        parsed.searchParams.set('category_id', categoryInput.value);
                    } else {
                        parsed.searchParams.delete('category_id');
                    }

                    parsed.searchParams.delete('sort_by[]');
                    parsed.searchParams.delete('sort_dir[]');

                    sorts.forEach(function (sort) {
                        parsed.searchParams.append('sort_by[]', sort.by);
                        parsed.searchParams.append('sort_dir[]', sort.dir);
                    });

                    return parsed.toString();
                }

                var url = new URL(form.action, window.location.origin);
                var value = searchInput.value.trim();

                if (value.length > 0) {
                    url.searchParams.set('search', value);
                }

                if (categoryInput && categoryInput.value) {
                    url.searchParams.set('category_id', categoryInput.value);
                }

                sorts.forEach(function (sort) {
                    url.searchParams.append('sort_by[]', sort.by);
                    url.searchParams.append('sort_dir[]', sort.dir);
                });

                return url.toString();
            }

            function updateSortState(column) {
                var existingIndex = sorts.findIndex(function (sort) {
                    return sort.by === column;
                });

                if (existingIndex === -1) {
                    sorts.push({ by: column, dir: 'asc' });
                    return;
                }

                if (sorts[existingIndex].dir === 'asc') {
                    sorts[existingIndex].dir = 'desc';
                    return;
                }

                sorts.splice(existingIndex, 1);
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                requestAndRender(buildUrl());
            });

            searchInput.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    requestAndRender(buildUrl());
                }, 300);
            });

            if (categoryInput) {
                categoryInput.addEventListener('change', function () {
                    requestAndRender(buildUrl());
                });
            }

            document.addEventListener('click', function (e) {
                var link = e.target.closest('#kpi-results .pagination a');

                if (!link) {
                    var sortButton = e.target.closest('#kpi-results .js-kpi-sort');

                    if (!sortButton) {
                        return;
                    }

                    e.preventDefault();
                    updateSortState(sortButton.getAttribute('data-sort-column'));
                    requestAndRender(buildUrl());
                    return;
                }

                e.preventDefault();
                requestAndRender(buildUrl(link.getAttribute('href')));
            });
        })();

        (function () {
            var exportForm = document.getElementById('kpi-export-form');
            var submitButton = document.getElementById('kpi-export-submit');
            var statusBox = document.getElementById('kpi-export-status');
            var pollingHandle = null;
            var downloadedExportId = null;

            if (!exportForm || !submitButton || !statusBox) {
                return;
            }

            function showStatus(message, type) {
                statusBox.classList.remove('d-none', 'alert-info', 'alert-success', 'alert-danger', 'alert-warning');
                statusBox.classList.add(type || 'alert-info');
                statusBox.innerHTML = message;
            }

            function setSubmitting(isSubmitting) {
                submitButton.disabled = isSubmitting;
                submitButton.textContent = isSubmitting ? 'Preparing...' : 'Start Export';
            }

            function triggerDownload(url) {
                if (!url) {
                    return;
                }

                var link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', '');
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            function getExportFormData() {
                var formData = new FormData(exportForm);
                return formData;
            }

            function startPolling(exportId) {
                if (pollingHandle) {
                    clearInterval(pollingHandle);
                }

                pollingHandle = setInterval(function () {
                    fetch('{{ route('admin.kpis.exports.status', ['export' => '__id__']) }}'.replace('__id__', String(exportId)), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        if (!data || data.status !== true) {
                            return;
                        }

                        var progressText = 'Progress: ' + (data.progress || 0) + '%';
                        var rowsText = '';
                        if ((data.total_rows || 0) > 0) {
                            rowsText = ' (' + (data.processed_rows || 0) + ' / ' + data.total_rows + ' rows)';
                        }

                        if (data.state === 'completed') {
                            clearInterval(pollingHandle);
                            pollingHandle = null;
                            setSubmitting(false);

                            if (downloadedExportId !== data.export_id) {
                                triggerDownload(data.download_link);
                                downloadedExportId = data.export_id;
                            }

                            showStatus('Export completed and download started automatically.', 'alert-success');
                            return;
                        }

                        if (data.state === 'failed') {
                            clearInterval(pollingHandle);
                            pollingHandle = null;
                            setSubmitting(false);
                            showStatus('Export failed: ' + (data.error_message || 'Unknown error'), 'alert-danger');
                            return;
                        }

                        showStatus('Export is running. ' + progressText + rowsText, 'alert-info');
                    })
                    .catch(function () {
                        showStatus('Unable to fetch export status. Retrying...', 'alert-warning');
                    });
                }, 2500);
            }

            exportForm.addEventListener('submit', function (e) {
                e.preventDefault();

                setSubmitting(true);
                showStatus('Export request submitted. Queueing job...', 'alert-info');

                fetch('{{ route('admin.kpis.exports.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: getExportFormData()
                })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (!data || data.status !== true) {
                        throw new Error('Invalid export response');
                    }

                    showStatus(data.message + ' Tracking progress...', 'alert-info');
                    startPolling(data.export_id);
                })
                .catch(function () {
                    setSubmitting(false);
                    showStatus('Could not start KPI export. Please check your filters and try again.', 'alert-danger');
                });
            });
        })();
    </script>
@endsection