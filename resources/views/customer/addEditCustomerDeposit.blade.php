@extends('layout.main')

@section('title')
    @if($edit)
        ویرایش رسید پرداخت
    @else
        افزودن رسید پرداخت
    @endif

@endsection

@section('files')
    <script>
        $(() => {
            $('.checkboxradio').checkboxradio();
            @php($pay_method = (old('pay_method')?:$deposit->pay_method?:'cash'))
            @if($pay_method!='cash')
            $('.cash').hide().prop('required', false);
            @endif
            @if($pay_method!='cheque')
            $('.cheque').hide().prop('required', false);
            @endif
            @if($pay_method!='cash2')
            $('.cash2').hide().prop('required', false);
            @endif

            const chequeDate = new mds.MdsPersianDateTimePicker($('#cheque_date_farsi')[0], {
                targetTextSelector: '#cheque_date_farsi',
                targetDateSelector: '#cheque_date',
                @if(old('cheque_date')?:$deposit->cheque_date)
                selectedDate: new Date("{{old('cheque_date')?:$deposit->cheque_date}}"),
                @endif
            });
        });
    </script>
@endsection

@section('content')
    <div class="m-3 p-3 bg-light">
        <span>ثبت سند واریزی برای: </span><b>{{$customer->name}}</b><br>
        <span>بدهی: </span><b dir="ltr">{{number_format($customer->balance)}}</b><span>ریال </span><br>
        @isset($order->id)
            <span>برای سفارش شماره:</span><b>{{$order->id}}</b><i class="btn btn-info fa fa-eye"
                                                                  onclick="view_order({{$order->id}})"></i>
        @endisset
    </div>
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form method="post" enctype="multipart/form-data"
          action="/customerDeposit/addEdit/{{$customer->id}}/{{$order->id?:'0'}}{{$deposit->id?('/'.$deposit->id):''}}">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="amount" class="input-group-text w-100">مبلغ واریزی:</label>
                    </div>
                    <input value="{{old('amount')?:$deposit->amount?:($order->unpaid()??'')}}"
                           type="text" id="amount" class="form-control price-input" name="amount"
                            dir="ltr" required>
                    <div class="input-group-prepend" style="min-width: 120px">
                        <label for="amount" class="input-group-text w-100"> ریال</label>
                    </div>
                </div>
            </div>

            {{--            نوع پرداخت--}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label class="input-group-text ">روش پرداخت:</label>
                    </div>
                    <label for="cash" class="">نقدی</label>
                    <input type="radio" class="checkboxradio" name="pay_method" id="cash" value="cash" checked
                           onclick="$('.cash').show().prop('required',true); $('.cheque, .cash2').hide().prop('required',false)">

                    <label for="cheque" class="">چکی</label>
                    <input type="radio" class="checkboxradio" name="pay_method" id="cheque" value="cheque" @checked($pay_method=='cheque')
                           onclick="$('.cash, .cash2').hide().prop('required',false); $('.cheque').show().prop('required',true)">

                    <label for="cash2" class="">پول نقد</label>
                    <input type="radio" class="checkboxradio" name="pay_method" id="cash2" value="cash2"
                           @checked($pay_method=='cash2') onclick="$('.cash,.cheque').hide().prop('required',false); $('.cash2').show().prop('required',true)">
                </div>
            </div>

            {{--            انتخاب بانک--}}
            <div class="col-md-6 my-2 cash">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label class="input-group-text " for="bank_id">انتخاب بانک مقصد</label>
                    </div>
                    <select id="bank_id" name="bank_id" class="form-control cash" required>
                        <option value="">لطفا انتخاب کنید</option>
                        @foreach($banks as $id => $bank)
                            <option
                                value="{{$id}}" @selected($id == (old('bank_id')?:$deposit->bank_id))>

                                {{$bank->name}}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{--            نام صاحب چک--}}
            <div class="col-md-6 my-2 cheque">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="cheque_name" class="input-group-text w-100">نام صاحب چک:</label>
                    </div>
                    <input type="text" class="form-control cheque" name="cheque_name"
                           id="cheque_name"
                           value="{{old('cheque_name')?:$deposit->cheque_name}}" required>
                </div>
            </div>

            {{--            تاریخ چک--}}
            <div class="col-md-6 my-2 cheque">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="cheque_date_farsi" class="input-group-text w-100">تاریخ
                            چک:</label>
                    </div>
                    <input type="text" class="form-control cheque" name="cheque_date_farsi"
                           id="cheque_date_farsi"
                           required>
                    <input type="hidden" name="cheque_date" id="cheque_date">
                </div>
            </div>

            {{--            کد 16 رقمی روی چک--}}
            <div class="col-md-6 my-2 cheque">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="cheque_code" class="input-group-text w-100">کد 16 رقمی روی
                            چک:</label>
                    </div>
                    <input type="text" class="form-control" name="cheque_code" id="cheque_code"
                           value="{{old('cheque_code',$deposit->cheque_code)}}">
                </div>
            </div>

            {{--            نوع گیرنده چک--}}
            <div class="col-md-6 my-2 cheque">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label class="input-group-text w-100">نوع گیرنده چک:</label>
                    </div>
                    <label for="company" class="">شرکتی</label>
                    <input type="radio" class="checkboxradio" name="official" id="company" value="1" @checked(old('official',$deposit->official)) >

                    <label for="person" class="">شخصی</label>
                    <input type="radio" class="checkboxradio" name="official" id="person" value="0" @checked(!old('official',$deposit->official)) >
                </div>
            </div>


            <div class="col-md-6">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="description" class="input-group-text w-100">توضیحات:</label>
                    </div>
                    <textarea name="description" id="description" class="form-control" rows="2"
                    >{{old('description')?:$deposit->description}}</textarea>
                </div>
            </div>
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="width: 160px">
                        <label for="photo" class="input-group-text cash">تصویر رسید بانکی:</label>
                        <label for="photo" class="input-group-text cheque">تصویر چک بانکی:</label>
                        <label for="photo" class="input-group-text cash2">تصویر پول نقد:</label>
                    </div>
                    <input type="file" id="photo" class="compress-image ms-2" name="photo">
                    <input type="hidden" name="old_Photo" value="{{$deposit->photo}}">
                </div>
            </div>
            @if($deposit->photo)
                <a href="/deposit/{{$deposit->photo}}" target="_blank">
                    <img style="height: 300px" src="/deposit/{{$deposit->photo}}">
                </a>
            @endif

            <div class="col-md-6 my-2 cheque required">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="width: 190px">
                        <label for="cheque_registration" class="input-group-text cheque">تصویر ثبت چک در بانک:</label>
                    </div>
                <input type="file" id="cheque_registration" class="compress-image ms-2" name="cheque_registration">
                </div>
            </div>
            @if($deposit->cheque_registration)
                <a href="/deposit/{{$deposit->cheque_registration}}" target="_blank">
                    <img style="height: 300px" src="/deposit/{{$deposit->cheque_registration}}">
                </a>
            @endif
        </div>

        <input type="submit" name="submit" class="btn btn-success" value="افزودن">
        &nbsp;
        <a href="/customer/transaction/{{$customer->id}}" class="btn btn-danger">بازگشت</a>

    </form>

@endsection


