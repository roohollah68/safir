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

        <label for="peykCost">هزینه ارسال با پیک(هزار تومان):</label>
        <input id="peykCost" name="peykCost" type="number" value="{{$peykCost}}">

        <br>

        <label for="postCost">هزینه ارسال با پست(هزار تومان):</label>
        <input id="postCost" name="postCost" type="number" value="{{$postCost}}">

        <br>

        <label for="freeDelivery">حداقل خرید برای ارسال رایگان(هزار تومان):</label>
        <input id="freeDelivery" name="freeDelivery" type="number" value="{{$freeDelivery}}">

        <br>
        <input type="submit" value="اعمال">
    </form>
@endsection


@section('files')

@endsection
