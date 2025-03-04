@extends('layout.main')

@section('title')
    لیست درخواست وجه
@endsection

@section('content')
    <a class="btn btn-outline-success" href="{{route('addWithdrawal')}}"><i class="fa fa-plus"></i> ثبت درخواست وجه جدید</a>
    <a class="btn btn-outline-info" href="/Supplier/list"><i class="fa fa-user"></i> مشاهده لیست تامین کنندگان</a>
    <a class="btn btn-outline-primary" href="/Withdrawal/tankhah/add"><i class="fa fa-plus"></i>ثبت فاکتور تنخواه</a>
    <i class="mx-3"></i>
    <i>از تاریخ: </i><input type="text" value="" style="width: 120px" id="from_date">
    <i class="mx-3"></i>
    <i>تا تاریخ: </i><input type="text" value="" style="width: 120px" id="to_date">
    <hr>
    <a class="btn btn-{{($get!='&')?'outline-':''}}success" href="/Withdrawal/list">همه</a>
    <i class="mx-3"></i>
    <a class="btn btn-{{$filter=='counter'?'':'outline-'}}primary" href="?{{$get}}filter=counter">منتظر تایید
        حسابدار</a>
    <a class="btn btn-{{$filter=='manager'?'':'outline-'}}primary" href="?{{$get}}filter=manager">منتظر تایید مدیر</a>
    <a class="btn btn-{{$filter=='payment'?'':'outline-'}}primary" href="?{{$get}}filter=payment">منتظر واریز</a>
    <a class="btn btn-{{$filter=='paid'?'':'outline-'}}primary" href="?{{$get}}filter=paid">واریز شده</a>
    <a class="btn btn-{{$filter=='recipient'?'':'outline-'}}primary" href="?{{$get}}filter=recipient">منتظر دریافت</a>
    <a class="btn btn-{{$filter=='complete'?'':'outline-'}}primary" href="?{{$get}}filter=complete">تکمیل شده</a>
    <a class="btn btn-{{$filter=='tankhah'?'':'outline-'}}primary" href="?{{$get}}filter=tankhah">تنخواه</a>
    <i class="mx-3"></i>
    <a class="btn btn-{{$payMethod=='cash'?'':'outline-'}}warning" href="?{{$get}}payMethod=cash">نقدی</a>
    <a class="btn btn-{{$payMethod=='cheque'?'':'outline-'}}warning" href="?{{$get}}payMethod=cheque">چکی</a>
    <hr>
    <a class="btn btn-{{$official=='1'?'':'outline-'}}info" href="?{{$get}}official=1">رسمی</a>
    <a class="btn btn-{{$official=='0'?'':'outline-'}}info" href="?{{$get}}official=0">غیر رسمی</a>
    <i class="mx-3"></i>
    @foreach(config('withdrawalLocation') as $id => $location)
        @continue($id == 0)
        <a class="btn btn-{{$Location==$id?'':'outline-'}}secondary" href="?{{$get}}Location={{$id}}">
            {{$location}}
        </a>
    @endforeach
    <i class="mx-3"></i>
    <label for="supplier">تامین کننده</label>
    <select id="supplier" onchange="window.location.replace('?{{$get}}Supplier='+this.value)">
        <option value="">همه</option>
        @foreach($suppliers as $id => $supplier)
            <option value="{{$id}}" @selected($id == $Supplier)>{{$supplier->name}}</option>
        @endforeach
    </select>
    <hr>
    <span>تعداد: </span><b>{{$withdrawals->count()}}</b>
    <i class="mx-3"></i>
    <span>مجموع: </span><b>{{number_format($withdrawals->sum('amount'))}}</b><span>ریال</span>
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
                <td>{{$withdrawal->expense}}{{$withdrawal->tankhah?' (تنخواه) ':''}}</td>
                <td><a href="?Supplier={{$withdrawal->supplier_id}}">{{$withdrawal->account_name}}</a></td>
                <td>{!! $withdrawal->counter_status() !!}</td>
                <td>{!! $withdrawal->manager_status() !!}</td>
                <td>{!! $withdrawal->payment_status() !!}</td>
                <td>{!! $withdrawal->recipient_status() !!}</td>
                <td>
                    <span class="fa fa-eye btn btn-info" onclick="view_withdrawal({{$id}})"
                          title="مشاهده"></span>
                    @if($withdrawal->manager_confirm != 1)
                        <a class="fa fa-edit btn btn-primary" href="/Withdrawal/edit/{{$id}}" title="ویرایش"></a>
                    @endif
                    @if( $withdrawal->tankhah)
                        <a class="fa fa-edit btn btn-primary" href="/Withdrawal/tankhah/edit/{{$id}}" title="ویرایش"></a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection

