@extends('layout.main')

@section('title')
    پیگیری پرداختی مشتریان
@endsection

@section('content')
    <form method="get" action="">
        <div class="col-md-6 m-1">
            <div class="form-group input-group ">
                <div class="input-group-append" style="min-width: 160px">
                    <label for="user" class="input-group-text w-100">کاربر مرتبط:</label>
                </div>
                <select class="form-control" name="user" id="user">
                    <option value="all" selected>همه</option>
                    @foreach($users as $id=>$user)
                        <option value="{{$id}}" @selected(isset($_GET['user']) &&  $id == $_GET['user'])>
                            {{$user->name}}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <label for="payInDate">پرداخت در تاریخ</label>
        <input type="checkbox" name="payInDate" id="payInDate" class="checkboxradio" checked>

        <label for="cod">پرداخت در محل</label>
        <input type="checkbox" name="cod" id="cod" class="checkboxradio" checked>

        <label for="else">باقی موارد</label>
        <input type="checkbox" name="else" id="else" class="checkboxradio" checked>
        <br>
        <br>
        <input type="submit" class="btn btn-success" value="فیلتر">

    </form>
    <br>
    <span>مجموع:</span><span class="btn btn-info"> {{number_format($orders->sum('total'))}} ریال</span><br>
    <span>تعداد:</span><span class="btn btn-primary"> {{$orders->count()}} </span><br>
    <br>
    <table class="table table-striped" id="orders-table">
        <thead>
        <tr>
            <th>شماره</th>
            <th>تاریخ تایید سفارش</th>
            <th>تاریخ ارسال</th>
            <th>تاریخ سر رسید پرداخت</th>
            <th>نام مشتری</th>
            <th>نوع پرداخت</th>
            <th>کاربر مرتبط</th>
            <th>مبلغ(ریال)</th>
            <th>درصد پرداخت شده</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($orders as $id => $order)
            <tr>
                <td>{{$id}}</td>
                <td dir="ltr">{{$order->confirmed_at?verta($order->confirmed_at)->formatJalaliDate():'-'}}</td>
                <td dir="ltr">{{$order->sent_at?verta($order->sent_at)->formatJalaliDate():'-'}}</td>
                <td dir="ltr">
                    @if($order->payInDate == '5' || $order->payInDate == 'payInDate')
                        {{$order->payInDate?verta($order->payInDate)->formatJalaliDate():''}}
                    @else
                        {{$order->sent_at?verta($order->sent_at)->addWeeks(2)->formatJalaliDate():''}}
                    @endif
                    {{$order->postponeDate?'->'.verta($order->postponeDate)->formatJalaliDate():''}}

                </td>
                <td><a href="/customer/transaction/{{$order->customer_id}}">{{$order->name}}</a></td>
                <td>{{$order->payMethod()}}</td>
                <td><a href="/customers?user={{$order->user->id}}">{{$order->user->name}}</a></td>
                <td>{{number_format($order->total)}}</td>
                <td>@if($order->payPercent() == 0)
                        <i class="btn btn-danger">0 %</i>
                    @elseif($order->payPercent() == 100)
                        <i class="btn btn-success">100 %</i>
                    @else
                        <i class="btn btn-warning">{{$order->payPercent()}} %</i>
                    @endif
                </td>
                <td>
                    <a class="btn btn-info fa fa-eye" onclick="view_order({{$id}})" title="مشاهده سفارش"></a>
                    <a class="fa fa-file-invoice-dollar btn btn-secondary" onclick="invoice({{$id}})"
                       title=" فاکتور"></a>
                    <a class="fa fa-comment btn btn-info" onclick="view_comment({{$id}})"></a>
                    <span class="btn btn-primary fa fa-chain" onclick="showOrderLink({{$id}})"></span>
                    @if($User->meta('allCustomers'))
                        <span class="btn btn-secondary fa fa-clock" onclick="postponed({{$id}})"></span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div id="postponeDiv">
        <div title="به تعویق انداختن پرداخت" class="dialogs">
            <input type="number" value="" id="days" style="width: 120px">
            <span class="btn btn-info m-1" onclick="postponedDay($('#days').val())">روز بعد</span><br>
            <span class="btn btn-outline-success m-1" onclick="postponedDay(1)">1 روز بعد</span><br>
            <span class="btn btn-outline-success m-1" onclick="postponedDay(7)">1 هفته بعد</span><br>
            <span class="btn btn-outline-success m-1" onclick="postponedDay(14)">2 هفته بعد</span><br>
            <span class="btn btn-outline-success m-1" onclick="postponedDay(30)">1 ماه بعد</span><br>
            <span class="btn btn-outline-success m-1" onclick="postponedDay(180)">6 ماه بعد</span><br>
        </div>
    </div>

@endsection


@section('files')
    <script>
        let postponedText;
        let postponedId;
        $(function () {
            $('.checkboxradio').checkboxradio();
            postponedText = $('#postponeDiv').html();
            $('#postponeDiv').html('');

            $('#orders-table').DataTable({
                // paging: false,
                pageLength: 100,
                order: [[0, "desc"]],
            });
        });

        function showOrderLink(id) {
            $.get('/customer/orderLink/' + id).done((res) => {
                let dialog = Dialog(res);
            })
        }

        function removePayLink(id) {
            $.post('/payLink/delete/' + id, {
                _token: token,
            }).done(() => {
                location.reload();
            })
        }

        function addPayLink(transaction_id, order_id) {
            $.post('/payLink/add/' + transaction_id + '/' + order_id, {
                _token: token,
            }).done(() => {
                location.reload();
            })
        }
        @if(auth()->user()->meta('editAllCustomers'))

        function postponed(id) {
            postponedId = id;
            dialog = Dialog(postponedText);
        }

        function postponedDay(days) {
            dialog.remove();
            $.get('/postponedDay/' + postponedId + '/' + days).done(() => {
                location.reload();
            })
        }
        @endif

    </script>
@endsection
