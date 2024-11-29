@extends('layout.main')

@section('title')
    ایجاد گزارش
@endsection

@section('content')

    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="" method="post">
        @csrf
        <h3>انبار {{$warehouse2->name}}</h3>
        <sapn>گزارش کار: </sapn><br>
        <textarea name="description" rows="10" style="width: 700px">{{$report->description}}</textarea>
        <br>
        <br>
        <input type="submit" class="btn btn-success" value="افزودن">
        <a href="{{route('reportList')}}" class="btn btn-danger">بازگشت</a>

    </form>

@endsection

@section('files')
    <script>

    </script>
    <style>

    </style>
@endsection
