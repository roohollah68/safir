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
        <input type="submit" class="btn btn-success" title="ذخیره تغییرات" onclick="save()" value = "ذخیره">
        <br>
        <br>
        <table class="table table-striped" id="orderExcel">
            <thead>
            <tr>
                <th>کد انبار</th>
                <th>کد مشتری</th>
                <th>کد کالا</th>
                <th>محصول</th>
                <th>تاریخ</th>
                <th>مبلغ</th>
                <th>شماره</th>
                <th>مقدار</th>
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
                        <span class="hide">{{$goodMeta->warehouse_code ?? ''}}</span>
                        <input type="text" id="warehouse_code_{{$id}}" name="warehouse_code_{{$id}}" class="w-101"
                               value="{{$goodMeta->warehouse_code ?? ''}}"
                               onchange="$(this).prev().html(this.value);reDraw()">
                    </td>
                    <td class="CC">{{$customerMeta->customer_code ?? ''}}</td>
                    <td>
                        <span class="hide">{{$goodMeta->stuff_code ?? ''}}</span>
                        <input type="text" id="stuff_code_{{$id}}" name="stuff_code_{{$id}}" class="w-101"
                               value="{{$goodMeta->stuff_code ?? ''}}"
                               onchange="$(this).prev().html(this.value);reDraw()">
                    </td>
                    <td>
                        <span class="hide">{{$orderProduct->name}}</span>
                        <input type="text" id="name_{{$id}}" value="{{$orderProduct->name}}">
                    </td>
                    <td class="date">{{verta($order->created_at)->formatJalaliDate()}}</td>
                    <td>{{+$orderProduct->price}}</td>
                    <td class="number"></td>
                    <td>{{+$orderProduct->number}}</td>
                    <td>{{+$orderProduct->discount}}</td>
                    <td>
                        <span class="">{{isset($goodMeta->added_value) ? round((+$goodMeta->added_value)*(+$orderProduct->price)/100): ''}}</span>
                        <input type="text" id="added_value_{{$id}}" name="added_value_{{$id}}" class="w-101"
                               value="{{$goodMeta->added_value ?? ''}}"
                               onchange="$(this).prev().html(Math.round((+this.value)*(+{{$orderProduct->price}})/100));reDraw()">
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </form>
@endsection


@section('files')
    @csrf
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

    </script>

    <style>
        .w-101 {
            width: 120px;
        }
    </style>
@endsection