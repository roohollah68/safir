@extends('layout.main')

@section('title')
    تاریخچه تراکنش ها
@endsection

@section('content')

    <br>
    <table class="table table-striped" id="transaction-table">
        <thead>
        <tr>
            <th>#</th>
            <th>تاریخ</th>
            <th>توضیح</th>
            <th>مقدار(ریال)</th>
            <th>اعتبار(ریال)</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($transactions as $tran)
            <tr style="color:{{$tran->type?'green':'red'}}">
                <td>{{$tran->id}}</td>
                <td>{{verta($tran->created_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</td>
                <td>{{$tran->description}}</td>
                <td>{{number_format($tran->amount)}}</td>
                <td dir="ltr">{{number_format($tran->balance)}}</td>
                <td>
                    @if($tran->order_id)
                        <span class="btn btn-info fa fa-eye" onclick="view_order({{$tran->order_id}})"></span>
                    @endif
                    @if($tran->deposit_id)
                        <span class="btn btn-primary fa fa-eye"
                              onclick="view_safir_deposit({{$tran->deposit_id}})"></span>
                    @endif
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
                language: language,
            });
        });
    </script>
@endsection
