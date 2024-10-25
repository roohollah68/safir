<div title="اتصال سفارش به پرداختی ها" class="dialogs">
        <span>شماره سفارش: </span><a class="btn btn-info" onclick="view_order({{$order->id}})" title="مشاهده فاکتور">{{$order->id}}</a><br>
        <span>مبلغ کل: </span><span>{{number_format($order->total)}}</span> <sapn>ریال</sapn><br>
        <span>مبلغ متصل شده به واریزی ها: </span><span>{{number_format($payLinkTotal)}}</span> <sapn>ریال</sapn><br>
        <span>مبلغ باقی مانده: </span><span>{{number_format($order->total-$payLinkTotal)}}</span> <sapn>ریال</sapn><br>
        <hr>
    @if($payLinkTotal>0)
        <b>پرداخت های متصل</b><br>
        @foreach($payLinks as $paylink)
            <span>شماره: </span>
            <span class="btn btn-info">{{$paylink->customer_transaction_id}}</span>
            <span>واریزی: </span><i>{{number_format($paylink->amount)}}</i> <sapn>ریال</sapn>
            <span class="btn btn-danger fa fa-chain-broken" title="حذف اتصال"
                  onclick="removePayLink({{$paylink->id}})"></span><br>
        @endforeach
        <hr>
    @endif
        @if($order->total-$payLinkTotal>0)
            <b>پرداختی های غیر متصل</b><br>
            @foreach($transactions as $transaction)
                @continue(+$transaction->linkedAmount() == $transaction->amount || $transaction->verified != 'approved')
                <span>شماره: </span>
                <span class="btn btn-secondary">{{$transaction->id}}</span>
                <span>واریزی: </span><i>{{number_format($transaction->amount)}}</i> <sapn>ریال</sapn>
                <span class="btn btn-success fa fa-chain" title="ایجاد اتصال"
                      onclick="addPayLink({{$transaction->id}},{{$order->id}})"></span><br>
            @endforeach
        @endif
</div>
