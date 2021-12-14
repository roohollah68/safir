@extends('layout.main')

@section('title')
    تنظیمات
@endsection

@section('content')
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="" method="post">
        @csrf
        <label for="loadOrders">تعداد سفارشات قابل نمایش:</label>
        <input id="loadOrders" name="loadOrders" type="number" value="{{$loadOrders}}">

        <br>

        <label for="minCoupon">حداقل تخفیف کل سامانه(درصد):</label>
        <input id="minCoupon" name="minCoupon" type="number" value="{{$minCoupon}}">

        <br>

        <label for="negative">سقف اعتبار منفی مجاز(هزار تومان):</label>
        <input id="negative" name="negative" type="number" value="{{$negative}}">

        <br>
        <input type="submit" value="اعمال">
    </form>
@endsection


@section('files')

@endsection
