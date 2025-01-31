<div title="مشاهده جزئیات چک" class="dialogs">
    @if ($viewCheque->user_file)
        <a class="btn btn-info mb-2" href="/withdrawal/{{ $viewCheque->user_file }}" target="_blank">مشاهده فایل</a>
    @endif
    @if ($viewCheque->payment_file)
        <a class="btn btn-info mb-2" href="/withdrawal/{{ $viewCheque->payment_file }}" target="_blank">رسید پرداخت</a>
    @endif
    @if ($viewCheque->payment_file2)
        <a class="btn btn-info mb-2" href="/withdrawal/{{ $viewCheque->payment_file2 }}" target="_blank">رسید پرداخت 2</a>
    @endif
    @if ($viewCheque->payment_file3)
        <a class="btn btn-info mb-2" href="/withdrawal/{{ $viewCheque->payment_file3 }}" target="_blank">رسید پرداخت
            3</a>
    @endif
    @if ($viewCheque->recipient_file)
        <a class="btn btn-info mb-2" href="/withdrawal/{{ $viewCheque->recipient_file }}" target="_blank">رسید
            دریافت</a>
    @endif
    <br>
    <span>مبلغ درخواستی:</span> <b>{{ number_format($viewCheque->amount) }}</b> <br>
    <span>بابت:</span> <b>{{ $viewCheque->expense }}</b> <br>
    <span>مکان درخواست:</span> <b>{{ config('withdrawalLocation')[$viewCheque->location] }}</b> <br>
    <span>توضیحات کاربر:</span> <b>{{ $viewCheque->user_desc }}</b> <br>
    <span>روش پرداخت:</span> <b>{{ $viewCheque->pay_method == 'cash' ? 'نقدی' : 'چکی' }}</b> <br>
    <span>صاحب حساب یا دریافت کننده چک:</span> <b>{{ $viewCheque->account_name }}</b> <br>
    @if ($viewCheque->pay_method == 'cheque')
        <span>کد ملی یا شناسه ملی:</span> <b>{{ $viewCheque->cheque_id }}</b> <br>
        <span>تاریخ چک:</span> <b>{{ verta($viewCheque->cheque_date)->formatJalaliDate() }}</b> <br>
    @endif
    <span>دسته هزینه:</span> <b>{{ $viewCheque->expense_type == 'current' ? 'هزینه' : 'دارایی' }}</b> <br>
    <span>نوع هزینه:</span> <b>{{ $viewCheque->expense_desc }}</b> <br>
    <span>نوع فاکتور:</span>
    <b>{{ $viewCheque->official != 1 ? 'غیر رسمی' : 'رسمی ' . ($viewCheque->vat == 1 ? 'با ارزش افزوده' : 'بدون ارزش افزوده') }}</b>
    <br>
    <span>بانک پرداخت کننده:</span>
    @if ($viewCheque->bank_id)
        <b>
            @switch($viewCheque->bank_id)
                @case(1)
                    بانک سپه
                @break

                @case(2)
                    بانک ملت
                @break

                @case(3)
                    بانک کشاورزی
                @break

                @case(4)
                    بانک رفاه
                @break

                @case(5)
                    بانک پارسیان
                @break

                @case(6)
                    بانک سپه کامیار
                @break

                @case(7)
                    بانک تجارت نعمتی
                @break

                @case(8)
                    بانک سپه احسان
                @break

                @default
                    نامشخص
            @endswitch
        </b>
    @else
        <b>نامشخص</b>
    @endif
</div>
