@extends('layout.main')

@section('title')
    بررسی سفارشات مشتریان
@endsection

@section('content')
    @csrf

    <form method="get" action="">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="user" class="input-group-text w-100">کاربر مرتبط:</label>
                    </div>
                    <select class="form-control" name="user" id="user">
                        <option value="all"
                                selected
                        >همه
                        </option>
                        @foreach($users as $user)
                            <option value="{{$user->id}}" @selected(isset($_GET['user']) && $user->id == $_GET['user'])>
                                {{$user->name}}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group input-group">
                    <input type="radio" id="waiting" name="verified" onclick="$('.hide').hide();$('.waiting').show()"
                           checked>
                    <label for="waiting">در انتظار بررسی</label>
                    <input type="radio" id="approved" name="verified" onclick="$('.hide').hide();$('.approved').show()">
                    <label for="approved">تایید شده</label>
                    <input type="radio" id="rejected" name="verified" onclick="$('.hide').hide();$('.rejected').show()">
                    <label for="rejected">رد شده</label>
                </div>
            </div>
        </div>
        <input type="submit" class="btn btn-primary" value="فیلتر">
    </form>
    <br>

    <table id="transaction-table" class="stripe">
        <br>
        <thead>
        <tr>
            <th>زمان</th>
            <th>مبلغ(ریال)</th>
            <th>نحوه پرداخت</th>
            <th>توضیح</th>
            <th>وضعیت</th>
            <th>مشتری</th>
            <th>کاربر مرتبط</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($orders as $order)

            @if($order->confirm && (!$order->status || $order->status==4) &&
                    $order->user->admin() &&
                    ($selectedUser == 'all' || $order->customer->user->id == $selectedUser))

                <tr class="hide {{$order->counter}}">
                    <td>
                        <span class="d-none">
                            {{verta($order->created_at)->getTimestamp()}}
                        </span>
                        {{verta($order->created_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}
                    </td>
                    <td dir="ltr">{{number_format($order->total)}}</td>
                    <td>{{$order->payMethod()}}</td>
                    <td>{{$order->paymentNote}}</td>
                    <td id="status_{{$order->id}}">
                        @if($order->counter == 'waiting')
                            <i class="btn btn-info">در انتظار بررسی</i>
                        @elseif($order->counter == 'approved')
                            <i class="btn btn-success">تایید شده</i>
                        @elseif($order->counter == 'rejected')
                            <i class="btn btn-danger">رد شده</i>
                        @endif
                    </td>
                    <td><a href="/customer/transaction/{{$order->customer_id}}">{{$order->customer->name}}</a></td>
                    <td>{{$order->user->name}}</td>
                    <td>
                        <a class="btn btn-info fa fa-eye"
                           onclick="view_order({{$order->id}})"
                           title="مشاهده سفارش"></a>
                        <a class="fa fa-file-invoice-dollar btn btn-secondary"
                           onclick="invoice({{$order->id}})"
                           title=" فاکتور"></a>
                        @if($order->receipt)
                            <a class="btn btn-info fa fa-image" href="/deposit/{{$order->receipt}}" target="_blank"></a>

                        @endif
                        <span id="button_{{$order->id}}">
                                @if($order->counter == 'waiting')
                                <span id="" class="btn btn-success fa fa-check"
                                      onclick="approveOrder({{$order->id}})"></span>
                                <span class="btn btn-danger fa fa-x"
                                      onclick="rejectOrder({{$order->id}})"></span>
                            @elseif($order->counter == 'rejected')
                                <span class="btn btn-success fa fa-check"
                                      onclick="approveOrder({{$order->id}})"></span>
                            @elseif($order->counter == 'approved')
                                <span class="btn btn-danger fa fa-x"
                                      onclick="rejectOrder({{$order->id}})"></span>
                            @endif
                            </span>
                    </td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
    <div id="invoice-wrapper"></div>
@endsection


@section('files')
    <script>
        let token;
        $(function () {
            $('#transaction-table').DataTable({
                order: [[0, "desc"]],
                pageLength: 100,
            });
            token = $('input[name=_token]').val();
            $('input[type=radio]').checkboxradio();
        });

        function approveOrder(id) {
            $.post('/approveOrder/' + id, {_token: token})
                .done(res => {
                    $('#button_' + id).html(rejectButton(id));
                    $('#status_' + id).html('<i class="btn btn-success">تایید شده</i>');
                })
        }

        function rejectOrder(id) {
            let reason = prompt("لطفا دلیل رد فاکتور را بنویسید", "");
            if (reason != null) {
                $.post('/rejectOrder/' + id, {_token: token, reason: reason})
                    .done(res => {
                        $('#button_' + id).html(approveButton(id));
                        $('#status_' + id).html('<i class="btn btn-danger">رد شده</i>');
                    })
            }
        }

        function approveButton(id) {
            return `<span class="btn btn-success fa fa-check"
          onclick="approveOrder(${id})"></span>`;
        }

        function rejectButton(id) {
            return `<span class="btn btn-danger fa fa-x"
onclick="rejectOrder(${id})"></span>`;
        }


    </script>

    <style>
        .waiting {
            display: table-row;
        }
    </style>
@endsection
