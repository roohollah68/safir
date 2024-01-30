@extends('layout.main')

@section('title')
    @if(!$deposit)
        افزودن رسید پرداخت
    @else
        ویرایش رسید پرداخت
    @endif
@endsection

@section('files')

@endsection

@section('content')
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="" method="post" enctype="multipart/form-data">
        @csrf
        @php
            $amount = old('amount')?old('amount'):($deposit?$deposit->amount:'');
            $desc = old('desc')?old('desc'):($deposit?$deposit->desc:'');
        @endphp
        <div class="row">
            <div class="col-md-6">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="amount" class="input-group-text w-100">میزان واریزی:</label>
                    </div>
                    <input value="{{$amount}}" type="text" id="amount" class="form-control price-input" name="amount"
                           pattern="^[0-9]*$" required>
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
                    <textarea name="desc" id="desc" class="form-control" rows="2">{{$desc}}</textarea>
                </div>
            </div>
            @if(isset($req['file']))
                <a href="/deposit/{{$req['file']}}.jpg" target="_blank">
                    <img style="max-width: 200px; max-height: 200px" src="/deposit/{{$req['file']}}.jpg">
                </a>
                <input type="hidden" name="file" value="{{$req['file']}}.jpg">
            @else
                <div class="col-md-6">
                    <div class="form-group input-group ">
                        <div class="input-group-append" style="width: 160px">
                            <label for="photo" class="input-group-text w-100">تصویر رسید بانکی:</label>
                        </div>
                        <input type="file" id="photo" class="" name="photo">
                    </div>
                </div>
            @endif
        </div>


        @if($deposit)
            <input type="submit" class="btn btn-success" value="ویرایش">
        @else
            <input type="submit" class="btn btn-success" value="افزودن">
        @endif
        &nbsp;
        <a href="{{route('DepositList')}}" class="btn btn-danger">بازگشت</a>

        @if($deposit && $deposit->photo)
            <a href="/deposit/{{$deposit->photo}}" target="_blank"><img style="width: 300px"
                                                                        src="/deposit/{{$deposit->photo}}"></a>
        @endif
    </form>

@endsection
