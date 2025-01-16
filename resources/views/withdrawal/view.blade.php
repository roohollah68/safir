<div title="مشاهده درخواست وجه" class="dialogs">
    @if($withdrawal->user_file)
        <a class="btn btn-info" href="/withdrawal/{{$withdrawal->user_file}}" target="_blank">مشاهده فایل</a>
    @endif
    <br>
    <span>شماره درخواست:</span> <b>{{$withdrawal->id}}</b> <br>
    <span>کاربر ثبت کننده:</span> <b>{{$withdrawal->user->name}}</b> <br>
    <span>مبلغ درخواستی:</span> <b>{{number_format($withdrawal->amount)}}</b> <br>
    <span>بابت:</span> <b>{{$withdrawal->expense}}</b> <br>
    <span>مکان درخواست:</span> <b>{{config('withdrawalLocation')[$withdrawal->location]}}</b> <br>
    <span>توضیحات کاربر:</span> <b>{{$withdrawal->user_desc}}</b> <br>
    <span>روش پرداخت:</span> <b>{{$withdrawal->pay_method=='cash'?'نقدی':'چکی'}}</b> <br>
    <span>صاحب حساب یا دریافت کنند چک:</span> <b>{{$withdrawal->account_name}}</b> <br>
    @if($withdrawal->pay_method=='cash')
        <span>شماره شبا یا کارت:</span> <b>{{$withdrawal->account_number}}</b> <br>
    @else
        <span>کد ملی یا شناسه ملی:</span> <b>{{$withdrawal->cheque_id}}</b> <br>
        <span>تاریخ چک:</span> <b>{{verta($withdrawal->cheque_date)->formatJalaliDate()}}</b> <br>
    @endif
    <span>دسته هزینه:</span> <b>{{$withdrawal->expense_type=='current'?'هزینه':'دارایی'}}</b> <br>
    <span>نوع هزینه:</span> <b>{{$withdrawal->expense_desc}}</b> <br>
    <span>نوع فاکتور:</span>
    <b>{{$withdrawal->official != 1?'غیر رسمی':('رسمی '.($withdrawal->vat == 1?'با ارزش افزوده':'بدون ارزش افزوده'))}}</b>
    <br>
    @if($withdrawal->deleted_at)
        <span>زمان حذف:</span><b>{{verta($withdrawal->deleted_at)->formatJalaliDate()}}</b><br>
    @endif
    <span>تایید حسابداری:</span> <b>{!! $withdrawal->counter_status() !!}</b> <br>
    <span>توضیحات حسابداری:</span> <b>{{$withdrawal->counter_desc}}</b> <br>
    <span>بانک پرداخت کننده:</span> <b>{{(isset($withdrawal->bank))?$withdrawal->bank->name:'نامشخص'}}</b>
    <hr>
    @if($withdrawal->counter_confirm != 0)
        <span>تایید مدیر:</span> <b>{!! $withdrawal->manager_status() !!}</b> <br>
        <span>توضیحات مدیر:</span> <b>{{$withdrawal->manager_desc}}</b>
        <hr>
    @endif
    @if($withdrawal->counter_confirm != 0 && $withdrawal->manager_confirm != 0)
        <span>تایید پرداخت:</span> <b>{!! $withdrawal->payment_status() !!}</b> <br>
        <span>توضیحات پرداخت:</span> <b>{{$withdrawal->payment_desc}}</b> <br>
        @if($withdrawal->payment_file)
            <span>رسید پرداخت:</span> <b><a href="/withdrawal/{{$withdrawal->payment_file}}" target="_blank">مشاهده
                    فایل</a></b>
            <br>
        @endif
        @if($withdrawal->payment_file2)
            <span>رسید پرداخت2:</span> <b><a href="/withdrawal/{{$withdrawal->payment_file2}}" target="_blank">مشاهده
                    فایل</a></b>
            <br>
        @endif
        @if($withdrawal->payment_file3)
            <span>رسید پرداخت3:</span> <b><a href="/withdrawal/{{$withdrawal->payment_file3}}" target="_blank">مشاهده
                    فایل</a></b>
            <br>
        @endif
        <hr>
    @endif
    @if($withdrawal->counter_confirm != 0 && $withdrawal->manager_confirm != 0 && $withdrawal->payment_confirm != 0)
        <span>تایید دریافت کالا یا خدمات:</span> <b>{!! $withdrawal->recipient_status() !!}</b> <br>
        <span>توضیحات دریافت:</span> <b>{{$withdrawal->recipient_desc}}</b> <br>
        @if($withdrawal->recipient_file)
            <span>رسید دریافت:</span> <b><a href="/withdrawal/{{$withdrawal->recipient_file}}" target="_blank">مشاهده
                    فایل</a></b>
            <br>
        @endif
    @endif
</div>
