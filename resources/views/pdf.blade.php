<html lang="fa" dir="rtl">
<div style="line-height: 1.5rem" >
    <span>نام و نام خانوادگی </span>: <b>{{$order->name}}</b> <br>
    <span>شماره تماس </span>: <b>{{$order->phone}}</b>&nbsp;&nbsp;&nbsp;

    @if($order->zip_code)

        <span>کد پستی </span>: <b>{{$order->zip_code}}</b>
    @endif
    <br>

    <span>آدرس </span>: <b>{{$order->address}}</b>
    <br>
    @if($order->orders)
        <span>سفارشات </span>: <b>{{$order->orders}}</b>
    @endif

    @if($order->desc)
        <br><span>توضیحات </span>: <b>{{$order->desc}}</b>
    @endif

</div>

</html>
