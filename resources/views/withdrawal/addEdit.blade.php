@extends('layout.main')

@section('title')
    @if(!$withdrawal->id)
        افزودن درخواست پرداخت
    @else
        ویرایش درخواست پرداخت
    @endif
@endsection

@section('files')

@endsection

@section('content')
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="amount" class="input-group-text w-100">میزان درخواست وجه:</label>
                    </div>
                    <input value="{{$withdrawal->amount}}" type="text" id="amount" class="form-control price-input"
                           name="amount"
                           required>
                    <div class="input-group-prepend" style="min-width: 120px">
                        <label for="amount" class="input-group-text w-100"> ریال</label>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="description" class="input-group-text w-100">توضیحات:</label>
                    </div>
                    <textarea name="description" id="description" class="form-control"
                              rows="3" required>{{$withdrawal->description}}</textarea>
                </div>
            </div>

            <input type="hidden" id="oldFile1" name="oldFile1" value="{{$withdrawal->file1}}">
            <div class="col-md-6 {{$withdrawal->file1?'hide':''}}" id="newFile">
                <div class="form-group input-group ">
                    <div class="input-group-append" style="width: 160px">
                        <label for="file1" class="input-group-text w-100">ارسال فایل توضیحات:</label>
                    </div>
                    <input type="file" id="file1" name="file1">
                </div>
                <span>فرمت های مجاز: jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx  | حجم مجاز: 3mb</span>
            </div>
        </div>
        <br>
        @if(isset($withdrawal->file1))
            <div id="oldFile">
            <a class="btn btn-info" href="/withdrawal/{{$withdrawal->file1}}" target="_blank">
                مشاهده فایل
            </a>
            <i class="fa fa-trash-alt btn btn-danger" onclick="$('#oldFile1').val('');$('#oldFile').hide();$('#newFile').show();"
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
