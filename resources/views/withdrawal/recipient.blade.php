
<div title="ثبت اطلاعات دریافت کالا یا خدمات" class="dialogs">
    <form method="post" action="/Withdrawal/recipientForm/${id}" enctype="multipart/form-data">
        @csrf
        <span>تغییر وضعیت:</span>

        <label class="btn btn-success" for="approved">تائید</label>
        <input type="radio" name="recipient_confirm" value="1" id="approved" class="checkboxradio">

        <label class="btn btn-info" for="waiting">بررسی</label>
        <input type="radio" name="recipient_confirm" value="0" id="waiting" class="checkboxradio">

        <label class="btn btn-danger" for="reject">عدم تائید</label>
        <input type="radio" name="recipient_confirm" value="-1" id="reject" class="checkboxradio">

        <br>
        <br>
        <label for="recipient_file">رسید انبار:</label>
        <input type="file" name="recipient_file" id="recipient_file" class="compress-image"><br><br>
        <a class="btn btn-info hide" id="recipient_file_old" href="/withdrawal/${withdrawal.recipient_file}" target="_blank">مشاهده فایل</a>
        <br>
        <br>
        <label for="recipient_desc">توضیحات</label><br>
        <textarea name="recipient_desc" id="recipient_desc" rows="3" class="w-100">${withdrawal.recipient_desc || ''}</textarea>

        <br>
        <br>

        <input class="btn btn-success" type="submit" value="ذخیره" selected>
    </form>
</div>
