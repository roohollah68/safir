<div title="بررسی حسابداری" class="dialogs">
    <form method="post" action="/Withdrawal/counterForm/${id}">
        @csrf
        <span>تغییر وضعیت:</span>

        <label class="btn btn-success" for="approved">تائید</label>
        <input type="radio" name="counter_confirm" value="1" id="approved" class="checkboxradio">

        <label class="btn btn-info" for="waiting">بررسی</label>
        <input type="radio" name="counter_confirm" value="0" id="waiting" class="checkboxradio">

        <label class="btn btn-danger" for="reject">عدم تائید</label>
        <input type="radio" name="counter_confirm" value="-1" id="reject" class="checkboxradio">

        <br>
        <br>

        <label for="counter_desc">توضیحات</label><br>
        <textarea name="counter_desc" id="counter_desc" rows="3"
                  class="w-100">${withdrawal.counter_desc || ''}</textarea>

        <br>
        <br>
        <div class="row">
            {{--            دسته هزینه--}}
            <div class="col-md-12 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label class="input-group-text ">دسته هزینه:</label>
                    </div>
                    <input type="radio" name="expense_type" value="" class="hide">
                    <label for="current" class="">هزینه</label>
                    <input type="radio" class="checkboxradio" name="expense_type" id="current"
                           value="current" onclick="$('#expense_desc').html(Current).change()">

                    <label for="property" class="">دارایی</label>
                    <input type="radio" class="checkboxradio" name="expense_type" id="property"
                           value="property" onclick="$('#expense_desc').html(Property).change()">

                </div>
            </div>

            {{--            نوع هزینه--}}
            <div class="col-md-12 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="expense_desc" class="input-group-text w-100">نوع هزینه:</label>
                    </div>
                    <select name="expense_desc" id="expense_desc" style="min-width: 300px;"></select>
                </div>
            </div>

            {{--            نوع فاکتور--}}
            <div class="col-md-12 my-2">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label class="input-group-text ">نوع فاکتور:</label>
                    </div>
                    <label for="official" class="">رسمی</label>
                    <input type="radio" class="checkboxradio" name="official" id="official"
                           value="1" onclick="$('.VAT').show()">

                    <label for="unofficial" class="">غیر رسمی</label>
                    <input type="radio" class="checkboxradio" name="official" id="unofficial"
                           value="0" onclick="$('.VAT').hide()">

                </div>
            </div>

            {{--            ارزش افزوده(10%)--}}
            <div class="col-md-12 my-2 VAT hide">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label class="input-group-text ">ارزش افزوده(10%):</label>
                    </div>
                    <label for="vat" class="">دارد</label>
                    <input type="radio" class="checkboxradio" name="vat" id="vat" value="1">
                    <label for="no-vat" class="">ندارد</label>
                    <input type="radio" class="checkboxradio" name="vat" id="no-vat" value="0">
                </div>
            </div>


            <label for="bank_id">انتخاب بانک</label>
            <select id="bank_id" name="bank_id" class="form-control w-50" required>
                <option value="">لطفا انتخاب کنید</option>
                @foreach($banks as $id => $bank)
                    <option value="{{$id}}">{{$bank->name}}</option>
                @endforeach
            </select>
        </div>
        <br>
        <br>

        <input class="btn btn-success" type="submit" value="ذخیره">
    </form>
</div>


