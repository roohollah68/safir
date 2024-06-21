@extends('layout.main')

@props(['edit' => false])
@section('title')
    {{$edit?'ویرایش سفارش':'افزودن سفارش جدید'}}
@endsection

@section('content')

    <x-auth-validation-errors class="mb-4" id="errors" :errors="$errors"/>
    <form id="form" action="" method="post" enctype="multipart/form-data" onsubmit="return beforeSubmit();">
        @include('addEditOrder.form')
        @includeWhen( $creatorIsAdmin || !$edit, 'addEditOrder.products')
    </form>

@endsection

@section('files')
    @include('addEditOrder.js_css')
@endsection

