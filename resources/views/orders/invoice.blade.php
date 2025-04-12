<div id="invoice{{$page}}" style="width: 2100px;height: 2970px; padding: 70px;" class="bg-white">
    <div id="invoice-content" class="">
        <div class="d-flex" style="border: 3px solid;border-bottom:0;height: 310px;z-index: 5;position: relative;">
            <span style="width: 25%">
                <img style="width: 90%; margin: 50px 5%" src="/Peptina-Logo.webp">
            </span>
            <span id="invoice-title" style="width: 50%">
                <p style="font-size: 45px;text-align: center;margin: 30px;font-weight: bold;">
                    {{$order->confirm?'':'پیش'}} فاکتور فروش کالا و خدمات
                </p>
                <p style="font-size: 85px;text-align: center;margin: 20px;font-weight: bold;">
                    {{$setting->invoice_title}}
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
                <p class="fs-35" style="text-align: center;margin: 50px 0 20px 0;">&nbsp; صفحه&nbsp;
                    {{$page}}  &nbsp;از&nbsp; {{$pages}}</p>
                <p class="fs-35"
                   style="text-align: center;margin: 20px; border:3px solid;padding: 10px;border-radius: 20px;">شماره:
                    {{$order->id}}
                </p>
                <p class="fs-35"
                   style="text-align: center;margin: 20px; border:3px solid;padding: 10px;border-radius: 20px;">تاریخ:
                    {{verta($order->created_at)->formatJalaliDate()}}
                </p>
            </span>
        </div>
        <div style="border:3px solid;border-bottom:0;height: 315px;z-index: 4;position: relative;"
             class="{{$firstPage}}">
            <div style="margin: 40px 40px 10px 0">
                <span class="fs-40 p-0">{{$setting->invoice_address}}</span>
            </div>
            <div class="w-100" style="padding: 10px 40px">
                <span class="fs-40 p-0 d-inline-block w-25">تلفن: {{$setting->invoice_phone}}</span>
                <span class="fs-40 p-0 d-inline-block w-24">ثبت / کدملی: {{$setting->invoice_code}}</span>
                <span class="fs-40 p-0 d-inline-block w-25">شناسه ملی: {{$setting->invoice_id}}</span>
                <span class="fs-40 p-0 d-inline-block w-25">استان: {{$setting->invoice_province}}</span>
            </div>
            <div class="w-100" style="padding: 10px 40px 20px 0">
                <span class="fs-40 p-0 d-inline-block w-25">موبایل: {{$order->warehouse->user->phone??''}}</span>
                <span class="p-0 d-inline-block w-24 fs-35">کد اقتصادی: {{$setting->invoice_e_code}}</span>
                <span class="fs-40 p-0 d-inline-block w-25">کدپستی: {{$setting->invoice_zip_code}}</span>
                <span class="fs-40 p-0 d-inline-block w-25">شهر: {{$setting->invoice_city}}</span>
            </div>
            <p class="p-0"
               style="font-size: 45px; text-align: center; border:3px solid; border-radius: 30px; margin: 0 750px;background: #ddd;">
                مشخصات خریدار</p>
        </div>

        <div style="border:3px solid;border-bottom:0;height: 450px;z-index: 3;position: relative;"
             class="{{$firstPage}}">
            <div style="margin: 30px 40px 10px 0">
                <span class="fs-40 p-0">عنوان: {{$order->name}}</span>
            </div>
            <div style="margin: 10px 40px 10px 0; height: 130px;">
                <span class="fs-40 p-0">آدرس: {{$order->address}}</span>
            </div>
            <div class="w-100" style="padding: 10px 40px">
                <span class="fs-40 p-0 d-inline-block w-25" style=" ">تلفن: </span>
                <span class="fs-40 p-0 d-inline-block w-24" style="">ثبت / کدملی: {{$order->customer?$order->customer->national_id:''}}</span>
                <span class="fs-40 p-0 d-inline-block w-25">شناسه ملی: {{$order->customer?$order->customer->national_code:''}}</span>
                <span class="fs-40 p-0 d-inline-block w-25">استان: {{$order->customer?$order->customer->city->province->name:''}}</span>
            </div>
            <div class="w-100" style="padding: 10px 40px 20px 0">
                <span class="fs-40 p-0 d-inline-block w-25" style=" ">موبایل: {{$order->phone}}</span>
                <span class="fs-35 p-0 d-inline-block w-24 ">کد اقتصادی: {{$order->customer?$order->customer->economic_code:''}}</span>
                <span class="fs-40 p-0 d-inline-block w-25">کدپستی: {{$order->zip_code}}</span>
                <span class="fs-40 p-0 d-inline-block w-25">شهر: {{$order->customer?$order->customer->city->name:''}}</span>
            </div>
            <p class="p-0"
               style="font-size: 45px; text-align: center; border:3px solid; border-radius: 30px; margin: 0 750px;background: #ddd;">
                اطلاعات فاکتور</p>
        </div>
        <div id="main" style="border: 3px solid;border-bottom:0; padding-top: 40px">
            <table class="border w-100 table2 round table-striped">
                <tr class="w-100" id="invoice-head">
                    <th style="width: 5%" class="border-left smaller">ردیف</th>
                    <th style="width: 43%" class="border-left ">شرح کالا/خدمات</th>
                    <th style="width: 5%" class="border-left">مقدار</th>
                    <th style="width: 12%" class="border-left">قیمت کالا (ریال)</th>
                    <th style="width: 8%" class="border-left">درصد تخفیف</th>
                    <th style="width: 9%" class="border-left smaller">{{$order->official? 'درصد مالیات' : 'قیمت بعد تخفیف'}}</th>
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
                        <td>{{$order->official? $orderProduct->product->good->vat * 10 : number_format($orderProduct->price)}}</td>
                        <td dir="ltr">{{number_format($orderProduct->price_dis_tax * (+$orderProduct->number))}}</td>
                    </tr>
                @endforeach
                @if($order->user->safir())
                    <tr class="{{$lastPage}}">
                        <td>{{$end+1}}</td>
                        <td>هزینه ارسال</td>
                        <td dir="ltr"></td>
                        <td>{{number_format($deliveryCost)}}</td>
                        <td colspan="2"></td>
                        <td></td>
                    </tr>
                @endif
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
                    <td dir="ltr">{{number_format($total_dis_tax - $total_dis + $total_no_dis)}}</td>
                </tr>
                <tr class="{{$lastPage}}">
                    <th colspan="4"> شما از این خرید {{number_format(abs($total_no_dis-$total_dis))}} ریال تخفیف
                        گرفتید
                    </th>
                    <th colspan="2">مبلغ قابل پرداخت</th>
                    <th dir="ltr">{{number_format($total_dis_tax)}}</th>
                </tr>
            </table>
            <div class="d-flex {{$lastPage}}" id="acount" style="padding: 20px 30px">
                <span style="width: 49%;white-space: pre-wrap;">{{$setting->invoice_bank1}}</span>
                <span style="width: 49%;white-space: pre-wrap;">{{$setting->invoice_bank2}}</span>
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
                    *
                @endif
                @if($order->sendMethod())
                    نحوه ارسال: {{$order->sendMethod()}}
                    *
                @endif
                @if($order->desc)
                    توضیحات: {{$order->desc}}
                    *
                @endif
                انبار: {{$order->warehouse->name}}
                *
                @if($order->user->admin())
                    بدهی: <span dir="ltr">{{-$order->customer->balance()}}</span>
                    *
                @endif
                @if(!$order->confirm)
                    <span style="white-space: pre-wrap;">{{$setting->invoice_note1}}</span>
                @endif
                <sapn style="font-weight: bold;white-space: pre-wrap;">{{$setting->invoice_note2}}</sapn>
            </div>
        </div>


        <style>

            #invoice-content .table2 td {
                border-bottom: 1px solid #000000 !important;
                border-left: 1px solid #000000 !important;
            }

            #invoice-content .table2 th {
                background: #eee;
            }

            #invoice-content .table2 th {
                border: 1px solid #000000 !important;
            }

            #invoice-content .table2 th {
                text-align: center !important;
            }

            #invoice-content th, #invoice-content .normal {
                font-size: 35px;
            }

            #invoice-content td, #invoice-content .smaller {
                font-size: 40px;
                text-align: center !important;
                font-weight: bold;
            }

            #invoice-content {
                font-family: IranSans;
            }

            #acount {
                font-size: 40px;
                font-weight: bold;
            }

            .w-100 {
                width: 100%;
            }

            .w-24 {
                width: 24% !important;
            }

            .fs-40 {
                font-size: 40px;
            }

            .fs-35 {
                font-size: 35px;
            }
        </style>
    </div>
</div>
