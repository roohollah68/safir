@extends('layout.main')

@section('title')
    {{ isset($fixedCost) ? 'ویرایش هزینه ثابت' : 'افزودن هزینه ثابت' }}
@endsection

@section('content')
    <form action="{{ route('fixed-costs.store', isset($fixedCost) ? $fixedCost->id : null) }}" method="post">
        @csrf
        <div class="row my-4">
            {{-- دسته بندی --}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="category" class="input-group-text w-100">دسته بندی:</label>
                    </div>
                    <select id="category" name="category" class="form-control" required>
                        <option value="" disabled {{ !isset($fixedCost) ? 'selected' : '' }}>انتخاب کنید</option>
                        <option value="0" {{ isset($fixedCost) && $fixedCost->category == '0' ? 'selected' : '' }}>حقوق تولید</option>
                        <option value="1" {{ isset($fixedCost) && $fixedCost->category == '1' ? 'selected' : '' }}>حقوق فروش</option>
                        <option value="2" {{ isset($fixedCost) && $fixedCost->category == '2' ? 'selected' : '' }}>اجاره</option>
                        <option value="3" {{ isset($fixedCost) && $fixedCost->category == '3' ? 'selected' : '' }}>بیمه</option>
                        <option value="4" {{ isset($fixedCost) && $fixedCost->category == '4' ? 'selected' : '' }}>بودجه‌ی ماهیانه‌ی تبلیغات</option>
                    </select>
                </div>
            </div>

            {{-- مبلغ --}}
            <div class="col-md-6 my-2">
                <div class="d-flex align-items-center">
                    <div class="required form-group d-flex align-items-center">
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="amount" class="input-group-text w-100">مبلغ:</label>
                        </div>
                        <input id="amount" type="text" class="form-control price-input" dir="ltr"
                               style="min-width: 265px" name="amount" required
                               value="{{ isset($fixedCost) ? $fixedCost->amount : '' }}">
                    </div>
                    <div class="input-group-prepend" style="min-width: 120px">
                        <label for="amount" class="input-group-text w-100" dir="rtl"> ریال</label>
                    </div>
                </div>
            </div>

            {{-- صاحب حساب --}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="account_owner" class="input-group-text w-100">صاحب حساب:</label>
                    </div>
                    <input id="account_owner" type="text" class="form-control" name="account_owner" required
                           value="{{ isset($fixedCost) ? $fixedCost->account_owner : '' }}">
                </div>
            </div>

            {{-- بابت --}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="desc" class="input-group-text w-100">بابت:</label>
                    </div>
                    <input id="desc" type="text" class="form-control" name="desc"
                           value="{{ isset($fixedCost) ? $fixedCost->desc : '' }}">
                </div>
            </div>

            {{-- شماره شبا --}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="iban" class="input-group-text w-100">شماره شبا:</label>
                    </div>
                    <input id="iban" type="text" class="form-control" name="iban" required
                           value="{{ isset($fixedCost) ? $fixedCost->iban : '' }}">
                </div>
            </div>

            {{-- روز سررسید --}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="due_day" class="input-group-text w-100">روز سررسید:</label>
                    </div>
                    <input id="due_day" type="number" class="form-control" name="due_day" required min="1" max="31" dir="rtl"
                           value="{{ isset($fixedCost) ? $fixedCost->due_day : '' }}">
                </div>
            </div>
        </div>

        {{-- دکمه‌ها --}}
        <div class="row my-4">
            <div class="col-md-6">
                <input type="submit" class="btn btn-success" value="{{ isset($fixedCost) ? 'ویرایش' : 'ثبت' }}">
                &nbsp;
                <a href="{{ route('fixed-costs.index') }}" class="btn btn-danger">بازگشت</a>
            </div>
        </div>
    </form>
@endsection