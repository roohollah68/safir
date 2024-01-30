@extends('layout.main')

@section('title')
    @if(!$product)
        افزودن محصول
    @else
        ویرایش محصول
    @endif
@endsection

@section('content')
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    @if($product)
        <h4 class="">{{$product->name}}</h4>
        <h4 class="">تعداد موجود در انبار: {{$product->quantity}}</h4>
        <a class="btn btn-danger" href="{{route('productList')}}">بازگشت</a>
        <hr>
    @endif
    <form action="" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            {{--نام محصول--}}
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
            {{--قیمت محصول--}}
            <div class="col-md-6">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="price" class="input-group-text w-100">قیمت:</label>
                    </div>
                    <input type="text" id="price" class="form-control price-input" name="price"
                           @if($product)
                           value="{{$product->price}}"
                           @else
                           value="{{old('price')}}"
                           @endif required>
                    <div class="input-group-append" style="min-width: 120px">
                        <label for="price" class="input-group-text w-100">ریال</label>
                    </div>
                </div>
            </div>
            {{--اصلاح موجودی--}}
            @if($product)
                <div class="col-md-6">
                    <div class="form-group input-group">
                        <div class="input-group-text">
                            <input type="radio" name="addType" value="add"
                                   aria-label="Radio button for following text input" checked
                                   onclick="$('#value').prop('disabled', true);$('#add').prop('disabled', false);">
                        </div>
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="add" class="input-group-text w-100">افزودن به موجودی :</label>
                        </div>
                        <input type="number" step="0.01" id="add" class="form-control" name="add" value="">
                    </div>
                    <div class="form-group input-group">
                        <div class="input-group-text">
                            <input type="radio" name="addType" value="value"
                                   aria-label="Radio button for following text input"
                                   onclick="$('#add').prop('disabled', true);$('#value').prop('disabled', false);">
                        </div>
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="value" class="input-group-text w-100">اصلاح موجودی :</label>
                        </div>
                        <input type="number" step="0.01" id="value" class="form-control" name="value"
                               value="{{+$product->quantity}}"
                               disabled>
                    </div>
                </div>
            @else
                <div class="col-md-6">
                    <div class="form-group input-group">
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="quantity" class="input-group-text w-100">موجودی :</label>
                        </div>
                        <input type="number" step="0.01" id="quantity" class="form-control" name="quantity" value="0"
                               required>
                    </div>
                </div>
            @endif
            {{--حد آلارم--}}
            <div class="col-md-6">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="alarm" class="input-group-text w-100">حد آلارم:</label>
                    </div>
                    <input type="number" id="alarm" class="form-control" name="alarm"
                           value="{{$product?$product->alarm:'10'}}"
                           required>

                </div>
            </div>
            {{--دسته بندی محصول--}}
            <div class="col-md-6 bg-light">
                <div class="form-group input-group">
                    <input type="radio" name="category" id="final" value="final">
                    <label for="final">محصول نهایی</label>
                    <input type="radio" name="category" id="raw" value="raw">
                    <label for="raw">مواد اولیه</label>
                    <input type="radio" name="category" id="pack" value="pack">
                    <label for="pack">ملزومات بسته بندی</label>

                </div>
            </div>
            {{--وضعیت موجودی--}}
            <div class="col-md-6">
                <div>
                    <input type="radio" id="available" name="available" value="true" checked>
                    <label for="available">موجود</label>

                    <input type="radio" id="notavailable" name="available" value="false">
                    <label id="navailable" for="notavailable">نا موجود</label>

                </div>
            </div>
            {{--تصویر محصول--}}
            <div class="col-md-6 d-none">
                <div class="form-group input-group ">
                    <div class="input-group-append" style="width: 160px">
                        <label for="photo" class="input-group-text w-100">تصویر محصول:</label>
                    </div>
                    <input type="file" id="photo" class="" name="photo" value="{{old('photo')}}">
                </div>
                @if($product && $product->photo)
                    <div id="product-image">
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
    @if($product)
        <hr>
        <span class="btn btn-warning m-2" onclick="$('.deleted').toggle()"><span class="deleted fa fa-check"></span>نمایش حذف شده ها</span>
        <table id="table1" class="stripe">
            <thead>
            <tr>
                <th>تاریخ</th>
                <th>توضیح</th>
                <th>میزان تغییر</th>
                <th>موجودی جدید</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            @foreach($productChanges as $productChange)
                <tr class="{{$productChange->isDeleted?'deleted':''}}">
                    <td>{{$productChange->created_at}}</td>
                    <td>{{$productChange->desc}}</td>
                    <td>{{$productChange->change}}</td>
                    <td>{{$productChange->quantity}}</td>
                    <td>
                        @if(!$productChange->order_id && !$productChange->isDeleted)
                            <span class="btn btn-danger fa fa-trash-alt"
                                  onclick="deleteRecord({{$productChange->id}})"></span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
@endsection

@section('files')
    <script>
        $(function () {
            $('input[name="available"], input[name="category"]').checkboxradio();
            @if($product)
            if (!({{$product->available}}))
                $('#notavailable').click();
            if (!!'{{$product->category}}')
                $('#{{$product->category}}').click();
            $('#table1').DataTable({
                order: [[0, "desc"]],
                pageLength: 100,
            });

            function deleteRecord(id) {
                if (confirm('آیا از حذف رکورد اطمینان دارید؟')) {
                    // $.get('/productQuantity/delete/'+id);
                    window.location.replace('/productQuantity/delete/' + id)
                }
            }
            @endif
        });
    </script>
    <style>
        #navailable.ui-state-active {
            background: #ff0000;
        }
    </style>
@endsection
