@extends('layout.main')

@section('title')
    لیست تامین کنندگان
@endsection

@section('content')
    <a class="btn btn-info" href="/Supplier/add">ثبت اطلاعات تامین کننده جدید</a>
    <br>
    <br>
    <table class="table table-striped" id="supplier-table">
        <thead>
        <tr>
            <th>شماره</th>
            <th>نام</th>
            <th>تعداد واریز</th>
            <th>مجموع دریافتی(ریال)</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($suppliers as $id => $supplier)
            <tr>
                <td>{{$id}}</td>
                <td><a href="{{route('WithdrawalList')}}?Supplier={{$id}}">{{$supplier->name}}</a></td>
                <td>{{$supplier->withdrawals->where('payment_confirm', 1)->count()}}</td>
                <td>{{number_format($supplier->withdrawals->where('payment_confirm', 1)->sum('amount'))}}</td>
                <td>
                    <a class="fa fa-edit btn btn-primary" href="/Supplier/edit/{{$id}}" title="ویرایش"></a>
                    <span class="fa fa-eye btn btn-info" onclick="view_supplier({{$id}})" title="مشاهده"></span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection

@section('files')
    <script>
        let suppliers = {!! json_encode($suppliers) !!};
        $(function () {
            $('#supplier-table').DataTable({
                pageLength: 100,
                order: [[2, "desc"]],
            });
        });
        function view_supplier(id){
            let supplier = suppliers[id];
            let text = `
            <div class="dialogs" title="اطلاعات تامین کننده">
<br><br>
    <i>نام: </i><b>${supplier.name}</b><br><br>
    <i>شماره شبا یا کارت: </i><b>${supplier.account}</b><br><br>
    <i>شماره تماس: </i><b>${supplier.phone}</b><br><br>
    <i>کد ملی یا شناسه ملی: </i><b>${supplier.code}</b><br><br>
    <i>توضیحات: </i><b>${supplier.description}</b><br><br>
</div>`;
            Dialog(text);
        }
    </script>
@endsection

