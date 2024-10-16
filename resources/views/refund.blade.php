@extends('layout.main')

@section('title')
    صدور فاکتور برگشت به انبار
@endsection

@section('content')
    <div class="w-100 m-2 p-2 bg-info rounded">
        <span>نام مشتری:</span> <b>{{$customer->name}}</b><br>
        <span>نام کاربر مرتبط:</span> <b>{{$customer->user->name}}</b><br>
        <span>شماره مشتری:</span> <b>{{$customer->id}}</b><br>
        <span>شماره تماس:</span> <b>{{$customer->phone}}</b><br>
        <span>شهر:</span> <b>{{$customer->city->name}}</b><br>
        <span>آدرس:</span> <b>{{$customer->address}}</b><br>
        <span>کد پستی:</span> <b>{{$customer->zip_code}}</b><br>
        <span class="h3">بدهکاری:</span>
        <b dir="ltr" class="h3 text-danger">{{number_format($customer->balance)}}</b>
        <span class="h3">ریال</span><br>
    </div><br>
    <form method="post" action="">
        {{--مکان انبار--}}
        <div class="col-md-6 mb-1">
            <div class="form-group input-group">
                <div class="input-group-append">
                    <label for="warehouse-dialog" class="input-group-text">بازگشت به انبار:</label>
                </div>
                <select name="warehouse" id="warehouse-dialog" class="form-control">
                    @foreach($warehouses as $warehouse)
                        <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>
    <br>
    <div id="tabs">
        <ul>
            <li><a href="#products">محصولات</a></li>
            <li><a href="#orders">سفارشات قبلی</a></li>
        </ul>
        <div id="products">
            products
        </div>
        <div id="orders">
            orders
        </div>


    </div>
@endsection

@section('files')

    <script>
        $(function () {
            $('#tabs').tabs();
        })

    </script>

@endsection
