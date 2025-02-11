@extends('layout.main')

@section('title')
    مشاهده سفارشات
@endsection

@section('files')
    @include('orders.js_css')
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


    <x-checkbox :id="'showDeleted'"> سفارشات حذف شده</x-checkbox>

    @if(!$safir)
        <x-checkbox :id="'confirmWait'">در انتظار تایید کاربر</x-checkbox>
        <x-checkbox :id="'counterWait'">در انتظار تایید حسابدار</x-checkbox>
        <x-checkbox :id="'printWait'"> در انتظار پرینت</x-checkbox>
        <x-checkbox :id="'proccessWait'"> در حال پردازش</x-checkbox>
        <x-checkbox :id="'sent'">ارسال شده</x-checkbox>
        <x-checkbox :id="'delivered'">تحویل داده شده</x-checkbox>

        @if($User->meta('showAllOrders'))
            <br>
            <br>
            <x-checkbox :id="'safirOrders'" checked>سفیران</x-checkbox>
            <x-checkbox :id="'siteOrders'" checked>سایت ها</x-checkbox>
            <x-checkbox :id="'adminOrders'" checked>فروشگاه ها</x-checkbox>
            <x-checkbox :id="'COD'">پرداخت در محل</x-checkbox>
            <x-checkbox :id="'refund'">بازگشت به انبار</x-checkbox>
        @endif
        <br>
        <br>
        <x-radio :id="'warehouse-all'"  onclick="warehouseId = 'all';prepare_data()" name="warehouse" checked>همه</x-radio>
        @foreach($warehouses as $warehouse)
            <x-radio :id="'warehouse-'.$warehouse->id"  onclick="warehouseId = {{$warehouse->id}};prepare_data()" name="warehouse">{{$warehouse->name}}</x-radio>
        @endforeach

    @endif
    <br>
    @if($User->meta('showAllOrders'))
        <div class="my-3">
            <div class="form-group col-md-4 d-flex">

                <label for="user" class="input-group-text">سفیر:</label>

                <select class="form-control" id="user"
                        onchange="user = this.value;prepare_data()">
                    <option value="all" selected>همه</option>
                    @foreach($users as $id=>$user)
                        <option value="{{$id}}">{{$user->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <button class="btn btn-secondary my-2" onclick="generatePDFs()"> فایل PDF انتخابی ها</button>
        <a id="pdf-link" target="_blank"></a>
    @endif

    <table id="main-table" class="table table-striped">
    </table>

{{--    <div class="d-none" id="sendMethodText">--}}
{{--        @include('orders.sendMethods')--}}
{{--    </div>--}}

@endsection
