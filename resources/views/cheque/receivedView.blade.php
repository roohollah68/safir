<div title="مشاهده جزئیات چک" class="dialogs">
    {{-- <a href="/deposit/{{ $viewCheque->photo }}" target="_blank">
        <img style="width: 300px" src="/deposit/{{ $viewCheque->photo }}">
    </a> --}}
    <span>کاربر مرتبط:</span> <b>
        <span onclick="window.location.href='/customer/transaction/{{ $viewCheque->customer_id }}'" class="text-primary"
            style="cursor: pointer;">
            {{ $viewCheque->customer->user->name }}
        </span>
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
    <span>نام صاحب چک:</span> <b>{{ $viewCheque->cheque_name }}</b> <br>
    <span>تاریخ چک:</span> <b>{{ verta($viewCheque->cheque_date)->formatJalaliDate() }}</b> <br>
    <span>کد 16 رقمی چک:</span> <b dir="ltr">{{ wordwrap($viewCheque->cheque_code, 4, ' ', true) }}</b> <br>
    <span>توضیحات:</span> <b>{{ $viewCheque->description }}</b> <br>
    <span>زمان ثبت:</span><b>{{ verta($viewCheque->created_at)->formatJalaliDate() }}</b><br>
    <span>زمان ویرایش:</span><b>{{ verta($viewCheque->updated_at)->formatJalaliDate() }}</b><br>
</div>
