<div title="مشاهده سند مدیریت نقدینگی" class="dialogs">
    <br>
    <span>شماره ثبت:</span> <b>{{$bankTransaction->id}}</b> <br>
    <span>کاربر ثبت کننده:</span> <b>{{$bankTransaction->user->name}}</b> <br>
    <span>مبلغ:</span> <b>{{number_format($bankTransaction->amount)}}</b> <br>
    <span>نام واریز کننده:</span> <b>{{$bankTransaction->name}}</b> <br>
    @if($bankTransaction->bankSource)
        <span>بانک مبدا:</span> <b>{{$bankTransaction->bankSource->name}}</b> <br>
    @endif
    <span>بانک مقصد:</span> <b>{{$bankTransaction->bank->name}}</b> <br>

    <span>توضیحات:</span> <b>{!! $bankTransaction->description !!}</b> <br><br>
    @if($bankTransaction->receipt)
        <span>رسید انتقال وجه:</span>
        <a class="btn btn-info" href="/withdrawal/{{$bankTransaction->receipt}}" target="_blank">
            مشاهده فایل
        </a>
        <br>
    @endif
    @if($bankTransaction->receipt2)
        <span>رسید صورت جلسه شرکت بابت قرض:</span>
        <a class="btn btn-info" href="/withdrawal/{{$bankTransaction->receipt2}}" target="_blank">
            مشاهده فایل
        </a>
        <br>
    @endif

</div>
