@extends('layout.main')

@section('title')
    مدیریت کاربران
@endsection

@section('files')
    <script src="js/manage-users.js"></script>
@endsection

@section('content')
    @csrf
    @if(auth()->user()->meta('usersEdit'))
        <a href="/add_user" class="btn btn-outline-info fa fa-plus mb-4">افزودن کاربر</a>
    @endif
    <br>
    <h3>لیست کاربران تایید شده:</h3>
    <table class="table table-striped">
        <thead>
        <tr>
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
                    <td>{{$user->name}}
                        @if($user->superAdmin())
                            ( سوپر ادمین )
                        @elseif($user->admin())
                            ( فروشنده با فاکتور )
                        @elseif($user->safir())
                            ( سفیر )
                        @elseif($user->print())
                            ( ارسال سفارش )
                        @elseif($user->warehouse())
                            ( انباردار )
                        @elseif($user->counter())
                            ( حسابدار )
                        @elseif($user->account())
                            ( صاحب حساب بانکی )
                        @endif
                    </td>
                    <td>{{$user->username}}</td>
                    <td>{{$user->phone}}</td>
                    <td dir="ltr">{{number_format($user->balance)}}</td>
                    <td>
                        <a class="btn btn-warning" href="/suspend_user/{{$user->id}}">تعلیق</a>
                        <a class="btn btn-info" href="edit_user/{{$user->id}}">ویرایش</a>
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
            @unless($user->verified)
                <tr>
                    <td>{{$user->name}}</td>
                    <td>{{$user->username}}</td>
                    <td>{{$user->phone}}</td>
                    <td>
                        <a class="btn btn-success" href="/confirm_user/{{$user->id}}">تایید</a>
                        <a class="btn btn-danger" href="/delete_user/{{$user->id}}">حذف</a>
                    </td>
                </tr>
            @endunless
        @endforeach
        </tbody>
    </table>

@endsection
