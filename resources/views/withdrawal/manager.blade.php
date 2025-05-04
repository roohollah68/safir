
<div title="بررسی مدیر" class="dialogs">
    <form method="post" action="/Withdrawal/managerForm/${id}">
        @csrf
        <span>تغییر وضعیت:</span>
        <input type="hidden" name="counter_confirm" value="0">
        <label class="btn btn-success" for="approved">تائید</label>
        <input type="radio" name="manager_confirm" value="1" id="approved" class="checkboxradio">

        <label class="btn btn-info" for="waiting">بررسی</label>
        <input type="radio" name="manager_confirm" value="0" id="waiting" class="checkboxradio">

        <label class="btn btn-danger" for="reject">عدم تائید</label>
        <input type="radio" name="manager_confirm" value="-1" id="reject" class="checkboxradio">

        <label class="btn btn-secondary" for="postpone_radio">تعویق</label>
        <input type="radio" name="manager_confirm" value="2" id="postpone_radio" class="checkboxradio">

        <br>
        <br>
{{--        <div id="postpone-section" style="display: none;">--}}
{{--            <div class="form-group input-group">--}}
{{--                <div class="input-group-append" id="postpone-trigger">--}}
{{--                    <label class="input-group-text"><i class="fas fa-calendar-alt me-2"></i>تاریخ پرداخت:</label>--}}
{{--                </div>--}}
{{--                <input type="text" name="postpone" id="postpone" class="form-control rounded"--}}
{{--                       style="cursor: pointer;">--}}

{{--                <input type="hidden" name="postpone_date" id="postpone_date">--}}
{{--            </div>--}}
{{--        </div>--}}
        <br>

        <label for="manager_desc">توضیحات</label><br>
        <textarea name="manager_desc" id="manager_desc" rows="3" class="w-100">${withdrawal.manager_desc || ''}</textarea>

        <br>
        <br>

        <input class="btn btn-success" type="submit" value="ذخیره">
    </form>
</div>
