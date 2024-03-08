@csrf
@if($creator)
    <label for="customerId">شماره مشتری:</label>
    <input type="number" value="{{old('customerId')?:$order->customer_id}}" min="0" step="1" name="customerId"
           id="customerId"
           style="width: 70px"
           onchange="customerFind()">
@endif
<div id="formElements" class="bg-white">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group input-group required">
                <div class="input-group-append" style="min-width: 160px">
                    <label for="name" class="input-group-text w-100">نام و نام خانوادگی:</label>
                </div>
                <input value="{{old('name')?:$order->name}}" type="text" id="name" class="form-control" name="name"
                       required="">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group input-group required minlength=11 maxlength=11 pattern=^[۰-۹0-9]*$">
                <div class="input-group-append" style="min-width: 160px">
                    <label for="phone" class="input-group-text w-100">شماره تماس:</label>
                </div>
                <input value="{{old('phone')?:$order->phone}}" type="text" id="phone" class="form-control" name="phone"
                       required=""
                       minlength="11"
                       maxlength="11" pattern="^[۰-۹0-9]*$"
                       oninvalid="this.setCustomValidity('لطفا شماره 11 رقمی تلفن را وارد کنید.')"
                       onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                       oninput="this.setCustomValidity('')" placeholder="مانند 09123456789">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group input-group required">
                <div class="input-group-append" style="min-width: 160px">
                    <label for="address" class="input-group-text w-100">آدرس:</label>
                </div>
                <textarea name="address" id="address" class="form-control" rows="2"
                          required="">{{old('address')?:$order->address}}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group input-group minlength=10 maxlength=10 pattern=^[۰-۹0-9]*$">
                <div class="input-group-append" style="min-width: 160px">
                    <label for="zip_code" class="input-group-text w-100">کد پستی:</label>
                </div>
                <input value="{{old('zip_code')?:$order->zip_code}}" type="text" id="zip_code" class="form-control"
                       name="zip_code"
                       minlength="10"
                       maxlength="10" pattern="^[۰-۹0-9]*$"
                       onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group input-group required">
                <div class="input-group-append w-25">
                    <label for="orders" class="input-group-text w-100">سفارشات:</label>
                </div>
                <div class="w-75 border">
                    <div id="orders">{{$order->orders}}</div>
                </div>
            </div>

        </div>

        <div class="col-md-6">
            <div class="form-group input-group ">
                <div class="input-group-append" style="min-width: 160px">
                    <label for="desc" class="input-group-text w-100">توضیحات:</label>
                </div>
                <textarea name="desc" id="desc" class="form-control" rows="2">{{old('desc')?:$order->desc}}</textarea>
            </div>
        </div>
        @if($creator)
            <div class="col-md-6">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="category" class="input-group-text w-100">دسته بندی:</label>
                    </div>
                    <select class="form-control" name="category" id="category">
                        @for($ii=0;$ii<11;$ii++)
                            <option value="{{$ii}}"
                                    @if($ii == $customer->category)
                                    selected
                                @endif
                            >{{$customer->categoryText($ii)}}</option>
                        @endfor
                    </select>
                </div>
            </div>
        @endif
    </div>


    @if($safir)
        <div class="p-3 m-2 border">
            <h4>نحوه پرداخت</h4>
            @if(!$edit)
                <input type="radio" name="paymentMethod" value="credit" id="credit" checked>
                <label for="credit">پرداخت اعتباری</label><br>

                <input type="radio" name="paymentMethod" value="receipt" id="receipt">
                <label for="receipt">تصویر رسید بانکی</label>
                <input type="file" id="receiptPhoto" name="receipt"><br>

                <input type="radio" name="paymentMethod" value="onDelivery" id="onDelivery">
                <label for="onDelivery">پرداخت در محل</label>
                <label for="customerDiscount">درصد تخفیف به مشتری</label>
                <input type="number" style="max-width: 60px" min="0" max="50" id="customerDiscount"
                       name="customerDiscount" value="{{old('customerDiscount')}}" onchange="refreshProducts()"><br>
            @else
                <div id="edit-payment-method"></div>
            @endif
        </div>

        <input type="hidden" name="paymentMethod" value="credit">

        <div class="p-3 m-2 border">
            <h4>شیوه ارسال</h4>
            @if(!$edit)
                <input type="radio" name="deliveryMethod" value="peyk" id="peyk" checked>
                <label for="peyk">ارسال با تیپاکس</label> <span
                    class="deliveryDesc"> <span>{{number_format($settings->peykCost)}}</span> ریال</span><br>

                <input type="radio" name="deliveryMethod" value="post" id="post">
                <label for="post">ارسال با پست</label><span class="deliveryDesc"> {{number_format($settings->postCost)}} ریال</span>
                <br>

                <input type="radio" name="deliveryMethod" value="paskerayeh" id="paskerayeh">
                <label for="paskerayeh">ارسال پس کرایه(ویژه تهران)</label><span class="deliveryDesc">هزینه ارسال به عهده مشتری</span>
                <br>

            @else
                @switch($order->deliveryMethod)
                    @case('peyk')
                    <p>ارسال با تیپاکس</p>
                    @break
                    @case('post')
                    <p>ارسال با پست</p>
                    @break
                    @case('paskerayeh')
                    <p>ارسال پس کرایه(ویژه تهران)</p>
                    @break
                @endswitch
            @endif
        </div>

        <div class="p-3 m-2 border" id="paymentDetails">
            <h4>فاکتور</h4>
            <ol id="order-list"></ol>
            <hr>
            <span>جمع اقلام: </span><span id="cartSum"></span><span> ریال</span><br>
            <span>هزینه حمل: </span> <span id="deliveryCost"></span><span>  ریال </span><br><br>
            <span class="font-weight-bold">مبلغ کل: </span><span id="total"></span></span>  ریال </span>
            <br><br>

            <span id="onDeliveryMode">
                <span>پرداختی مشتری: </span><span
                    id="customerTotal">{{$edit?$order->customerCost:''}}</span><span> ریال </span><br>
                <span>سهم سفیر: </span><span
                    id="safirShare">{{$edit?$order->customerCost-$order->total:''}}</span><span>  ریال </span><br>
            </span>
        </div>

    @endif

    <input type="checkbox" name="addToCustomers" id="addToCustomers">
    @if($edit)
        <label for="addToCustomers">ویرایش مشتری</label><br>
    @else
        <label for="addToCustomers">افزودن به لیست مشتریان</label><br>
    @endif

    <div class="d-flex justify-content-around">
        @if($edit)
            <input type="submit" class="btn btn-success" value="ویرایش">&nbsp;
        @else
            <input type="submit" class="btn btn-success" value="ثبت">&nbsp;
        @endif
        <a class="btn btn-danger"
           onclick="confirm('آیا از ثبت سفارش منصرف شدید؟')?(window.location.href = '{{route('listOrders')}}'):''">بازگشت</a>
    </div>

</div>
