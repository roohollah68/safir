@extends('layout.main')

@section('title')
    تاریخچه تراکنش ها
@endsection

@section('content')
    @csrf
    <br>
    <div class="w-100 m-2 p-2 bg-info rounded">
        <span>نام مشتری:</span> <b>{{$customer->name}}</b><br>
        <span>شماره مشتری:</span> <b>{{$customer->id}}</b><br>
        <span>شماره تماس:</span> <b>{{$customer->phone}}</b><br>
        <span>آدرس:</span> <b>{{$customer->address}}</b><br>
        <span>کد پستی:</span> <b>{{$customer->zip_code}}</b><br>
        <span>اعتبار:</span> <b dir="ltr">{{$customer->balance}}</b><br>
        <a class="fa fa-edit btn btn-primary"
           href="/customer/edit/{{$customer->id}}"
           title="ویرایش مشتری">
        </a>
    </div>
    <a class="btn btn-info" href="/customerDeposit/add/{{$customer->id}}">ثبت واریزی</a>
    <br>
    <table class="stripe" id="transaction-table">
        <br>
        <thead>
        <tr>
            <th>id</th>
            <th>زمان</th>
            <th>توضیح</th>
            <th>مقدار(تومان)</th>
            <th>اعتبار(تومان)</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($transactions as $tran)
            <tr style="color:{{$tran->type?'green':'red'}}">
                <td>{{$tran->id}}</td>
                <td>{{verta($tran->created_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</td>
                <td>{{$tran->description}}</td>
                <td dir="ltr">{{number_format($tran->amount)}}</td>
                <td dir="ltr">{{number_format($tran->balance)}}</td>
                <td>

                    @if($tran->order_id)
                        @if(!$tran->type && !$tran->deleted)
                            <a class="btn btn-info" href="/invoice/{{$tran->order_id}}" target="_blank">مشاهده فاکتور </a>
                        @endif
                    @else
                        @if($tran->type && !$tran->deleted)
                            <a class="btn btn-danger" onclick="deleteDeposit({{$tran->id}})">حذف</a>
                        @endif
                        @if($tran->photo)
                            <a class="btn btn-info" href="/deposit/{{$tran->photo}}" target="_blank">مشاهده سند</a>
                        @endif
                    @endif


                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection


@section('files')
    <script>
        let token;
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
            token = $('input[name=_token]').val();
        });

        function deleteDeposit(id) {
            let text = "آیا میخواهید سند واریزی باطل شود!";
            if (confirm(text) == true) {
                $.post('/customerDeposit/delete/' + id, {_token: token})
                    .done(res => {
                        location.reload()
                    });
            }
        }
    </script>
@endsection
