@extends('layout.main')

@section('title')
    تعیین مدیر انبار
@endsection

@section('content')
    <form action="" method="post">
        @csrf
        @foreach($warehouses as $id => $warehouse)
            <label for="user-{{$id}}">
                {{$warehouse->name}}
            </label>
            <select id="user-{{$id}}" name="user-{{$id}}" class="m-2 w-25">
                <option selected></option>
                @foreach($users as $Id => $user)
                    <option value="{{$Id}}" @selected($warehouse->user_id == $Id)>{{$user->name}}</option>
                @endforeach
            </select>
            <br>
        @endforeach
        <br>
        <input class="btn btn-success" type="submit" value="ذخیره">
        <a class="btn btn-danger" href="/products">بازگشت</a>
    </form>
@endsection

@section('files')

    <script>

    </script>
@endsection
