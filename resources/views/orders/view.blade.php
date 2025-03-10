<div title="مشاهده سفارش" class="dialogs">
    @if($order->receipt)
        <a href="receipt/{{$order->receipt}}" target="_blank">
            <img style="width: 300px" src="receipt/{{$order->receipt}}">
        </a>
    @endif
    <br>
    <span>شماره سفارش:</span> <b>{{$order->id}}</b> <br>
    <span>نام و نام خانوادگی:</span> <b>{{$order->name}}</b> <br>
    <span>شماره تماس:</span> <b>{{$order->phone}}</b> <br>
    <span>آدرس:</span> <b>{{$order->address}}</b> <br>
    @if($order->zip_code)
        <span>کد پستی:</span> <b>{{$order->zip_code}}</b> <br>
    @endif
    <span>سفارشات:</span> <b>{{$order->orders()}}</b> <br>
    <span>مبلغ کل:</span> <b dir="ltr">{{number_format($order->total)}}</b> <b> ریال</b> <br>
    @if($order->customerCost > 0)
        <span>پرداختی مشتری:</span> <b>{{number_format($order->customerCost)}}</b> <b> ریال</b> <br>
    @endif
    @if($order->payMethod())
        <span>نحوه پرداخت:</span> <b>{{$order->payMethod()}}</b> <br>
    @endif
    @if($order->payInDate)
        <span>تاریخ پرداخت:</span> <b>{{verta($order->payInDate)->timezone('Asia/tehran')->format('Y/m/d')}}</b> <br>
    @endif
    @if($order->paymentNote)
        <span>توضیح پرداخت:</span> <b>{{$order->paymentNote}}</b> <br>
    @endif
    <span>درصد پرداخت شده: </span><b>{{$order->payPercent()}} %</b> <br>
    @if(isset($order->customer) && $order->customer->agreement)
        <span>تفاهم: </span><b>{{$order->customer->agreement}}</b><br>
    @endif
    @if($order->sendMethod())
        <span>نحوه ارسال:</span> <b>{{$order->sendMethod()}}</b> <br>
    @endif
    <span>انبار:</span> <b>{{$order->warehouse?$order->warehouse->name:'نامشخص'}}</b> <br>
    @if($order->desc)
        <span>توضیحات:</span> <b>{{$order->desc}}</b> <br>
    @endif
    <span>ثبت کننده:</span> <b>{{$order->user()->first()->name}}</b> <br>
    <span>زمان ثبت:</span> <b dir="ltr">{{verta($order->created_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</b>
    <br>
    <span>زمان آخرین ویرایش:</span>
    <b dir="ltr">{{verta($order->updated_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</b> <br>

    @if($order->deleted_at)
        <span>زمان حذف:</span>
        <b dir="ltr">{{verta($order->deleted_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</b><br>
    @endif
    @if($order->confirmed_at)
        <span>زمان تائید کاربر:</span>
        <b dir="ltr">{{verta($order->confirmed_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</b><br>
    @endif
    @if($order->processed_at)
        <span>زمان شروع پردازش:</span>
        <b dir="ltr">{{verta($order->processed_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</b><br>
    @endif
    @if($order->sent_at)
        <span>زمان ارسال:</span>
        <b dir="ltr">{{verta($order->sent_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</b><br>
    @endif
</div>
