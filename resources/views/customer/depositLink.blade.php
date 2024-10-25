<div title="اتصال پرداختی به سفارشات" class="dialogs">
        <span>شماره پرداختی: </span><span>{{$transaction->id}}</span><br>
        <span>مبلغ واریزی: </span><span>{{number_format($transaction->amount)}}</span><sapn>ریال</sapn><br>
        <span>مبلغ متصل شده به سفارشات: </span><span>{{number_format($payLinkTotal)}}</span><sapn>ریال</sapn><br>
        <span>مبلغ باقی مانده: </span><span>{{number_format($transaction->amount-$payLinkTotal)}}</span><sapn>ریال</sapn><br>
        <hr>
        <b>سفارشات متصل</b><br>
        @foreach($payLinks as $paylink)
            <span>سفارش: </span>
            <a class="btn btn-info" onclick="view_order({{$paylink->order_id}})"
               title="مشاهده فاکتور">{{$paylink->order_id}}</a>
            <span>مبلغ متصل شده: </span><i>{{number_format($paylink->amount)}}</i><sapn>ریال</sapn>
            <span class="btn btn-danger fa fa-chain-broken" title="حذف اتصال"
                  onclick="removePayLink({{$paylink->id}})"></span><br>
        @endforeach
        <hr>
        @if($transaction->amount-$payLinkTotal>0)
            <b>سفارشات غیر متصل</b><br>
            @foreach($orders as $order)
                @continue(+$order->payPercent() == 100)
                <span>سفارش: </span>
                <a class="btn btn-info" onclick="view_order({{$order->id}})" title="مشاهده فاکتور">{{$order->id}}</a>
                <span>مبلغ سفارش: </span><i>{{number_format($order->total)}}</i><sapn>ریال</sapn>
                <span class="btn btn-success fa fa-chain" title="ایجاد اتصال"
                      onclick="addPayLink({{$transaction->id}},{{$order->id}})"></span><br>
            @endforeach
        @endif
    </>

</div>
