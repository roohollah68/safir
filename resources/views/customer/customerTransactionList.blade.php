@extends('layout.main')

@section('title')
    تاریخچه تراکنش ها
@endsection

@section('content')
    @csrf
    <br>
    <div class="w-100 m-2 p-2 bg-info rounded">
        <span>نام مشتری:</span> <b>{{$customer->name}}</b><br>
        <span>نام کاربر مرتبط:</span> <b>{{$customer->user->name}}</b><br>
        <span>شماره مشتری:</span> <b>{{$customer->id}}</b><br>
        <span>شماره تماس:</span> <b>{{$customer->phone}}</b><br>
        <span>شهر:</span> <b>{{$customer->city->name}}</b><br>
        <span>آدرس:</span> <b>{{$customer->address}}</b><br>
        <span>کد پستی:</span> <b>{{$customer->zip_code}}</b><br>
        <span class="h3">بدهکاری:</span>
        <b dir="ltr" class="h3 text-danger">{{number_format($customer->balance)}}</b>
        <span class="h3">ریال</span><br>
        <a class="btn btn-secondary fa fa-file-pdf" title="گردش حساب"
           onclick="transactionReport({{$customer->id}})"></a>
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
    <div id="tabs">
        <ul>
            <li><a href="#deposits">پرداختی ها</a></li>
            <li><a href="#orders">سفارشات</a></li>
        </ul>
        <div id="deposits">
            <table id="deposit" class="table table-striped">
                <thead>
                <tr>
                    <th>شماره</th>
                    <th>زمان</th>
                    <th>توضیح</th>
                    <th>وضعیت</th>
                    <th>مبلغ(ریال)</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($transactions as $id => $tran)
                    <tr>
                        <td>{{$tran->id}}</td>
                        <td>{{verta($tran->created_at)->timezone('Asia/tehran')->format('Y/m/d')}}</td>
                        <td>{{$tran->description}}</td>
                        <td>
                            @if($tran->verified == 'waiting')
                                <i class="btn btn-info">در انتظار بررسی</i>
                            @elseif($tran->verified == 'approved')
                                <i class="btn btn-success">تایید شده</i>
                            @elseif($tran->verified == 'rejected')
                                <i class="btn btn-danger">رد شده</i>
                            @endif
                        </td>
                        <td dir="ltr">{{number_format($tran->amount)}}</td>
                        <td>
                            @if( $tran->verified != 'rejected')
                                <span class="btn btn-primary fa fa-chain" onclick="showDepositLink({{$id}})"></span>
                            @endif
                            @if( $tran->verified != 'approved')
                                <a class="btn btn-danger fa fa-trash" onclick="deleteDeposit({{$id}})"></a>
                            @endif
                            @if($tran->photo)
                                <a class="btn btn-info fa fa-image" href="/deposit/{{$tran->photo}}"
                                   target="_blank"></a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div id="orders">
            <table class="table table-striped" id="orders-table">
                <thead>
                <tr>
                    <th>شماره</th>
                    <th>زمان</th>
                    <th>وضعیت</th>
                    <th>درصد پرداخت</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $id => $order)
                    <tr>
                        <td>{{$order->id}}</td>
                        <td>{{verta($order->created_at)->timezone('Asia/tehran')->format('Y/m/d')}}</td>
                        <td>
                            @if($order->counter == 'waiting')
                                <i class="btn btn-info">در انتظار بررسی</i>
                            @elseif($order->counter == 'approved')
                                <i class="btn btn-success">تایید شده</i>
                            @elseif($order->counter == 'rejected')
                                <i class="btn btn-danger">رد شده</i>
                            @endif
                        </td>
                        <td>
                            @if($order->payPercent() == 0)
                                <i class="btn btn-danger">0 %</i>
                            @elseif($order->payPercent() == 100)
                                <i class="btn btn-success">100 %</i>
                            @else
                                <i class="btn btn-warning">{{$order->payPercent()}} %</i>
                            @endif
                        </td>
                        <td>
                            <a class="btn btn-info fa fa-eye" onclick="view_order({{$id}})"
                               title="مشاهده فاکتور"></a>
                            <a class="fa fa-file-invoice-dollar btn btn-secondary"
                               onclick="invoice({{$id}})" title=" فاکتور"></a>
                            <span class="btn btn-primary fa fa-chain" onclick="showOrderLink({{$id}})"></span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>



    <div id="invoice-wrapper"></div>
    <div id="transactionReportTXT">
        <div title="مشاهده سفارش" class="dialogs">
            <form method="post" id="report" action="">
                @csrf
                <label for="allTime">همه زمان ها</label>
                <input class="checkboxradio" type="radio" name="timeFilter" id="allTime" value="allTime"
                       onclick="$('#timeInterval').hide()" checked><br><br>
                <label for="specifiedTime">بازه زمانی مشخص</label>
                <input class="checkboxradio" type="radio" name="timeFilter" id="specifiedTime" value="specifiedTime"
                       onclick="$('#timeInterval').show()">
                <div class="input-group col-12 mb-3 hide" id="timeInterval">
                    <div class="col-md-10 d-flex">
                        <span class="input-group-text cursor-pointer" id="date1">📅</span>
                        <input type="text" name="from" class="form-control" placeholder="از تاریخ" id="date1-text">
                    </div>
                    <div class=" col-md-10 d-flex">
                        <span class="input-group-text cursor-pointer" id="date2">📅</span>
                        <input type="text" name="to" class="form-control" placeholder="تا تاریخ" id="date2-text">
                    </div>
                </div>
                <br>
                <hr>
                <br>
                <label for="allInvoice">به همراه فاکتورها</label>
                <input class="checkboxradio" type="checkbox" name="allInvoice" id="allInvoice">
                <br><br>
                <input type="submit" class="btn btn-outline-success" name="submit" value="دریافت فایل">
            </form>

        </div>
    </div>
@endsection


@section('files')
    <script src="/date-time-picker/mds.bs.datetimepicker.js"></script>
    <link rel="stylesheet" href="/date-time-picker/mds.bs.datetimepicker.style.css">
    <script>
        let token = '{{csrf_token()}}';
        let transactionReportTXT;
        $(function () {
            token = $('input[name=_token]').val();
            transactionReportTXT = $('#transactionReportTXT').html();
            $('#transactionReportTXT').html('');
            $('#tabs').tabs();
            $('#deposit').DataTable({
                paging: false,
                order: [[0, "desc"]],
            });
            $('#orders-table').DataTable({
                paging: false,
                order: [[0, "desc"]],
            });
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

        function transactionReport(id) {

            let dialog = Dialog(transactionReportTXT);

            $(".checkboxradio").checkboxradio();

            const date1 = new mds.MdsPersianDateTimePicker($('#date1')[0], {
                targetTextSelector: '#date1-text',
            });
            const date2 = new mds.MdsPersianDateTimePicker($('#date2')[0], {
                targetTextSelector: '#date2-text',
            });

            $("#report").submit(function (e) {
                e.preventDefault();
                $.ajax({
                    type: "post",
                    url: '/customer/SOA/' + id,
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    headers: {
                        "Accept": "application/pdf"
                    }
                }).done(res => {
                    window.open(res, '_blank');
                })
                dialog.remove();
            });
        }

        function showDepositLink(id) {
            $.get('/customer/depositLink/' + id).done((res) => {
                let dialog = Dialog(res);
            })
        }

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

    </script>
    <style>
        .deleted {
            display: none;
        }
    </style>
@endsection
