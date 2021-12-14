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
        @endphp

        <div id="formElements">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group input-group required">
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="name" class="input-group-text w-100">نام و نام خانوادگی:</label>
                        </div>
                        <input value="{{$name}}" type="text" id="name" class="form-control" name="name" required="">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group input-group required minlength=11 maxlength=11 pattern=^[۰-۹0-9]*$">
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="phone" class="input-group-text w-100">شماره تماس:</label>
                        </div>
                        <input value="{{$phone}}" type="text" id="phone" class="form-control" name="phone" required=""
                               minlength="11"
                               maxlength="11" pattern="^[۰-۹0-9]*$">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group input-group required">
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="address" class="input-group-text w-100">آدرس:</label>
                        </div>
                        <textarea name="address" id="address" class="form-control" rows="2"
                                  required="">{{$address}}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group input-group minlength=10 maxlength=10 pattern=^[۰-۹0-9]*$">
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="zip_code" class="input-group-text w-100">کد پستی:</label>
                        </div>
                        <input value="{{$zip_code}}" type="text" id="zip_code" class="form-control" name="zip_code"
                               minlength="10"
                               maxlength="10" pattern="^[۰-۹0-9]*$">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group input-group required">
                        <div class="input-group-append w-25" style="/*min-width: 160px">
                            <label for="orders" class="input-group-text w-100">سفارشات:</label>
                        </div>
                        <div class="w-75 border" id="orders">{{$orders}}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group input-group ">
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="desc" class="input-group-text w-100">توضیحات:</label>
                        </div>
                        <textarea name="desc" id="desc" class="form-control" rows="2">{{$desc}}</textarea>
                    </div>
                </div>
            </div>
            <span>جمع کل :</span>
            <span id="total">{{$total}}</span>
            <span>هزار تومان</span>
            <br>
            @if(!$order)
                <input type="checkbox" name="credit" id="credit" checked>
                <label for="credit">پرداخت اعتباری</label><br>

                @unless(isset($req['file']))
                    <div class="col-md-6">
                        <div class="form-group input-group ">
                            <div class="input-group-append" style="width: 160px">
                                <label for="receipt" class="input-group-text w-100">تصویر رسید بانکی:</label>
                            </div>
                            <input type="file" id="receipt" class="" name="receipt" disabled>
                        </div>
                    </div>
                @endunless
            @endif
            <input type="checkbox" name="addToCustomers" id="addToCustomers">
            <label for="addToCustomers">افزودن به لیست مشتریان</label><br>

            @if($order)
                <input type="submit" class="btn btn-success" value="ویرایش">&nbsp;
            @else
                <input type="submit" class="btn btn-success" value="افزودن">&nbsp;
            @endif
            <a class="btn btn-danger" href="{{route('listOrders')}}">بازگشت</a>

            @if(isset($req['file']))
                <a href="/receipt/{{$req['file']}}.jpg" target="_blank"><img style="max-width: 200px; max-height: 200px"
                                                                             src="/receipt/{{$req['file']}}.jpg"></a>
                <input type="hidden" name="file" value="{{$req['file']}}.jpg">
            @endif
            @if($order)
                @if($order->receipt)
                    <a href="/receipt/{{$order->receipt}}" target="_blank"><img
                            style="max-width: 200px; max-height: 200px"
                            src="/receipt/{{$order->receipt}}"></a>
                @else
                    <p>نحوه پرداخت اعتباری است.</p>
                @endif
            @endif

        </div>
        @if(!$order)
            <div id="products" style="display: none">
                <span class="btn btn-info m-1" onclick="$('#products').hide();$('#formElements').show();">بازگشت</span>
                <table class="stripe" id="product-table">
                    <thead>
                    <tr>
                        <th>نام محصول</th>
                        <th>قیمت(هزار تومان)</th>
                        <th>درصد تخفیف</th>
                        <th>تعداد</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $product)
                        @if($product->available)
                            <tr>
                                <td>{{$product->name}}</td>
                                <td>
                                    <span class="text-danger"
                                          style="text-decoration: line-through">{{$product->price}}</span>
                                    <span class="text-success">{{$product->price2}} </span>
                                </td>
                                <td>{{$product->coupon}}</td>
                                <td>
                                    <span class="btn btn-primary plusOne">+</span>
                                    <input class="product-number" name="{{$product->name}}" id="{{$product->name}}"
                                           type="number" value="0" style="width: 50px" min="0">
                                    <span class="btn btn-primary minusOne">-</span>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
                <span class="btn btn-info" onclick="$('#products').hide();$('#formElements').show();">بازگشت</span>
            </div>
        @endif
    </form>



@endsection

@section('files')
    <script>
        let customers = {!!json_encode($customers)!!};
        $(function () {
            setTimeout(function () {
                $("#errors").hide()
            }, 5000);
            $("#name").autocomplete({
                source: Object.keys(customers),
                select: function (event, ui) {
                    let customer = customers[ui.item.value];
                    $('#phone').val(customer.phone);
                    $('#address').val(customer.address);
                    $('#zip_code').val(customer.zip_code);
                }
            });
            $("#addToCustomers").checkboxradio();
        });
        @if(!$order)
        let cart = {};
        let products = {!!json_encode($products)!!};
        $(function () {
            refreshProducts()

            $("#credit").checkboxradio().change(() => {
                $('#receipt').prop('disabled', $('#credit').prop('checked'))
            });
            $('#product-table').DataTable();

            $('.plusOne').click(function () {
                $(this).next().val((i, n) => {
                    return ++n
                })
                refreshProducts();
            })
            $('.minusOne').click(function () {
                $(this).prev().val((i, n) => {
                    return Math.max(0, --n)
                })
                refreshProducts();
            })
            $('.product-number').change(function (e) {
                refreshProducts();
            })
        });

        function refreshProducts() {
            $('.product-number').each(function () {
                cart[$(this).attr('id')] = $(this).val();
            });

            $('#orders').html(`<p class="btn btn-info" onclick="$('#products').show();$('#formElements').hide();" style="cursor: pointer">`
                + 'برای انتخاب محصول اینجا کلیک کنید' +
                '</p><br>');
            let total = 0;
            $.each(cart, (name, number) => {
                if (number > 0) {
                    total = total + products[name].price2 * number;
                    let deleteBTN = '<span class="btn btn-danger" style=" padding: 0 4px; " ' +
                        'onclick="$(`#' + name + '`).val(0);refreshProducts()">X</span>';
                    $('#orders').append(name + ' ' + number + ' عدد ' + deleteBTN + '| ')
                }
            })
            $('#total').html(total);
        }
        @endif
    </script>
@endsection
