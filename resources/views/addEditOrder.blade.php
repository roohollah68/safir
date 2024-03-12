@extends('layout.main')

@section('title')
    @if($edit)
        ویرایش سفارش
    @else
        افزودن سفارش جدید
    @endif
@endsection

@section('content')

    <x-auth-validation-errors class="mb-4" id="errors" :errors="$errors"/>
    <form id="form" action="" method="post" enctype="multipart/form-data" onsubmit="return beforeSubmit();">
        @include('addEditOrder.form')
        @include('addEditOrder.products')
    </form>

    @include('addEditOrder.invoice')

@endsection

@section('files')
    @include('addEditOrder.js_css')
    <script src="/js/dom-to-image.min.js"></script>
@endsection

