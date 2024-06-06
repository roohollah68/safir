@extends('layout.main')

@section('title')
    تاریخچه واریزی مشتریان
@endsection

@section('content')
    @csrf

    <form method="get" action="">
        <div class="col-md-6">
            <div class="form-group input-group required">
                <div class="input-group-append" style="min-width: 160px">
                    <label for="user" class="input-group-text w-100">کاربر مرتبط:</label>
                </div>
                <select class="form-control" name="user" id="user">
                    <option value="all"
                            selected
                    >همه
                    </option>
                    @foreach($users as $user)
                        <option value="{{$user->id}}"
                                @if( isset($_GET['user']) && $user->id == $_GET['user'])
                                selected
                            @endif
                        >{{$user->name}}</option>
                    @endforeach
                </select> <input type="submit" class="btn btn-primary" value="فیلتر">
            </div>
        </div>
    </form>
    <br>

    <table  id="transaction-table">
        <br>
        <thead>
        <tr>
            <th>زمان</th>
            <th>مبلغ(ریال)</th>
            <th>توضیح</th>
            <th>مشتری</th>
            <th>کاربر مرتبط</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($transactions as $tran)
            @if($selectedUser == 'all' || $tran->customer()->first()->user()->first()->id== $selectedUser)
            <tr class="test {{$tran->deleted?'deleted':''}} {{$tran->verified}}">
                <td><span class="d-none">{{verta($tran->created_at)->timestamp}}</span>{{verta($tran->created_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</td>
                <td dir="ltr">{{number_format($tran->amount)}}</td>
                <td>{{$tran->description}}</td>
                <td ><a href="/customer/transaction/{{$tran->customer_id}}">{{$tran->customer->name}}</a> </td>
                <td >{{$tran->customer->user->name}}</td>
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

        .waiting{
            background-color: lightblue;
        }

        .approved{
            background-color: lightgreen;
        }

        .rejected{
            background-color: lightyellow;
        }
    </style>
@endsection
