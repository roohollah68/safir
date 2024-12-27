@extends('layout.main')

@section('title')
    لیست درخواست وجه
@endsection

@section('content')
    <a class="btn btn-info" href="{{route('addWithdrawal')}}">ثبت درخواست وجه جدید</a>
    <br>
    <br>
    <a class="btn btn-{{$filter?'outline-':''}}primary" href="/Withdrawal/list">همه</a>
    <a class="btn btn-{{$filter=='counter'?'':'outline-'}}primary" href="?filter=counter">منتظر تایید حسابدار</a>
    <a class="btn btn-{{$filter=='manager'?'':'outline-'}}primary" href="?filter=manager">منتظر تایید مدیر</a>
    <a class="btn btn-{{$filter=='payment'?'':'outline-'}}primary" href="?filter=payment">منتظر واریز</a>
    <a class="btn btn-{{$filter=='paid'?'':'outline-'}}primary" href="?filter=paid">پرداخت شده</a>
    <br>
    <br>
    <table class="table table-striped" id="withdrawal-table">
        <thead>
        <tr>
            <th>شماره</th>
            <th>تاریخ ثبت</th>
            <th>کاربر</th>
            <th>مبلغ (ریال)</th>
            <th>بابت</th>
            <th>حسابدار</th>
            <th>مدیر</th>
            <th>واریز</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>

        @foreach($withdrawals as $id => $withdrawal)

            <tr>
                <td>{{$id}}</td>
                <td>{{verta($withdrawal->created_at)->formatJalaliDate()}}</td>
                <td>{{$withdrawal->user->name}}</td>
                <td>{{number_format($withdrawal->amount)}}</td>
                <td>{{$withdrawal->expense}}</td>
                <td>{!! $withdrawal->counter_status() !!}</td>
                <td>{!! $withdrawal->manager_status() !!}</td>
                <td>{!! $withdrawal->payment_status() !!}</td>
                <td>
                    <span class="fa fa-eye btn btn-info" onclick="view_withdrawal({{$id}})"
                          title="مشاهده"></span>
                    @if($withdrawal->manager_confirm != 1)
                        <a class="fa fa-edit btn btn-primary" href="/Withdrawal/edit/{{$id}}"
                           title="ویرایش"></a>
                        <a class="fa fa-trash-alt btn btn-danger" href="/Withdrawal/delete/{{$id}}"
                           title="حذف"></a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection

@section('files')
    <script>
        let token = '{{csrf_token()}}';
        let withdrawals = {!!json_encode($withdrawals)!!}
        $(function () {
            $('#withdrawal-table').DataTable({
                pageLength: 100,
                order: [[0, "desc"]],
            });
        });
        @if(auth()->user()->meta('counter'))
            function counter_form(id) {
                withdrawal = withdrawals[id]
                if (withdrawal.manager_confirm == 1)
                    return;
                let dialog = Dialog(`
            <div title="بررسی حسابداری" class="dialogs">
<form method="post" action="/withdrawal/counterForm/${id}">
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

<label for="bank">انتخاب بانک</label>
<select id="bank" name="bank" class="form-control w-50">
<option>سپه</option>
<option>ملت</option>
<option>رفاه</option>
<option>کشاورزی</option>
<option>پارسیان</option>
</select>

<br>
<br>

<input class="btn btn-success" type="submit" value="ذخیره">
</form>
</div>
            `);
                $('.checkboxradio').checkboxradio();
                $(`input[value=${withdrawal.counter_confirm}]`).click();
                $('select[name=bank]').val(withdrawal.bank).change();
            }
        @endif

        @if(auth()->user()->id == 122)
        function manager_form(id) {
            withdrawal = withdrawals[id]
            if (withdrawal.payment_confirm == 1)
                return;
            let dialog = Dialog(`
            <div title="بررسی مدیر" class="dialogs">
<form method="post" action="/withdrawal/managerForm/${id}">
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
            `);
            $('.checkboxradio').checkboxradio();
            $(`input[value=${withdrawal.manager_confirm}]`).click();
        }
        @endif

        @if(auth()->user()->id == 122 || auth()->user()->id == 41)
        function payment_form(id) {
            withdrawal = withdrawals[id]
            if (withdrawal.manager_confirm != 1)
                return;
            let dialog = Dialog(`
            <div title="ثبت اطلاعات پرداخت" class="dialogs">
<form method="post" action="/withdrawal/paymentForm/${id}" enctype="multipart/form-data">
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
<br>

<label for="payment_file">رسید پرداخت:</label>
<input type="file" name="payment_file" id="payment_file"><br>
<a class="btn btn-info hide" id="payment_file_old" href="/withdrawal/${withdrawal.payment_file}" target="_blank">مشاهده فایل</a>
<br>
<br>

<input class="btn btn-success" type="submit" value="ذخیره">
</form>
</div>
            `);
            $('.checkboxradio').checkboxradio();
            $(`input[value=${withdrawal.payment_confirm}]`).click();
            if(withdrawal.payment_file){
                $('#payment_file_old').show();
            }
        }
        @endif

    </script>
@endsection
