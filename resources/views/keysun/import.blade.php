@extends('layout.main')

@section('title')
    ورود اکسل
@endsection

@section('content')

    <form action="/keysun/import-excel" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="excel_file" accept=".xlsx,.xls">
        <button type="submit">آپلود و پردازش</button>
    </form>

@endsection

@section('files')


@endsection
