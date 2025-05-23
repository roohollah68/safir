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
            $('.checkboxradio').checkboxradio();
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

            @if($User->meta('usersEdit'))

                <div class="col-md-6 mb-2">
                    <div class="form-group input-group">
                        <div class="input-group-append">
                            <label for="role" class="input-group-text" style="min-width: 160px">نوع حساب کاربری:</label>
                        </div>
                        <select name="role" id="role" class="form-control">
                            <option value="admin">فروشنده با فاکتور</option>
                            <option value="user">سفیر</option>
                        </select>
                    </div>
                </div>
                <x-col-md-6 :name="'credit'" value="{{$user->credit}}"
                            class="price-input form-control">اعتبار (ریال):
                </x-col-md-6>

                <x-col-md-6 :name="'discount'" type="number" min="0" max="100" step="0.01"
                            value="{{$user->meta('discount')}}"
                            class="form-control" required>تخفیف (%):
                </x-col-md-6>
                @if($edit)
                    <div class="col-md-6">
                        <a class="btn btn-info" href="/user/changeAccount/{{$user->id}}">انتقال به حساب کاربری {{$user->name}}</a>
                    </div>
                @endif
            @endif

            <hr>
            @if($edit)
                <x-col-md-6 :name="'NuRecords'" value="{{$user->meta('NuRecords')}}" :type="'number'"
                            min="1" step="1">تعداد نمایش سفارشات:
                </x-col-md-6>

                <div class="col-md-6 mb-2">
                    <div class="form-group input-group">
                        <div class="input-group-append">
                            <label for="warehouseId" class="input-group-text w-100">انبار پیش فرض:</label>
                        </div>
                        <select name="warehouseId" id="warehouseId" class="form-control">
                            @foreach($warehouses as $warehouse)
                                <option
                                    value="{{$warehouse->id}}" @selected($warehouse->id == $user->meta('warehouseId'))>{{$warehouse->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @if($User->meta('usersEdit'))
                    <a href="/invoiceData" class="btn btn-secondary my-2" style="width: 200px">ویرایش اطلاعات فاکتور</a>
                    <hr>
                    <h4>دسترسی ها</h4>

                    @foreach(config('userMeta.access') as $access => $desc)
                        <div class="m-1">
                            <label for="{{$access}}">{{$desc}}</label>
                            <input type="checkbox" name="{{$access}}" id="{{$access}}"
                                   class="checkboxradio" @checked($user->meta($access))>
                            <br>
                        </div>
                    @endforeach
                @endif
            @endif
        </div>


        <input type="submit" class="btn btn-success" value="ذخیره">&nbsp;
        @if($User->meta('usersEdit'))
            <a href="{{route('usersList')}}" class="btn btn-danger">بازگشت</a>
        @else
            <a href="{{route('listOrders')}}" class="btn btn-danger">بازگشت</a>
        @endif
    </form>
@endsection
