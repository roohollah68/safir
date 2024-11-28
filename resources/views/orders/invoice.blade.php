<div id="invoice" style="width: 2100px;height: 2970px; padding: 70px;" class="bg-white">
    <div id="invoice-content" class="">
        <div class="d-flex" style="border: 3px solid;border-bottom:0;height: 310px;z-index: 5;position: relative;">
            <span style="width: 25%">
                <img style="width: 90%; margin: 50px 5%" src="/Peptina-Logo.webp">
            </span>
            <span id="invoice-title" style="width: 50%">
                <p style="font-size: 45px;text-align: center;margin: 30px;font-weight: bold;">
                @if(!$order->confirm)
                        پیش
                    @endif فاکتور فروش کالا و خدمات
            </p>
                <p style="font-size: 85px;text-align: center;margin: 20px;font-weight: bold;">
                پپتینا
                </p>
                <p style="font-size: 45px; text-align: center;padding: 0; border:3px solid; border-radius: 30px; margin: 0 250px;background: #ddd;">
                    @if(!$firstPage)
                        مشخصات فروشنده
                    @else
                        اطلاعات فاکتور
                    @endif
                </p>
            </span>
            <span style="width: 25%">
                <p style="font-size: 35px; text-align: center;margin: 50px 0 20px 0;">&nbsp; صفحه&nbsp;
                    {{$page}}  &nbsp;از&nbsp; {{$pages}}</p>
                <p style="font-size: 35px; text-align: center;margin: 20px; border:3px solid;padding: 10px;border-radius: 20px;">شماره:
                    {{$order->id}}
                </p>
                <p style="font-size: 35px; text-align: center;margin: 20px; border:3px solid;padding: 10px;border-radius: 20px;">تاریخ:
                    {{verta($order->created_at)->formatJalaliDate()}}
                </p>
            </span>
        </div>
        <div style="border:3px solid;border-bottom:0;height: 315px;z-index: 4;position: relative;"
             class="{{$firstPage}}">
            <div style="margin: 40px 40px 10px 0">
                <span style="font-size: 40px; padding: 0;">آدرس: شهرک صنعتی فریمان، تلاش 2، پلاک 3، شرکت سلامت تدبیر اصیل مهر آسا</span>
            </div>
            <div class="w-100" style="padding: 10px 40px">
                <span style="font-size: 40px; padding: 0;display: inline-block; width: 25%;">تلفن: 05134685278</span>
                <span style="font-size: 40px; padding: 0;display: inline-block;width: 24%;">ثبت / کدملی:</span>
                <span
                        style="font-size: 40px; padding: 0;display: inline-block;width: 25%;">شناسه ملی: 14008245509</span>
                <span style="font-size: 40px; padding: 0;display: inline-block;width: 25%;">استان: خراسان رضوی</span>
            </div>
            <div class="w-100" style="padding: 10px 40px 20px 0">
                <span style="font-size: 40px; padding: 0;display: inline-block; width: 25%;">موبایل: </span>
                <span
                        style="font-size: 35px; padding: 0;display: inline-block;width: 24%;">کد اقتصادی: 411647355789</span>
                <span style="font-size: 40px; padding: 0;display: inline-block;width: 25%;">کدپستی: 9391181341</span>
                <span style="font-size: 40px; padding: 0;display: inline-block;width: 25%;">شهر: فریمان</span>
            </div>
            <p style="font-size: 45px; text-align: center;padding: 0; border:3px solid; border-radius: 30px; margin: 0 750px;background: #ddd;">
                مشخصات خریدار</p>
        </div>

        <div style="border:3px solid;border-bottom:0;height: 450px;z-index: 3;position: relative;"
             class="{{$firstPage}}">
            <div style="margin: 30px 40px 10px 0">
                <span style="font-size: 40px; padding: 0;">عنوان: {{$order->name}}</span>
            </div>
            <div style="margin: 10px 40px 10px 0; height: 130px;">
                <span style="font-size: 40px; padding: 0;">آدرس: {{$order->address}}</span>
            </div>
            <div class="w-100" style="padding: 10px 40px">
                <span style="font-size: 40px; padding: 0;display: inline-block; width: 25%;">تلفن: </span>
                <span style="font-size: 40px; padding: 0;display: inline-block;width: 24%;">ثبت / کدملی:</span>
                <span style="font-size: 40px; padding: 0;display: inline-block;width: 25%;">شناسه ملی: </span>
                <span
                        style="font-size: 40px; padding: 0;display: inline-block;width: 25%;">استان: {{$order->customer->city->province->name}}</span>
            </div>
            <div class="w-100" style="padding: 10px 40px 20px 0">
                <span
                        style="font-size: 40px; padding: 0;display: inline-block; width: 25%;">موبایل: {{$order->phone}}</span>
                <span style="font-size: 35px; padding: 0;display: inline-block;width: 24%;">کد اقتصادی: </span>
                <span
                        style="font-size: 40px; padding: 0;display: inline-block;width: 25%;">کدپستی: {{$order->zip_code}}</span>
                <span
                        style="font-size: 40px; padding: 0;display: inline-block;width: 25%;">شهر: {{$order->customer->city->name}}</span>
            </div>
            <p style="font-size: 45px; text-align: center;padding: 0; border:3px solid; border-radius: 30px; margin: 0 750px;background: #ddd;">
                اطلاعات فاکتور</p>
        </div>
        <div id="main" style="border: 3px solid;border-bottom:0; padding-top: 40px">
            <table class="border w-100 table2 round table-striped">
                <tr class="w-100" id="invoice-head">
                    <th style="width: 5%" class="border-left smaller">ردیف</th>
                    <th style="width: 43%" class="border-left ">شرح کالا/خدمات</th>
                    <th style="width: 5%" class="border-left">مقدار</th>
                    <th style="width: 12%" class="border-left">قیمت (ریال)</th>
                    <th style="width: 8%" class="border-left">درصد تخفیف</th>
                    <th style="width: 9%" class="border-left smaller">قیمت بعد تخفیف</th>
                    <th style="width: 18%" class="border-left">جمع (ریال)</th>
                </tr>
                @props(['total_dis'=>0,'total_no_dis'=>0,'totalProducts'=>0])

                @foreach($order->orderProducts as $id => $orderProduct)
                    @continue(($id < $start) || ($id >= $end))
                    <tr>
                        <td dir="ltr">{{$id + 1}}
                            <span>{{$orderProduct->editPrice?'*':''}}</span>
                        </td>
                        <td>{{$orderProduct->name}}</td>
                        <td dir="ltr">{{+$orderProduct->number}}</td>
                        <td>{{number_format($orderProduct->price_no_dis)}}</td>
                        <td>{{+$orderProduct->discount}}</td>
                        <td>{{number_format($orderProduct->price)}}</td>
                        <td dir="ltr">{{number_format($orderProduct->price*(+$orderProduct->number))}}</td>
                    </tr>
                @endforeach
                <tr class="{{$lastPage}}">
                    <td colspan="2">مجموع تعداد اقلام</td>
                    <td dir="ltr">{{$totalProducts}}</td>
                    <td colspan="3"></td>
                    <td></td>
                </tr>
                <tr class="{{$lastPage}}">
                    <td colspan="6" style="border-bottom: none;"><br><br></td>
                    <td></td>
                </tr>
                <tr class="{{$lastPage}}">
                    <td colspan="4" style="border: none;"></td>
                    <td colspan="2" style="font-size: 35px;">مبلغ کل بدون تخفیف</td>
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
            <div class="d-flex {{$lastPage}}" id="acount" style="padding: 20px 30px">
                <span style="width: 49%">
                    <p>حساب بانک سپه شرکت سلامت تدبیر اصیل مهر آسا</p>
                    <p>شماره شبا: IR750150000003100006261636</p>
                    <p>شماره حساب: 3100006261636</p>
                    <p>شماره کارت: 5892108833365860</p>
                </span>
                <span style="width: 49%">
                    <p>حساب بانک رفاه شرکت سلامت تدبیر اصیل مهرآسا</p>
                    <p>شماره شبا: IR750130100000000355393499</p>
                    <p>شماره حساب: 355393499</p>
                    <p>شماره کارت: 5894637000235207</p>
                </span>
            </div>
        </div>
        <div id="main" style="border: 3px solid; padding-top: 40px" class="{{$lastPage}}">
            <div class="d-flex" style="padding: 40px 30px">
                <span style="width: 49%">
                    <p style="text-align: center;font-size: 50px">مدیر فروش</p>
                </span>
                <span style="width: 49%">
                    <p style="text-align: center;font-size: 50px">خریدار</p>
                </span>
            </div>
            <div class="w-100 normal {{$lastPage}}">
                @if($order->payMethod())
                    نحوه پرداخت: {{$order->payMethod()}}
                    /
                @endif
                @if($order->sendMethod())
                    نحوه ارسال: {{$order->sendMethod()}}
                    /
                @endif
                @if($order->desc)
                    توضیحات: {{$order->desc}}
                    /
                @endif
                @unless($order->confirm)
                    * اعتبار این پیش فاکتور برای ۴۸ ساعت است.
                    * فقط پرداخت به شماره حساب های ذکر شده قابل قبول است.
                @endunless
            </div>
        </div>


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

            #invoice .table2 th {
                text-align: center !important;
            }

            #invoice th, #invoice .normal {
                font-size: 35px;
            }

            #invoice td, #invoice .smaller {
                font-size: 40px;
                text-align: center !important;
                font-weight: bold;
            }

            #invoice {
                font-family: IranSans;
            }

            #acount {
                font-size: 40px;
                font-weight: bold;
            }

            .w-100 {
                width: 100%;
            }</style>
    </div>
</div>
