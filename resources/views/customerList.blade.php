@extends('layout.main')

@section('title')
    لیست مشتریان
@endsection

@section('content')
    <a class="btn btn-info" href="{{route('newCustomer')}}">افزودن مشتری جدید</a>
{{--    <a class="btn btn-info" href="{{route('newCustomer')}}">مشتریان دارای حساب</a>--}}
    <br>
    <br>
    <table class="stripe" id="customer-table">
        <thead>
        <tr>
            <th>شماره</th>
            <th>نام</th>
            <th>شماره تماس</th>
            <th>آدرس</th>
            @if($admin)
                <th>اعتبار(تومان)</th>
            @else
                <th>کد پستی</th>
            @endif

            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($customers as $customer)
            <tr>
                <td>{{$customer->id}}</td>
                <td>{{$customer->name}}</td>
                <td>{{$customer->phone}}</td>
                <td>{{$customer->address}}</td>
                @if($admin)
                    <td dir="ltr">{{$customer->balance}}</td>
                @else
                    <td>{{$customer->zip_code}}</td>
                @endif

                <td>
                    <a class="btn btn-primary" href="/customer/edit/{{$customer->id}}">ویرایش</a>
{{--                    <span class="fa fa-edit btn btn-primary"--}}
{{--                          onclick="window.open('/customer/edit/{{$customer->id}}','_self');"--}}
{{--                          title="ویرایش مشتری">--}}
{{--                    </span>--}}
                    @if($admin)
                        <a class="btn btn-info" href="/customer/transaction/{{$customer->id}}">تراکنش ها</a>
{{--                    <span class="fa fa-eye btn btn-primary"--}}
{{--                          onclick="window.open('/customer/transaction/{{$customer->id}}','_self');"--}}
{{--                          title="تراکنش های مشتری">--}}
{{--                    </span>--}}
                    @endif
                    <a class="btn btn-danger" onclick="delete_customer({{$customer->id}})">حذف</a>
{{--                    <span class="fa fa-trash-alt btn btn-danger" onclick="delete_customer({{$customer->id}})"--}}
{{--                          title="حذف مشتری"></span>--}}
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
