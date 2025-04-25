@extends('layout.main')

@section('title')
    سامانه مودیان
@endsection

@section('content')

    <form action="" method="post">
        @csrf
        <div class="mb-3">
            <label for="from">از تاریخ: </label>
            <input type="text" class="form-control" style="width: 160px" id="from" name="from">
            <label for="to">تا تاریخ: </label>
            <input type="text" class="form-control" style="width: 160px" id="to" name="to">
        </div>
        <label for="sent">فاکتورهایی که قبلا دانلود شده</label>
        <input type="checkbox" class="checkboxradio" id="sent" name="sent" @checked($_POST['sent'] ?? false)>
        <br>
        <button type="submit" class="btn btn-primary mb-2">فیلتر</button>

    </form>
    <br>
    <div id="main-tabs" dir="rtl">
        <ul class="nav nav-tabs">
            <li><a href="#peptina">سفارشات سایت پپتینا</a></li>
            <li><a href="#customers">پرداختی های مشتریان</a></li>
        </ul>
        <div id="peptina">
            <span class="btn btn-success mb-4" onclick="generatePeptinaExcel(ids);">دانلود اکسل</span>
            <table id="peptina-table" class="table table-striped">
                <thead>
                <tr>
                    <th>شماره سفارش</th>
                    <th>نام</th>
                    <th>مبلغ (ریال)</th>
                    <th>تاریخ ثبت</th>
                    {{--                    <th>وضعیت</th>--}}
                    <th>عملیات</th>
                    <th>ضریب</th>
                    {{--                    <th><input type="checkbox"--}}
                    {{--                               onclick="this.checked?$('.order-select:not(:checked)').click():$('.order-select:checked').click()"--}}
                    {{--                               class="form-check-input" checked></th>--}}
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{$order->id}}</td>
                        <td>{{$order->name}}</td>
                        <td>{{number_format($order->total)}}</td>
                        <td>{{verta($order->created_at)->formatJalaliDate()}}</td>
                        {{--                        <td>{!! $order->orderCondition() !!}</td>--}}
                        <td>
                            <span class="btn btn-info fa fa-eye" onclick="view_order({{$order->id}})"></span>
                            <a class="btn btn-info fa fa-comment"
                               onclick="view_comment({{$order->id}})"
                               title="مشاهده کامنت ها"></a>
                            <a class="fa fa-file-invoice-dollar btn btn-secondary"
                               onclick="invoice({{$order->id}},event)"
                               title=" فاکتور"></a>
                        </td>
                        <td>{!! $order->keysun->conversion() !!}</td>
                        {{--                        <td><input type="checkbox" orderId="{{$order->id}}" class="order-select form-check-input" checked></td>--}}
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div id="customers">
            <span class="btn btn-success mb-4" onclick="generatePeptinaExcel(ids2);">دانلود اکسل</span>
            <table class="table table-striped" id="transactions-table">
                <thead>
                <tr>
                    <th>شماره</th>
                    <th>مشتری</th>
                    <th>تاریخ</th>
                    <th>مبلغ(ریال)</th>
                    <th>کاربر مرتبط</th>
                    <th>عملیات</th>
                    <th>درصد اتصال</th>
                    <th>ضریب</th>
                </tr>
                </thead>
                <tbody>
                @foreach($transactions as $transaction)
                    <tr>
                        <td>{{$transaction->id}}</td>
                        <td>
                            <a href="/customer/transaction/{{$transaction->customer->id}}">{{$transaction->customer->name}}</a>
                        </td>
                        <td>{{verta($transaction->created_at)->formatJalaliDate()}}</td>
                        <td>{{number_format($transaction->amount)}}</td>
                        <td>{{$transaction->customer->user->name}}</td>
                        <td><i class="btn btn-info fa fa-eye" onclick="view_deposit({{$transaction->id}})"
                               title="جزئیات پرداخت"></i></td>
                        <td>{{round($transaction->linkedAmount() / $transaction->amount * 100)}}</td>
                        <td>{!! $transaction->keysun->conversion() !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('files')

    <script>
        {{--let orders = {!! json_encode($orders) !!};--}}
        let ids = {!! json_encode($orders->filter(fn($order)=>($order->keysun->conv>0.4 && $order->keysun->conv<1.5))->pluck('keysun.id')) !!};
        let ids2 = {!! json_encode($transactions->filter(fn($transaction)=>($transaction->keysun->conv>0.4 && $transaction->keysun->conv<1.5))->pluck('keysun.id')) !!}
        $(function () {
            $('.checkboxradio').checkboxradio();
            $('#main-tabs').tabs();

            $('#peptina-table, #transactions-table').DataTable({
                language: language,
                paging: false,
            })
            new mds.MdsPersianDateTimePicker($('#from')[0], {
                targetTextSelector: '#from',
            });

            new mds.MdsPersianDateTimePicker($('#to')[0], {
                targetTextSelector: '#to',
            });
            setTimeout(() => {
                $('#from').val('{{$_POST['from'] ?? '1404/1/1'}}');
                $('#to').val('{{$_POST['to'] ?? verta()->formatJalaliDate()}}');
            }, 500)

        });

        function generatePeptinaExcel(ids) {
            $.post('/keysun/orders/excel', {
                _token: token,
                ids : ids,
            })
                .done(res => {
                    let table1 = document.createElement('table');
                    $(table1).html(res[0]).attr('data-excel-name', 'صورتحساب');
                    let table2 = document.createElement('table');
                    $(table2).html(res[1]).attr('data-excel-name', 'اقلام صورتحساب');
                    let table2excel = new Table2Excel();
                    table2excel.export([table1, table2], 'keysun');
                    $(table1).remove();
                    $(table2).remove();
                })
        }

        function changeKeysun(id) {
            $.get('/keysun/change/' + id, {
                _token: token
            })
                .done(res => {
                    dialog = Dialog(res);
                })
        }
    </script>
@endsection
