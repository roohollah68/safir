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
                    <select class="form-control" name="user" id="user"
                            onchange="window.location.replace('?{{$get}}user_id='+this.value)">
                        <option value="" selected>همه</option>
                        @foreach($users as $id => $user)
                            <option value="{{$id}}" @selected($id == $user_id)>
                                {{$user->name}}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group input-group">
                    <a href="?{{$get}}verified=waiting" class="btn btn-{{$verified == 'waiting'?'':'outline-'}}info m-2">در انتظار بررسی</a>
                    <a href="?{{$get}}verified=approved" class="btn btn-{{$verified == 'approved'?'':'outline-'}}success m-2">تایید شده</a>
                    <a href="?{{$get}}verified=rejected" class="btn btn-{{$verified == 'rejected'?'':'outline-'}}danger m-2">رد شده</a>
                </div>
            </div>
        </div>
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
        @foreach($transactions as $id => $tran)
            <tr>
                <td>
                    <span class="d-none">
                         {{verta($tran->created_at)->getTimestamp()}}
                    </span>
                    {{verta($tran->created_at)->timezone('Asia/tehran')->formatJalaliDate()}}
                </td>
                <td dir="ltr">{{number_format($tran->amount)}}</td>
                <td>{{$tran->description}}</td>
                <td id="status_{{$tran->id}}">{!! $tran->verified() !!}</td>
                <td><a href="/customer/transaction/{{$tran->customer_id}}">{{$tran->customer->name}}</a></td>
                <td>{{$tran->customer->user->name}}</td>
                <td>
                    <i class="btn btn-info fa fa-eye" onclick="view_deposit({{$id}})" title="جزئیات پرداخت"></i>
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
@endsection


@section('files')
    <script>

        $(function () {
            $('#transaction-table').DataTable({
                order: [[0, "desc"]],
                pageLength: 100,
                // paging: false,
            });
            $('input[type=radio]').checkboxradio();
        });


        function approveDeposit(id) {
            $.post('/approveDeposit/' + id, {_token: token})
                .done(res => {
                    if(res == 'approved') {
                        $('#button_' + id).html(rejectButton(id));
                        $('#status_' + id).html('<i class="btn btn-success">تایید شده</i>');
                    }
                })
        }

        function rejectDeposit(id) {
            let reason = prompt("لطفا دلیل رد سند واریز را بنویسید", "");
            if (reason != null) {
                $.post('/rejectDeposit/' + id, {_token: token, reason: reason})
                    .done(res => {
                        if(res == 'rejected') {
                            $('#button_' + id).html(approveButton(id));
                            $('#status_' + id).html('<i class="btn btn-danger">رد شده</i>');
                        }
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

@endsection
