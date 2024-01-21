@extends('layout.main')

@section('title')
        افزودن رسید پرداخت
@endsection

@section('files')

@endsection

@section('content')
    <div class="m-3 p-3 bg-light">
    <span>ثبت سند واریزی برای: </span><b>{{$customer->name}}</b><br>
    <span>بدهی: </span><b dir="ltr">{{$customer->balance}}</b><br>
    </div>
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="/customerDeposit/add/{{$customer->id}}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{$customer->id}}">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="amount" class="input-group-text w-100">میزان واریزی:</label>
                    </div>
                    <input value="{{old('amount')?:($link?$link->amount:'')}}" type="text" id="amount" class="form-control" name="amount"
                           pattern="^([-+,0-9.]+)" dir="ltr" required>
                    <input type="hidden" name="link" value="{{$link?$link->id:''}}">
                    <div class="input-group-prepend" style="min-width: 120px">
                        <label for="amount" class="input-group-text w-100"> ریال</label>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="desc" class="input-group-text w-100">توضیحات:</label>
                    </div>
                    <textarea name="desc" id="desc" class="form-control" rows="2">{{old('desc')}}</textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group input-group ">
                    <div class="input-group-append" style="width: 160px">
                        <label for="photo" class="input-group-text w-100">تصویر رسید بانکی:</label>
                    </div>
                    <input type="file" id="photo" class="" name="photo">
                </div>
            </div>
        </div>

        <input type="submit" name="submit" class="btn btn-success" value="افزودن">
        &nbsp;
        <a href="/customer/transaction/{{$customer->id}}" class="btn btn-danger">بازگشت</a>

    </form>

@endsection
