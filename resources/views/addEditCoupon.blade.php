@extends('layout.main')

@section('title')
    @if($coupon->id)
        ویرایش کد تخفیف
    @else
        افزودن کد تخفیف
    @endif
@endsection

@section('files')
    <script>
        $(function () {
            $("input[type=checkbox]").checkboxradio();
        });

        function selectAllUsers() {
            selectNoUsers()
            $('.users input[type=checkbox]').click();
        }

        function selectNoUsers() {
            $('.users input[type=checkbox]:checked').click();
        }

        function selectAllGoods() {
            selectNoGoods()
            $('.goods input[type=checkbox]').click();
        }

        function selectNoGoods() {
            $('.goods input[type=checkbox]:checked').click();
        }
    </script>
    <style>

    </style>
@endsection

@section('content')

    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="" method="post">
        @csrf
        @if($coupon->id)
            <input type="submit" class="btn btn-success" value="ویرایش">
        @else
            <input type="submit" class="btn btn-success" value="افزودن">
        @endif
        &nbsp;
        <a href="{{route('couponList')}}" class="btn btn-danger">بازگشت</a>
        <br>
        <br>
        @php

            if($coupon->id){
                $user_ids  = [];
                $good_ids = [];
                foreach($coupon->couponLinks as $couponLink){
                    $user_ids[$couponLink->user_id] = true;
                    $good_ids[$couponLink->good_id] = true;
                }
            }

        @endphp
        <div class="row">
            <div class="col-md-12">
                <label for="percent">میزان تخفیف(درصد):</label>
                <input id="percent" type="number" name="percent" value="{{old('percent')?:$coupon->percent}}" required>
            </div>
            <div class="col-md-6 users">
                <h3>کاربران</h3>
                <span onclick="selectAllUsers()" class="btn btn-info">همه</span>
                <span onclick="selectNoUsers()" class="btn btn-secondary">هیچ</span>
                <br>
                @foreach($users as $user)
                    <input id="user_{{$user->id}}" type="checkbox" name="user_{{$user->id}}"
                        @checked(isset($user_ids[$user->id])) >
                    <label class="btn btn-info m-1" for="user_{{$user->id}}">{{$user->name}}({{$user->username}}
                        )</label>
                    <br>
                @endforeach
            </div>

            <div class="col-md-6 goods">
                <h3>محصولات</h3>
                <span onclick="selectAllGoods()" class="btn btn-info">همه</span>
                <span onclick="selectNoGoods()" class="btn btn-secondary">هیچ</span>
                <br>
                @foreach($goods as $good)
                    <input id="good_{{$good->id}}" type="checkbox" name="good_{{$good->id}}"
                           @checked( isset($good_ids[$good->id]))>
                    <label class="btn btn-primary m-1" for="good_{{$good->id}}">{{$good->name}}
                        ({{number_format($good->price)}})</label>
                    <br>
                @endforeach
            </div>

        </div>
        @if($coupon->id)
            <input type="submit" class="btn btn-success" value="ویرایش">
        @else
            <input type="submit" class="btn btn-success" value="افزودن">
        @endif
        &nbsp;
        <a href="{{route('couponList')}}" class="btn btn-danger">بازگشت</a>

    </form>

@endsection
