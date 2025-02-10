<div title=" ویرایش سریع({{$product->id}})" class="dialogs ">

    <form action="" method="post" id="fastEditForm">
        <input type="hidden" name="supplier_inf" value="{{$product->good->Supplier_inf()}}">
        @csrf
        {{--نام محصول--}}
        <div class="col-md-12 mb-1">
            <div class="form-group input-group">
                <div class="input-group-append">
                    <label for="name-dialog" class="input-group-text">نام محصول:</label>
                </div>
                <br>

            </div>
        </div>
        <input type="text" id="name-dialog" class="form-control w-100" name="name"
               value="{{$product->good->name}}" required>
        {{--قیمت محصول--}}
        <div class="col-md-12 mb-1">
            <div class="form-group input-group">
                <div class="input-group-append">
                    <label for="price-dialog" class="input-group-text">قیمت:</label>
                </div>
                <input type="text" id="price-dialog" class="form-control price-input" name="price"
                       value="{{$product->good->price}}" required>
                <div class="input-group-append">
                    <label for="price" class="input-group-text">ریال</label>
                </div>
            </div>
        </div>
        {{--قیمت تولید محصول--}}
        <div class="col-md-12 mb-1">
            <div class="form-group input-group">
                <div class="input-group-append">
                    <label for="productPrice" class="input-group-text w-100">قیمت تولید:</label>
                </div>
                <input type="text" id="productPrice" class="form-control price-input" name="productPrice"
                       value="{{$product->good->productPrice}}">
                <div class="input-group-append">
                    <label for="productPrice" class="input-group-text">ریال</label>
                </div>
            </div>
        </div>
        {{--مکان انبار--}}
        <div class="col-md-12 mb-1">
            <div class="form-group input-group">
                <div class="input-group-append">
                    <label for="warehouse-dialog" class="input-group-text">مکان انبار:</label>
                </div>
                <select name="warehouse" id="warehouse-dialog" class="form-control" disabled>
                    @foreach($warehouses as $warehouse)
                        <option value="{{$warehouse->id}}"
                                @selected($product->warehouse_id == $warehouse->id) >{{$warehouse->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- موجودی--}}
        <div class="col-md-12 mb-1">
            <div class="form-group input-group">
                <div class="input-group-append">
                    <label for="quantity-dialog" class="input-group-text w-100">موجودی :</label>
                </div>
                <input type="number" step="0.01" id="quantity-dialog" class="form-control" name="value"
                       value="{{$product->quantity}}"
                       required>
            </div>
        </div>
        {{--حد آلارم--}}
        <div class="col-md-12 mb-1">
            <div class="form-group input-group">
                <div class="input-group-append">
                    <label for="alarm-dialog" class="input-group-text w-100">حد پایین:</label>
                </div>
                <input type="number" id="alarm-dialog" class="form-control" name="alarm"
                       value="{{$product->alarm}}"
                       required>

            </div>
        </div>
        <div class="col-md-12 mb-1">
            <div class="form-group input-group">
                <div class="input-group-append">
                    <label for="high_alarm-dialog" class="input-group-text w-100">حد بالا:</label>
                </div>
                <input type="number" id="high_alarm-dialog" class="form-control" name="high_alarm"
                       value="{{$product->high_alarm}}"
                       required>
            </div>
        </div>

        {{--دسته بندی محصول--}}
        <div class="col-md-12">
            <div class="form-group input-group">
                <div class="input-group-append" style="min-width: 160px">
                    <label for="category" class="input-group-text w-100">دسته بندی:</label>
                </div>
                <select id="category" class="form-control" name="category">
                    @foreach(config('goodCat') as $cat => $desc)
                        <option value="{{$cat}}" @selected(old('category')?:$product->good->category == $cat)>{{$desc}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{--وضعیت موجودی--}}
        <br>
        <input type="radio" class="checkboxradio" id="available-dialog" name="available" value="true"
               @checked($product->available)>
        <label for="available-dialog">موجود</label>

        <input type="radio" class="checkboxradio" id="unavailable-dialog" name="available" value="false"
               @checked(!$product->available)>
        <label for="unavailable-dialog">نا موجود</label>
        <br>
        <input type="hidden" name="fast" value="true">
        <input type="submit" class="btn btn-success mt-3" value="ویرایش">
    </form>
</div>





