@extends('layout.main')

@section('title')
    دریافت خروجی اکسل سفارش
@endsection

@section('content')
    <form action="" id="form">
        @csrf
        <br>
        <span>شماره سفارش: </span><b>{{$order->id}}</b><br>
        <span>مبلغ سفارش: </span><b>{{number_format($order->total)}}</b><span> ریال</span><br>
        <label for="customer_code">شماره: </label>
        <input type="text" value=""
               onchange="$('.number').html(this.value);reDraw()">

        <br>
        <label for="customer_code">تاریخ: </label>
        <input type="text" value="{{verta($order->created_at)->formatJalaliDate()}}"
               onchange="$('.date').html(this.value);reDraw()">

        <br>
        <span>نام مشتری: </span><b>{{$customer->name}}</b><br>
        <span>شماره مشتری: </span><b>{{$customer->id}}</b><br>
        <br>
        <label for="customer_code">کد مشتری: </label>
        <input type="text" id="customer_code" name="customer_code" value="{{$customerMeta->customer_code ?? ''}}"
               onchange="$('.CC').html(this.value);reDraw()">
        <br>
        <br>
        <input type="submit" class="btn btn-success" title="ذخیره تغییرات" onclick="save()" value="ذخیره">
        <br>
        <br>
        <table class="table table-striped" id="orderExcel">
            <thead>
            <tr>
                <th>کد انبار</th>
                <th>کد مشتری</th>
                <th>کد کالا</th>
                <th>محصول</th>
                <th>مقدار</th>
                <th>تاریخ</th>
                <th>نرخ</th>
                <th>مبلغ</th>
                <th>شماره</th>
                <th>تخفیف</th>
                <th>ارزش افزوده</th>
            </tr>
            </thead>
            <tbody>
            @foreach($orderProducts as $id => $orderProduct)
                @continue(!isset($orderProduct->product))
                @php
                    $goodMeta = $orderProduct->product->good->goodMetas->first();
                @endphp
                <tr>
                    <td>
                        <span class="hide">{{$goodMeta->warehouse_code ?? '50'}}</span>
                        <input type="text" id="warehouse_code_{{$id}}" name="warehouse_code_{{$id}}" class="w-63"
                               value="{{$goodMeta->warehouse_code ?? '50'}}"
                               onchange="$(this).prev().html(this.value);reDraw()">
                    </td>
                    <td class="CC">{{$customerMeta->customer_code ?? ''}}</td>
                    <td>
                        <span class="hide">{{$goodMeta->stuff_code ?? ''}}</span>
                        <input type="text" id="stuff_code_{{$id}}" name="stuff_code_{{$id}}" class="w-63"
                               value="{{$goodMeta->stuff_code ?? ''}}"
                               onchange="$(this).prev().html(this.value);reDraw()">
                    </td>
                    <td>
                        <span class="hide">{{$orderProduct->name}}</span>
                        <input type="text" id="name_{{$id}}" value="{{$orderProduct->name}}">
                    </td>
                    <td>{{+$orderProduct->number}}</td>
                    <td class="date">{{verta($order->created_at)->formatJalaliDate()}}</td>
                    <td>{{$orderProduct->original_price}}</td>
                    <td>{{$orderProduct->original_price * (+$orderProduct->number)}}</td>
                    <td class="number"></td>
                    <td>{{(+$orderProduct->discount)/100*$orderProduct->original_price * (+$orderProduct->number)}}</td>
                    <td>
                        <input type="checkbox" onclick="$(this).next().html(this.checked?{{$orderProduct->add_value}}:0);reDraw();">
                        <span>0</span>
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
