@extends('layout.main')

@section('title')
    @if(!$supplier->id)
        افزودن تامین کننده
    @else
        ویرایش اطلاعات تامین کننده
    @endif
@endsection

@section('files')


@endsection

@section('content')

    <h1>افزودن یا ویرایش اطلاعات تامین کننده</h1>
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="/Supplier/add" method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{$supplier->id}}">
        <div class="row my-4">

            {{--            نام--}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="name" class="input-group-text w-100">نام:</label>
                    </div>
                    <input type="text" class="form-control" name="name" id="name"
                           value="{{old('name')?:$supplier->name}}" required>
                </div>
            </div>

            {{--            شماره شبا یا کارت--}}
            <div class="col-md-6 my-2 ">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="account" class="input-group-text w-100">شماره شبا یا کارت:</label>
                    </div>
                    <input type="text" class="form-control" name="account" id="account"
                           value="{{old('account')?:$supplier->account}}">
                </div>
            </div>

            {{--            شماره تماس--}}
            <div class="col-md-6 my-2 ">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="phone" class="input-group-text w-100">شماره تماس:</label>
                    </div>
                    <input type="text" class="form-control " name="phone" id="phone"
                           value="{{old('phone')?:$supplier->phone}}" >
                </div>
            </div>

            {{--            کد ملی یا شناسه ملی--}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group ">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="code" class="input-group-text w-100">کد ملی یا شناسه ملی:</label>
                    </div>
                    <input type="text" class="form-control" name="code" id="code"
                           value="{{old('code')?:$supplier->code}}" >
                </div>
            </div>

            {{--            توضیحات--}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="description" class="input-group-text w-100">توضیحات:</label>
                    </div>
                    <textarea class="form-control" name="description" id="description">{{old('description')?:$supplier->description}}</textarea>
                </div>
            </div>
        </div>

        @if($supplier->id)
            <input type="submit" class="btn btn-success" value="ویرایش">
        @else
            <input type="submit" class="btn btn-success" value="افزودن">
        @endif
        &nbsp;
        <a href="/Supplier/list" class="btn btn-danger">بازگشت</a>

    </form>

@endsection
