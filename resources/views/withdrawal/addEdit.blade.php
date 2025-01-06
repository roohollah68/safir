@extends('layout.main')

@section('title')
    @if(!$withdrawal->id)
        افزودن درخواست پرداخت
    @else
        ویرایش درخواست پرداخت
    @endif
@endsection

@section('files')

    <script>
        let Current =
            `@foreach(config('expense_type')['current'] as $data)
            <option>{{$data}}</option>
            @endforeach`
        let Property =
            `@foreach(config('expense_type')['property'] as $data)
            <option>{{$data}}</option>
            @endforeach`

        $(() => {
            $(".checkboxradio").checkboxradio();
            let date_property = {
                targetTextSelector: '[name="cheque_date"]',
                targetDateSelector: '[name="cheque_date_hide"]',
            }
            @if(old('cheque_date')?:$withdrawal->cheque_date)
                date_property.selectedDate = new Date('{{old('cheque_date_hide')?:$withdrawal->cheque_date}}');
            @endif
            const chequeDate = new mds.MdsPersianDateTimePicker(document.getElementById('cheque_date'), date_property);
            @if((old('pay_method')?:$withdrawal->pay_method)=='cheque')
            $('.cash').hide().prop('required', false);
            @else
            $('.cheque').hide().prop('required', false);
            @endif

            @if((old('expense_type')?:$withdrawal->expense_type)=='current')
            $('#expense_desc').html(Current).val('{{old('expense_desc')?:$withdrawal->expense_desc}}').change()
            @elseif((old('expense_type')?:$withdrawal->expense_type)=='property')
            $('#expense_desc').html(Property).val('{{old('expense_desc')?:$withdrawal->expense_desc}}').change()
            @endif

            @if((old('official')?:$withdrawal->official)==1)
            $('.VAT').show();
            @endif
        })
    </script>
    <script src="/date-time-picker/mds.bs.datetimepicker.js"></script>
    <link rel="stylesheet" href="/date-time-picker/mds.bs.datetimepicker.style.css">
@endsection

