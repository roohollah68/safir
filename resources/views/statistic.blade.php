@extends('layout.main')

@section('title')
    آمار فروش محصولات
@endsection

@section('content')
    <form action="">

        <div class="form-group">
            <div class="input-group">
                <div class="input-group-addon" data-mddatetimepicker="true" data-trigger="click" data-targetselector="#fromDate2" data-groupid="group2" data-fromdate="true" data-enabletimepicker="false" data-placement="left">
                    <span class="glyphicon glyphicon-calendar"></span>
                </div>
                <input type="text" name="start" value="{{$start}}" class="form-control" id="fromDate2" placeholder="از تاریخ" data-mddatetimepicker="true" data-trigger="click" data-targetselector="#fromDate2" data-groupid="group2" data-fromdate="true" data-enabletimepicker="false" data-placement="right" />
            </div>

            <div class="input-group">
                <div class="input-group-addon" data-mddatetimepicker="true" data-trigger="click" data-targetselector="#toDate2" data-groupid="group2" data-todate="true" data-enabletimepicker="false" data-placement="left">
                    <span class="glyphicon glyphicon-calendar"></span>
                </div>
                <input type="text" name="end" value="{{$end}}" class="form-control" id="toDate2" placeholder="تا تاریخ" data-mddatetimepicker="true" data-trigger="click" data-targetselector="#toDate2" data-groupid="group2" data-todate="true" data-enabletimepicker="false" data-placement="right" />
            </div>
        </div>
      <br>
        <input class="btn btn-success" type="submit" value="اعمال">
    </form>


    <br>
    <h4>کل مبلغ آمار فروش در این دوره :   <span>{{number_format($totalSale)}}</span> ریال </h4>
    <br>
    <p>نمایش آمار از <span>{{$start}}</span> تا <span>{{$end}}</span></p>
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

@endsection


@section('files')
    <script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/popper.min.js"></script>
    <script src="/date-time-picker/mds.bs.datetimepicker.js"></script>
    <link rel="stylesheet" href="/date-time-picker/mds.bs.datetimepicker.style.css">

    @csrf
    <script>
        $(function () {
            $('#statistic-table').DataTable({
                order: [[2, "desc"]],
            });
        });
    </script>


@endsection
