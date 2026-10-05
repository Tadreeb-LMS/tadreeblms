@if(session()->has('flash_success') || session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('flash_success', session('success')) }}

        <button type="button"
                class="close"
                data-dismiss="alert"
                aria-label="Close">
            <span>&times;</span>
        </button>
    </div>
@endif

@if (session()->has('error') || session()->has('flash_danger'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error', session('flash_danger')) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session()->has('warning') || session()->has('flash_warning'))
    <div class="alert alert-warning alert-dismissible fade show">
        {{ session('warning', session('flash_warning')) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session()->has('info') || session()->has('flash_info'))
    <div class="alert alert-info alert-dismissible fade show">
        {{ session('info', session('flash_info')) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
