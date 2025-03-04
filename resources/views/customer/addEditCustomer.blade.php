@extends('layout.main')

@section('title')
    @if($edit)
        ویرایش مشتری
    @else
        افزودن مشتری
    @endif
@endsection

@section('files')
    <script>
        let cities = {!!json_encode($cities)!!};
        let citiesId = {!!json_encode($citiesId)!!};
        let province = {!!json_encode($province)!!};
        $(function () {

            $("#city").autocomplete({
                source: Object.keys(cities),
                select: function (event, ui) {
                    $('#city').change();
                }
            });

            $('#city').change(function () {
                let city = cities[this.value];
                if (city) {
                    $('#city_id').val(city.id);
                    $('#province').html(province[city.province_id].name);
                } else {
                    let city = citiesId[$('#city_id').val()];
                    $('#city').val(city.name)
                    $('#province').html(province[city.province_id].name);
                }
            }).click(function () {
                this.value = '';
                $('#province').html('<sapn class="fa fa-arrow-rotate-back"></span>');
            });

        });

    </script>
    <style>
        .ui-autocomplete {
            max-height: 150px;
            overflow-y: auto;
            /* prevent horizontal scrollbar */
            overflow-x: hidden;
        }
    </style>
@endsection

@section('content')
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="/customer/add{{$edit ? "/$customer->id" : ""}}" method="post">
        @csrf
        <div class="row">
            <div class="col-md-6 mt-1 mb-1">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="name" class="input-group-text w-100">نام:</label>
                    </div>
                    <input value="{{old('name') ?: $customer->name}}" type="text" id="name" class="form-control"
                           name="name" required="">
                </div>
            </div>
            <div class="col-md-6 mt-1 mb-1">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="phone" class="input-group-text w-100">شماره تماس:</label>
                    </div>
                    <input value="{{old('phone') ?: $customer->phone}}" type="text" id="phone" class="form-control"
                           name="phone" required=""
                           minlength="11" maxlength="11" pattern="^[۰-۹0-9]*$">
                </div>
            </div>
            <div class="col-md-6 mt-1 mb-1">
                <div class="form-group">
                    <div class="d-flex align-items-center">
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="customer_type" class="input-group-text w-100">نوع مشتری:</label>
                        </div>
                        <div class="form-check me-3 ms-3">
                            <input class="form-check-input" type="radio" name="customer_type" id="Individual" value="Individual"
                                @checked(old('customer_type', $customer->customer_type ?? 'Individual') === 'Individual')>
                            <label class="form-check-label" for="Individual">حقیقی</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="customer_type" id="LegalEntity" value="LegalEntity"
                                @checked(old('customer_type', $customer->customer_type ?? 'Individual') === 'LegalEntity')>
                            <label class="form-check-label" for="LegalEntity">حقوقی</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-1 mb-1">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="name" class="input-group-text w-100">کد ملی:</label>
                    </div>
                    <input value="{{old('national_code ') ?: $customer->national_code}}" type="text" id="national_code" class="form-control"
                           name="national_code" minlength="10" maxlength="10" pattern="^[۰-۹0-9]*$">
                </div>
            </div>
            <div class="col-md-6 mt-1 mb-1">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="name" class="input-group-text w-100">شناسه‌ی ملی:</label>
                    </div>
                    <input value="{{old('national_id') ?: $customer->national_id}}" type="text" id="national_id" class="form-control"
                           name="national_id" minlength="11" maxlength="11" pattern="^[۰-۹0-9]*$">
                </div>
            </div>
            <div class="col-md-6 mt-1 mb-1">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="economic_code" class="input-group-text w-100">کد اقتصادی:</label>
                    </div>
                    <input value="{{old('economic_code') ?: $customer->economic_code}}" type="text" id="economic_code" class="form-control"
                           name="economic_code" minlength="12" maxlength="12" pattern="^[۰-۹0-9]*$">
                </div>
            </div>
            <div class="col-md-6 mt-1 mb-1">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="address" class="input-group-text w-100">آدرس:</label>
                    </div>
                    <textarea name="address" id="address" class="form-control" rows="2"
                              required>{{old('address') ?: $customer->address}}</textarea>
                </div>
            </div>
            <div class="col-md-6 mt-1 mb-1">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="city" class="input-group-text w-100">شهر:</label>
                    </div>
                    <input name="city" id="city" class="form-control" rows="2"
                           required value="{{old('city') ?: $customer->city->name}}">
                    <input type="hidden" id="city_id" name="city_id"
                           value="{{old('city_id') ?: $customer->city->id}}">
                    <div class="input-group-append" style="min-width: 120px">
                        <span id="province" onclick="$('#city').change()"
                              class="input-group-text w-100">{{$customer->city->province->name}}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-1 mb-1">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="zip_code" class="input-group-text w-100">کد پستی:</label>
                    </div>
                    <input value="{{old('zip_code') ?: $customer->zip_code}}" type="text" id="zip_code"
                           class="form-control" name="zip_code"
                           minlength="10" maxlength="10" pattern="^[۰-۹0-9]*$">
                </div>
            </div>

            @if($User->meta('editAllCustomers'))
                <div class="col-md-6 mt-1 mb-1">
                    <div class="form-group input-group required">
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="user" class="input-group-text w-100">کاربر مرتبط:</label>
                        </div>
                        <select class="form-control" name="user" id="user">
                            @foreach($users as $user)
                                <option value="{{$user->id}}"
                                        @if($user->id == $customer->user_id)
                                            selected
                                    @endif
                                >{{$user->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6 mt-1 mb-1">
                    <div class="form-group input-group">
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="credit_limit" class="input-group-text w-100">سقف اعتبار (ریال):</label>
                        </div>
                        <input value="{{old('credit_limit') ?: $customer->credit_limit ?: 0}}" type="text"
                               id="credit_limit" class="form-control price-input" name="credit_limit" step="1">
                    </div>
                </div>
            @else
                <input type="hidden" name="user"
                       value="{{old('user') ?: $customer->user->id}}">
            @endif
            @if(!$User->safir())
                <div class="col-md-6 mt-1 mb-1">
                    <div class="form-group input-group">
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="discount" class="input-group-text w-100">تخفیف پیشفرض:</label>
                        </div>
                        <input value="{{old('discount') ?: $customer->discount ?: 0}}" type="number" id="discount"
                               class="form-control" name="discount" min="0" max="100" step="1">
                    </div>
                </div>

                <div class="col-md-6 mt-1 mb-1">
                    <div class="form-group input-group required">
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="agreement" class="input-group-text w-100">تفاهم:</label>
                        </div>
                        <textarea id="agreement" class="form-control" name="agreement" rows="3" required
                        >{{old('agreement') ?: $customer->agreement ?: ''}}</textarea>
                    </div>
                </div>
            @endunless
        </div>

        @if($edit)
            <input type="submit" class="btn btn-success" value="ویرایش">
        @else
            <input type="submit" class="btn btn-success" value="افزودن">
        @endif

        <a href="{{route('CustomerList')}}" class="btn btn-danger">بازگشت</a>

    </form>

@endsection
