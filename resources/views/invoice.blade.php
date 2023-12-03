<html lang="fa" dir="rtl">


<body>
<div>
    <h4 style="text-align: center; ">فاکتور فروش</h4>
</div>
<div id="main">

    <div class="w-100">
        <table class="w-100 border table1 round" style="text-align: right;">
            <tr>
                <td rowspan="3" class="w-8 border-left text-center">
                    خریدار
                </td>
                <th class="w-42 tar"> نام: {{$order->name}}</th>
                <th class="w-30 tar"> تلفن: {{$order->phone}}</th>
                <td rowspan="2" class="w-20 text-center border-bottom">شماره فاکتور: {{$order->id}}</td>
            </tr>
            <tr>
                <td colspan="3"><br></td>
            </tr>
            <tr>
                <th colspan="2" class="tar">آدرس: {{$order->address}}
                    @if($order->zip_code)
                        ،کدپستی:&nbsp;{{$order->zip_code}}
                    @endif
                </th>
                <td class="text-center">{{$order->created_at_p}}</td>
            </tr>
        </table>
        <table class="border w-100 table2 round">
            <tr class="w-100">
                <th class="w-5 border-left" style="font-size:12px;">ردیف</th>
                <th class="w-35 border-left">شرح کالا/خدمات</th>
                <th class="w-8 border-left">مقدار</th>
                <th class="w-9 border-left">واحد</th>
                <th class="w-12 border-left">فی (ریال)</th>
                <th class="w-8 border-left" style="font-size:12px;">درصد تخفیف</th>
                <th class="w-23 border-left">جمع (ریال)</th>

            </tr>

            @php
                $ii = 1;
                $total = 0;
            @endphp
            @foreach($orderProducts as $orderProduct)
                @php
                    $t= ($orderProduct->price * $orderProduct->number);
                    $total = $total + $t;
                @endphp
                <tr class="">
                    <td>{{$ii++}}</td>
                    <td>{{$orderProduct->name}}</td>
                    <td>{{$orderProduct->number}}</td>
                    <td>عدد</td>
                    <td>{{number_format($orderProduct->price)}}</td>
                    <td>{{$orderProduct->discount}}</td>
                    <td>{{number_format($t)}}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="6"><br><br><br><br></td>
                <td></td>
            </tr>

            <tr class="">
                <th colspan="4"></th>
                <th colspan="2">مبلغ قابل پرداخت</th>
                <th>{{number_format($total)}}</th>
            </tr>

        </table>

    </div>
    <div class=" w-100">
        توضیحات: {{$order->desc}}
    </div>
    <br>
    <div class="w-100">
        &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
        <span>امضای خریدار</span>
        &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp;
        &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;
        <span class="w-30">تایید حسابداری</span>
        &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp;
        &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;
        <span class="w-30">امضای فروشنده</span>
    </div>

</div>

<style>
    body {
        width: 210mm;
        height: 297mm;
        padding: 1rem;
    }

    #main {
    }

    .tar {
        text-align: right !important;
    }

    .table2 td {
        border-bottom: 1px solid #000000 !important;
        border-left: 1px solid #000000 !important;
    }

    .table2 th {
        background: #eee;
    }

    .border, .table2 th {
        border: 1px solid #000000 !important;
    }

    .border-top {
        border-top: 1px solid #000000 !important;
    }

    .border-bottom {
        border-bottom: 1px solid #000000 !important;
    }

    .border-left {
        border-left: 1px solid #000000 !important;
    }

    .border-right {
        border-right: 1px solid #000000 !important;
    }

    .round {
        border-radius: 5px;
    }

    td {
        font-size: 12px;
        text-align: center !important;
    }

    .text-center {
        text-align: center !important;
    }

    .w-5 {
        width: 5%;
    }

    .w-8 {
        width: 8%;
    }

    .w-9 {
        width: 9%;
    }

    .w-10 {
        width: 10%;
    }

    .w-11 {
        width: 11%;
    }

    .w-12 {
        width: 12%;
    }

    .w-15 {
        width: 15%;
    }

    .w-20 {
        width: 20%;
    }

    .w-23 {
        width: 23%;
    }

    .w-30 {
        width: 30%;
    }

    .w-33 {
        width: 33%;
    }

    .w-35 {
        width: 35%;
    }

    .w-42 {
        width: 42%;
    }

    .w-70 {
        width: 70%;
    }

    .w-80 {
        width: 80%;
    }

    .w-90 {
        width: 90%;
    }

    .w-95 {
        width: 95%;
    }

    .w-100 {
        width: 100%;
    }

</style>

</body>
</html>