@section('files')
    <script>
        let withdrawals = {!!json_encode($withdrawals)!!};

        let Current =
            `@foreach(config('expense_type')['current'] as $data)
            <option>{{$data}}</option>
            @endforeach`
        let Property =
            `@foreach(config('expense_type')['property'] as $data)
            <option>{{$data}}</option>
            @endforeach`

        $(function () {
            $('#withdrawal-table').DataTable({
                pageLength: 100,
                order: [[0, "desc"]],
            });

            const date1 = new mds.MdsPersianDateTimePicker($('#from_date')[0], {
                targetTextSelector: '#from_date',
                @if($from)
                selectedDate : new Date('{{$from}}'),
                @endif
                onDayClick: ()=> {
                    window.location.replace("?{!! $get !!}from=" + date1.getText())
                }
            });

            const date2 = new mds.MdsPersianDateTimePicker($('#to_date')[0], {
                targetTextSelector: '#to_date',
                @if($to)
                selectedDate : new Date('{{$to}}'),
                @endif
                onDayClick: ()=> {
                    window.location.replace("?{!! $get !!}to=" + date2.getText())
                }
            });
        });

        @if($User->meta('counter'))
            function counter_form(id) {
                withdrawal = withdrawals[id]
                if (withdrawal.manager_confirm == 1)
                    return;
                Dialog(`@include('withdrawal.counter')`);
                $('.checkboxradio').checkboxradio();
                $(`input[value=${withdrawal.counter_confirm}]`).click();
                $('select[name=bank_id]').val(withdrawal.bank_id).change();
                $(`input[value=${withdrawal.expense_type}]`).click();
                // $('select[name=expense_desc]').select2();
                // $('#expense_desc').select2({
                //     dropdownParent: $('.dialogs'),
                //     placeholder: "انتخاب"
                // });
                $('select[name=expense_desc]').val(withdrawal.expense_desc).change()
                $(`input[name=official][value=${withdrawal.official}]`).click();
                $(`input[name=vat][value=${withdrawal.vat}]`).click();
            }
        @endif

        @if($User->id == 122 || $User->id == 107)
        function manager_form(id) {
            withdrawal = withdrawals[id]
            if (withdrawal.payment_confirm == 1 || withdrawal.counter_confirm != 1)
                return;
            Dialog(`@include('withdrawal.manager')`);
            $('.checkboxradio').checkboxradio();
            $(`input[value=${withdrawal.manager_confirm}]`).click();
        }
        @endif

        @if($User->meta('withdrawalPay'))
        function payment_form(id) {
            withdrawal = withdrawals[id]
            if (withdrawal.manager_confirm != 1 || withdrawal.recipient_confirm == 1)
                return;
            Dialog(`@include('withdrawal.payment')`);
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

        @if($User->meta('withdrawalRecipient'))
        function recipient_form(id) {
            withdrawal = withdrawals[id]
            if (withdrawal.payment_confirm != 1)
                return;
            Dialog(`@include('withdrawal.recipient')`);
            $('.checkboxradio').checkboxradio();
            $(`input[value=${withdrawal.recipient_confirm}]`).click();
            if (withdrawal.recipient_file) {
                $('#recipient_file_old').show();
            }
        }
        @endif

    </script>
@endsection
