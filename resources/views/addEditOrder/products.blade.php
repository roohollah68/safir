<br>
<div id="products" class="my-4">
    <table class="table table-striped" id="product-table">
        <thead>
        <tr>
            <th>نام محصول</th>
            <th>قیمت(ریال)</th>
            <th>تعداد موجودی انبار</th>
            <th>افزودن</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $id => $product)
            <tr>
                <td>{{$product->good->name}}</td>
                <td>{{number_format($product->priceWithDiscount).($product->discount > 0? "($product->discount%)":"")}}</td>
                <td>{{+$product->quantity}}</td>
                <td><span class="btn btn-primary fa fa-add" onclick="addProduct({{$id}});refreshProducts();"></span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
