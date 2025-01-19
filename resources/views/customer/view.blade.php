<div title="مشاهده پرداخت وجه" class="dialogs">
    <a href="/deposit/{{$deposit->photo}}" target="_blank">
        <img style="width: 300px" src="/deposit/{{$deposit->photo}}">
    </a>
    <br>
    <span>کاربر مرتبط:</span> <b>{{$deposit->customer->user->name}}</b> <br>
    <span>نام مشتری:</span> <b>{{$deposit->customer->name}}</b> <br>
    <span>شماره مشتری:</span> <b>{{$deposit->customer_id}}</b> <br>
    <span>شماره واریز:</span> <b>{{$deposit->id}}</b> <br>
    <span>مبلغ:</span> <b>{{number_format($deposit->amount)}}</b> <br>
    <span>روش پرداخت:</span> <b>{{config('payMethods')[$deposit->pay_method]}}</b> <br>
    @if($deposit->pay_method=='cash')
        <span>بانک:</span> <b>{{$deposit->bank_id?$deposit->bank->name:'نا مشخص'}}</b> <br>
    @elseif($deposit->pay_method=='cheque')
        <span>نام صاحب چک:</span> <b>{{$deposit->cheque_name}}</b> <br>
        <span>تاریخ چک:</span> <b>{{verta($deposit->cheque_date)->formatJalaliDate()}}</b> <br>
        <span>کد 16 رقمی چک:</span> <b dir="ltr">{{wordwrap($deposit->cheque_code , 4 , ' ' , true )}}</b> <br>
    @endif
    <span>وضعیت:</span> <b>{!! $deposit->verified() !!}</b> <br>
    <span>توضیحات:</span> <b>{{$deposit->description}}</b> <br>
    <span>زمان ثبت:</span><b>{{verta($deposit->created_at)->formatJalaliDate()}}</b><br>
    <span>زمان ویرایش:</span><b>{{verta($deposit->updated_at)->formatJalaliDate()}}</b><br>
</div>
