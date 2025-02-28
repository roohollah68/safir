    <div class="my-2">
        <table id="history" class="table table-striped">
            @if (isset($orders) && $orders->count())
                <h5 class="mb-3 btn btn-outline-dark disabled">تعداد سفارشات: {{ $orders->count() }} </h5>
                <thead>
                    <tr>
                        <th>نام محصول</th>
                        <th>تعداد</th>
                        <th>مقدار تخفیف</th>
                        <th>قیمت(ریال)</th>
                        <th>قیمت فعلی(ریال)</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($orders as $order)
                        <tr class="order-header bg-secondary p-2 bg-opacity-50 fw-bold">
                            <td colspan="7">
                                <span class="me-5">شماره‌ی سفارش: {{ $order->id }}</span>
                                <span class="ms-5">تاریخ سفارش:
                                    {{ verta($order->created_at)->formatJalaliDate() }}</span>
                            </td>
                        </tr>

                        @foreach ($order->orderProducts as $orderProduct)
                            @continue(!isset($orderProduct->product))
                            <tr class="product-row">
                                <td>{{ $orderProduct->name }}</td>
                                <td>{{ number_format($orderProduct->number) }}</td>
                                <td>{{ number_format($orderProduct->discount) }}%</td>
                                <td>{{ number_format($orderProduct->price) }}</td>
                                <td>{{ number_format($orderProduct->product->good->price) }}</td>
                                <td>
                                    <span class="btn btn-primary fa fa-add"
                                        onclick="addProduct({{ $orderProduct->product_id }});refreshProducts();"></span>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach

                </tbody>
            @else
                <tr>
                    <td colspan="7" class="text-center">مشتری انتخاب نشده است.</td>
                </tr>
            @endif
        </table>
    </div>


    <style>
        .order-header td {
            color: white !important;
        }

        td {
            text-align: center;
        }
    </style>
