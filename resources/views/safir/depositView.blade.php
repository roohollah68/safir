<div title="مشاهده پرداخت وجه" class="dialogs">
    <a href="/deposit/{{$deposit->photo}}" target="_blank">
        <img style="width: 300px" src="/deposit/{{$deposit->photo}}">
    </a>
    <br>
    <span>سفیر:</span> <b>{{$deposit->user->name}}</b> <br>
    <span>شماره واریز:</span> <b>{{$deposit->id}}</b> <br>
    <span>مبلغ:</span> <b>{{number_format($deposit->amount)}}</b> <br>
    <span>وضعیت:</span> <b>{{ $deposit->confirmed?'تائید':'عدم تائید' }}</b> <br>
    <span>توضیحات:</span> <b>{{$deposit->desc}}</b> <br>
    <span>زمان ثبت:</span><b>{{verta($deposit->created_at)->formatJalaliDate()}}</b><br>
    <span>زمان ویرایش:</span><b>{{verta($deposit->updated_at)->formatJalaliDate()}}</b><br>
</div>
