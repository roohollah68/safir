@extends('layout.main')

@section('title')
    @if(!$coupon)
        افزودن کد تخفیف
    @else
        ویرایش کد تخفیف
    @endif
@endsection

@section('files')
    <script>
        $(function () {
            $("input[type=checkbox]").checkboxradio();
        });
        function selectAllUsers(){
            selectNoUsers()
            $('.users input[type=checkbox]').click( );
        }
        function selectNoUsers(){
            $('.users input[type=checkbox]:checked').click( );
        }
        function selectAllProducts(){
            selectNoProducts()
            $('.products input[type=checkbox]').click( );
        }
        function selectNoProducts(){
            $('.products input[type=checkbox]:checked').click( );
        }
    </script>
    <style>

    </style>
@endsection

@section('content')
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="" method="post">
        @csrf
        @php
            $percent = old('percent')?old('percent'):($coupon?$coupon->percent:'');
            if($coupon){
                $user_ids  = [];
                $product_ids = [];
                foreach($coupon->couponLinks as $couponLink){
                    $user_ids[$couponLink->user_id] = true;
                    $product_ids[$couponLink->product_id] = true;
                }
            }

        @endphp
        <div class="row">
            <div class="col-md-12">
                <label for="percent">میزان تخفیف(درصد):</label>
                <input id="percent" type="number" name="percent" value="{{$percent}}" required>
            </div>
            <div class="col-md-6 users">
                <h3>کاربران</h3>
                <span onclick="selectAllUsers()" class="btn btn-info">همه</span>
                <span onclick="selectNoUsers()" class="btn btn-secondary">هیچ</span>
                <br>
                @foreach($users as $user)
                    <label for="{{$user->username}}">{{$user->name}}({{$user->username}})</label>
                    <input id="{{$user->username}}" type="checkbox" name="{{$user->username}}"
                           @if($coupon && isset($user_ids[$user->id])) checked @endif >
                    <br>
                @endforeach
            </div>

            <div class="col-md-6 products">
                <h3>محصولات</h3>
                <span onclick="selectAllProducts()" class="btn btn-info">همه</span>
                <span onclick="selectNoProducts()" class="btn btn-secondary">هیچ</span>
                <br>
                @foreach($products as $product)
                    <label for="{{$product->name}}">{{$product->name}}({{$product->price}})</label>
                    <input id="{{$product->name}}" type="checkbox" name="{{$product->name}}"
                           @if($coupon && isset($product_ids[$product->id])) checked @endif>
                    <br>
                @endforeach
            </div>


            @if($coupon)
                <input type="submit" class="btn btn-success" value="ویرایش">
            @else
                <input type="submit" class="btn btn-success" value="افزودن">
            @endif
            &nbsp;
            <a href="{{route('couponList')}}" class="btn btn-danger">بازگشت</a>
        </div>
    </form>

@endsection
