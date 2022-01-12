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
                       maxlength="11" pattern="^[۰-۹0-9]*$"
                       oninvalid="this.setCustomValidity('لطفا شماره 11 رقمی تلفن را وارد کنید.')"
                       oninput="this.setCustomValidity('')" placeholder="مانند 09123456789">
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
                <div class="w-75 border">
                    <p class="btn btn-info" onclick="productMode()">برای انتخاب محصول اینجا کلیک کنید</p><br>
                    @if($admin && !$order)
                        <input type="checkbox" id="factor" name="factor" checked
                               onchange="this.checked?$('#orders').hide():$('#orders').show()">
                        <label for="factor">طبق فاکتور</label>
                    @endif
                    <div id="orders">{{$orders}}</div>
                </div>


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


    @if(!$admin)
        <div class="p-3 m-2 border">
            <h4>نحوه پرداخت</h4>
            @if(!$order && !isset($req['file']))
                <input type="radio" name="paymentMethod" value="credit" id="credit" checked>
                <label for="credit">پرداخت اعتباری</label><br>

                <input type="radio" name="paymentMethod" value="receipt" id="receipt">
                <label for="receipt">تصویر رسید بانکی</label>
                <input type="file" id="receiptPhoto" name="receipt"><br>

                <input type="radio" name="paymentMethod" value="onDelivery" id="onDelivery">
                <label for="onDelivery">پرداخت در محل</label>
                <label for="customerDiscount">درصد تخفیف به مشتری</label>
                <input type="number" style="max-width: 60px" min="0" max="50" id="customerDiscount"
                       name="customerDiscount" value="{{$customerDiscount}}" onchange="refreshProducts()"><br>

            @elseif(isset($req['file']))
                <a href="/receipt/{{$req['file']}}.jpg" target="_blank">
                    <img style="max-width: 200px; max-height: 200px" src="/receipt/{{$req['file']}}.jpg">
                </a>
                <input type="hidden" name="file" value="{{$req['file']}}.jpg">
                <input type="hidden" name="paymentMethod" value="receipt">

            @elseif($order)
                @if($order->paymentMethod == 'receipt')
                    <p>نحوه پرداخت کارت به کارت با رسید بانکی است.</p>
                    <a href="/receipt/{{$order->receipt}}" target="_blank"><img
                            style="max-width: 200px; max-height: 200px"
                            src="/receipt/{{$order->receipt}}"></a>
                @elseif($order->paymentMethod == 'credit')
                    <p>نحوه پرداخت اعتباری است.</p>
                @else
                    <p>نحوه پرداخت ، پرداخت در محل است.</p>
                @endif
            @endif
        </div>
    @else
        <input type="hidden" name="paymentMethod" value="credit">
    @endif
        <div class="p-3 m-2 border">
            <h4>شیوه ارسال</h4>
            @if(!$order)
                <input type="radio" name="deliveryMethod" value="peyk" id="peyk" checked>
                <label for="peyk">ارسال با پیک</label> <span class="deliveryDesc"> <span>{{number_format($settings->peykCost)}}</span > تومان</span><br>

                <input type="radio" name="deliveryMethod" value="post" id="post">
                <label for="post">ارسال با پست</label><span class="deliveryDesc"> {{number_format($settings->postCost)}} تومان</span><br>

                <input type="radio" name="deliveryMethod" value="paskerayeh" id="paskerayeh">
                <label for="paskerayeh">پس کرایه</label><span class="deliveryDesc">هزینه ارسال به عهده مشتری</span><br>

            @else
                @switch($order->deliveryMethod)
                    @case('peyk')
                    <p>ارسال با پیک</p>
                    @break
                    @case('post')
                    <p>ارسال با پست</p>
                    @break
                    @case('paskerayeh')
                    <p> پس کرایه</p>
                    @break
                @endswitch
            @endif
        </div>

        <div class="p-3 m-2 border" id="paymentDetails">
            <h4>فاکتور</h4>
            @if(!$order)
                <ol id="order-list">
                </ol>
                <hr>
                <span>جمع اقلام: </span><span id="cartSum"></span><span> تومان</span><br>
                <span>هزینه حمل: <span id="deliveryCost"></span>  تومان </span><br><br>
            @endif
            <span class="font-weight-bold">مبلغ کل: </span><span id="total">{{$total}}</span></span>  تومان </span>
            <br><br>

            <span id="onDeliveryMode">
                <span>پرداختی مشتری: </span><span id="customerTotal">{{$order?$order->customerCost:''}}</span><span> تومان </span><br>
                <span>سهم سفیر: </span><span id="safirShare">{{$order?$order->customerCost-$order->total:''}}</span><span>  تومان </span><br>
            </span>
        </div>

{{--    @else--}}
{{--        <input type="hidden" name="paymentMethod" value="admin">--}}
{{--        <input type="hidden" name="deliveryMethod" value="admin">--}}
{{--    @endif--}}

    <input type="checkbox" name="addToCustomers" id="addToCustomers">
    <label for="addToCustomers">افزودن به لیست مشتریان</label><br>


    @if($order)
        <input type="submit" class="btn btn-success" value="ویرایش">&nbsp;
    @else
        <input type="submit" class="btn btn-success" value="ثبت">&nbsp;
    @endif
    <a class="btn btn-danger" href="{{route('listOrders')}}">بازگشت</a>


</div>
