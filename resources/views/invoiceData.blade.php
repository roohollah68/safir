@extends('layout.main')

@section('title')
    ویرایش اطلاعات فاکتور
@endsection

@section('content')
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="" method="post">
        @csrf

        <label for="invoice_title">عنوان:</label>
        <input id="invoice_title" name="invoice_title" type="text" class="w-50" value="{{$setting->invoice_title}}">
        <br>
        <br>
        <label for="invoice_address">آدرس:</label>
        <textarea id="invoice_address" name="invoice_address" class="w-50">{{$setting->invoice_address}}</textarea><br>
        <br>
        <br>
        <label for="invoice_phone">تلفن:</label>
        <input id="invoice_phone" name="invoice_phone" type="text" class="w-50" value="{{$setting->invoice_phone}}">
        <br>
        <br>
        <label for="invoice_code">ثبت / کدملی:</label>
        <input id="invoice_code" name="invoice_code" type="text" class="w-50" value="{{$setting->invoice_code}}">
        <br>
        <br>
        <label for="invoice_id">شناسه ملی:</label>
        <input id="invoice_id" name="invoice_id" type="text" class="w-50" value="{{$setting->invoice_id}}">
        <br>
        <br>
        <label for="invoice_province">استان:</label>
        <input id="invoice_province" name="invoice_province" type="text" class="w-50" value="{{$setting->invoice_province}}">
        <br>
        <br>
        <label for="invoice_e_code">کد اقتصادی:</label>
        <input id="invoice_e_code" name="invoice_e_code" type="text" class="w-50" value="{{$setting->invoice_e_code}}">
        <br>
        <br>
        <label for="invoice_zip_code">کدپستی:</label>
        <input id="invoice_zip_code" name="invoice_zip_code" type="text" class="w-50" value="{{$setting->invoice_zip_code}}">
        <br>
        <br>
        <label for="invoice_city">شهر:</label>
        <input id="invoice_city" name="invoice_city" type="text" class="w-50" value="{{$setting->invoice_city}}">
        <br>
        <br>
        @foreach($warehouses as $id => $warehouse)
            <label for="invoice_mobile_{{$id}}">موبایل {{$warehouse->name}}:</label>
            <input id="invoice_mobile_{{$id}}" name="invoice_mobile_{{$id}}" type="text" class="w-50" value="{{$warehouse->phone}}">
            <br>
            <br>
        @endforeach
        <input type="submit" value="ذخیره">
    </form>
@endsection


@section('files')

@endsection