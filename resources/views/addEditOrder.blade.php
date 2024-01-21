@extends('layout.main')

@section('title')
    @if($edit)
        ویرایش سفارش
    @else
        افزودن سفارش جدید
    @endif
@endsection

@section('content')

        @include('addEditOrder.form')
        @include('addEditOrder.products')
        @include('addEditOrder.invoice')

@endsection

@section('files')
    @include('addEditOrder.js_css')
    <script src="/js/dom-to-image.min.js"></script>
{{--    <script src="/js/less.js"></script>--}}
@endsection

