@extends('layout.main')

@section('title')
    دریافت خروجی اکسل سفارش
@endsection

@section('content')
    <br>
    <span>شماره سفارش: </span><b>{{$order->id}}</b><br>
    <span>مبلغ سفارش: </span><b>{{number_format($order->total)}}</b><span> ریال</span><br>
    <label for="customer_code">شماره: </label>
    <input type="text" value=""
           onchange="$('.number').html(this.value)">

    <br>
    <span>نام مشتری: </span><b>{{$customer->name}}</b><br>
    <span>شماره مشتری: </span><b>{{$customer->id}}</b><br>
    <br>
    <label for="customer_code">کد مشتری: </label>
    <input type="text" id="customer_code" value="{{$customerMeta->customer_code ?? ''}}"
           onchange="$('.CC').html(this.value)">
    <br>
    <br>
    <span class="btn btn-success fa fa-save" title="ذخیره تغییرات"></span>
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
            <th>مبلغ(ریال)</th>
            <th>شماره</th>
            <th>مقدار</th>
            <th>تخفیف</th>
            <th>ارزش افزوده</th>
        </tr>
        </thead>
        <tbody>
        @foreach($orderProducts as $id => $orderProduct)
            <tr>
                <td>
                    <span class="hide">{{$orderProduct->product->good->goodMetas->first()->warehouse_code ?? ''}}"</span>
                    <input type="text" id="warehouse_code_{{$id}}" class="w-101"
                           value="{{$orderProduct->product->good->goodMetas->first()->warehouse_code ?? ''}}"
                           onchange="$(this).prev().html(this.value)">
                </td>
                <td class="CC"></td>
                <td><input type="text" id="stuff_code_{{$id}}" class="w-101"
                           value="{{$orderProduct->product->good->goodMetas->first()->stuff_code ?? ''}}"></td>
                <td><span class="hide">{{$orderProduct->name}}</span><input type="text" id="name_{{$id}}"
                           value="{{$orderProduct->name}}"></td>
                <td>{{verta($order->created_at)->formatJalaliDate()}}</td>
                <td>{{number_format($orderProduct->price)}}</td>
                <td class="number"></td>
                <td>{{+$orderProduct->number}}</td>
                <td>{{+$orderProduct->discount}}</td>
                <td><input type="text" id="added_value_{{$id}}" class="w-101"
                           value="{{$orderProduct->product->good->goodMetas->first()->added_value ?? ''}}"></td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection


@section('files')
    @csrf
    <script>
        $(function () {
            $('#orderExcel').DataTable({
                searching: false,
                paging: false,
                layout: {
                    topStart: {
                        buttons: [
                            {
                                extend: 'excel',
                                text: 'دریافت فایل اکسل',
                                filename : '{{$order->id}}',
                                title : null,
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
        });

        {{--function delete_deposit(id) {--}}
        {{--    confirm("برای همیشه حذف شود؟") ?--}}
        {{--        $.post('/deposit/delete/' + id, {_token: "{{ csrf_token() }}"})--}}
        {{--            .done(res => {--}}
        {{--                location.reload();--}}
        {{--            })--}}
        {{--        :--}}
        {{--        ""--}}
        {{--}--}}

        {{--@if($superAdmin)--}}
        {{--function confirm_deposit(id) {--}}
        {{--    $.post('/deposit/changeConfirm/' + id, {_token: "{{ csrf_token() }}"})--}}
        {{--        .done(res => {--}}
        {{--            if (res) {--}}
        {{--                $('#confirm' + id).removeClass('btn-danger').addClass('btn-success').html('تایید شده');--}}
        {{--                $('#operation' + id).hide();--}}
        {{--            } else {--}}
        {{--                $('#confirm' + id).removeClass('btn-success').addClass('btn-danger').html('تایید نشده');--}}
        {{--                $('#operation' + id).show();--}}
        {{--            }--}}
        {{--        })--}}
        {{--}--}}
        {{--@endif--}}

    </script>

    <style>
        .w-101{
            width: 120px;
        }
    </style>
@endsection
