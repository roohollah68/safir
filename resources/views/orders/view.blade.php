<div title="مشاهده سفارش" class="dialogs">
    @if($order->receipt)
        <a href="receipt/{{$order->receipt}}" target="_blank"><img style="width: 300px"
                                                                   src="receipt/{{$order->receipt}}"></a>
    @endif
    <span>نام و نام خانوادگی:</span> <b>{{$order->name}}</b> <br>
    <span>شماره تماس:</span> <b>{{$order->phone}}</b> <br>
    <span>آدرس:</span> <b>{{$order->address}}</b> <br>
    <span>کد پستی:</span> <b>{{$order->zip_code}}</b> <br>
    <span>سفارشات:</span> <b>{{$order->orders}}</b> <br>
    <span>مبلغ کل:</span> <b>{{number_format($order->total)}}</b> <b> ریال</b> <br>
    <span>پرداختی مشتری:</span> <b>{{number_format($order->customerCost)}}</b> <b> ریال</b> <br>
    <span>نحوه پرداخت:</span> <b>{{$order->payMethod()}}</b> <br>
    <span>نحوه ارسال:</span> <b>{{$order->sendMethod()}}</b> <br>
    <span>توضیحات:</span> <b>{{$order->desc}}</b> <br>
    <span>زمان ثبت:</span> <b>{{verta($order->created_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</b>
    <br>
    <span>زمان آخرین ویرایش:</span>
    <b>{{verta($order->updated_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</b> <br>

    @if($order->deleted_at)
        <span>زمان حذف:</span> <b>{{verta($order->deleted_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</b>
        <br>
    @endif
</div>
