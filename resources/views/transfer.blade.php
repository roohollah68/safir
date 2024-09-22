<div title=" انتقال محصول بین انبارها" class="dialogs ">
    <span>نام محصول:</span> <b>{{$product->name}}</b><br>
    <h3>مبدا:</h3>
    <span>مکان انبار:</span> <b>{{$warehouse2->name}}</b><br>
    <span>تعداد موجود:</span> <b dir="ltr">{{$product->quantity}}</b><br>

    <h3>مقصد:</h3>

    <form method="post" id="transferForm">
        @csrf

        {{--مکان انبار--}}
        <div class="col-md-12 mb-1">
            <div class="form-group input-group">
                <div class="input-group-append">
                    <label for="warehouse-dialog" class="input-group-text">مکان انبار:</label>
                </div>
                <select name="productId" id="warehouse-dialog" class="form-control">
                    @foreach($products as $product)
                        <option value="{{$product->id}}">{{$product->warehouse->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- موجودی--}}
        <div class="col-md-12 mb-1">
            <div class="form-group input-group">
                <div class="input-group-append">
                    <label for="quantity-dialog" class="input-group-text w-100">مقدار جابجایی:</label>
                </div>
                <input type="number" step="0.01" id="quantity-dialog" class="form-control" name="value"
                       value="0"
                       required>
            </div>
        </div>

        <input type="submit" class="btn btn-success mt-3" value="جابجایی">
    </form>
</div>





