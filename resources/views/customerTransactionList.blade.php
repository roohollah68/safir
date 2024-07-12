@extends('layout.main')

@section('title')
    تاریخچه تراکنش ها
@endsection

@section('content')
    @csrf
    <br>
    <div class="w-100 m-2 p-2 bg-info rounded">
        <span>نام مشتری:</span> <b>{{$customer->name}}</b><br>
        @if($superAdmin)
            <span>نام کاربر مرتبط:</span> <b>{{$customer->user()->first()->name}}</b><br>
        @endif
        <span>شماره مشتری:</span> <b>{{$customer->id}}</b><br>
        <span>شماره تماس:</span> <b>{{$customer->phone}}</b><br>
        <span>شهر:</span> <b>{{$customer->cityName()}}</b><br>
        <span>آدرس:</span> <b>{{$customer->address}}</b><br>
        <span>کد پستی:</span> <b>{{$customer->zip_code}}</b><br>
        <span class="h3">بدهکاری:</span> <b dir="ltr"
                                            class="h3 text-danger">{{number_format($customer->balance)}}</b><span
            class="h3">ریال</span><br>
        <a class="btn btn-secondary fa fa-file-pdf" title="گردش حساب" href="/customer/SOA/{{$customer->id}}"></a>
        <a class="fa fa-edit btn btn-primary"
           href="/customer/edit/{{$customer->id}}"
           title="ویرایش مشتری">
        </a>
    </div>
    <a class="btn btn-info" href="/customerDeposit/add/{{$customer->id}}">ثبت واریزی</a>
    <a class="btn btn-secondary" href="{{route('CustomerList')}}">بازگشت</a>
    <br>
    <span class="btn btn-warning" onclick="$('.deleted').toggle()"><span class="fa fa-check deleted"></span> نمایش موارد حذف شده</span>
    <br>
    <table class="stripe" id="transaction-table">
        <br>
        <thead>
        <tr>
            <th>id</th>
            <th>زمان</th>
            <th>توضیح</th>
            <th>وضعیت</th>
            <th>بستانکاری(ریال)</th>
            <th>بدهکاری(ریال)</th>
            {{--            <th>بدهی کل(ریال)</th>--}}
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>

        @foreach($transactions as $tran)
            <tr style="color:{{$tran->type?'green':'red'}}" class="{{$tran->deleted?'deleted':''}}">
                <td>{{$tran->id}}</td>
                <td>{{verta($tran->created_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</td>
                <td>{{$tran->description}}</td>
                <td>
                    @if(!$tran->order_id)
                        @if($tran->verified == 'waiting')
                            <i class="btn btn-info">در انتظار بررسی</i>
                        @elseif($tran->verified == 'approved')
                            <i class="btn btn-success">تایید شده</i>
                        @elseif($tran->verified == 'rejected')
                            <i class="btn btn-danger">رد شده</i>
                        @endif
                    @endif
                </td>
                <td dir="ltr">{{$tran->type?number_format($tran->amount):'0'}}</td>
                <td dir="ltr">{{!$tran->type?number_format($tran->amount):'0'}}</td>
                {{--                <td dir="ltr">{{number_format($tran->balance)}}</td>--}}
                <td>
                    @if($tran->order_id)
                        <a class="btn btn-info fa fa-eye" onclick="view_order({{$tran->order_id}})"
                           title="مشاهده فاکتور"></a>
                        @if(!$tran->deleted)
                            <a class="fa fa-file-invoice-dollar btn btn-secondary"
                               onclick="invoice({{$tran->order_id}})" title=" فاکتور"></a>
                            @if(!$tran->paymentLink)
                                <a class="btn btn-outline-success"
                                   href="/customerDeposit/add/{{$customer->id}}/{{$tran->id}}">پرداخت فاکتور </a>
                            @elseif($transactions[$tran->paymentLink]->verified == 'approved')
                                <a class="btn btn-success fa fa-check" title="پرداخت شده"></a>
                            @endif
                        @endif
                    @else
                        @if($tran->type && !$tran->deleted && $tran->verified != 'approved')
                            <a class="btn btn-danger fa fa-trash" onclick="deleteDeposit({{$tran->id}})"></a>
                        @endif
                    @endif
                    @if($tran->photo)
                        <a class="btn btn-info fa fa-image" href="/deposit/{{$tran->photo}}" target="_blank"></a>
                    @endif
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
                paging: false,
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

                function cancelInvoice(id) {
            if (confirm('آیا از حذف کردن فاکتور مطمئن هستید؟')) {
                $.post('/cancel_invoice/' + id, {_token: token})
                    .done(res => {
                        location.reload()
                    });
            }

        }

    </script>
    <style>
        .deleted {
            display: none;
        }
    </style>
@endsection
