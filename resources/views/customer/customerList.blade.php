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
        <span class="h5">ریال</span><br><br>
        <form method="get" action="">
            <div class="col-md-6">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="user" class="input-group-text w-100">کاربر مرتبط:</label>
                    </div>
                    <select class="form-control" name="user" id="user">
                        <option value="all"
                                selected
                        >همه
                        </option>
                        @foreach($users as $user)
                            <option
                                value="{{$user->id}}" @selected( isset($_GET['user']) && $user->id == $_GET['user'])>
                                {{$user->name}}</option>
                        @endforeach
                    </select> <input type="submit" class="btn btn-primary" value="فیلتر">
                </div>
            </div>

        </form>
    @endif
    <a class="btn btn-info" href="{{route('newCustomer')}}">افزودن مشتری جدید</a>
    <br>
    <br>
    <div id="table-container">
        <table class="table table-striped" id="customer-table">
            <thead>
            <tr>
                <th>شماره</th>
                <th>نام</th>
                <th>شماره تماس</th>

                @if(!$safir)
                    <th>بدهکاری(ریال)</th>
                @else
                    <th>آدرس</th>
                    <th>کد پستی</th>
                @endif
                @if($superAdmin)
                    <th>کاربر مرتبط</th>
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
                        <td dir="ltr"><a href="/customer/transaction/{{$customer->id}}"
                                         class="btn btn-outline-danger">{{number_format($customer->balance)}}</a></td>
                    @else
                        <td>{{$customer->address}}</td>
                        <td>{{$customer->zip_code}}</td>
                    @endif

                    @if($superAdmin)
                        <th>{{$customer->user->name}}</th>
                    @endif

                    <td>
                        <a class="btn btn-primary" href="/customer/edit/{{$customer->id}}">ویرایش</a>

                        @if(!$safir)
                            <a class="btn btn-info" href="/customer/transaction/{{$customer->id}}">تراکنش ها</a>
{{--                            <a class="btn btn-secondary fa fa-file-pdf" title="گردش حساب" href="/customer/SOA/{{$customer->id}}"></a>--}}
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
                order: [[3, "asc"]],
                pageLength: 100,
            });
            $('#brief-table table').DataTable({
                order: [[2, "asc"]],
                pageLength: 50,
            });
            $('#brief-table').hide();
        });
        // function customerSOA(id){
        //     const doc = new jsPDF();
        //
        //     doc.text("Hello world!", 10, 10);
        //     doc.save("a4.pdf");
        // }

    </script>
@endsection
