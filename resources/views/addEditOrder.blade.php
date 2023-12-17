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
                        @if($admin)
                            <th>تخفیف(%)</th>
                        @endif
                        <th>قیمت(تومان)</th>
                        <th>تعداد</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $product)
                        @if($product->available)
                            <tr>
                                @if($admin)
                                    <td>{{$product->name}}</td>
                                    <td>
                                        <input type="number" name="discount_{{$product->id}}"
                                               class="discount-value"
                                               id="discount_{{$product->id}}"
                                               value="{{$product->coupon}}"
                                               style="width: 50px"
                                               pattern="^[1-9][0-9]?$|^100$"
                                               min="0" max="100" minlength="1" maxlength="3">
                                    </td>
                                    <td id="price_{{$product->id}}">
                                        <span class="text-danger original"
                                            @if($product->priceWithDiscount!=$product->price)
                                            style="text-decoration: line-through"
                                            @endif
                                        >
                                            {{number_format($product->price)}}
                                        </span>
                                        <span class="text-success discount">
                                            {{$product->priceWithDiscount!=$product->price?number_format($product->priceWithDiscount):''}}
                                        </span>
                                    </td>
                                @else
                                    <td>{{$product->name}}({{$product->coupon}}%)</td>
                                    <td>
                                        <span class="text-danger"
                                            @if($product->priceWithDiscount!=$product->price)
                                            style="text-decoration: line-through"
                                          @endif
                                        >
                                            {{number_format($product->price)}}
                                        </span>
                                        <span class="text-success">
                                            {{$product->priceWithDiscount!=$product->price?number_format($product->priceWithDiscount):''}}
                                        </span>
                                    </td>
                                @endif

                                <td>
                                    <span class="btn btn-primary" onclick="num_plus({{$product->id}})">+</span>
                                    <input class="product-number" product_id="{{$product->id}}"
                                           name="product_{{$product->id}}" id="product_{{$product->id}}"
                                           onchange="num_product({{$product->id}})"
                                           type="number" value="0" style="width: 50px" min="0">
                                    <span class="btn btn-primary" onclick="num_minus({{$product->id}})">-</span>
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

