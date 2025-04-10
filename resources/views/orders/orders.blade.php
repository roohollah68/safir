@extends('layout.main')

@section('title')
    مشاهده سفارشات
@endsection

@section('files')
    @include('orders.js_css')
@endsection

@section('content')

    <form action="" method="post" id="dateForm" class="input-group col-12 mb-3" onsubmit="return filter();">
        <div class="col-md-3 d-flex">
            <label for="fromDate" class="input-group-text">📅</label>
            <input type="text" id="fromDate" class="form-control" placeholder="از تاریخ">
        </div>
        <div class=" col-md-3 d-flex">
            <label for="toDate" class="input-group-text">📅</label>
            <input type="text" id="toDate" class="form-control" placeholder="تا تاریخ">
        </div>
        <div class="col-md-3 d-flex">
            <label for="fromId" class="input-group-text">از شماره:</label>
            <input type="number" step="1" id="fromId" class="form-control">
        </div>
        <div class=" col-md-3 d-flex">
            <label for="toId" class="input-group-text">تا شماره:</label>
            <input type="number" step="1" id="toId" class="form-control">
            <button class="btn btn-success fa fa-search"></button>
        </div>
    </form>


    <x-checkbox :id="'showDeleted'" :class="'bg-secondary'"> سفارشات حذف شده</x-checkbox>

    @if(!$safir)
        <x-checkbox :id="'confirmWait'" :class="'bg-primary'">در انتظار تایید کاربر</x-checkbox>
        <x-checkbox :id="'counterWait'" :class="'bg-info'">در انتظار تایید حسابدار</x-checkbox>
        <x-checkbox :id="'printWait'" :class="'bg-secondary'"> در انتظار پرینت</x-checkbox>
        <x-checkbox :id="'proccessWait'" :class="'bg-warning'"> در حال پردازش</x-checkbox>
        <x-checkbox :id="'editAfterProccess'" :class="'bg-danger'"> ویرایش بعد پردازش</x-checkbox>
        <x-checkbox :id="'notsent'" :class="'bg-notsent'">ارسال نشده</x-checkbox>
        <x-checkbox :id="'sent'" :class="'bg-success'">ارسال شده</x-checkbox>
        <x-checkbox :id="'delivered'" :class="'bg-delivered'">تحویل داده شده</x-checkbox>

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
        <x-radio :id="'warehouse-all'" onclick="warehouseId = 'all';prepare_data()" name="warehouse" checked>همه
        </x-radio>
        @foreach($warehouses as $warehouse)
            <x-radio :id="'warehouse-'.$warehouse->id" onclick="warehouseId = {{$warehouse->id}};prepare_data()"
                     name="warehouse">{{$warehouse->name}}</x-radio>
        @endforeach

    @endif
    <br><br>
    <div class="col-md-4">
        <div class="form-group input-group">
            <div class="input-group-append">
                <label for="NuRecords" class="input-group-text">تعداد نمایش سفارشات:</label>
            </div>
            <input type="number" min="1" max="10000" value="{{$nuRecords ?? '' }}"
                   onchange="updateNuRecords(this.value, {{ auth()->user()->id }}) " id="NuRecords" class="form-control">
        </div>
    </div>
    <br>
    @if($User->meta('showAllOrders'))
        <div class="mb-3">
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

        <div class="mb-3">
            <span>نمایش ستون‌ها:</span>
            <x-checkbox :id="'toggle-column-3'" checked onclick="table.columns(3).visible(this.checked);">سفیر</x-checkbox>
            <x-checkbox :id="'toggle-column-5'" checked onclick="table.columns(5).visible(this.checked);">زمان ثبت</x-checkbox>
            <x-checkbox :id="'toggle-column-6'" checked onclick="table.columns(6).visible(this.checked);">تائید کاربر</x-checkbox>
            <x-checkbox :id="'toggle-column-8'" checked onclick="table.columns(8).visible(this.checked);">وضعیت</x-checkbox>
            <x-checkbox :id="'toggle-column-9'" checked onclick="table.columns(9).visible(this.checked);">آدرس</x-checkbox>
            <x-checkbox :id="'toggle-column-10'" checked onclick="table.columns(10).visible(this.checked);">توضیحات</x-checkbox>
            <x-checkbox :id="'toggle-column-12'" checked onclick="table.columns(12).visible(this.checked);">همراه</x-checkbox>
            <x-checkbox :id="'toggle-column-13'" checked onclick="table.columns(13).visible(this.checked);">کدپستی</x-checkbox>
            <x-checkbox :id="'toggle-column-14'" checked onclick="table.columns(14).visible(this.checked);">مبلغ</x-checkbox>
        </div>

        <button class="btn btn-secondary my-2" onclick="generatePDFs()"> دانلود PDF لیبل</button>
        <a id="pdf-link" target="_blank"></a>
        <button class="btn btn-success my-2" onclick="generateExcels()"> دانلود Excel کیسان</button>

    @endif

    <table id="main-table" class="table table-striped">
    </table>

@endsection
