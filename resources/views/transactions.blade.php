@extends('layout.main')

@section('title')
    تاریخچه تراکنش ها
@endsection

@section('content')

    <br>
    <table class="stripe" id="transaction-table">
        <thead>
        <tr>
            <th>id</th>
            <th>تاریخ</th>
            <th>توضیح</th>
            <th>مقدار(تومان)</th>
            <th>اعتبار(تومان)</th>
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
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection


@section('files')
    <script>
        $(function () {
            $('#transaction-table').DataTable({
                columnDefs: [
                    {
                        targets: [2, 3, 4],
                        orderable: false
                    },

                    {
                        targets: [0],
                        visible: false
                    }
                ],
                order: [[0, "desc"]],
            });
        });
    </script>
@endsection
