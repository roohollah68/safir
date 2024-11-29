@extends('layout.main')

@section('title')
    ایجاد گزارش
@endsection

@section('content')

    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="" method="post">
        @csrf
        <h3>انبار {{$warehouse->name}}</h3>
        <label>گزارش کار</label>
        <textarea>{{$report->description}}</textarea>

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
