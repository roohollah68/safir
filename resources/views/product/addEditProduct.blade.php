@extends('layout.main')

@section('title')
    @if($edit)
        ویرایش محصول
    @else
        افزودن محصول
    @endif
@endsection

@section('content')
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    @if($edit)
        <h4 class="">{{$good->name}}</h4>
        <h4 class="">تعداد موجود در انبار
            {{+$product->quantity}}
        </h4>

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
                           value="{{old('name')?:$good->name}}" required>
                </div>
            </div>
            {{--قیمت محصول--}}
            <div class="col-md-6">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="price" class="input-group-text w-100">قیمت:</label>
                    </div>
                    <input type="text" id="price" class="form-control price-input" name="price"
                           value="{{old('price')?:$good->price}}" required>
                    <div class="input-group-append" style="min-width: 120px">
                        <label for="price" class="input-group-text w-100">ریال</label>
                    </div>
                </div>
            </div>
            {{--قیمت تولید محصول--}}
            <div class="col-md-6">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="PPrice" class="input-group-text w-100">قیمت تولید:</label>
                    </div>
                    <input type="text" id="PPrice" class="form-control price-input" name="PPrice"
                           value="{{old('PPrice')?:$good->productPrice}}">
                    <div class="input-group-append" style="min-width: 120px">
                        <label for="PPrice" class="input-group-text w-100">ریال</label>
                    </div>
                </div>
            </div>
            @if($edit)
            {{--مکان انبار--}}
            <div class="col-md-6">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="warehouse" class="input-group-text w-100">مکان انبار:</label>
                    </div>
                    <select name="warehouse" id="warehouse" class="form-control" disabled>
                        @foreach($warehouses as $warehouse)
                            <option value="{{$warehouse->id}}"
                                    @selected($product->warehouse_id == $warehouse->id) >{{$warehouse->name}}</option>
                        @endforeach
                    </select>

                </div>
            </div>
            {{--اصلاح موجودی--}}

                <div class="col-md-6 my-2">
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

            <div class="col-md-6 my-2">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="alarm" class="input-group-text w-100">حد پایین:</label>
                    </div>
                    <input type="number" id="alarm" class="form-control" name="alarm"
                           value="{{$product->alarm?:0}}"
                           required>

                </div>

                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="high_alarm" class="input-group-text w-100">حد بالا:</label>
                    </div>
                    <input type="number" id="high_alarm" class="form-control" name="high_alarm"
                           value="{{$product->high_alarm?:100}}"
                           required>

                </div>
            </div>
            @endif

            {{--دسته بندی محصول--}}
            <div class="col-md-6 bg-light">
                <div class="form-group input-group">
                    <input type="radio" name="category" id="final" value="final" @checked($good->category == 'final')>
                    <label for="final">محصول نهایی</label>
                    <input type="radio" name="category" id="raw" value="raw" @checked($good->category == 'raw')>
                    <label for="raw">مواد اولیه</label>
                    <input type="radio" name="category" id="pack" value="pack" @checked($good->category == 'pack')>
                    <label for="pack">ملزومات بسته بندی</label>

                </div>
            </div>
            @if($edit)
            {{--وضعیت موجودی--}}
            <div class="col-md-6">
                <div>
                    <input type="radio" id="available" name="available" value="true" @checked($product->available)>
                    <label for="available">موجود</label>

                    <input type="radio" id="notavailable" name="available" value="false" @checked(!$product->available)>
                    <label id="navailable" for="notavailable">نا موجود</label>

                </div>
            </div>
            @endif
            {{--تصویر محصول--}}
            {{--
            <div class="col-md-6 d-none">
                <div class="form-group input-group ">
                    <div class="input-group-append" style="width: 160px">
                        <label for="photo" class="input-group-text w-100">تصویر محصول:</label>
                    </div>
                    <input type="file" id="photo" class="" name="photo" value="{{old('photo')}}">
                </div>
                @if($product->photo)
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
        --}}
        </div>

        @if($edit)
            <input type="submit" class="btn btn-success" value="ویرایش">
        @else
            <input type="submit" class="btn btn-success" value="افزودن">
        @endif
        &nbsp;
        <a href="{{route('productList')}}" class="btn btn-danger">بازگشت</a>

    </form>
    @if($edit)
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
            @foreach($product->productChange()->get()->keyBy('id') as $productChange)
                <tr class="{{$productChange->isDeleted?'deleted':''}}">
                    <td dir="ltr">{{verta($productChange->created_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</td>
                    <td>{{$productChange->desc}}</td>
                    <td dir="ltr">{{+$productChange->change}}</td>
                    <td dir="ltr">{{+$productChange->quantity}}</td>
                    <td>
                        @if(!$productChange->order_id && !$productChange->isDeleted)
                            <span class="btn btn-danger fa fa-trash-alt"
                                  onclick="deleteRecord({{$productChange->id}})"></span>
                        @endif
                        @if($productChange->order_id)
                            <i id="view_order_{{$productChange->order_id}}" class="fa fa-eye btn btn-info" onclick="view_order({{$productChange->order_id}})"></i>
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
            @if($edit)
            {{--if (!({{$product->available}}))--}}
            {{--    $('#notavailable').click();--}}
            {{--if (!!'{{$good->category}}')--}}
            {{--    $('#{{$good->category}}').click();--}}
            $('#table1').DataTable({
                order: [[0, "desc"]],
                pageLength: 100,
            });
            $('select#location').val('{{$product->location}}')
            @endif
        });

        function deleteRecord(id) {
            if (confirm('آیا از حذف رکورد اطمینان دارید؟')) {
                window.location.replace('/productQuantity/delete/' + id)
            }
        }

    </script>
    <style>
        #navailable.ui-state-active {
            background: #ff0000;
        }

        .deleted {
            display: none;
        }
    </style>
@endsection
