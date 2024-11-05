<html lang="fa" dir="rtl">

<h3 style="text-align: center;">گزارش گردش حساب</h3>
<span>نام:</span> <b>{{$customer->name}}</b>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
<span>شماره مشتری:</span> <b>{{$customer->id}}</b>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
<span>شماره تماس:</span> <b>{{$customer->phone}}</b><br>
<span>تاریخ:</span> <b dir="ltr">{{verta()->formatDate()}}</b>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
<span>آدرس:</span> <b>{{$customer->address}}</b><br>
<span class="x-large">مانده کل:</span>
<b class="x-large" dir="ltr">{{number_format($customer->balance)}} </b>
<span class="x-large"> ریال</span><br>
<span>{{$timeDescription}}</span>
<table class="customers">
    <thead>
    <tr>
        <th>شماره سند</th>
        <th>تاریخ</th>
        <th>شرح</th>
        <th>بدهکاری(ریال)</th>
        <th>بستانکاری(ریال)</th>
        <th>مانده(ریال)</th>
    </tr>
    </thead>
    <tbody>
    {{--    @foreach($transactions as $trans)--}}
    {{--        @if($trans->verified != 'approved')--}}
    {{--            @continue--}}
    {{--        @endif--}}
    {{--        @php--}}
    {{--            $total2 += $trans->amount;--}}
    {{--        @endphp--}}
    {{--    @endforeach--}}

    {{--    @foreach($orders as $order)--}}
    {{--        @if(!$order->confirm)--}}
    {{--            @continue--}}
    {{--        @endif--}}
    {{--        @php--}}
    {{--            $total1 += $order->total;--}}
    {{--        @endphp--}}
    {{--    @endforeach--}}

    {{--    @php--}}
    {{--        $total = $total2 -$total1;--}}
    {{--    @endphp--}}

    @foreach($transactions->merge($orders)->sortBy('created_at') as $trans)
        @if($trans->getTable() == 'customer_transactions')
            @if( $trans->verified != 'approved')
                @continue
            @endif
            @php
                $total2 += $trans->amount;
            @endphp
        @endif
        @if($trans->getTable() == 'orders')
            @if(!$trans->confirm)
                @continue
            @endif
            @php
                $total1 += $trans->total;
            @endphp
        @endif
        @php
            $total = $total2-$total1;
        @endphp

        <tr>
            <td>{{$trans->id}}</td>
            <td>{{verta($trans->created_at)->formatJalaliDate()}}</td>
            <td>{{$trans->description?:$trans->desc}}</td>
            <td dir="ltr">{{number_format($trans->total)}}</td>
            <td dir="ltr">{{number_format($trans->amount)}}</td>
            <td dir="ltr">{{number_format($total)}}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="3" class="large">مجموع</td>
        <td dir="ltr" class="large">{{number_format($total1)}}</td>
        <td dir="ltr" class="large">{{number_format($total2)}}</td>
        <td dir="ltr" class="large">{{number_format($total)}}</td>
    </tr>
    </tbody>
</table>

@if($withInvoice)
    @foreach($orders as $order)
        <pagebreak>
            <span>شماره فاکتور: {{$order->id}}</span><br>
            <span>تاریخ فاکتور: </span>
            <span>{{verta($order->created_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</span>
            <table class="customers">
                <tr>
                    <th>ردیف</th>
                    <th>شرح کالا/خدمات</th>
                    <th>مقدار</th>
                    <th>قیمت (ریال)</th>
                    <th>درصد تخفیف</th>
                    <th>قیمت بعد تخفیف</th>
                    <th>جمع (ریال)</th>
                </tr>
                @php
                    $total_dis = 0;
                    $total_no_dis = 0;
                    $totalProducts = 0;
                @endphp
                @foreach($order->orderProducts as $orderProduct)
                    @php
                        $price_dis = $orderProduct->price;
                        $sub_total_dis= ($orderProduct->price * $orderProduct->number); //قیمت * تعداد
                        if($orderProduct->discount != 100)
                            $price_no_dis = round((100/(100-$orderProduct->discount))*$orderProduct->price);
                        else
                            $price_no_dis = $orderProduct->product->price;
                        $sub_total_no_dis = $price_no_dis * $orderProduct->number;
                        $total_no_dis = $total_no_dis + $sub_total_no_dis;
                        $total_dis = $total_dis + $sub_total_dis;
                        $totalProducts += $orderProduct->number;
                    @endphp
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$orderProduct->name}}</td>
                        <td>{{+$orderProduct->number}}</td>
                        <td>{{number_format($price_no_dis)}}</td>
                        <td>{{+$orderProduct->discount}}</td>
                        <td>{{number_format($price_dis)}}</td>
                        <td>{{number_format($sub_total_dis)}}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2">مجموع تعداد اقلام</td>
                    <td>{{$totalProducts}}</td>
                    <td colspan="3"></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="6" style="border-bottom: none;"><br><br></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="4"></td>
                    <td colspan="2">مبلغ کل بدون تخفیف</td>
                    <td>{{number_format($total_no_dis)}}</td>
                </tr>
                <tr>
                    <th colspan="4"> شما از این خرید {{number_format(abs($total_no_dis-$total_dis))}} ریال تخفیف
                        گرفتید
                    </th>
                    <th colspan="2">مبلغ قابل پرداخت</th>
                    <th>{{number_format($total_dis)}}</th>
                </tr>
            </table>
            @endforeach
            @endif

            <style>
                .customers {
                    border-collapse: collapse;
                    width: 100%;
                }

                .customers td, .customers th {
                    border: 1px solid #ddd;
                    padding: 8px;
                }

                .customers tr:nth-child(even) {
                    background-color: #f2f2f2;
                }

                .customers tr:hover {
                    background-color: #ddd;
                }

                .customers th {
                    padding-top: 12px;
                    padding-bottom: 12px;
                    background-color: gray;
                    color: white;
                }

                .x-large {
                    font-size: x-large;
                }

                .large {
                    font-size: large;
                }
            </style>
</html>

