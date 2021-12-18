@extends('layout.main')

@section('title')
    @if($order)
        ویرایش سفارش
    @else
        افزودن سفارش جدید
    @endif
@endsection

@section('content')
    <x-auth-validation-errors class="mb-4" id="errors" :errors="$errors"/>
    <form action="" method="post" enctype="multipart/form-data">
        @csrf
        @php
            $name = old('name')?old('name'):($order?$order->name:'');
            $phone = old('phone')?old('phone'):($order?$order->phone:'');
            $address = old('address')?old('address'):($order?$order->address:'');
            $zip_code = old('zip_code')?old('zip_code'):($order?$order->zip_code:'');
            $orders = $order?$order->orders:'';
            $desc = old('desc')?old('desc'):($order?$order->desc:'');
            $total = $order?$order->total:'0';
            $customerDiscount = old('customerDiscount')?old('customerDiscount'):0;
        @endphp

        @include('addEditOrder.form')

        @if(!$order)
            <div id="products" class="D-none">
                <span class="btn btn-info m-1" onclick="formMode()">بازگشت</span>
                <table class="stripe" id="product-table">
                    <thead>
                    <tr>
                        <th>محصول</th>
                        <th>قیمت</th>
                        <th>تعداد</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $product)
                        @if($product->available)
                            <tr>
                                <td>{{$product->name}}({{$product->coupon}}%)</td>
                                <td>
                                    <span class="text-danger"
                                          style="text-decoration: line-through">{{$product->price}}</span>
                                    <span class="text-success">{{$product->priceWithDiscount}} </span>
                                </td>
                                <td>
                                    <span class="btn btn-primary plusOne">+</span>
                                    <input class="product-number" product_id="{{$product->id}}" name="product_{{$product->id}}" id="product_{{$product->id}}"
                                           type="number" value="0" style="width: 50px" min="0">
                                    <span class="btn btn-primary minusOne">-</span>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
                <span class="btn btn-info" onclick="formMode()">بازگشت</span>
            </div>
        @endif
    </form>

@endsection

@section('files')
    @include('addEditOrder.js_css')
@endsection

