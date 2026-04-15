@extends('backend.layouts.app')

@section('title', 'KPI Management | ' . app_name())

@section('content')
    <div class="d-flex justify-content-between align-items-center pb-3">
        <h4 class="mb-0">KPI Management</h4>
        <a href="{{ route('admin.kpis.create') }}" class="add-btn">Add KPI</a>
    </div>

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
                </div>
            </form>

            <div id="kpi-results">
                @include('backend.kpis.partials.table', ['kpis' => $kpis, 'kpiTypes' => $kpiTypes])
            </div>
        </div>
    </div>

    <script>
        (function () {
            var form = document.querySelector('form[action="{{ route('admin.kpis.index') }}"]');
            var searchInput = document.getElementById('kpi-search');
            var resultsContainer = document.getElementById('kpi-results');
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
    </script>
@endsection
