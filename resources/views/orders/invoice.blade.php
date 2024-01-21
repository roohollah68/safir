
<div id="invoice" style="background: white;" class="bg-white m-3">
        <div class="m-3 p-3">
            <div>
                <h4 class="text-center m-3 title" id="invoice-title">
                    @if($order->paymentMethod == 'admin')
                        پیش فاکتور
                    @else
                        فاکتور فروش
                    @endif
                </h4>

            </div>
            <div id="main">

                <div class="w-100">
                    <table class="w-100 border table1 round" style="text-align: right;">
                        <tr>
                            <th rowspan="3" class="w-5 border-left text-center" style="writing-mode:vertical-rl;">
                                خریدار
                            </th>
                            <th class="w-42 p-2"> نام: {{$order->name}}</th>
                            <th class="w-33 p-2"> تلفن: {{$order->phone}}</th>
                            <th rowspan="2" class="w-20 text-center border-bottom">شماره فاکتور: {{$order->id}}</th>
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
                            <td class="text-center" id="invoice-time">{{$order->created_at_p}}</td>
                        </tr>
                    </table>
                    <table class="border w-100 table2 round">
                        <tr class="w-100" id="invoice-head">
                            <th class="w-5 border-left smaller">ردیف</th>
                            <th class="w-35 border-left ">شرح کالا/خدمات</th>
                            <th class="w-8 border-left">مقدار</th>
                            <th class="w-12 border-left">قیمت (ریال)</th>
                            <th class="w-8 border-left" >درصد تخفیف</th>
                            <th class="w-9 border-left smaller" >قیمت بعد تخفیف</th>
                            <th class="w-23 border-left">جمع (ریال)</th>
                        </tr>
                        @php
                            $counter = 1;
                            $total = 0;
                            $total_original=0;
                        @endphp
                        @foreach($orderProducts as $orderProduct)
                            @php
                                $t= ($orderProduct->price * $orderProduct->number)*10; //قیمت * تعداد
                                $original = round((100/(100-$orderProduct->discount))*$orderProduct->price)*10;
                                $total_original = $total_original + $original;
                                $total = $total + $t;
                            @endphp
                            <tr class="">
                                <td>{{$counter++}}</td>
                                <td>{{$orderProduct->name}}</td>
                                <td>{{$orderProduct->number}}</td>
                                <td>{{number_format($original)}}</td>
                                <td>{{$orderProduct->discount}}</td>
                                <td>{{number_format($orderProduct->price*10)}}</td>
                                <td>{{number_format($t)}}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="6" style="border-bottom: none;"><br><br><br><br></td>
                            <td></td>
                        </tr>
                        <tr class="">
                            <td colspan="4" style="border: none;"></td>
                            <td colspan="2">مبلغ کل بدون تخفیف</td>
                            <td>{{number_format($total_original)}}</td>
                        </tr>
                        <tr class="">
                            <th colspan="4"> شما از این خرید {{number_format(abs($total_original-$total))}} ریال تخفیف گرفتید</th>
                            <th colspan="2">مبلغ قابل پرداخت</th>
                            <th>{{number_format($total)}}</th>
                        </tr>
                    </table>

                </div>
                <div class="w-100 normal">
                    توضیحات: {{$order->desc}}
                </div>
                <br>
                <div class="w-100 normal d-flex justify-content-around">
                    <span>امضای خریدار</span>
                    <span>تایید حسابداری</span>
                    <span>امضای فروشنده</span>
                </div>

            </div>

            <style>

                .table2 td {
                    border-bottom: 1px solid #000000 !important;
                    border-left: 1px solid #000000 !important;
                }

                .table2 th {
                    background: #eee;
                }

                .table2 th {
                    border: 1px solid #000000 !important;
                }

                td {
                    font-size: 12px;
                    text-align: center !important;
                }

                .text-center, .table2 th {
                    text-align: center !important;
                }

                #invoice h4{
                    font-size: 60px;
                }

                #invoice th, #invoice .normal{
                    font-size: 30px;
                }

                #invoice td, #invoice .smaller{
                    font-size: 26px;
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
                }</style>
        </div>
    </div>
</div>
