@extends('layout.main')

@section('title')
    لیست درخواست وجه
@endsection

@section('content')
    <a class="btn btn-outline-success" href="{{route('addWithdrawal')}}"><i class="fa fa-plus"></i> ثبت درخواست وجه جدید</a>
    <a class="btn btn-outline-info" href="/Supplier/list"><i class="fa fa-user"></i> مشاهده لیست تامین کنندگان</a>
    <br>
    <br>
    @php
        $F = is_null($filter)?'':'&filter='.$filter;
        $O = is_null($official)?'':'&official='.$official;
        $L = is_null($Location)?'':'&Location='.$Location;
    @endphp
    <a class="btn btn-{{(is_null($filter) && is_null($official) && is_null($Location))?'':'outline-'}}primary"
       href="/Withdrawal/list">همه</a>
    <hr>
    <a class="btn btn-{{$filter=='counter'?'':'outline-'}}primary" href="?filter=counter{{$O.$L}}">منتظر تایید حسابدار</a>
    <a class="btn btn-{{$filter=='manager'?'':'outline-'}}primary" href="?filter=manager{{$O.$L}}">منتظر تایید مدیر</a>
    <a class="btn btn-{{$filter=='payment'?'':'outline-'}}primary" href="?filter=payment{{$O.$L}}">منتظر واریز</a>
    <a class="btn btn-{{$filter=='recipient'?'':'outline-'}}primary" href="?filter=recipient{{$O.$L}}">منتظر دریافت</a>
    <a class="btn btn-{{$filter=='complete'?'':'outline-'}}primary" href="?filter=complete{{$O.$L}}">تکمیل شده</a>
    <hr>
    <a class="btn btn-{{$official=='1'?'':'outline-'}}primary" href="?official=1{{$F.$L}}">رسمی</a>
    <a class="btn btn-{{$official=='0'?'':'outline-'}}primary" href="?official=0{{$F.$L}}">غیر رسمی</a>
    <hr>
    @foreach(config('withdrawalLocation') as $id => $location)
        @continue($id == 0)
        <a class="btn btn-{{$Location==$id?'':'outline-'}}primary" href="?Location={{$id}}{{$F.$O}}">
            {{$location}}
        </a>
    @endforeach


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
            <th>صاحب حساب</th>
            <th>حسابدار</th>
            <th>مدیر</th>
            <th>واریز</th>
            <th>دریافت</th>
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
                <td>{{$withdrawal->account_name}}</td>
                <td>{!! $withdrawal->counter_status() !!}</td>
                <td>{!! $withdrawal->manager_status() !!}</td>
                <td>{!! $withdrawal->payment_status() !!}</td>
                <td>{!! $withdrawal->recipient_status() !!}</td>
                <td>
                    <span class="fa fa-eye btn btn-info" onclick="view_withdrawal({{$id}})"
                          title="مشاهده"></span>
                    @if($withdrawal->manager_confirm != 1)
                        <a class="fa fa-edit btn btn-primary" href="/Withdrawal/edit/{{$id}}"
                           title="ویرایش"></a>
                        {{--                        <a class="fa fa-trash-alt btn btn-danger" href="/Withdrawal/delete/{{$id}}"--}}
                        {{--                           title="حذف"></a>--}}
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
        let withdrawals = {!!json_encode($withdrawals)!!};
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
            if (withdrawal.payment_confirm == 1 || withdrawal.counter_confirm != 1)
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

        @if(auth()->user()->meta('withdrawalPay'))
        function payment_form(id) {
            withdrawal = withdrawals[id]
            if (withdrawal.manager_confirm != 1 || withdrawal.recipient_confirm == 1)
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
<label for="payment_file">رسید پرداخت:</label>
<input type="file" name="payment_file" id="payment_file"><br>
<a class="btn btn-info hide" id="payment_file_old" href="/withdrawal/${withdrawal.payment_file}" target="_blank">مشاهده فایل</a>
<br>
<label for="payment_file2">رسید پرداخت 2:</label>
<input type="file" name="payment_file2" id="payment_file2"><br>
<a class="btn btn-info hide" id="payment_file_old2" href="/withdrawal/${withdrawal.payment_file2}" target="_blank">مشاهده فایل2</a>
<br>
<label for="payment_file3">رسید پرداخت 3:</label>
<input type="file" name="payment_file3" id="payment_file3"><br>
<a class="btn btn-info hide" id="payment_file_old3" href="/withdrawal/${withdrawal.payment_file3}" target="_blank">مشاهده فایل3</a>
<br>

<input class="btn btn-success" type="submit" value="ذخیره">
</form>
</div>
            `);
            $('.checkboxradio').checkboxradio();
            $(`input[value=${withdrawal.payment_confirm}]`).click();
            if (withdrawal.payment_file) {
                $('#payment_file_old').show();
            }
            if (withdrawal.payment_file2) {
                $('#payment_file_old2').show();
            }
            if (withdrawal.payment_file3) {
                $('#payment_file_old3').show();
            }
        }
        @endif

        @if(auth()->user()->meta('withdrawalRecipient'))
        function recipient_form(id) {
            withdrawal = withdrawals[id]
            if (withdrawal.payment_confirm != 1)
                return;
            let dialog = Dialog(`
            <div title="ثبت اطلاعات دریافت کالا یا خدمات" class="dialogs">
<form method="post" action="/withdrawal/recipientForm/${id}" enctype="multipart/form-data">
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

            <label for="recipient_desc">توضیحات</label><br>
            <textarea name="recipient_desc" id="recipient_desc" rows="3" class="w-100">${withdrawal.recipient_desc || ''}</textarea>

<br>
<br>

<input class="btn btn-success" type="submit" value="ذخیره" selected>
</form>
</div>
            `);
            $('.checkboxradio').checkboxradio();
            $(`input[value=${withdrawal.recipient_confirm}]`).click();
        }
        @endif

    </script>
@endsection
