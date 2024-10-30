@extends('layout.main')

@section('title')
    پیگیری پرداختی مشتریان
@endsection

@section('content')
    <table class="table table-striped" id="orders-table">
        <thead>
        <tr>
            <th>شماره</th>
            <th>تاریخ ثبت</th>
            <th>نام مشتری</th>
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
                <td>{{verta($order->created_at)->formatJalaliDate()}}</td>
                <td><a href="/customer/transaction/{{$order->customer_id}}">{{$order->name}}</a></td>
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
                    <a class="fa fa-comment btn btn-info" onclick="view_comment({{$id}})"></a>
                    <span class="btn btn-primary fa fa-chain" onclick="showOrderLink({{$id}})"></span>
                    @if(auth()->user()->meta('allCustomers'))
                        <span class="btn btn-secondary fa fa-clock" onclick="postpond({{$id}})"></span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div id="postpond">
        <div title="به تعویق انداختن پرداخت" class="dialogs">
            <input type="number" value="" id="days" style="width: 120px">
            <span class="btn btn-info m-1" onclick="postpondDay($('#days').val())">روز بعد</span><br>
            <span class="btn btn-outline-success m-1" onclick="postpondDay(1)">1 روز بعد</span><br>
            <span class="btn btn-outline-success m-1" onclick="postpondDay(7)">1 هفته بعد</span><br>
            <span class="btn btn-outline-success m-1" onclick="postpondDay(14)">2 هفته بعد</span><br>
            <span class="btn btn-outline-success m-1" onclick="postpondDay(30)">1 ماه بعد</span><br>
            <span class="btn btn-outline-success m-1" onclick="postpondDay(180)">6 ماه بعد</span><br>
        </div>
    </div>

@endsection


@section('files')
    <script>
        let token = '{{csrf_token()}}';
        let postpondText;
        let postpondId;
        $(function () {
            postpondText = $('#postpond').html();
            $('#postpond').html('');

            $('#orders-table').DataTable({
                paging: false,
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
        @if(auth()->user()->meta('allCustomers'))
        function postpond(id) {
            postpondId = id;
            dialog = Dialog(postpondText);
        }

        function postpondDay(days) {
            dialog.remove();
            $.get('/postpondDay/' + postpondId + '/' + days).done(() => {
                location.reload();
            })
        }
        @endif

    </script>
@endsection
