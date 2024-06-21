@csrf
@if($creatorIsAdmin)
    <label for="customerId">شماره مشتری:</label>
    <input type="number" value="{{old('customerId')?:$order->customer_id}}" name="customerId"
           id="customerId" style="width: 70px" readonly>
@endif

<input type="hidden" name="location" value="{{$location}}">

<div id="formElements" class="bg-white">
    <div class="row">

        <x-col-md-6 :name="'name'" value="{{old('name')?:$order->name}}" :required="true"
                    :readonly="$edit && $creatorIsAdmin">نام و نام خانوادگی:
        </x-col-md-6>

        <x-col-md-6 :name="'phone'" value="{{old('phone')?:$order->phone}}" :required="true"
                    minlength="11" maxlength="11" pattern="^[۰-۹0-9]*$"
                    oninvalid="this.setCustomValidity('لطفا شماره 11 رقمی تلفن را وارد کنید.')"
                    onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                    oninput="this.setCustomValidity('')" placeholder="مانند 09123456789">شماره تماس:
        </x-col-md-6>

        @if($creatorIsAdmin)
            <x-col-md-6 :name="'city'" value="{{old('city')?:$customer->city->name}}">شهر:</x-col-md-6>
            <input type="hidden" id="city_id" name="city_id" value="{{old('city_id')?:$customer->city->id}}">

        @else
            <input type="hidden" name="city_id" value="0">
        @endif

        <x-col-md-6 :name="'address'" :content="old('address')?:$order->address" :required="true"
                    rows="2" :tag="'textarea'">آدرس:
        </x-col-md-6>

        <x-col-md-6 :name="'orders'" :content="$order->orders"
                    :tag="'div'">سفارشات:
        </x-col-md-6>

        <x-col-md-6 :name="'zip_code'" value="{{old('zip_code')?:$order->zip_code}}"
                    minlength="10"
                    maxlength="10" pattern="^[۰-۹0-9]*$"
                    onkeypress="return event.charCode >= 48 && event.charCode <= 57">کد پستی:
        </x-col-md-6>

        <x-col-md-6 :name="'desc'" :content="old('desc')?:$order->desc"
                    rows="2" :tag="'textarea'">توضیحات:
        </x-col-md-6>

        @if($creatorIsAdmin)
            <div class="col-md-6">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="category" class="input-group-text w-100">دسته بندی:</label>
                    </div>
                    <select class="form-control" name="category" id="category">
                        @foreach($customer->categories() as $ii => $category)
                            <option value="{{$ii}}" @selected($ii == $customer->category) >
                                {{$category}}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @else
            <input type="hidden" name="category" value="0">
        @endif
    </div>


    @if($safir)
        <div class="p-3 m-2 border">
            <h4>نحوه پرداخت</h4>
            @if(!$edit)
                <x-radio :id="'credit'" :name="'paymentMethod'" value="credit" checked>
                    {{$payMethods['credit']}}</x-radio>
                <x-radio :id="'receipt'" :name="'paymentMethod'" value="receipt">
                    {{$payMethods['receipt']}}</x-radio>
                <x-radio :id="'onDelivery'" :name="'paymentMethod'" value="onDelivery">
                    {{$payMethods['onDelivery']}}</x-radio><br>
                <label for='receiptPhoto' class="btn btn-info m-2 hide receiptPhoto">بارگذاری تصویر رسید بانکی  <i class="fa fa-image"></i></label>
                <input type="file" id="receiptPhoto" class="hide" name="receipt">
                <label for="customerDiscount">درصد تخفیف به مشتری</label>
                <input type="number" style="max-width: 60px" min="0" max="50" id="customerDiscount"
                       name="customerDiscount" value="{{old('customerDiscount')}}" onchange="refreshProducts()"><br>

            @else
                <div id="edit-payment-method"><p>{{$payMethods[$order->paymentMethod]}}</p>
                    @if($order->receipt)
                        <a href="/receipt/{{$order->receipt}}" target="_blank"><img
                                style="max-width: 200px; max-height: 200px"
                                src="/receipt/{{$order->receipt}}"></a>
                    @endif
                </div>
            @endif
        </div>



        <div class="p-3 m-2 border">
            <h4>شیوه ارسال</h4>
            @if(!$edit)
                <x-radio :id="'peyk'" :name="'deliveryMethod'" value="peyk" checked>
                    {{$sendMethods['peyk']}} ({{number_format($settings->peykCost)}} ریال)</x-radio>

                <x-radio :id="'post'" :name="'deliveryMethod'" value="post" >
                    {{$sendMethods['post']}} ({{number_format($settings->postCost)}} ریال)</x-radio>

                 <x-radio :id="'peykeShahri'" :name="'deliveryMethod'" value="peykeShahri" >
                    {{$sendMethods['peykeShahri']}} ({{number_format($settings->peykeShahri)}} ریال)</x-radio>

                <x-radio :id="'paskerayeh'" :name="'deliveryMethod'" value="paskerayeh" >
                    {{$sendMethods['paskerayeh']}} (هزینه ارسال به عهده مشتری)</x-radio>

                <br>

            @else
                <p>{{$sendMethods[$order->deliveryMethod]}}</p>
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

    <input type="checkbox" name="addToCustomers" id="addToCustomers" @checked($creatorIsAdmin)
    class="checkboxradio" onclick="$('#city, #category').prop('disabled', (i, v) => !v);">
    <label for="addToCustomers">افزودن/ ویرایش مشتری</label><br>


    <div class="d-flex justify-content-around">
        <input type="submit" class="btn btn-success" value="{{$edit?'ویرایش':'ثبت'}}">&nbsp;
        <a class="btn btn-danger"
           onclick="confirm('آیا از ثبت سفارش منصرف شدید؟')?(window.location.href = '{{route('listOrders')}}'):''">بازگشت</a>
    </div>

</div>
