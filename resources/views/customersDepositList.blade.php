@extends('layout.main')

@section('title')
    تاریخچه واریزی مشتریان
@endsection

@section('content')
    @csrf
    <br>
{{--    <div class="w-100 m-2 p-2 bg-info rounded">--}}
{{--        <span>نام مشتری:</span> <b>{{$customer->name}}</b><br>--}}
{{--        @if($superAdmin)--}}
{{--        <span>نام کاربر مرتبط:</span> <b>{{$customer->user()->first()->name}}</b><br>--}}
{{--        @endif--}}
{{--        <span>شماره مشتری:</span> <b>{{$customer->id}}</b><br>--}}
{{--        <span>شماره تماس:</span> <b>{{$customer->phone}}</b><br>--}}
{{--        <span>شهر:</span> <b>{{$customer->cityName()}}</b><br>--}}
{{--        <span>آدرس:</span> <b>{{$customer->address}}</b><br>--}}
{{--        <span>کد پستی:</span> <b>{{$customer->zip_code}}</b><br>--}}
{{--        <span class="h3">بدهکاری:</span> <b dir="ltr"--}}
{{--                                            class="h3 text-danger">{{number_format($customer->balance)}}</b><span--}}
{{--            class="h3">ریال</span><br>--}}
{{--        <a class="fa fa-edit btn btn-primary"--}}
{{--           href="/customer/edit/{{$customer->id}}"--}}
{{--           title="ویرایش مشتری">--}}
{{--        </a>--}}
{{--    </div>--}}

{{--    <a class="btn btn-info" href="/customerDeposit/add/{{$customer->id}}">ثبت واریزی</a>--}}
{{--    <a class="btn btn-secondary" href="{{route('CustomerList')}}">بازگشت</a>--}}
{{--    <br>--}}
{{--    <span class="btn btn-warning" onclick="$('.deleted').toggle()"><span class="fa fa-check deleted"></span> نمایش موارد حذف شده</span>--}}
{{--    <br>--}}
    <table class="stripe" id="transaction-table">
        <br>
        <thead>
        <tr>
            <th>id</th>
            <th>زمان</th>
            <th>توضیح</th>
            <th>مشتری</th>
            <th>کاربر مرتبط</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($transactions as $tran)
            <tr class="{{$tran->deleted?'deleted':''}}">
                <td>{{$tran->id}}</td>
                <td>{{verta($tran->created_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</td>
                <td>{{$tran->description}}</td>
                <td dir="ltr"><a href="/customer/transaction/{{$tran->customer()->first()->id}}">{{$tran->customer()->first()->name}}</a> </td>
                <td dir="ltr">{{$tran->customer()->first()->user()->first()->name}}</td>
                <td>
                    @if($tran->order_id)
                            <a class="btn btn-info fa fa-eye" onclick="view_order({{$tran->order_id}})"
                               title="مشاهده فاکتور"></a>
                            <a class="fa fa-file-invoice-dollar btn btn-secondary"
                               onclick="invoice({{$tran->order_id}})" title=" فاکتور"></a>

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

        let totalPages = 1;
        let firstPageItems = 40;

        function invoice(id) {
            $.post('/invoice/' + id, {_token: token, firstPageItems: firstPageItems, totalPages: totalPages})
                .done(res => {
                    $('#invoice-wrapper').html(res[0][0]);
                    if ($('#invoice-content')[0].offsetHeight > 2900) {
                        totalPages = 2;
                        firstPageItems--;
                        invoice(id);
                        return
                    }
                    domtoimage.toJpeg($('#invoice')[0], {width: 2100, height: 2970})
                        .then(function (dataUrl) {
                            let link = document.createElement('a');
                            link.download = res[0][1] + '.jpg';
                            link.href = dataUrl;
                            link.click();
                            $('#invoice-wrapper').html('');
                            if (res.length > 1) {
                                $('#invoice-wrapper').html(res[1][0]);
                                domtoimage.toJpeg($('#invoice')[0], {width: 2100, height: 2970})
                                    .then(function (dataUrl) {
                                        let link = document.createElement('a');
                                        link.download = res[1][1] + '.jpg';
                                        link.href = dataUrl;
                                        link.click();
                                        $('#invoice-wrapper').html('');
                                        totalPages = 1;
                                        firstPageItems = 40;
                                    });
                            }
                        });
                })
        }

        function view_order(id) {
            $.post('/viewOrder/' + id, {_token: token})
                .done(res => {
                    $(res).dialog({
                        modal: true,
                        open: () => {
                            $('.ui-dialog-titlebar-close').hide();
                            $('.ui-widget-overlay').bind('click', function () {
                                $(".dialogs").dialog('close');
                            });
                        }
                    });
                })
        }

    </script>
    <script src="/js/dom-to-image.min.js"></script>
    <style>
        .deleted {
            display: none;
        }
    </style>
@endsection
