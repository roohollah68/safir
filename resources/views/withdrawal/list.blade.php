@extends('layout.main')

@section('title')
    لیست درخواست وجه
@endsection

@section('content')
    <a class="btn btn-info" href="{{route('addWithdrawal')}}">ثبت درخواست وجه جدید</a>
    <br>
    <br>
    <table class="table table-striped" id="withdrawal-table">
        <thead>
        <tr>
            <th>شماره</th>
            <th>تاریخ ثبت</th>
            <th>درخواست کننده</th>
            <th>مبلغ (ریال)</th>
            <th>توضیحات</th>
            <th>وضعیت</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>

        @foreach($withdrawals as $id => $withdrawal)

            <tr>
                <td>{{$id}}</td>
                <td>{{verta($withdrawal->created_at)->formatJalaliDate()}}</td>
                <td>{{$withdrawal->user->name}}</td>
                <td>{{number_format($withdrawal->amount)}}</td>
                <td>{{substr($withdrawal->description , 0, 30)}}<span class="d-none">{{$withdrawal->description}}</span>
                </td>
                <td>
                    @if($withdrawal->pay)
                        <i class="btn btn-success">پرداخت شده</i>
                    @elseif($withdrawal->confirm == 1)
                        <i class="btn btn-info">تایید شده</i>
                    @elseif($withdrawal->confirm == -1)
                        <i class="btn btn-danger">رد شده</i>
                    @else
                        <i class="btn btn-secondary">منتظر بررسی</i>
                    @endif
                </td>
                <td>
                    @if(!$withdrawal->pay && ($user->meta('addWithdrawal') || $user->meta('confirmWithdrawal')))
                        <a class="fa fa-edit btn btn-primary" href="/Withdrawal/edit/{{$id}}"
                           title="ویرایش"></a>
                        <a class="fa fa-trash-alt btn btn-danger" href="/Withdrawal/delete/{{$id}}"
                           title="حذف"></a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection


@section('files')
    @csrf
    <script>
        $(function () {
            $('#withdrawal-table').DataTable();
        });

        {{--        function delete_deposit(id) {--}}
        {{--            confirm("برای همیشه حذف شود؟") ?--}}
        {{--                $.post('/deposit/delete/' + id, {_token: "{{ csrf_token() }}"})--}}
        {{--                    .done(res => {--}}
        {{--                        location.reload();--}}
        {{--                    })--}}
        {{--                :--}}
        {{--                ""--}}
        {{--        }--}}

        {{--        @if($superAdmin)--}}
        {{--        function confirm_deposit(id) {--}}
        {{--            $.post('/deposit/changeConfirm/' + id, {_token: "{{ csrf_token() }}"})--}}
        {{--                .done(res => {--}}
        {{--                    if (res) {--}}
        {{--                        $('#confirm' + id).removeClass('btn-danger').addClass('btn-success').html('تایید شده');--}}
        {{--                        $('#operation' + id).hide();--}}
        {{--                    } else {--}}
        {{--                        $('#confirm' + id).removeClass('btn-success').addClass('btn-danger').html('تایید نشده');--}}
        {{--                        $('#operation' + id).show();--}}
        {{--                    }--}}
        {{--                })--}}
        {{--        }--}}
        {{--        @endif--}}

    </script>
@endsection
