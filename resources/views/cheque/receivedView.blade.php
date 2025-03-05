<div title="مشاهده جزئیات چک" class="dialogs">
    @if ($viewCheque->photo)
        <a class="btn btn-info mb-2" href="/deposit/{{ $viewCheque->photo }}" target="_blank">مشاهده فایل</a>
        <br>
    @endif
    <span>کاربر مرتبط:</span> <b>
        <a href="/customer/transaction/{{ $viewCheque->customer_id }}" class="text-primary text-decoration-none">
            {{ $viewCheque->customer->user->name }}
        </a>
    </b>
    <br>
    <span>نام مشتری:</span>
    <a href="/customer/transaction/{{ $viewCheque->customer_id }}" class="text-primary text-decoration-none">
        <b>{{ $viewCheque->customer->name }}</b>
    </a> <br>
    <span>شماره مشتری:</span> <b>{{ $viewCheque->customer_id }}</b> <br>
    <span>شماره واریز:</span> <b>{{ $viewCheque->id }}</b> <br>
    <span>مبلغ:</span> <b>{{ number_format($viewCheque->amount) }}</b> <br>
    <span>روش پرداخت:</span> <b>{{ config('payMethods')[$viewCheque->pay_method] }}</b> <br>
    <span>وضعیت:</span> <b>{{ $viewCheque->cheque_pass ? 'پاس شده' : 'پاس نشده' }}</b> <br>
    <span>نام صاحب چک:</span> <b>{{ $viewCheque->cheque_name }}</b> <br>
    <span>تاریخ چک:</span> <b>{{ verta($viewCheque->cheque_date)->formatJalaliDate() }}</b> <br>
    <span>کد 16 رقمی چک:</span> <b dir="ltr">{{ wordwrap($viewCheque->cheque_code, 4, ' ', true) }}</b> <br>
    <span>توضیحات:</span> <b>{{ $viewCheque->description }}</b> <br>
    <span>زمان ثبت:</span><b>{{ verta($viewCheque->created_at)->formatJalaliDate() }}</b><br>
    <span>زمان ویرایش:</span><b>{{ verta($viewCheque->updated_at)->formatJalaliDate() }}</b><br>

    @if ($viewCheque->cheque_registration)
        <span>رسید چک:</span>
        <a class="btn btn-secondary text-light mb-2" href="/deposit/{{ $viewCheque->cheque_registration }}"
            target="_blank">مشاهده
            فایل</a>
        <br>
    @else
        <form action="{{ route('cheque.view2', $viewCheque->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="cheque_registration">بارگزاری تصویر ثبت چک در بانک:</label>
                <br>
                <input type="file" id="cheque_registration" name="cheque_registration" class="me-5" required>
                <button type="submit" class="btn btn-success" style="font-family: inherit">ذخیره</button>
            </div>
        </form>
    @endif
</div>
