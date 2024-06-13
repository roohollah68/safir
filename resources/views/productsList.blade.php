<html dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="/css/bootstrap.min.css">

</head>

<body>
    <table class="stripe" id="product-table">
        <thead>
        <tr>
            <th>شماره</th>
            <th>نام</th>
            <th>قیمت(ریال)</th>
            <th>موجودی</th>
            <th>حدآلارم</th>
            <th>وضعیت</th>

        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)
            <tr>
                <td>{{$product->id}}</td>
                <td><a class="btn" >{{$product->name}}</a></td>
                <td>{{number_format($product->price)}}</td>
                <td>{{+$product->quantity}}</td>
                <td>{{$product->alarm}}</td>
                <td>
                    @if($product->available)
                        <p class="btn btn-success">موجود</p>
                    @else
                        <p class="btn btn-danger">ناموجود</p>
                    @endif
                </td>

            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
