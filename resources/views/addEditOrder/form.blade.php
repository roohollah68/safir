@csrf
@if(!$safir && !$edit)
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
<input type="hidden" name="warehouseId" value="{{$warehouseId}}">

<div id="formElements" class="bg-white">
    <div class="row">

        <div class="col-md-6 mb-2">
            <div class="form-group input-group required">
                <div class="input-group-append" style="min-width: 160px">
                    <label for="name" class="input-group-text w-100">نام و نام خانوادگی:</label>
                </div>
                <input value="{{old('name')?:$order->name}}" type="text" id="name" class="form-control"
                       name="name" required @readonly($edit && $creatorIsAdmin)>
                @if($creatorIsAdmin)
                    <input type="number" value="{{old('customerId')?:$order->customer_id}}" name="customerId"
                           id="customerId" style="width: 70px" readonly>
                @endif
            </div>
        </div>

        <x-col-md-6 :name="'phone'" value="{{old('phone')?:$order->phone}}" :required="true"
                    minlength="11" maxlength="11" pattern="^[۰-۹0-9]*$"
                    oninvalid="this.setCustomValidity('لطفا شماره 11 رقمی تلفن را وارد کنید.')"
                    onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                    oninput="this.setCustomValidity('')" placeholder="مانند 09123456789">شماره تماس:
        </x-col-md-6>

        @if($creatorIsAdmin)
            <x-col-md-6 :name="'city'" value="{{old('city')?:$order->customer->city->name}}">شهر:</x-col-md-6>
            <input type="hidden" id="city_id" name="city_id" value="{{old('city_id')?:$order->customer->city->id}}">

        @else
            <input type="hidden" name="city_id" value="0">
        @endif

        <x-col-md-6 :name="'zip_code'" value="{{old('zip_code')?:$order->zip_code}}"
                    minlength="10"
                    maxlength="10" pattern="^[۰-۹0-9]*$"
                    onkeypress="return event.charCode >= 48 && event.charCode <= 57">کد پستی:
        </x-col-md-6>
        <x-col-md-6 :name="'address'" :content="old('address')?:$order->address" :required="true"
                    rows="2" :tag="'textarea'">آدرس:
        </x-col-md-6>


        <x-col-md-6 :name="'desc'" :content="old('desc')?:$order->desc"
                    rows="2" :tag="'textarea'">توضیحات:
        </x-col-md-6>
    </div>


    @if($safir || !$creatorIsAdmin)
        <div class="p-3 m-2 border">
            <h4>نحوه پرداخت</h4>
            @if(!$edit)
                <x-radio :id="'credit'" :name="'paymentMethod'" value="credit" onclick="paymentAction()" checked>
                    {{$payMethods['credit']}}</x-radio>
                <x-radio :id="'receipt'" :name="'paymentMethod'" value="receipt" onclick="paymentAction()">
                    {{$payMethods['receipt']}}</x-radio>
                <x-radio :id="'onDelivery'" :name="'paymentMethod'" value="onDelivery" onclick="paymentAction()">
                    {{$payMethods['onDelivery']}}</x-radio><br>
                <label for='receiptPhoto' class="btn btn-info m-2 hide receiptPhoto">بارگذاری تصویر رسید بانکی <i
                        class="fa fa-image"></i></label>
                <input type="file" id="receiptPhoto" class="hide" name="receipt">
                <label for="customerDiscount">درصد تخفیف به مشتری</label>
                <input type="number" style="max-width: 60px" min="0" max="50" id="customerDiscount"
                       name="customerDiscount" value="{{old('customerDiscount')}}" onchange="refreshProducts()"><br>

            @else
                <div id="edit-payment-method"><p>{{$payMethods[$order->paymentMethod]}}</p>
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
                <x-radio :id="'peyk'" :name="'deliveryMethod'" value="peykCost" onclick="deliveryAction()" checked>
                    {{$sendMethods['peyk']}} ({{number_format($settings->peykCost)}} ریال)
                </x-radio>

                <x-radio :id="'post'" :name="'deliveryMethod'" value="postCost" onclick="deliveryAction()">
                    {{$sendMethods['post']}} ({{number_format($settings->postCost)}} ریال)
                </x-radio>

                <x-radio :id="'peykeShahri'" :name="'deliveryMethod'" value="peykeShahri" onclick="deliveryAction()">
                    {{$sendMethods['peykeShahri']}} ({{number_format($settings->peykeShahri)}} ریال)
                </x-radio>

                <x-radio :id="'paskerayeh'" :name="'deliveryMethod'" value="paskerayeh" onclick="deliveryAction()">
                    {{$sendMethods['paskerayeh']}} (هزینه ارسال به عهده مشتری)
                </x-radio>

                <br>

            @else
                <p>{{$sendMethods[$order->deliveryMethod]??$order->deliveryMethod}}</p>
            @endif
        </div>

    @endif

    <input type="checkbox" name="addToCustomers" id="addToCustomers" @checked($creatorIsAdmin)
    class="checkboxradio" onclick="$('#city, #category').prop('disabled', (i, v) => !v);">
    <label for="addToCustomers">افزودن/ ویرایش مشتری</label>


    <input type="submit" class="btn btn-success mx-4" style="width: 200px;" value="{{$edit?'ویرایش':'ثبت'}}">&nbsp;
    <a class="btn btn-danger"
       onclick="confirm('آیا از ثبت سفارش منصرف شدید؟')?(window.location.href = '{{route('listOrders')}}'):''">بازگشت</a>

    <table class="table-striped table hide" id="selected-product-table">
        <thead>
        <tr>
            <th>نام محصول</th>
            <th>تعداد</th>
            <th>قیمت(ریال)</th>
            <th>تخفیف(%)</th>
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
