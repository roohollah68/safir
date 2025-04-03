@extends('layout.main')

@section('title')
    لیست فرمول تولید
@endsection

@section('content')
    <a class="btn btn-info" href="/formulation/add">ثبت فرمول جدید</a>
    <br>
    <br>
    <table class="table table-striped" id="formulation-table">
        <thead>
        <tr>
            <th>شماره</th>
            <th>نام کالا</th>
            <th>فرمول</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($formulations as $id => $formulation)
            <tr>
                <td>{{$id}}</td>
                <td>{{$formulation[0]->good->name}}</td>
                <td>
                    @foreach($formulation as $formule)
                    {{$formule->rawGood->name}} -> {{+$formule->amount}} <br>
                    @endforeach
                </td>
                <td>
                    <a class="fa fa-edit btn btn-primary" href="/formulation/edit/{{$id}}" title="ویرایش"></a>
                    <a class="fa fa-trash btn btn-danger" href="/formulation/deleteAll/{{$formulation[0]->good_id}}" title="حذف"></a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection

@section('files')
    <script>
        {{--let suppliers = {!! json_encode($suppliers) !!};--}}
        $(function () {
            $('#formulation-table').DataTable({
                pageLength: 100,
                order: [[2, "desc"]],
            });
        });

    </script>
@endsection

