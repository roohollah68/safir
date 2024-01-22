@extends('layout.main')

@section('title')
    لیست مشتریان
@endsection

@section('content')
    @if($admin)
        <span class="h5">مجموع بدهکاری مشتریان </span>
        <span class="h3 btn btn-danger" dir="ltr" onclick="$('#table-container ,#brief-table ').toggle(); ">
            {{number_format($total)}}
        </span>
        <span
            class="h5">ریال</span><br><br>
    @endif
    <a class="btn btn-info" href="{{route('newCustomer')}}">افزودن مشتری جدید</a>
    <br>
    <br>
    <div id="table-container">
        <table class="stripe" id="customer-table">
            <thead>
            <tr>
                <th>شماره</th>
                <th>نام</th>
                <th>شماره تماس</th>
                <th>آدرس</th>
                @if($admin)
                    <th>بدهکاری(ریال)</th>
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
                        <td dir="ltr"><a href="/customer/transaction/{{$customer->id}}"
                                         class="btn btn-outline-danger">{{number_format($customer->balance)}}</a></td>
                    @else
                        <td>{{$customer->zip_code}}</td>
                    @endif

                    <td>
                        <a class="btn btn-primary" href="/customer/edit/{{$customer->id}}">ویرایش</a>

                        @if($admin)
                            <a class="btn btn-info" href="/customer/transaction/{{$customer->id}}">تراکنش ها</a>
                        @endif
                        @if($customer->balance == 0)
                            <a class="btn btn-danger" onclick="delete_customer({{$customer->id}})">حذف</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div id="brief-table">
        <table class="stripe">
            <thead>
            <tr>
                <th>شماره مشتری</th>
                <th>نام</th>
                <th>بدهی(ریال)</th>
            </tr>
            </thead>
            <tbody>
            @foreach($customers as $customer)
                @if($customer->balance == 0)
                    @continue
                @endif
                <tr>
                    <td>{{$customer->id}}</td>
                    <td>{{$customer->name}}</td>
                    <td dir="ltr"><a href="/customer/transaction/{{$customer->id}}"
                                     class="btn btn-outline-danger">{{number_format($customer->balance)}}</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endsection


@section('files')
    @csrf
    <script>
        $(function () {
            $('#customer-table').DataTable({
                order: [[4, "asc"]],
                pageLength: 100,
            });
            $('#brief-table table').DataTable({
                order: [[2, "asc"]],
                pageLength: 100,
            });
            // setTimeout(function (){
            //     $('#brief-table').hide()
            // },200)

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
