<div title="تغییر سفارش کیسان" class="dialogs">
    <h3>سفارش اصلی</h3>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>شماره</th>
            <th>نام</th>
            <th>تعداد</th>
            <th>قیمت</th>
        </tr>
        </thead>
        <thead>
{{--        @dd([$order , $transaction , $keysun])--}}
        @if($order)
            @foreach($order->orderProducts as $orderProduct)
                <tr>
                    <td>{{$orderProduct->product->good_id}}</td>
                    <td>{{$orderProduct->name}}</td>
                    <td>{{+$orderProduct->number}}</td>
                    <td>{{number_format($orderProduct->price)}}</td>
                </tr>
            @endforeach
        @endif
        @if($transaction)
            @foreach($transaction->paymentLinks as $payLink)
                @foreach($payLink->order->orderProducts as $orderProduct)
                    <tr>
                        <td>{{$orderProduct->product->good_id}}</td>
                        <td>{{$orderProduct->name}}</td>
                        <td>{{+$orderProduct->number}}</td>
                        <td>{{number_format($orderProduct->price)}}</td>
                    </tr>
                @endforeach
            @endforeach
        @endif
        </thead>
    </table>
    <h3>سفارش کیسان</h3>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>شماره</th>
            <th>نام</th>
            <th>تعداد</th>
            <th>قیمت</th>
        </tr>
        </thead>
        <thead>
        @foreach($keysun->keysunMetas as $keysunMeta)
            <tr>
                <td>{{$keysunMeta->keysungood_id}}</td>
                <td>{{$keysunMeta->keysungood->name}}</td>
                <td>{{+$keysunMeta->number}}</td>
                <td>{{number_format($keysunMeta->price)}}</td>
            </tr>
        @endforeach
        </thead>
    </table>


</div>
