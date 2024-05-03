@foreach($orders as $index => $order)
    @if($index != 0)
        <pagebreak>
            @endif
            <html lang="fa" dir="rtl">
            <div style="font-size: {{$fonts[$index]}}pt;">
                <div style=" line-height: {{round($fonts[$index]*1.33*1.5)}}px ;">
                    <span>نام و نام خانوادگی </span>: <b>{{$order->name}}</b>
                    <br>

                    <span>شماره تماس </span>: <b>{{$order->phone}}
                    </b>&nbsp;&nbsp;&nbsp;

                    @if($order->zip_code)
                        <span>کد پستی </span>: <b>{{$order->zip_code}}</b>
                    @endif
                    <br>

                    <span>آدرس </span>: <b>{{$order->address}}</b>
                    <br>

                    <span>سفارشات </span>: <b>{{$order->orders}}</b>


                    @if($order->desc)
                        <br><span>توضیحات </span>: <b>{{$order->desc}}</b>
                    @endif

                    @if($order->user()->first()->safir())
                        <span>نحوه ارسال </span>: <b>{{$order->sendMethod()}}</b>
                    @endif
                </div>
            </div>

            </html>

        @endforeach


