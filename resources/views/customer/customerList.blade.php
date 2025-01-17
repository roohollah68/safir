@extends('layout.main')

@section('title')
    لیست مشتریان
@endsection

@section('content')
    @if(!$safir)
        <span class="h5">مجموع بدهکاری مشتریان </span>
        <span class="h3 btn btn-danger" dir="ltr">
            {{number_format($total)}}
        </span><span class="h5">ریال</span><br><br>
        <a class="btn btn-warning m-3" href="/customerPaymentTracking">پیگیری پرداختی مشتریان</a>
        @if($viewAllAuth)
            <form method="get" action="">
                <div class="col-md-6">
                    <div class="form-group input-group required">
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="user" class="input-group-text w-100">کاربر مرتبط:</label>
                        </div>
                        <select class="form-control" name="user" id="user">
                            <option value="" selected>همه</option>
                            @foreach($users as $id=>$user)
                                <option value="{{$id}}" @selected(isset($_GET['user']) &&  $id == $_GET['user'])>
                                    {{$user->name}}
                                </option>
                            @endforeach
                        </select> <input type="submit" class="btn btn-primary" value="فیلتر">
                    </div>
                </div>
            </form>
            <a href="/customers" class="btn btn-info">همه</a>
            <a href="?trust=1" class="btn btn-success">مطمئن</a>
            <a href="?trust=0" class="btn btn-danger">نا مطمئن</a>
            <br>
        @endif

    @endif
    <a class="btn btn-info m-3 fa fa-user-plus" title="افزودن مشتری جدید" href="{{route('newCustomer')}}"></a>
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
                @if($viewAllAuth)
                    <th>کاربر مرتبط</th>
                @endif
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            @foreach($customers as $customer)
                @continue(!$customer->user)
                @isset($_GET['trust'])
                    @continue(+$_GET['trust'] ^ $customer->trust)
                @endisset
                <tr>
                    <td>{{$customer->id}}</td>
                    <td>{{$customer->name}}
                        @if($customer->block)
                            <span class="btn btn-danger">مسدود</span>
                        @endif
                    </td>
                    <td>{{$customer->phone}}</td>

                    @if(!$safir)
                        <td dir="ltr"><a href="/customer/transaction/{{$customer->id}}"
                                         class="btn btn-outline-danger">{{number_format($customer->balance)}}</a></td>
                        {{--                                         class="btn btn-outline-danger">{{number_format($customer->balance())}}</a></td>--}}
                    @else
                        <td>{{$customer->address}}</td>
                        <td>{{$customer->zip_code}}</td>
                    @endif

                    @if($viewAllAuth)
                        <th>{{$customer->user->name}}</th>
                    @endif

                    <td>
                        <a class="btn btn-primary fa fa-user-edit" title="ویرایش مشتری"
                           href="/customer/edit/{{$customer->id}}"></a>
                        @if(!$safir)
                            <a class="btn btn-info fa fa-file-invoice" title="تراکنش ها"
                               href="/customer/transaction/{{$customer->id}}"></a>
                            @if($viewAllAuth)
                                @if($customer->trust)
                                    <span class="btn btn-success fa fa-check"
                                          onclick="changeTrust({{$customer->id}} , this)"
                                          title="مورد اطمینان است."></span>
                                @else
                                    <span class="btn btn-danger fa fa-x" onclick="changeTrust({{$customer->id}} , this)"
                                          title="هنوز قابل اطمینان نیست."></span>
                                @endif
                            @endif
                        @endif
                    </td>
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
                order: [[3, "asc"]],
                pageLength: 100,
                language:language,
            });
        });

        function changeTrust(id, object) {
            $.get('/changeTrust/' + id).done((res) => {
                if (res) {
                    $(object).addClass('fa-check btn-success').removeClass('fa-x btn-danger');
                } else {
                    $(object).removeClass('fa-check btn-success').addClass('fa-x btn-danger');
                }
            }).fail(() => {
                $.notify('مشکلی پیش آمده است.')
            })
        }
    </script>
@endsection
