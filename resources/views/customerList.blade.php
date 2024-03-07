@extends('layout.main')

@section('title')
    لیست مشتریان
@endsection

@section('content')
    @if($superAdmin)
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

                @if(!$safir)
                    <th>دسته</th>
                    <th>بدهکاری(ریال)</th>
                @else
                    <th>آدرس</th>
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

                    @if(!$safir)
                        <td>{{$customer->categoryText($customer->category)}}</td>
                        <td dir="ltr"><a href="/customer/transaction/{{$customer->id}}"
                                         class="btn btn-outline-danger">{{number_format($customer->balance)}}</a></td>
                    @else
                        <td>{{$customer->address}}</td>
                        <td>{{$customer->zip_code}}</td>
                    @endif

                    <td>
                        <a class="btn btn-primary" href="/customer/edit/{{$customer->id}}">ویرایش</a>

                        @if(!$safir)
                            <a class="btn btn-info" href="/customer/transaction/{{$customer->id}}">تراکنش ها</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($superAdmin)
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
    @endif

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
            $('#brief-table').hide();


        });


    </script>
@endsection
