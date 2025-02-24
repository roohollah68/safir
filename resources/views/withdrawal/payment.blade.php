
<div title="ثبت اطلاعات پرداخت" class="dialogs">
    <form method="post" action="/Withdrawal/paymentForm/${id}" enctype="multipart/form-data">
        @csrf
        <span>تغییر وضعیت:</span>

        <label class="btn btn-success" for="approved">تائید</label>
        <input type="radio" name="payment_confirm" value="1" id="approved" class="checkboxradio">

        <label class="btn btn-info" for="waiting">بررسی</label>
        <input type="radio" name="payment_confirm" value="0" id="waiting" class="checkboxradio">

        <label class="btn btn-danger" for="reject">عدم تائید</label>
        <input type="radio" name="payment_confirm" value="-1" id="reject" class="checkboxradio">

        <br>
        <br>

        <label for="payment_desc">توضیحات</label><br>
        <textarea name="payment_desc" id="payment_desc" rows="3" class="w-100">${withdrawal.payment_desc || ''}</textarea>

        <br>
        <label for="payment_file">رسید پرداخت:</label>
        <input type="file" name="payment_file" id="payment_file" class="compress-image"><br><br>
        <a class="btn btn-info hide" id="payment_file_old" href="/withdrawal/${withdrawal.payment_file}" target="_blank">مشاهده فایل</a>
        <br>
        <br>
        <label for="payment_file2">رسید پرداخت 2:</label>
        <input type="file" name="payment_file2" id="payment_file2" class="compress-image"><br><br>
        <a class="btn btn-info hide" id="payment_file_old2" href="/withdrawal/${withdrawal.payment_file2}" target="_blank">مشاهده فایل2</a>
        <br>
        <br>
        <label for="payment_file3">رسید پرداخت 3:</label>
        <input type="file" name="payment_file3" id="payment_file3" class="compress-image"><br><br>
        <a class="btn btn-info hide" id="payment_file_old3" href="/withdrawal/${withdrawal.payment_file3}" target="_blank">مشاهده فایل3</a>
        <br>
        <br>

        <input class="btn btn-success" type="submit" value="ذخیره">
    </form>
</div>