@section('content')
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row my-4">

            {{--            مبلغ--}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="amount" class="input-group-text w-100">مبلغ:</label>
                    </div>
                    <input value="{{old('amount')?:$withdrawal->amount}}" type="text" id="amount"
                           class="form-control price-input" name="amount" required>
                    <div class="input-group-prepend" style="min-width: 120px">
                        <label for="amount" class="input-group-text w-100"> ریال</label>
                    </div>
                </div>
            </div>

            {{--            بابت--}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="description" class="input-group-text w-100">بابت:</label>
                    </div>
                    <input type="text" class="form-control" name="expense"
                           value="{{old('expense')?:$withdrawal->expense}}" required>
                </div>
            </div>

            {{--            نوع هزینه--}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="location" class="input-group-text w-100">مکان درخواست:</label>
                    </div>
                    <select name="location" id="location">
                        @foreach(config('withdrawalLocation') as $id => $location)
                            <option value="{{$id}}" @selected((old('location')?:$withdrawal->location) == $id)>
                                {{$location}}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{--            روش پرداخت--}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label class="input-group-text ">روش پرداخت:</label>
                    </div>
                    <label for="cash" class="">نقدی</label>
                    <input type="radio" class="checkboxradio" name="pay_method" id="cash"
                           value="cash" @checked((old('pay_method')?:$withdrawal->pay_method)!='cheque')
                           onclick="$('.cash').show().prop('required',true);
                           $('.cheque').hide().prop('required',false)">
                    <label for="cheque" class="">چکی</label>
                    <input type="radio" class="checkboxradio" name="pay_method" id="cheque"
                           value="cheque" @checked((old('pay_method')?:$withdrawal->pay_method)=='cheque')
                           onclick="$('.cash').hide().prop('required',false);
                           $('.cheque').show().prop('required',true)">
                </div>
            </div>

            {{--            نام صاحب حساب--}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="account_name" class="input-group-text w-100">نام صاحب حساب:</label>
                    </div>
                    <input type="text" class="form-control" name="account_name"
                           value="{{old('account_name')?:$withdrawal->account_name}}" required>
                </div>
            </div>

            {{--            شماره شبا یا کارت--}}
            <div class="col-md-6 my-2 cash">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="account_number" class="input-group-text w-100">شماره شبا یا کارت:</label>
                    </div>
                    <input type="text" class="form-control cash" name="account_number"
                           value="{{old('account_number')?:$withdrawal->account_number}}" required>
                </div>
            </div>

            {{--            کد ملی یا شناسه ملی--}}
            <div class="col-md-6 my-2 cheque">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="cheque_id" class="input-group-text w-100">کد ملی یا شناسه ملی:</label>
                    </div>
                    <input type="text" class="form-control cheque" name="cheque_id"
                           value="{{old('cheque_id')?:$withdrawal->cheque_id}}" required>
                </div>
            </div>

            {{--            تاریخ چک--}}
            <div class="col-md-6 my-2 cheque">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="cheque_date" class="input-group-text w-100">تاریخ چک:</label>
                    </div>
                    <input type="text" class="form-control cheque" name="cheque_date" id="cheque_date" required>
                    <input type="hidden" name="cheque_date_hide">
                </div>
            </div>

            {{--            دسته هزینه--}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label class="input-group-text ">دسته هزینه:</label>
                    </div>
                    <input type="radio" name="expense_type" value="" class="hide">
                    <label for="current" class="">هزینه</label>
                    <input type="radio" class="checkboxradio" name="expense_type" id="current"
                           value="current" @checked((old('expense_type')?:$withdrawal->expense_type)=='current')
                           onclick="$('#expense_desc').html(Current).change()">
                    <label for="property" class="">دارایی</label>
                    <input type="radio" class="checkboxradio" name="expense_type" id="property"
                           value="property" @checked((old('expense_type')?:$withdrawal->expense_type)=='property')
                           onclick="$('#expense_desc').html(Property).change()">
                </div>
            </div>

            {{--            نوع هزینه--}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="expense_desc" class="input-group-text w-100">نوع هزینه:</label>
                    </div>
                    <select name="expense_desc" id="expense_desc"></select>
                </div>
            </div>

            {{--            نوع فاکتور--}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label class="input-group-text ">نوع فاکتور:</label>
                    </div>
                    <label for="official" class="">رسمی</label>
                    <input type="radio" class="checkboxradio" name="official" id="official"
                           value="1" @checked((old('official')?:$withdrawal->official)==1)
                           onclick="$('.VAT').show()">
                    <label for="unofficial" class="">غیر رسمی</label>
                    <input type="radio" class="checkboxradio" name="official" id="unofficial"
                           value="0" @checked((old('official')?:$withdrawal->official)!=1)
                           onclick="$('.VAT').hide()">
                </div>
            </div>

            {{--            ارزش افزوده(10%)--}}
            <div class="col-md-6 my-2 VAT hide">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label class="input-group-text ">ارزش افزوده(10%):</label>
                    </div>
                    <label for="vat" class="">دارد</label>
                    <input type="radio" class="checkboxradio" name="vat" id="vat"
                           value="1" @checked((old('vat')?:$withdrawal->vat)==1)>
                    <label for="no-vat" class="">ندارد</label>
                    <input type="radio" class="checkboxradio" name="vat" id="no-vat"
                           value="0" @checked((old('vat')?:$withdrawal->vat)!=1)>
                </div>
            </div>

            {{--            توضیحات--}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="user_desc" class="input-group-text w-100">توضیحات:</label>
                    </div>
                    <textarea name="user_desc" id="user_desc" class="form-control"
                              rows="3">{{old('user_desc')?:$withdrawal->user_desc}}</textarea>
                </div>
            </div>
        </div>

        {{--        ارسال فایل توضیحات--}}
        <input type="hidden" id="old_user_file" name="old_user_file" value="{{$withdrawal->user_file}}">
        <div class="col-md-6 {{$withdrawal->user_file?'hide':''}}" id="newFile">
            <div class="form-group input-group ">
                <div class="input-group-append" style="width: 160px">
                    <label for="user_file" class="input-group-text w-100">ارسال فایل توضیحات:</label>
                </div>
                <input type="file" id="user_file" name="user_file">
            </div>
            <span>فرمت های مجاز: jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx  | حجم مجاز: 3mb</span>
        </div>
        <br>
        @if(isset($withdrawal->user_file))
            <div id="oldFile">
                <a class="btn btn-info" href="/withdrawal/{{$withdrawal->user_file}}" target="_blank">مشاهده فایل</a>
                <i class="fa fa-trash-alt btn btn-danger"
                   onclick="$('#old_user_file').val('');$('#oldFile').hide();$('#newFile').show();"
                   title="حذف"></i>
            </div>
        @endif
        <br>
        <br>
        @if($withdrawal->id)
            <input type="submit" class="btn btn-success" value="ویرایش">
        @else
            <input type="submit" class="btn btn-success" value="افزودن">
        @endif
        &nbsp;
        <a href="{{route('WithdrawalList')}}" class="btn btn-danger">بازگشت</a>

    </form>

@endsection
