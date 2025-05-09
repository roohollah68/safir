@extends('layout.main')

@section('title', 'اعلان‌ها')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm rounded-lg">
        <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #14697B">
            <h3><i class="fa fa-bell me-2 my-2"></i>اعلان‌های شما</h3>
            <div>
                 <form action="{{ route('notifications.markAllRead') }}" method="POST">
                    @csrf
                     <button type="submit" id="mark-all-read" class="btn btn-outline-light my-auto">خواندن همه</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            @if ($notifications->isEmpty())
                <p class="text-muted text-center">هیچ اعلانی برای نمایش وجود ندارد.</p>
            @else
                <div class="list-group">
                    @foreach ($notifications as $notification)
                        <div class="card mb-2 {{ $notification->read_at ? '' : 'fw-bold' }}" data-notification-id="{{ $notification->id }}">
                            <div class="card-body d-flex justify-content-between align-items-center" style="max-height: 60px">
                                <a href="{{ $notification->link ?? '#' }}" class="text-decoration-none text-dark">
                                    <span style="font-size: 1.1rem">{{ $notification->message }}</span>
                                </a>
                                <div class="d-flex align-items-center">
                                    <small class="text-muted me-2">{{ \Carbon\Carbon::parse($notification->created_at)->locale('fa')->diffForHumans() }}</small>
                                    @if ($notification->read_at === null)
                                        <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm">
                                                <i class="fa fa-check-double" style="font-size: 1.1rem; text-align: center;" title="علامت‌گذاری به عنوان خوانده‌شده"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $notifications->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.getElementById('mark-all-read').addEventListener('click', function() {
        fetch("{{ route('notifications.markAllRead') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(response => {
            window.location.reload();
        });
    });

    document.querySelectorAll('.list-group-item').forEach(item => {
        item.addEventListener('click', function() {
            const notificationId = this.dataset.notificationId;
            fetch(`/notifications/mark-read/${notificationId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
        });
    });
</script>
@endsection