<div class="history-items">
    @php 
        $badgeClass = config('chequeStatus.statusClasses'); 
        $statuses = config('chequeStatus.statuses');
    @endphp
    @isset($history)
        @forelse($history as $record)
            <div class="history-item">
                در تاریخ {{ verta($record->created_at)->formatJalaliDate() }}
                توسط <strong>{{ $record->changer->name ?? 'سیستم' }}</strong>
                از وضعیت <span class="bg-{{ $badgeClass[$record->old_status] }} badge">{{ $statuses[$record->old_status] }}</span>
                به <span class="bg-{{ $badgeClass[$record->new_status] }} badge">{{ $statuses[$record->new_status] }}</span> تغییر یافت.
            </div>
        @empty
            <div class="text-center">تاریخچه ای یافت نشد</div>
        @endforelse
    @endisset
</div>

<style>
.history-items {
    direction: rtl;
    padding: 15px;
}
.history-item {
    margin: 10px 0;
    padding: 8px;
    border-bottom: 1px solid #eee;
}
</style>