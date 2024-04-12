@extends('layout.main')

@section('title')
    مشاهده سفارشات
@endsection

@section('files')
    @include('orders.js_css')
    <script src="/js/dom-to-image.min.js"></script>
    <script src="/date-time-picker/mds.bs.datetimepicker.js"></script>
    <link rel="stylesheet" href="/date-time-picker/mds.bs.datetimepicker.style.css">
@endsection

@section('content')


    <form action="" method="post" class="input-group col-12 mb-3" onsubmit="return dateFilter();">
        <div class="col-md-6 d-flex">
            <span class="input-group-text cursor-pointer" id="date1">📅</span>
            <input type="text" class="form-control" placeholder="از تاریخ" data-name="date1-text" required>
            <input type="hidden" name="from" data-name="date1-date">
        </div>
        <div class=" col-md-6 d-flex">
            <span class="input-group-text cursor-pointer" id="date2">📅</span>
            <input type="text" class="form-control" placeholder="تا تاریخ" data-name="date2-text" required>
            <input type="hidden" name="to" data-name="date2-date">
            <input type="number" name="limit" value="{{$limit}}" placeholder="تعداد" min="1" max="5000" step="1">
            <input type="submit" value="اعمال فیلتر">
        </div>
    </form>


    <label for="deleted_orders"> سفارشات حذف شده</label>
    <input type="checkbox" id="deleted_orders" class="checkboxradio"
           onclick="deleted = this.checked;prepare_data()">
    @if(!$safir)
        <label for="print-wait"> در انتظار پرینت</label>
        <input type="checkbox" id="print-wait" class="checkboxradio"
               onclick="printWait = this.checked;prepare_data()">

        <label for="confirm-wait"> در انتظار تایید</label>
        <input type="checkbox" id="confirm-wait" class="checkboxradio"
               onclick="confirmWait = this.checked;prepare_data()">
    @endif
    <br>
    @if($superAdmin || $print)
        <div class="my-3">
            <div class="form-group col-md-4 d-flex">

                <label for="user" class="input-group-text">سفیر:</label>

                <select class="form-control" id="user"
                        onchange="user = $('#user option:selected').val() || 'all';prepare_data()">
                    <option value="all" selected>همه</option>
                    @foreach($users as $id=>$user)
                        <option value="{{$id}}">{{$user->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <button class="btn btn-secondary my-2" onclick="generatePDFs()"> فایل PDF انتخابی ها</button>
        <a id="pdf-link"></a>
    @endif

    <table id="main-table" class="stripe">
    </table>
    <div id="invoice-wrapper"></div>




@endsection
