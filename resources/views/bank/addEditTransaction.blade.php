@extends('layout.main')

@section('title')
    @if(!$edit)
        افزودن سند نقدینگی
    @else
        ویرایش سند نقدینگی
    @endif
@endsection

@section('files')

    <script>
        let banks = {!! $banks !!}
        $(() => {
            $(".checkboxradio").checkboxradio();

            $('input[value={{old('type')?:$bankTransaction->type?:'deposit'}}]').click();
        })
    </script>
@endsection

@section('content')
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="/BankTransaction/addEdit{{$edit?('/'.$bankTransaction->id):''}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row my-4">

            {{--            نوع سند--}}
            <div class="col-md-12 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append">
                        <label class="input-group-text" style="min-width: 160px">نوع سند:</label>
                    </div>
                    <label for="deposit" class="">واریز شرکا</label>
                    <input type="radio" class="checkboxradio" name="type" id="deposit"
                           value="deposit" onclick="$('.source_bank, .loan').hide();$('.Depositor_name').show();">

                    <label for="loan" class="">قرض به شرکت</label>
                    <input type="radio" class="checkboxradio" name="type" id="loan"
                           value="loan" onclick="$('.source_bank').hide();$('.Depositor_name, .loan').show();">

                    <label for="transfer" class="">انتقال بین حساب ها</label>
                    <input type="radio" class="checkboxradio" name="type" id="transfer"
                           value="transfer" onclick="$('.source_bank').show();$('.Depositor_name, .loan').hide();">
                </div>
            </div>

            {{--            مبلغ--}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="amount" class="input-group-text w-100">مبلغ:</label>
                    </div>
                    <input value="{{old('amount')?:$bankTransaction->amount}}" type="text" id="amount"
                           class="form-control price-input" name="amount" required>
                    <div class="input-group-prepend" style="min-width: 120px">
                        <label class="input-group-text w-100"> ریال</label>
                    </div>
                </div>
            </div>

            {{--           نام واریز کننده--}}
            <div class="col-md-6 my-2 Depositor_name">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="name" class="input-group-text w-100">نام واریز کننده:</label>
                    </div>
                    <input type="text" class="form-control" name="name" id="name"
                           value="{{old('name')?:$bankTransaction->name}}" required>
                </div>
            </div>


            {{--            بانک مبدا--}}
            <div class="col-md-6 my-2 source_bank">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="bank_source_id" class="input-group-text w-100">بانک مبدا:</label>
                    </div>
                    <select class="form-control" name="bank_source_id" id="bank_source_id"
                            onclick="$('#name').val('بانک '+banks[this.value].name)">
                        <option value=""></option>
                        @foreach($banks as $id=>$bank)
                            <option
                                value="{{$id}}" @selected((old('bank_source_id')?:$bankTransaction->bank_source_id) == $id)>
                                {{$bank->name}}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{--            بانک مقصد--}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="bank_id" class="input-group-text w-100">بانک مقصد:</label>
                    </div>
                    <select class="form-control" name="bank_id" id="bank_id">
                        <option value=""></option>
                        @foreach($banks as $id=>$bank)
                            <option value="{{$id}}" @selected((old('bank_id')?:$bankTransaction->bank_id) == $id)>
                                {{$bank->name}}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{--            توضیحات--}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="description" class="input-group-text w-100">توضیحات:</label>
                    </div>
                    <textarea name="description" id="description" class="form-control"
                              rows="3">{{old('description')?:$bankTransaction->description}}</textarea>
                </div>
            </div>

        </div>

        {{--        ارسال فایل رسید--}}
        <input type="hidden" id="old_file" name="old_receipt" value="{{$bankTransaction->receipt}}">
        <div>
            <div class="form-group input-group ">
                <div class="input-group-append">
                    <label for="receipt" class="input-group-text w-100"> فایل رسید:</label>
                </div>
                <input type="file" id="receipt" name="receipt">
            </div>

        </div>
        <br>
        @if(isset($bankTransaction->receipt))
            <div id="oldReceipt">
                <a class="btn btn-info" href="/withdrawal/{{$bankTransaction->receipt}}" target="_blank">مشاهده فایل
                    رسید</a>
                <i class="fa fa-trash-alt btn btn-danger"
                   onclick="$('#old_file').val('');$('#oldReceipt').hide();"
                   title="حذف"></i>
            </div>
        @endif

        {{--        رسید صورت جلسه شرکت بابت قرض--}}
        <div class="loan">
            <input type="hidden" id="old_file2" name="old_receipt2" value="{{$bankTransaction->receipt2}}">
            <div>
                <div class="form-group input-group ">
                    <div class="input-group-append">
                        <label for="receipt2" class="input-group-text w-100"> فایل رسید صورت جلسه شرکت بابت قرض:</label>
                    </div>
                    <input type="file" id="receipt2" name="receipt2">
                </div>
            </div>
            <br>
            @if(isset($bankTransaction->receipt2))
                <div id="oldReceipt2">
                    <a class="btn btn-info" href="/withdrawal/{{$bankTransaction->receipt2}}" target="_blank">مشاهده
                        فایل
                        رسید صورت جلسه شرکت بابت قرض</a>
                    <i class="fa fa-trash-alt btn btn-danger"
                       onclick="$('#old_file2').val('');$('#oldReceipt2').hide();"
                       title="حذف"></i>
                </div>
            @endif
        </div>
        <span>فرمت های مجاز: jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx  | حجم مجاز: 3mb</span>
        <br>
        @if($edit)
            <input type="submit" class="btn btn-success" value="ویرایش">
        @else
            <input type="submit" class="btn btn-success" value="افزودن">
        @endif
        &nbsp;
        <a href="{{route('BankTransactionList')}}" class="btn btn-danger">بازگشت</a>

    </form>

@endsection
