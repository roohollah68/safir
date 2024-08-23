@extends('layout.main')

@section('title')
    @if($edit)
        ویرایش کاربر
    @else
        افزودن کاربر
    @endif
@endsection

@section('files')
    <script>
        $(function () {
            $('select#role').val('{{$user->role}}').change();
            $('select#city').val('{{$user->meta('city')}}').change();
        })
    </script>
@endsection

@section('content')
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="" method="post">
        @csrf
        <div class="row">

            <x-col-md-6 :name="'name'" value="{{old('name')?:$user->name}}" :required="true">
                نام و نام خانوادگی:
            </x-col-md-6>

            <x-col-md-6 :name="'username'" value="{{old('username')?:$user->username}}" minlength="5" :required="true">
                نام کاربری:
            </x-col-md-6>

            <x-col-md-6 :name="'phone'" value="{{old('phone')?:$user->phone}}" :required="true"
                        minlength="11" maxlength="11" pattern="^[۰-۹0-9]*$"
                        oninvalid="this.setCustomValidity('لطفا شماره 11 رقمی تلفن را وارد کنید.')"
                        onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                        oninput="this.setCustomValidity('')" placeholder="مانند 09123456789">شماره تماس:
            </x-col-md-6>

            <x-col-md-6 :name="'password'" placeholder="همان رمز عبور قبلی">
                رمز عبور:
            </x-col-md-6>

            @if($superAdmin)

                <div class="col-md-6 mb-2">
                    <div class="form-group input-group">
                        <div class="input-group-append">
                            <label for="role" class="input-group-text" style="min-width: 160px">نوع حساب کاربری:</label>
                        </div>
                        <select name="role" id="role" class="form-control">
                            <option value="superAdmin">سوپر ادمین</option>
                            <option value="admin">فروشنده با فاکتور</option>
                            <option value="user">سفیر</option>
                            <option value="warehouse">انبار دار</option>
                            <option value="counter">حسابدار</option>
                            <option value="account">صاحب حساب بانکی</option>
                        </select>
                    </div>
                </div>

            @endif

            <hr>
            @if($edit)
                <x-col-md-6 :name="'NuRecords'" value="{{$user->meta('NuRecords')}}" :type="'number'"
                            min="1" max="3000" step="1">تعداد سفارشات قابل نمایش:
                </x-col-md-6>

                <div class="col-md-6 mb-2">
                    <div class="form-group input-group">
                        <div class="input-group-append">
                            <label for="city" class="input-group-text w-100">شهر پیش فرض نمایش سفارشات:</label>
                        </div>
                        <select name="city" id="city" class="form-control">
                            <option>پیشفرض</option>
                            <option value="t">تهران</option>
                            <option value="m">مشهد</option>
                            <option value="s">شیراز</option>
                        </select>
                    </div>
                </div>
            @endif
        </div>
        @if($edit)
            <input type="submit" class="btn btn-success" value="ویرایش">&nbsp;
        @else
            <input type="submit" class="btn btn-success" value="افزودن">&nbsp;
        @endif
        @if($superAdmin)
            <a href="{{route('manageUsers')}}" class="btn btn-danger">بازگشت</a>
        @else
            <a href="{{route('listOrders')}}" class="btn btn-danger">بازگشت</a>
        @endif
    </form>
@endsection
