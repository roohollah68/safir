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
    <form action="/product/addOrEdit{{$edit ? '/'.$product->id : ''}}" method="post" enctype="multipart/form-data">
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
                        <label for="productPrice" class="input-group-text w-100">قیمت تولید:</label>
                    </div>
                    <input type="text" id="productPrice" class="form-control price-input" name="productPrice"
                           value="{{old('productPrice')?:$good->productPrice}}">
                    <div class="input-group-append" style="min-width: 120px">
                        <label for="productPrice" class="input-group-text w-100">ریال</label>
                    </div>
                </div>
            </div>

            {{--اینتا کد--}}
            <div class="col-md-6">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="isic" class="input-group-text w-100">اینتا کد:</label>
                    </div>
                    <select id="isic" class="form-control" name="isic" value="{{old('isic')?:$good->isic}}">
                        <option value="1020250" @selected(old('isic')?:$good->isic==1020250)>قهوه، کاکائو، پودر و خمیر حاصل از آنها</option>
                        <option value="1010020" @selected(old('isic')?:$good->isic==1010020)>انواع چای(خشک کردن، سورت و بسته بندی)</option>
                        <option value="1010030" @selected(old('isic')?:$good->isic==1010030)>انواع گیاهان طبی و دارویی</option>
                    </select>

                </div>
            </div>

            {{--شناسه کالا--}}
            <div class="col-md-6">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="tag" class="input-group-text w-100">شناسه کالا:</label>
                    </div>
                    <input type="text" id="tag" class="form-control" name="tag"
                           value="{{old('tag')?:$good->tag}}"
                           onkeypress="return event.charCode >= 48 && event.charCode <= 57" pattern="^[0-9]*$">
                </div>
            </div>

            {{-- ارزش افزوده(10%)--}}
            <div class="col-md-6 my-2 VAT">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label class="input-group-text ">ارزش افزوده(10%):</label>
                    </div>
                    <label for="vat" class="">دارد</label>
                    <input type="radio" class="checkboxradio" name="vat" id="vat"
                           value="1" @checked((old('vat')?:$good->vat)==1)>
                    <label for="no-vat" class="">ندارد</label>
                    <input type="radio" class="checkboxradio" name="vat" id="no-vat"
                           value="0" @checked((old('vat')?:$good->vat)!=1)>
                </div>
            </div>

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

            {{--اطلاعات تامین کننده--}}
            <div class="col-md-6 bg-light">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="supplier_inf">اطلاعات تامین کننده:</label>
                    </div>
                    <textarea name="supplier_inf" class="form-control"
                              id="supplier_inf">{{old('supplier_inf')?:$good->Supplier_inf()}}</textarea>
                </div>
            </div>

            @if($edit)
                <input type="hidden" name="productId" value="{{$product->id}}">
                {{--مکان انبار--}}
                <div class="col-md-6">
                    <div class="form-group input-group">
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="warehouse" class="input-group-text w-100">مکان انبار:</label>
                        </div>
                        <select name="warehouse" id="warehouse" class="form-control" disabled>
                            @foreach($warehouses as $id => $warehouse)
                                <option
                                    value="{{$id}}" @selected($product->warehouse_id == $id)>{{$warehouse->name}}</option>
                            @endforeach
                        </select>

                    </div>
                </div>

                {{--اصلاح موجودی--}}
                <div class="col-md-6 my-2">
                    <div class="form-group input-group">
                        <div class="input-group-text">
                            <input type="radio" name="changeType" checked
                                   onclick="$('#value').prop('readonly', true).val({{+$product->quantity}});$('#add').prop('readonly', false);">
                        </div>
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="add" class="input-group-text w-100">افزودن به موجودی :</label>
                        </div>
                        <input type="number" step="0.01" id="add" class="form-control" name="add" value="0">
                    </div>
                    <div class="form-group input-group">
                        <div class="input-group-text">
                            <input type="radio" name="changeType"
                                   onclick="$('#add').prop('readonly', true).val(0);$('#value').prop('readonly', false);">
                        </div>
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="value" class="input-group-text w-100">اصلاح موجودی :</label>
                        </div>
                        <input type="number" step="0.01" id="value" class="form-control" name="value"
                               value="{{+$product->quantity}}"
                               readonly>
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

                {{--وضعیت موجودی--}}
                <div class="col-md-6">
                    <div>
                        <input type="radio" id="available" name="available" value="true" @checked($product->available)>
                        <label for="available">موجود</label>

                        <input type="radio" id="notavailable" name="available"
                               value="false" @checked(!$product->available)>
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
            @foreach($product->productChange->keyBy('id') as $productChange)
                <tr class="{{$productChange->isDeleted?'deleted':''}}">
                    <td dir="ltr">{{verta($productChange->created_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</td>
                    <td>
                        @if($productChange->order_id)
                            @if($productChange->change>0)
                                حذف رکورد :
                            @endif
                            خرید مشتری {{$productChange->order()->withTrashed()->first()->name}}
                        @else
                            {{$productChange->desc}}
                        @endif
                    </td>
                    <td dir="ltr">{{+$productChange->change}}</td>
                    <td dir="ltr">{{+$productChange->quantity}}</td>
                    <td>
                        @if(!$productChange->order_id && !$productChange->isDeleted)
                            <span class="btn btn-danger fa fa-trash-alt"
                                  onclick="deleteRecord({{$productChange->id}})"></span>
                        @endif
                        @if($productChange->order_id)
                            <i id="view_order_{{$productChange->order_id}}" class="fa fa-eye btn btn-info"
                               onclick="view_order({{$productChange->order_id}})"></i>
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
            $('.checkboxradio').checkboxradio();
            $('input[name="available"], input[name="category"]').checkboxradio();
            @if($edit)
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
