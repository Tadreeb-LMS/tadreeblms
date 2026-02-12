@extends('backend.layouts.app')

@section('title', __('labels.notifications.title').' | '.app_name())

@push('after-styles')
<style>
    .notification-card {
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }
    .notification-card:hover {
        background-color: #f8f9fa;
    }
    .notification-card.unread {
        background-color: #f0f7ff;
        border-left-color: #007bff;
    }
    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .notification-time {
        font-size: 12px;
        color: #6c757d;
    }
</style>
@endpush

@section('content')

<div class="pb-3 d-flex justify-content-between align-items-center">
    <h4>{{ __('labels.notifications.title') }}</h4>
    @if($notifications->where('is_read', false)->count() > 0)
    <form action="{{ route('admin.notifications.mark_all_read') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-check-double mr-1"></i> {{ __('labels.notifications.mark_all_as_read') }}
        </button>
    </form>
    @endif
</div>

<div class="card">
    <div class="card-body">
        @if($notifications->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($notifications as $notification)
                    <div class="list-group-item notification-card {{ !$notification->is_read ? 'unread' : '' }} px-3 py-3">
                        <div class="d-flex align-items-start">
                            <div class="notification-icon bg-{{ $notification->icon_color ?? 'primary' }} bg-opacity-10 mr-3">
                                <i class="fas {{ $notification->icon ?? 'fa-bell' }} text-{{ $notification->icon_color ?? 'primary' }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1 {{ !$notification->is_read ? 'font-weight-bold' : '' }}">
                                            @if($notification->link)
                                                <a href="{{ $notification->link }}" class="text-dark">{{ $notification->title }}</a>
                                            @else
                                                {{ $notification->title }}
                                            @endif
                                        </h6>
                                        @if($notification->message)
                                            <p class="mb-1 text-muted">{{ $notification->message }}</p>
                                        @endif
                                        <small class="notification-time">
                                            <i class="far fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    @if(!$notification->is_read)
                                        <form action="{{ route('admin.notifications.mark_read', $notification->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-link text-primary p-0" title="{{ __('labels.notifications.mark_as_read') }}">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                <p class="text-muted">{{ __('labels.notifications.no_notifications_found') }}</p>
            </div>
        @endif
    </div>
</div>

@stop

@push('after-scripts')
<script>
    // Auto-refresh notifications every 30 seconds
    setTimeout(function() {
        location.reload();
    }, 30000);
</script>
@endpush
