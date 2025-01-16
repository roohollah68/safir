
<div title="بررسی مدیر" class="dialogs">
    <form method="post" action="/Withdrawal/managerForm/${id}">
        @csrf
        <span>تغییر وضعیت:</span>

        <label class="btn btn-success" for="approved">تائید</label>
        <input type="radio" name="manager_confirm" value="1" id="approved" class="checkboxradio">

        <label class="btn btn-info" for="waiting">بررسی</label>
        <input type="radio" name="manager_confirm" value="0" id="waiting" class="checkboxradio">

        <label class="btn btn-danger" for="reject">عدم تائید</label>
        <input type="radio" name="manager_confirm" value="-1" id="reject" class="checkboxradio">

        <br>
        <br>

        <label for="manager_desc">توضیحات</label><br>
        <textarea name="manager_desc" id="manager_desc" rows="3" class="w-100">${withdrawal.manager_desc || ''}</textarea>

        <br>
        <br>

        <input class="btn btn-success" type="submit" value="ذخیره">
    </form>
</div>
