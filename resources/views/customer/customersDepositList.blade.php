@extends('layout.main')

@section('title')
    بررسی واریزی مشتریان
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

    <table id="transaction-table" class="table table-striped">
        <br>
        <thead>
        <tr>
            <th>زمان</th>
            <th>مبلغ(ریال)</th>
            <th>توضیح</th>
            <th>وضعیت</th>
            <th>مشتری</th>
            <th>کاربر مرتبط</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($transactions as $tran)
            @continue($selectedUser != 'all' && $tran->customer->user->id != $selectedUser)
            <tr class="hide {{$tran->verified}}">
                <td>
                    <span class="d-none">
                         {{verta($tran->created_at)->getTimestamp()}}
                    </span>
                    {{verta($tran->created_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}
                </td>
                <td dir="ltr">{{number_format($tran->amount)}}</td>
                <td>{{$tran->description}}</td>
                <td id="status_{{$tran->id}}">
                    @if($tran->verified == 'waiting')
                        <i class="btn btn-info">در انتظار بررسی</i>
                    @elseif($tran->verified == 'approved')
                        <i class="btn btn-success">تایید شده</i>
                    @elseif($tran->verified == 'rejected')
                        <i class="btn btn-danger">رد شده</i>
                    @endif
                </td>
                <td><a href="/customer/transaction/{{$tran->customer_id}}">{{$tran->customer->name}}</a></td>
                <td>{{$tran->customer->user->name}}</td>
                <td>
                    @if($tran->paymentLink && isset($transactions[$tran->paymentLink]))
                        <a class="btn btn-info fa fa-eye"
                           onclick="view_order({{$transactions[$tran->paymentLink]->order_id}})"
                           title="مشاهده سفارش"></a>
                        <a class="fa fa-file-invoice-dollar btn btn-secondary"
                           onclick="invoice({{$transactions[$tran->paymentLink]->order_id}})"
                           title=" فاکتور"></a>

                    @endif
                    <a class="btn btn-info fa fa-image" href="/deposit/{{$tran->photo}}" target="_blank"></a>
                    <span id="button_{{$tran->id}}">
                        @if($tran->verified == 'waiting')
                            <span id="" class="btn btn-success fa fa-check"
                                  onclick="approveDeposit({{$tran->id}})"></span>
                            <span class="btn btn-danger fa fa-x"
                                  onclick="rejectDeposit({{$tran->id}})"></span>
                        @elseif($tran->verified == 'rejected')
                            <span class="btn btn-success fa fa-check"
                                  onclick="approveDeposit({{$tran->id}})"></span>
                        @elseif($tran->verified == 'approved')
                            <span class="btn btn-danger fa fa-x"
                                  onclick="rejectDeposit({{$tran->id}})"></span>
                        @endif
                            </span>
                </td>
            </tr>
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
                // pageLength: 100,
                paging: false,
            });
            token = $('input[name=_token]').val();
            $('input[type=radio]').checkboxradio();
        });


        function approveDeposit(id) {
            $.post('/approveDeposit/' + id, {_token: token})
                .done(res => {
                    $('#button_' + id).html(rejectButton(id));
                    $('#status_' + id).html('<i class="btn btn-success">تایید شده</i>');
                })
        }

        function rejectDeposit(id) {
            let reason = prompt("لطفا دلیل رد سند واریز را بنویسید", "");
            if (reason != null) {
                $.post('/rejectDeposit/' + id, {_token: token, reason: reason})
                    .done(res => {
                        $('#button_' + id).html(approveButton(id));
                        $('#status_' + id).html('<i class="btn btn-danger">رد شده</i>');
                    })
            }
        }

        function approveButton(id) {
            return `<span class="btn btn-success fa fa-check"
          onclick="approveDeposit(${id})"></span>`;
        }

        function rejectButton(id) {
            return `<span class="btn btn-danger fa fa-x"
onclick="rejectDeposit(${id})"></span>`;
        }


    </script>
    <style>
        .waiting {
            display: table-row;
        }
    </style>
@endsection
