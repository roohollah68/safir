@extends('layout.main')
@section('title')
    {{$edit?'ویرایش سفارش':'افزودن سفارش جدید'}}
@endsection

@section('content')
{{--@dd($errors)--}}
    <x-auth-validation-errors class="mb-4" id="errors" :errors="$errors"/>
    <form id="form" action="{{$edit?'':'/add_order'}}" method="post" enctype="multipart/form-data" onsubmit="return beforeSubmit();">
        @include('addEditOrder.form')
        @includeWhen( $creatorIsAdmin || !$edit, 'addEditOrder.products')
    </form>

@endsection

@section('files')
    @include('addEditOrder.js_css')
@endsection

