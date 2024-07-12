@extends('layout.main')

@section('title')
    آمار فروش محصولات
@endsection

@section('content')
    <form action="" method="post" onsubmit="return dateFilter();">
        @csrf
        <div class="input-group col-12 mb-3">
            <div class="col-md-6 d-flex">
                <span class="input-group-text cursor-pointer" id="date1">📅</span>
                <input type="text" name="from" class="form-control" placeholder="از تاریخ" id="date1-text" required>
            </div>
            <div class=" col-md-6 d-flex">
                <span class="input-group-text cursor-pointer" id="date2">📅</span>
                <input type="text" name="to" class="form-control" placeholder="تا تاریخ" id="date2-text" required>
            </div>
        </div>
        <span>نوع فروشنده:</span>
        <label for="safirOrders">سفیران</label>
        <input type="checkbox" id="safirOrders" name="safirOrders" class="checkboxradio" checked>
        <label for="siteOrders">سایت ها</label>
        <input type="checkbox" id="siteOrders" name="siteOrders" class="checkboxradio" checked>
        <label for="adminOrders">فروشگاه ها</label>
        <input type="checkbox" id="adminOrders" name="adminOrders" class="checkboxradio" checked>
        <br>
        <div class="my-3">
            <div class="form-group col-md-4 d-flex">
                <label for="user" class="input-group-text">فروشنده:</label>
                <select class="form-control" name="user">
                    <option value="all" selected>همه</option>
                    @foreach($users as $id=>$user)
                        <option value="{{$id}}">{{$user->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <label for="productBase">بر اساس محصول</label>
        <input type="radio" name="Base" value="productBase" id="productBase" class="checkboxradio" checked>
        <label for="safirBase">بر اساس فروشنده</label>
        <input type="radio" name="Base" value="safirBase" id="safirBase" class="checkboxradio">
        <label for="customerBase">بر اساس مشتری</label>
        <input type="radio" name="Base" value="customerBase" id="customerBase" class="checkboxradio">
        <br>
        <input class="btn btn-success m-3" type="submit" value="اعمال فیلتر">

    </form>

@if(isset($totalSale))
        <br>
        <h4>کل مبلغ آمار فروش در این دوره : <span>{{number_format($totalSale)}}</span> ریال </h4>
        <br>

        <table class="stripe" id="statistic-table">
            <thead>
            <tr>
                <th>نام محصول</th>
                <th>تعداد فروش</th>
                <th>مبلغ کل(ریال)</th>
                <th>قیمت میانگین(ریال)</th>
            </tr>
            </thead>
            <tbody>
            @foreach($products as $product)
                <tr>
                    <td>{{$product->name}}</td>
                    <td>{{$product->number}}</td>
                    <td>{{number_format($product->total)}}</td>
                    <td>{{number_format(($product->number>0)?$product->total/$product->number:0)}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
@endif
@endsection


@section('files')
    <script src="/date-time-picker/mds.bs.datetimepicker.js"></script>
    <link rel="stylesheet" href="/date-time-picker/mds.bs.datetimepicker.style.css">

    <script>
        $(function () {
            $('#statistic-table').DataTable({
                order: [[2, "desc"]],
            });
            $(".checkboxradio").checkboxradio();

            const dtp1Instance2 = new mds.MdsPersianDateTimePicker($('#date1')[0], {
                targetTextSelector: '#date1-text',
                selectedDate: new Date('{{verta()->addMonths(-1)->toCarbon()}}'),
            });
            const dtp1Instance3 = new mds.MdsPersianDateTimePicker($('#date2')[0], {
                targetTextSelector: '#date2-text',
                selectedDate: new Date(),
            });


        });
    </script>

@endsection
