@extends('layout.main')

@section('title')
    مدیریت کاربران
@endsection

@section('files')
    <script>
        $(() => {
            $('#table').DataTable({
                // order: [[0, "desc"]],
                pageLength: 100,
                language: language,
            });
        })
    </script>
@endsection

@section('content')

    @if(auth()->user()->meta('usersEdit'))
        <a href="/user/add" class="btn btn-outline-info fa fa-plus mb-4 p-2" style="font: inherit">&nbsp;افزودن کاربر</a>
        <a href="/user/accesslist" class="btn btn-outline-primary mb-4 p-2"><i class="fa fa-pencil" aria-hidden="true"></i>&nbsp;ویرایش دسترسی کاربران</a>
    @endif
    <br>
    <h3>لیست کاربران تایید شده:</h3>
    <table class="table table-striped" id="table">
        <thead>
        <tr>
            <th>شماره</th>
            <th>نام و نام خانوادگی</th>
            <th>نام کاربری</th>
            <th>شماره تماس</th>
            <th>اعتبار(ریال)</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            @if($user->verified)
                <tr>
                    <td>{{$user->id}}</td>
                    <td>{{$user->name}}
                        @if($user->admin())
                            ( فروشنده با فاکتور )
                        @elseif($user->safir())
                            ( سفیر )
                        @endif
                    </td>
                    <td>{{$user->username}}</td>
                    <td>{{$user->phone}}</td>
                    <td dir="ltr">{{number_format($user->balance)}}</td>
                    <td>
                        <a class="btn btn-warning" href="/user/suspend/{{$user->id}}">تعلیق</a>
                        <a class="btn btn-info" href="/user/edit/{{$user->id}}">ویرایش</a>
                    </td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
    <hr>
    <h3>لیست کاربران تایید نشده:</h3>
    <table class="table mb-5">
        <thead>
        <tr>
            <th>نام و نام خانوادگی</th>
            <th>نام کاربری</th>
            <th>شماره تماس</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            @if(!$user->verified)
                <tr>
                    <td>{{$user->name}}</td>
                    <td>{{$user->username}}</td>
                    <td>{{$user->phone}}</td>
                    <td>
                        <a class="btn btn-success" href="/user/confirm/{{$user->id}}">تایید</a>
                        <a class="btn btn-danger" href="/user/delete/{{$user->id}}">حذف</a>
                    </td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>

@endsection
