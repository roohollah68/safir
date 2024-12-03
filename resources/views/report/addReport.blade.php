@extends('layout.main')

@section('title')
    ایجاد گزارش
@endsection

@section('content')

    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="" method="post" enctype="multipart/form-data">
        @csrf
        <h3> {{$warehouse2->name}}</h3>
        <sapn>گزارش کار: </sapn><br>
        <textarea name="description" rows="10" style="width: 700px">{{$report->description}}</textarea>
        <br>
{{--        <label for="photo">الصاق تصویر:</label>--}}
{{--        <input type="file" name="photo" id="photo">--}}
{{--        @if($report->photo)--}}
{{--            <span class="btn btn-danger" onclick="">حذف تصویر</span>--}}
{{--        @endif--}}
{{--        <input type="hidden" name="oldPhoto" value="{{$report->photo}}">--}}
{{--        <img style="width: 200px; height: auto" src="/report/{{$report->photo}}">--}}
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
