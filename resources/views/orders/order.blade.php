@extends('layout.main')

@section('title')
    مشاهده سفارش {{$order->id}}
@endsection

@section('content')
    <form action="" id="form">
        @csrf
        <br>
        <span>شماره سفارش: </span><b>{{$order->id}}</b><br>
        <span>نام و نام خانوادگی:</span> <b>{{$order->name}}</b> <br>
        <span>شماره تماس:</span> <b>{{$order->phone}}</b> <br>
        <span>آدرس:</span> <b>{{$order->address}}</b> <br>
        @if($order->zip_code)
            <span>کد پستی:</span> <b>{{$order->zip_code}}</b> <br>
        @endif
        <span>مبلغ سفارش: </span><b>{{number_format($order->total)}}</b><span> ریال</span><br>
        @if($order->customerCost > 0)
            <span>پرداختی مشتری:</span> <b>{{number_format($order->customerCost)}}</b> <b> ریال</b> <br>
        @endif
        @if($order->payMethod())
            <span>نحوه پرداخت:</span> <b>{{$order->payMethod()}}</b> <br>
        @endif
        @if($order->payInDate)
            <span>تاریخ پرداخت:</span> <b>{{verta($order->payInDate)->timezone('Asia/tehran')->format('Y/m/d')}}</b>
            <br>
        @endif
        @if($order->paymentNote)
            <span>توضیح پرداخت:</span> <b>{{$order->paymentNote}}</b> <br>
        @endif
        <span>درصد پرداخت شده: </span><b>{{$order->payPercent()}} %</b> <br>
        @if($order->sendMethod())
            <span>نحوه ارسال:</span> <b>{{$order->sendMethod()}}</b> <br>
        @endif
        <span>انبار:</span> <b>{{$order->warehouse->name}}</b> <br>
        @if($order->desc)
            <span>توضیحات:</span> <b>{{$order->desc}}</b> <br>
        @endif
        <span>ثبت کننده:</span> <b>{{$order->user()->first()->name}}</b> <br>
        <span>زمان ثبت:</span> <b>{{verta($order->created_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</b>
        <br>
        <span>زمان آخرین ویرایش:</span>
        <b>{{verta($order->updated_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</b> <br>
        <br>
        <br>
        <table class="table table-striped" id="orderExcel">
            <thead>
            <tr>
                <th>کد کالا</th>
                <th>نام کالا</th>
                <th>تعداد</th>
                <th>تخفیف</th>
                <th>قیمت بعد تخفیف(ریال)</th>
                <th>قیمت حال حاضر(ریال)</th>
            </tr>
            </thead>
            <tbody>
            @foreach($order->orderProducts as $orderProduct)
                <tr>
                    <td>{{$orderProduct->product_id}}</td>
                    <td>{{$orderProduct->name}}</td>
                    <td>{{+$orderProduct->number}}</td>
                    <td>{{$orderProduct->discount}}</td>
                    <td>{{number_format($orderProduct->price)}}</td>
                    <td>
                        @isset($orderProduct->product)
                            {{number_format($orderProduct->product->good->price)}}
                        @endisset
                    </td>

                </tr>
            @endforeach
            </tbody>
        </table>
    </form>
@endsection


@section('files')
    <script>
        let table;
        data =
            $(function () {
                draw()
                $("#form").submit(function (e) {
                    e.preventDefault();
                    $.ajax({
                        type: "POST",
                        url: '/saveExcelData/' + {{$order->id}},
                        data: new FormData(this),
                        processData: false,
                        contentType: false,
                        headers: {
                            "Accept": "application/json"
                        }
                    }).done(function (res) {
                        $.notify(res, "success");
                    }).fail(function () {
                        $.notify('خطایی رخ داده است.', 'warn');
                    });
                });

            });

        function draw() {
            table = $('#orderExcel').DataTable({
                searching: false,
                paging: false,
                ordering: false,
                layout: {
                    topStart: {
                        buttons: [
                            {
                                extend: 'excel',
                                text: 'دریافت فایل اکسل',
                                filename: '{{$order->id}}',
                                title: null,
                                exportOptions: {
                                    modifier: {
                                        page: 'current'
                                    }
                                }
                            }
                        ]
                    }
                }
            });
        }

        function reDraw() {
            table.destroy();
            draw();
        }

        {{--function addedValue(checked) {--}}
        {{--    if (checked) {--}}
        {{--        @foreach($orderProducts as $id => $orderProduct)--}}
        {{--        $('#added_value_{{$id}}').html({{$orderProduct->price * $orderProduct->number * 0.1}});--}}
        {{--        @endforeach--}}
        {{--    } else {--}}
        {{--        @foreach($orderProducts as $id => $orderProduct)--}}
        {{--        $('#added_value_{{$id}}').html(0);--}}
        {{--        @endforeach--}}
        {{--    }--}}
        {{--    reDraw();--}}
        {{--}--}}

    </script>

    <style>
        .w-101 {
            width: 120px;
        }

        .w-63 {
            width: 63px;
        }


    </style>
@endsection
