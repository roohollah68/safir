@extends('layout.main')

@section('title')
    لیست مشتریان
@endsection

@section('content')
    <a class="btn btn-info" href="{{route('newCustomer')}}">افزودن مشتری جدید</a>
    <br>
    <br>
    <table class="stripe" id="customer-table">
        <thead>
        <tr>
            <th>نام</th>
            <th>شماره تماس</th>
            <th>آدرس</th>
            <th>کد پستی</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($customers as $customer)
            <tr>

                <td>{{$customer->name}}</td>
                <td>{{$customer->phone}}</td>
                <td>{{$customer->address}}</td>
                <td>{{$customer->zip_code}}</td>
                <td>
                    <a class="fa fa-edit btn btn-primary" href="/customer/edit/{{$customer->id}}"
                       title="ویرایش مشتری"></a>
                    <i class="fa fa-trash-alt btn btn-danger" onclick="delete_customer({{$customer->id}})"
                       title="حذف مشتری"></i>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection


@section('files')
    @csrf
    <script>
        $(function () {
            $('#customer-table').DataTable();
        });

        function delete_customer(id) {
            confirm("برای همیشه حذف شود؟") ?
                $.post('/customer/delete/' + id, {_token: "{{ csrf_token() }}"})
                    .done(res => {
                        location.reload();
                    })
                :
                ""
        }


    </script>
@endsection
