@extends('layout.main')

@section('title')
    افزودن رسید پرداخت
@endsection

@section('content')
    <div class="m-3 p-3 bg-light">
        <span>ثبت سند واریزی برای: </span><b>{{$customer->name}}</b><br>
        <span>بدهی: </span><b dir="ltr">{{number_format($customer->balance)}}</b><span>ریال </span><br>
        @isset($order->id)
            <span>برای سفارش شماره:</span><b>{{$order->id}}</b><i class="btn btn-info fa fa-eye" onclick="view_order({{$order->id}})"></i>
        @endisset
    </div>
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="/customerDeposit/addEdit/{{$customer->id}}/{{$order->id?:0}}/{{$deposit->id}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="amount" class="input-group-text w-100">مبلغ واریزی:</label>
                    </div>
                    <input value="{{old('amount')?:$deposit->amount?:($order->unpaid()??'')}}" type="text" id="amount"
                           class="form-control price-input" name="amount"
                           pattern="^([-+,0-9.]+)" dir="ltr" required>
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
                    <input type="radio" class="checkboxradio" name="pay_method" id="cash"
                           value="cash" @checked((old('pay_method')?:$deposit->pay_method)!='cheque')
                           onclick="$('.cash').show().prop('required',true);
                           $('.cheque').hide().prop('required',false)">
                    <label for="cheque" class="">چکی</label>
                    <input type="radio" class="checkboxradio" name="pay_method" id="cheque"
                           value="cheque" @checked((old('pay_method')?:$deposit->pay_method)=='cheque')
                           onclick="$('.cash').hide().prop('required',false);
                           $('.cheque').show().prop('required',true)">
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
                            <option value="{{$id}}" @selected($id == (old('bank_id')?:$deposit->bank_id))>

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
                    <input type="text" class="form-control cheque" name="cheque_name" id="cheque_name"
                           value="{{old('cheque_name')?:$deposit->cheque_name}}" required>
                </div>
            </div>

            {{--            تاریخ چک--}}
            <div class="col-md-6 my-2 cheque">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="cheque_date_farsi" class="input-group-text w-100">تاریخ چک:</label>
                    </div>
                    <input type="text" class="form-control cheque" name="cheque_date_farsi" id="cheque_date_farsi"
                           required>
                    <input type="hidden" name="cheque_date" id="cheque_date">
                </div>
            </div>

            {{--            کد 16 رقمی روی چک--}}
            <div class="col-md-6 my-2 cheque">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="cheque_code" class="input-group-text w-100">کد 16 رقمی روی چک:</label>
                    </div>
                    <input type="text" class="form-control" name="cheque_code" id="cheque_code"
                           value="{{old('cheque_code')?:$deposit->cheque_code}}">
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
            <div class="col-md-6">
                <div class="form-group input-group ">
                    <div class="input-group-append" style="width: 160px">
                        <label for="photo" class="input-group-text cash">تصویر رسید بانکی:</label>
                        <label for="photo" class="input-group-text cheque">تصویر چک بانکی:</label>
                    </div>
                    <input type="file" id="photo" class="" name="photo">
                    <input type="hidden" name="old_Photo" value="{{$deposit->photo}}">
                </div>
            </div>
            @if($deposit->photo)
                <a href="/deposit/{{$deposit->photo}}" target="_blank">
                    <img style="height: 300px" src="/deposit/{{$deposit->photo}}">
                </a>
            @endif
        </div>

        <input type="submit" name="submit" class="btn btn-success" value="افزودن">
        &nbsp;
        <a href="/customer/transaction/{{$customer->id}}" class="btn btn-danger">بازگشت</a>

    </form>

@endsection

@section('files')
    <script>
        $(() => {
            $('.checkboxradio').checkboxradio();

            @if((old('pay_method')?:$deposit->pay_method)=='cheque')
            $('.cash').hide().prop('required', false);
            @else
            $('.cheque').hide().prop('required', false);
            @endif

            const chequeDate = new mds.MdsPersianDateTimePicker($('#cheque_date_farsi')[0], {
                targetTextSelector: '#cheque_date_farsi',
                targetDateSelector: '#cheque_date',
                @if(old('cheque_date')?:$deposit->cheque_date)
                selectedDate: new Date('{{old('cheque_date')?:$deposit->cheque_date}}'),
                @endif
            });


        });
    </script>
@endsection
