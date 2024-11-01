<div id="invoice" style="background: white; width: 2100px;height: 2970px;" class="bg-white m-3">
    <div id="invoice-content" class="m-3 p-3">
        <div class="d-flex">
                <span class="m-3 w-25" style="font-size: 35px; display:flex;">&nbsp; صفحه&nbsp;
                    {{$page}}  &nbsp;از&nbsp; {{$pages}}</span>
            <h4 class="text-center m-3 title w-50 " id="invoice-title">
                @if($order->confirm)
                    فاکتور فروش
                @else
                    پیش فاکتور
                @endif
            </h4>

        </div>
        <div id="main">

            <div class="w-100">
                <table class="w-100 border table1 round {{$firstPage}}" style="text-align: right;">
                    <tr>
                        <th rowspan="3" class="w-5 border-left text-center" style="writing-mode:vertical-rl;">
                            خریدار
                        </th>
                        <th class="w-42 p-2"> نام: {{$order->name}}</th>
                        <th class="w-33 p-2"> تلفن: {{$order->phone}}</th>
                        <th rowspan="2" class="w-20 text-center border-bottom">شماره فاکتور: {{$order->id%1000}}</th>
                    </tr>
                    <tr>
                        <td colspan="3"><br></td>
                    </tr>
                    <tr>
                        <th colspan="2" class="p-2">آدرس: {{$order->address}}
                            @if($order->zip_code)
                                ،کدپستی:&nbsp;{{$order->zip_code}}
                            @endif
                        </th>
                        <td class="text-center" id="invoice-time">{{verta($order->created_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</td>
                    </tr>
                </table>
                <table class="border w-100 table2 round table-striped">
                    <tr class="w-100" id="invoice-head">
                        <th class="w-5 border-left smaller">ردیف</th>
                        <th class="w-35 border-left ">شرح کالا/خدمات</th>
                        <th class="w-8 border-left">مقدار</th>
                        <th class="w-12 border-left">قیمت (ریال)</th>
                        <th class="w-8 border-left">درصد تخفیف</th>
                        <th class="w-9 border-left smaller">قیمت بعد تخفیف</th>
                        <th class="w-23 border-left">جمع (ریال)</th>
                    </tr>
                    @props(['total_dis'=>0,'total_no_dis'=>0,'totalProducts'=>0])

                    @foreach($orderProducts as $orderProduct)
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
                        <tr class="{{$loop->iteration>$firstPageItems?$lastPage:$firstPage}}">
                            <td dir="ltr">{{$loop->iteration}} <span>{{$price_no_dis!=$orderProduct->product->good->price?'*':''}}</span></td>
                            <td>{{$orderProduct->name}}</td>
                            <td dir="ltr">{{+$orderProduct->number}}</td>
                            <td>{{number_format($price_no_dis)}}</td>
                            <td>{{+$orderProduct->discount}}</td>
                            <td>{{number_format($price_dis)}}</td>
                            <td dir="ltr">{{number_format($sub_total_dis)}}</td>
                        </tr>
                    @endforeach
                    <tr class="{{$lastPage}}">
                        <td colspan="2" >مجموع تعداد اقلام</td>
                        <td dir="ltr">{{$totalProducts}}</td>
                        <td colspan="3"></td>
                        <td></td>
                    </tr>
                    <tr class="{{$lastPage}}">
                        <td colspan="6" style="border-bottom: none;" ><br><br></td>
                        <td></td>
                    </tr>
                    <tr class="{{$lastPage}}">
                        <td colspan="4" style="border: none;"></td>
                        <td colspan="2">مبلغ کل بدون تخفیف</td>
                        <td dir="ltr">{{number_format($total_no_dis)}}</td>
                    </tr>
                    <tr class="{{$lastPage}}">
                        <th colspan="4"> شما از این خرید {{number_format(abs($total_no_dis-$total_dis))}} ریال تخفیف
                            گرفتید
                        </th>
                        <th colspan="2">مبلغ قابل پرداخت</th>
                        <th dir="ltr">{{number_format($total_dis)}}</th>
                    </tr>
                </table>

            </div>
            <div class="w-100 normal {{$lastPage}}">
                نحوه پرداخت: {{$order->payMethod()}}
                /
                نحوه ارسال: {{$order->sendMethod()}}
                /
                توضیحات: {{$order->desc}}
                <br>
                @unless($order->confirm)
                    <<اعتبار این پیش فاکتور برای ۴۸ ساعت است>>
                @endunless
            </div>
           <div class="{{$lastPage}}">
            <div class="w-100 normal d-flex justify-content-around">
                <span>امضای خریدار</span>
                <span>تایید حسابداری</span>
                <span>امضای فروشنده</span>
            </div>
           </div>
        <</div>

        <style>

            #invoice .table2 td {
                border-bottom: 1px solid #000000 !important;
                border-left: 1px solid #000000 !important;
            }

            #invoice .table2 th {
                background: #eee;
            }

            #invoice .table2 th {
                border: 1px solid #000000 !important;
            }

            #invoice .text-center, #invoice .table2 th {
                text-align: center !important;
            }

            #invoice h4 {
                font-size: 80px;
            }

            #invoice th, #invoice .normal {
                font-size: 45px;
            }

            #invoice td, #invoice .smaller {
                font-size: 40px;
                text-align: center !important;
                font-weight: bold;
            }

            #invoice {
                font-family: IranSans;
            }

        </style>

        <style>
            .w-5 {
                width: 5%;
            }

            .w-8 {
                width: 8%;
            }

            .w-9 {
                width: 9%;
            }

            .w-12 {
                width: 12%;
            }

            .w-20 {
                width: 20%;
            }

            .w-23 {
                width: 23%;
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

            .w-100 {
                width: 100%;
            }</style>
    </div>
</div>
</div>
