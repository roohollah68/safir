@csrf
@if(!$edit && $creatorIsAdmin)
    <div class="mb-2">
        <span>انتخاب انبار:</span>
        @foreach($warehouses as $warehouse)
            <a class="btn btn{{($warehouseId == $warehouse->id)?'':'-outline'}}-info"
               href="/add_order?warehouseId={{$warehouse->id}}">{{$warehouse->name}}</a>
        @endforeach
    </div>
    <br>
@endif
<div id="hidden-input"></div>
<input type="hidden" name="warehouse_id" value="{{$warehouseId}}">
<span id="customer-agreement"></span>
<div id="formElements" class="bg-white">
    <div class="row">

        {{--        نام و نام خانوادگی--}}
        <div class="col-md-6 mb-2">
            <div class="form-group input-group required">
                <div class="input-group-append" style="min-width: 160px">
                    <label for="name" class="input-group-text w-100">نام و نام خانوادگی:</label>
                </div>
                <input value="{{old('name')?:$order->name}}" type="text" id="name"
                       class="form-control " name="name" required @readonly($edit && $creatorIsAdmin)>
                @if($creatorIsAdmin)
                    <input type="hidden" name="customer_id" id="customer_id" class="form-control hide" required>
                    @if(!$edit)
                        <a class="btn btn-success fa fa-user-plus" title="افزودن مشتری"
                           href="{{route("newCustomer")}}"></a>
                    @endif
                    @if($user->meta('changeDiscount'))
                        <span class="btn btn-info" id="set-customer-discount">0%</span>
                    @endif
                    <span id="customerId" style="width: 70px; text-align: left; padding-top: 5px;">
                        {{old('customerId')?:$order->customer_id}}
                    </span>
                @endif
            </div>
        </div>

        {{--        شماره تماس--}}
        <x-col-md-6 :name="'phone'" value="{{old('phone')?:$order->phone}}" :required="true"
                    minlength="11" maxlength="11" pattern="^[۰-۹0-9]*$"
                    oninvalid="this.setCustomValidity('لطفا شماره 11 رقمی تلفن را وارد کنید.')"
                    onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                    oninput="this.setCustomValidity('')" placeholder="مانند 09123456789">شماره تماس:
        </x-col-md-6>

        {{--        کد پستی--}}
        <x-col-md-6 :name="'zip_code'" value="{{old('zip_code')?:$order->zip_code}}"
                    minlength="10"
                    maxlength="10" pattern="^[۰-۹0-9]*$"
                    onkeypress="return event.charCode >= 48 && event.charCode <= 57">کد پستی:
        </x-col-md-6>

        {{--        آدرس--}}
        <x-col-md-6 :name="'address'" :content="old('address')?:$order->address" :required="true"
                    rows="2" :tag="'textarea'">آدرس:
        </x-col-md-6>

        {{--                نوع فاکتور--}}
        @if(!$user->safir())
            <div class="col-md-6 my-1">
                <div class="form-group">
                    <div class="d-flex">
                        <div class="input-group-append" style="min-width: 160px">
                            <label class="input-group-text w-100">نوع فاکتور:</label>
                        </div>
                        <div>
                            <input class="checkboxradio" type="radio" name="official" id="unofficial" value="0"
                                @checked(!old('official',$order->official)) onclick="chaneOfficial(false);refreshProducts();">
                            <label for="unofficial">غیر رسمی</label>
                            <input class="checkboxradio" type="radio" name="official" id="official" value="1"
                                @checked(old('official',$order->official)) onclick="chaneOfficial(true);refreshProducts();">
                            <label for="official">رسمی</label>
                        </div>
                    </div>
                </div>
            </div>
        @endif
                توضیحات
        <x-col-md-6 :name="'desc'" :content="old('desc')?:$order->desc"
                    rows="2" :tag="'textarea'">توضیحات:
        </x-col-md-6>
    </div>


    @if(!$creatorIsAdmin)
        <div class="p-3 m-2 border">
            <h4>نحوه پرداخت</h4>
            @if(!$edit)
                <x-radio :id="'credit'" :name="'paymentMethod'" value="credit" onclick="paymentAction()" checked>
                    {{config('payMethods')['credit']}}</x-radio>
{{--                <x-radio :id="'receipt'" :name="'paymentMethod'" value="receipt" onclick="paymentAction()">--}}
{{--                    {{config('payMethods')['receipt']}}</x-radio>--}}
                <x-radio :id="'onDelivery'" :name="'paymentMethod'" value="onDelivery" onclick="paymentAction()">
                    {{config('payMethods')['onDelivery']}}</x-radio><br>
{{--                <label for='receiptPhoto' class="btn btn-info m-2 hide receiptPhoto">بارگذاری تصویر رسید بانکی <i--}}
{{--                        class="fa fa-image"></i></label>--}}
                <input type="file" id="receiptPhoto" class="hide compress-image*" name="receipt">
                <label for="customerDiscount">درصد تخفیف به مشتری</label>
                <input type="number" style="max-width: 60px" min="0" max="50" id="customerDiscount"
                       name="customerDiscount" value="{{old('customerDiscount')}}" onchange="refreshProducts()"><br>

            @else
                <div id="edit-payment-method"><p>{{$order->payMethod()}}</p>
                    @if($order->receipt)
                        <a href="/receipt/{{$order->receipt}}" target="_blank">
                            <img style="max-width: 200px; max-height: 200px" alt="😔" src="/receipt/{{$order->receipt}}">
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <div class="p-3 m-2 border">
            <h4>شیوه ارسال</h4>
            @if(!$edit)
                <label for="peyk" onclick="deliveryMethod=`peyk`;refreshProducts()">{{config('sendMethods')['peyk']}}
                    ({{number_format($settings->peykCost)}} ریال)</label>
                <input value="peykCost" onclick="deliveryMethod=`peyk`;refreshProducts()" type="radio"
                       name="deliveryMethod"
                       id="peyk" class="checkboxradio" checked>

                <label for="post" onclick="deliveryMethod=`post`;refreshProducts()">{{config('sendMethods')['post']}}
                    ({{number_format($settings->postCost)}} ریال)</label>
                <input value="postCost" onclick="deliveryMethod=`post`;refreshProducts()" type="radio"
                       name="deliveryMethod"
                       id="post" class="checkboxradio">

                <label for="peykeShahri"
                       onclick="deliveryMethod=`peykeShahri`;refreshProducts()">{{config('sendMethods')['peykeShahri']}}
                    ({{number_format($settings->peykeShahri)}} ریال)</label>
                <input value="peykeShahri" onclick="deliveryMethod=`peykeShahri`;refreshProducts()" type="radio"
                       name="deliveryMethod"
                       id="peykeShahri" class="checkboxradio">

                <label for="paskerayeh"
                       onclick="deliveryMethod=`paskerayeh`;refreshProducts()">{{config('sendMethods')['paskerayeh']}}
                    (هزینه
                    ارسال به عهده مشتری)</label>
                <input value="paskerayeh" onclick="deliveryMethod=`paskerayeh`;refreshProducts()" type="radio"
                       name="deliveryMethod"
                       id="paskerayeh" class="checkboxradio">
                <br>

            @else
                <p>{{$order->sendMethod()}}</p>
            @endif
        </div>

    @endif
    <input type="submit" class="btn btn-success mx-4" style="width: 200px;" value="{{$edit?'ویرایش':'ثبت'}}">&nbsp;
    <a class="btn btn-danger"
       onclick="confirm('آیا از ثبت سفارش منصرف شدید؟')?(window.location.href = '{{route('listOrders')}}'):''">بازگشت</a>

    <table class="table-striped table hide" id="selected-product-table">
        <thead>
        <tr>
            <th>نام محصول</th>
            <th>تعداد</th>
            <th>قیمت قبل تخفیف</th>
            <th>تخفیف(%)
                @if($user->meta('changeDiscount'))
                    <input type="number" min="0" max="100" step="0.25" style="width: 50px"
                           onchange="$('.discount-value').val(this.value).change()">
                @endif
            </th>
            <th class="hide official">مالیات</th>
            <th>قیمت نهایی</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody id="product-form">
        </tbody>
    </table>

    <div class="p-3 m-2 border" id="paymentDetails">
        @if(!$creatorIsAdmin)
            <span>جمع اقلام: </span><span id="cartSum"></span><span> ریال</span> ||
            <span>هزینه حمل: </span> <span id="deliveryCost"></span><span>  ریال </span> ||
        @endif
        <span>مجموع تخفیف: </span><span id="total-discount" dir="ltr"></span><span>  ریال </span> ||
        <b>مجموع تعداد اقلام: </b><b id="total-num"></b> <b>عدد</b>||
        <b>مبلغ کل: </b><b id="total" dir="ltr"></b><b> ریال </b>
        <br/>

        <span id="onDeliveryMode">
            <span>پرداختی مشتری: </span>
            <span id="customerTotal">{{$edit?$order->customerCost:''}}</span>
            <span> ریال </span> ||
            <span>سهم سفیر: </span>
            <span id="safirShare">{{$edit?$order->customerCost-$order->total:''}}</span>
            <span>  ریال </span>
            <br/>
        </span>
    </div>

</div>
