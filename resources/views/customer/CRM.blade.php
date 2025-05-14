@extends('layout.main')

@section('title')
    CRM
@endsection

@section('content')
    @if($User->meta('allCustomers'))
    <form type="get">
        <div class="row">
            <div class="col-md-4">
                <label for="user">نام کاربر:</label>
                <select class="form-control" name="user" id="user">
                    <option value="" selected>همه</option>
                    @foreach($users as $id=>$user)
                        <option value="{{$id}}" @selected(($_GET['user']??'') == $id)>
                            {{$user->name}}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <input type="submit" class="btn btn-primary" value="فیلتر">
    </form>
    @endif
    <table class="table table-striped" id="crm-table">
        <thead>
        <tr>
            <th>شماره مشتری</th>
            <th>نام مشتری</th>
            <th>شماره تماس</th>
            <th>کاربر مرتبط</th>
            <th>آخرین ارتباط</th>
            <th>آخرین خرید</th>
            <th>تاریخ پیگیری بعدی</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            @foreach($user->customers as $customer)
                @php($order = $customer->orders->last())
                @php($CRM = $customer->CRMs->last())
                @continue(!$order)
                <tr>
                    <td>{{$customer->id}}</td>
                    <td><a href="/customer/transaction/{{$customer->id}}" target="_blank">{{$customer->name}}</a></td>
                    <td><a href="tel:{{$customer->phone}}">{{$customer->phone}}</a></td>
                    <td><a href="?user={{$user->id}}">{{$user->name}}</a></td>
                    <td>{{$CRM? verta($CRM->created_at)->formatJalaliDate(): '-'}}</td>
                    <td>{{$order? verta($order->created_at)->formatJalaliDate(): '-'}}</td>
                    <td>{{ $CRM && $CRM->next_date ? verta($CRM->next_date)->formatJalaliDate() : '-' }}</td>
                    <td>
                        @if($order)
                            <span class="btn btn-info fa fa-eye"
                                  onclick="view_order({{$order->id}})"></span>
                        @endif
                        <span class="btn btn-primary fa fa-user"
                              onclick="view_CRM({{$customer->id}})"></span>
                    </td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
@endsection

@section('files')
    <script>
        $(function () {
            $('#crm-table').DataTable({
                // order: [[3, "asc"]],
                pageLength: 100,
                language: language,
            });
        });
    </script>
@endsection
