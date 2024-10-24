<div title="اتصال پرداختی به سفارشات" class="dialogs">
    <form method="post" id="form" action="">
        @csrf
        <span>شماره پرداختی: </span><span>{{$transaction->id}}</span><br>
        <span>مبلغ واریزی: </span><span>{{number_format($transaction->amount)}}</span><br>
        <span>مبلغ متصل شده به سفارشات: </span><span>{{number_format($payLinkTotal)}}</span><br>
        <span>مبلغ باقی مانده: </span><span>{{number_format($transaction->amount-$payLinkTotal)}}</span><br>
        <hr>
        <b>سفارشات متصل</b><br>
        @foreach($payLinks as $paylink)
            <span>سفارش: </span>
            <a class="btn btn-info" onclick="view_order({{$paylink->order_id}})" title="مشاهده فاکتور">{{$paylink->order_id}}</a>
            <span>مبلغ متصل شده: </span><i>{{number_format($paylink->amount)}}</i>
            <span class="btn btn-danger fa fa-trash" onclick="removeLink({{$paylink->id}})"></span>
        @endforeach
    </form>

</div>
