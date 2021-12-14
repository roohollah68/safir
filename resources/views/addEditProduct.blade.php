@extends('layout.main')

@section('title')
    @if(!$product)
        افزودن محصول
    @else
        ویرایش محصول
    @endif
@endsection

@section('files')
    <script>
        $(function () {
            $('input[type="radio"]').checkboxradio();
            @if($product)
            if (!({{$product->available}}))
                $('#notavailable').click();
            @endif
        });
    </script>
    <style>
        #navailable.ui-state-active {
            background: #ff0000;
        }
    </style>
@endsection

@section('content')
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <form action="" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">

            <div class="col-md-6">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="name" class="input-group-text w-100">نام محصول:</label>
                    </div>
                    <input type="text" id="name" class="form-control" name="name"
                           @if($product)
                           value="{{$product->name}}"
                           @else
                           value="{{old('name')}}"
                           @endif required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="price" class="input-group-text w-100">قیمت:</label>
                    </div>
                    <input type="text" id="price" class="form-control" name="price"
                           @if($product)
                           value="{{$product->price}}"
                           @else
                           value="{{old('price')}}"
                           @endif pattern="^[0-9]*$" required>
                    <div class="input-group-append" style="min-width: 120px">
                        <label for="price" class="input-group-text w-100">هزار تومان</label>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div>
                    <input type="radio" id="available" name="available" value="true" checked>
                    <label for="available">موجود</label>

                    <input type="radio" id="notavailable" name="available" value="false">
                    <label id="navailable" for="notavailable">نا موجود</label>

                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group input-group ">
                    <div class="input-group-append" style="width: 160px">
                        <label for="photo" class="input-group-text w-100">تصویر محصول:</label>
                    </div>
                    <input type="file" id="photo" class="" name="photo" value="{{old('photo')}}">
                </div>
                @if($product && $product->photo)
                    <div id="product-image" >
                        <img style="max-height: 200px;max-width: 200px;" src="/product_photo/{{$product->photo}}">
                        <i class="fa fa-trash-alt btn btn-danger" onclick="delete_photo({{$product->id}})"
                           title="حذف تصویر"></i>
                        <script>
                            function delete_photo(id) {
                                $.post('/product/deletePhoto/' + id, {_token: "{{ csrf_token() }}"})
                                    .done(res => {
                                        $('#product-image').hide();
                                    })
                            }
                        </script>
                    </div>
                @endif
            </div>
        </div>
        @if($product)
            <input type="submit" class="btn btn-success" value="ویرایش">
        @else
            <input type="submit" class="btn btn-success" value="افزودن">
        @endif
        &nbsp;
        <a href="{{route('productList')}}" class="btn btn-danger">بازگشت</a>

    </form>

@endsection
