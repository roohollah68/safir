@extends('layout.main')

@section('title')
    تنظیمات
@endsection

@section('content')
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="" method="post">
        @csrf
        <label for="loadOrders">تعداد سفارشات قابل نمایش:</label>
        <input id="loadOrders" name="loadOrders" type="text" class="price-input" value="{{$setting->loadOrders}}">

        <br>

        <label for="minCoupon"> تخفیف مازاد کل سامانه(درصد) این عدد به کل تخفیف ها افزوده میشود:</label>
        <input id="minCoupon" name="minCoupon" type="number" value="{{$setting->minCoupon}}">

        <br>

        <label for="negative">سقف اعتبار منفی مجاز(ریال):</label>
        <input id="negative" name="negative" type="text" class="price-input" value="{{$setting->negative}}">

        <br>

        <label for="peykCost">هزینه ارسال با تیپاکس(ریال):</label>
        <input id="peykCost" name="peykCost" type="text" class="price-input" value="{{$setting->peykCost}}">

        <br>

        <label for="postCost">هزینه ارسال با پست(ریال):</label>
        <input id="postCost" name="postCost" type="text" class="price-input" value="{{$setting->postCost}}">

        <br>

        <label for="peykeShahri">هزینه ارسال با پیک شهری(ریال):</label>
        <input id="peykeShahri" name="peykeShahri" type="text" class="price-input" value="{{$setting->peykeShahri}}">

        <br>

        <label for="freeDelivery">حداقل خرید برای ارسال رایگان(ریال):</label>
        <input id="freeDelivery" name="freeDelivery" type="text" class="price-input" value="{{$setting->freeDelivery}}">

        <br>
        <input type="submit" value="اعمال">
    </form>
@endsection


@section('files')

@endsection
