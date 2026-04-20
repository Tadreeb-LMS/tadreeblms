@extends('backend.layouts.app')

@section('content')

@push('after-styles')
<style>
table th,
table td {
    text-align: center !important;
    vertical-align: middle !important;
}

td .btn {
    display: inline-block;
    margin: 2px;
}

td {
    white-space: nowrap;
}
/* Reduce button size only in Language Settings table */
table td .btn {
    padding: 4px 8px !important;
    font-size: 12px !important;
    line-height: 1.2 !important;
        border: none !important;
    box-shadow: none !important;

}

/* Optional: make buttons even tighter */
table td .btn-sm {
    padding: 3px 6px !important;
    font-size: 10px !important;
}
</style>
@endpush


<div class="card shadow-sm">
    <div class="card-header">
        <h4 class="mb-0">Language Settings</h4>
    </div>

    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        <!-- Add Language -->
        <div class="mb-4">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                         @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('admin.languages.store') }}" class="row g-2">
                @csrf
                <div class="col-md-4">
                    <input type="text" name="name" class="form-control" placeholder="Language Name" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="code" class="form-control" placeholder="Code (en, ar)" required>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Add</button>
                </div>
            </form>
        </div>

        <!-- Upload -->
        <div class="mb-4">
            <form method="POST" action="{{ route('admin.languages.upload') }}" enctype="multipart/form-data" class="row g-2">
                @csrf
                <div class="col-md-4">
                    <input type="text" name="code" class="form-control" placeholder="Language Code" required>
                </div>
                <div class="col-md-4">
                    <input type="file" name="file" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success w-100">Upload</button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Status</th>
                        <th width="180">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($languages as $lang)
                        <tr>
                            <td>{{ $lang->name }}</td>
                            <td>{{ $lang->code }}</td>
                            <td>
                                <span class="badge {{ $lang->is_enabled ? 'bg-success' : 'bg-danger' }}">
                                    {{ $lang->is_enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.languages.toggle', $lang->id) }}" class="btn btn-sm btn-info   ">
                                    Toggle
                                </a>
                                <a href="{{ route('admin.languages.download', $lang->code) }}" class="btn btn-sm btn-info">
                                    Download
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection