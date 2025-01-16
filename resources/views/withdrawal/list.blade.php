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
            let dialog = Dialog(`@include('withdrawal.counter')`);
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
            let dialog = Dialog(`@include('withdrawal.manager')`);
            $('.checkboxradio').checkboxradio();
            $(`input[value=${withdrawal.manager_confirm}]`).click();
        }
        @endif

        @if(auth()->user()->meta('withdrawalPay'))
        function payment_form(id) {
            withdrawal = withdrawals[id]
            if (withdrawal.manager_confirm != 1 || withdrawal.recipient_confirm == 1)
                return;
            let dialog = Dialog(`@include('withdrawal.payment')`);
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
            let dialog = Dialog(`@include('withdrawal.recipient')`);
            $('.checkboxradio').checkboxradio();
            $(`input[value=${withdrawal.recipient_confirm}]`).click();
            if (withdrawal.recipient_file) {
                $('#recipient_file_old').show();
            }
        }
        @endif

    </script>
@endsection
