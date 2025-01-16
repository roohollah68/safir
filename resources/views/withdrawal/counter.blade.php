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
        <textarea name="counter_desc" id="counter_desc" rows="3" class="w-100">${withdrawal.counter_desc || ''}</textarea>

        <br>
        <br>

        <label for="bank_id">انتخاب بانک</label>
        <select id="bank_id" name="bank_id" class="form-control w-50" required>
            <option value="">لطفا انتخاب کنید</option>
            @foreach($banks as $id => $bank)
                <option value="{{$id}}">{{$bank->name}}</option>
            @endforeach
        </select>
        <br>
        <br>

        <input class="btn btn-success" type="submit" value="ذخیره">
    </form>
</div>


