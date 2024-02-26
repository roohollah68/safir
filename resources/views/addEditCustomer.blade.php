@extends('layout.main')

@section('title')
    @if($edit)
        ویرایش مشتری
    @else
        افزودن مشتری
    @endif
@endsection

@section('files')

@endsection

@section('content')
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="" method="post">
        @csrf
        @php
            $name = old('name')?:($edit?$customer->name:'');
            $phone = old('phone')?:($edit?$customer->phone:'');
            $address = old('address')?:($edit?$customer->address:'');
            $zip_code = old('zip_code')?:($edit?$customer->zip_code:'');
        @endphp
        <div class="row">
            <div class="col-md-6">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="name" class="input-group-text w-100">نام:</label>
                    </div>
                    <input value="{{$name}}" type="text" id="name" class="form-control" name="name" required="">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group input-group required minlength=11 maxlength=11 pattern=^[۰-۹0-9]*$">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="phone" class="input-group-text w-100">شماره تماس:</label>
                    </div>
                    <input value="{{$phone}}" type="text" id="phone" class="form-control" name="phone" required="" minlength="11" maxlength="11" pattern="^[۰-۹0-9]*$">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="address" class="input-group-text w-100">آدرس:</label>
                    </div>
                    <textarea name="address" id="address" class="form-control" rows="2" required="">{{$address}}</textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group input-group minlength=10 maxlength=10 pattern=^[۰-۹0-9]*$">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="zip_code" class="input-group-text w-100">کد پستی:</label>
                    </div>
                    <input value="{{$zip_code}}" type="text" id="zip_code" class="form-control" name="zip_code" minlength="10" maxlength="10" pattern="^[۰-۹0-9]*$">
                </div>
            </div>
        </div>

        @if($edit)
            <input type="submit" class="btn btn-success" value="ویرایش">
        @else
            <input type="submit" class="btn btn-success" value="افزودن">
        @endif

        <a href="{{route('CustomerList')}}" class="btn btn-danger">بازگشت</a>

    </form>

@endsection
