<div class="d-none">
    <div id="invoice" style="background: white;" class="bg-white m-3">
        <div class="m-3 p-3">
            <div>
                <h4 class="text-center m-3 title" id="invoice-title">پیش فاکتور </h4>
            </div>
            <div id="main">

                <div class="w-100">
                    <table class="w-100 border table1 round" style="text-align: right;">
                        <tr>
                            <th rowspan="3" class="w-5 border-left text-center" style="writing-mode:vertical-rl;">
                                خریدار
                            </th>
                            <th class="w-42 p-2"> نام: <span id="invoice-name"></span></th>
                            <th class="w-33 p-2"> تلفن: <span id="invoice-phone"></span></th>
                            <th rowspan="2" class="w-20 text-center border-bottom">شماره فاکتور: <span
                                    id="invoice-number">پیش فاکتور</span></th>
                        </tr>
                        <tr>
                            <td colspan="3"><br></td>
                        </tr>
                        <tr>
                            <th colspan="2" class="p-2"> آدرس:
                                <sapn id="invoice-address"></sapn>
                                <sapn id="invoice-zip">،کدپستی:</sapn>&nbsp;<sapn id="invoice-zip_code"></sapn>
                            </th>
                            <td class="text-center" id="invoice-time"></td>
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

                        <tr>
                            <td colspan="6" style="border-bottom: none;"><br><br></td>
                            <td></td>
                        </tr>
                        <tr class="">
                            <td colspan="4" style="border: none;"></td>
                            <td colspan="2">مبلغ کل بدون تخفیف</td>
                            <td id="invoice-total-no-discount">0</td>
                        </tr>
                        <tr class="">
                            <th colspan="4"> شما از این خرید <span id="invoice-total-discount">0</span> ریال تخفیف
                                گرفتید
                            </th>
                            <th colspan="2">مبلغ قابل پرداخت</th>
                            <th id="invoice-total-with-discount">0</th>
                        </tr>

                    </table>

                </div>
                <div class="w-100 normal">
                    توضیحات:
                    <span id="invoice-description"></span>
                </div>
                <br>
                <div class="w-100 normal d-flex justify-content-around">
                    <span>امضای خریدار</span>
                    <span>تایید حسابداری</span>
                    <span>امضای فروشنده</span>
                </div>

            </div>
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

        #invoice .text-center, #invoice .table2 th {
            text-align: center !important;
        }

        #invoice h4{
            font-size: 80px;
        }

        #invoice th, #invoice .normal{
            font-size: 45px;
        }

        #invoice td, #invoice .smaller{
            font-size: 40px;
            text-align: center !important;
            font-weight: bold;
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
        }
    </style>
</div>
