@extends('layout.main')

@section('title')
    @if($customer->name)
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
        <div class="row">
            <div class="col-md-6">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="name" class="input-group-text w-100">نام:</label>
                    </div>
                    <input value="{{old('name')?:$customer->name}}" type="text" id="name" class="form-control"
                           name="name" required="">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="phone" class="input-group-text w-100">شماره تماس:</label>
                    </div>
                    <input value="{{old('phone')?:$customer->phone}}" type="text" id="phone" class="form-control"
                           name="phone" required=""
                           minlength="11" maxlength="11" pattern="^[۰-۹0-9]*$">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="address" class="input-group-text w-100">آدرس:</label>
                    </div>
                    <textarea name="address" id="address" class="form-control" rows="2"
                              required="">{{old('address')?:$customer->address}}</textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="zip_code" class="input-group-text w-100">کد پستی:</label>
                    </div>
                    <input value="{{old('zip_code')?:$customer->zip_code}}" type="text" id="zip_code"
                           class="form-control" name="zip_code"
                           minlength="10" maxlength="10" pattern="^[۰-۹0-9]*$">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="category" class="input-group-text w-100">دسته بندی:</label>
                    </div>
                    <select class="form-control" name="category" id="category">
                        @for($ii=0;$ii<11;$ii++)
                            <option value="{{$ii}}"
                            @if($ii == $customer->category)
                                selected
                                @endif
                                >{{$customer->categoryText($ii)}}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>

        @if($customer->name)
            <input type="submit" class="btn btn-success" value="ویرایش">
        @else
            <input type="submit" class="btn btn-success" value="افزودن">
        @endif

        <a href="{{route('CustomerList')}}" class="btn btn-danger">بازگشت</a>

    </form>

@endsection
