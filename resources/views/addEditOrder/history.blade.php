    <div class="my-2">
        <table id="history" class="table table-striped">
            @if (isset($orders) && $orders->count())
                <h5 class="mb-3 btn btn-outline-dark disabled">تعداد سفارشات: {{ $orders->count() }} </h5>
                <thead>
                    <tr>
                        <th>شماره‌ی سفارش</th>
                        <th>تاریخ سفارش</th>
                        <th>نام محصول</th>
                        <th>تعداد</th>
                        <th>مقدار تخفیف</th>
                        <th>قیمت(ریال)</th>
                        <th>قیمت فعلی(ریال)</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($orders as $order)
                        <tr class="order-header bg-light">
                            <td class="fw-bold">{{ $order->id }}</td>
                            <td>{{ verta($order->created_at)->formatJalaliDate() }}</td>
                            <td colspan="4"></td>
                            <td><span class="btn btn-primary fa fa-add"></span></td>
                        </tr>

                        @foreach ($order->orderProducts as $orderProduct)
                            @continue($orderProduct->product)
                            <tr class="product-row">
                                <td></td>
                                <td></td>
                                <td>{{ $orderProduct->product->name }}</td>
                                <td>{{ number_format($orderProduct->number) }}</td>
                                <td>{{ number_format($orderProduct->discount) }}%</td>
                                <td>{{ number_format($orderProduct->price) }}</td>
                                <td>{{ number_format($orderProduct->product->good->price) }}</td>
                            </tr>
                        @endforeach

                        <tr class="order-footer">
                            <td colspan="7" class="bg-white" style="height: 2px"></td>
                        </tr>
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
            border-bottom: 2px solid #dee2e6 !important;
        }

        .product-row td:first-child,
        .product-row td:nth-child(2) {
            background: repeating-linear-gradient(135deg,
                    rgba(0, 0, 0, 0.05),
                    rgba(0, 0, 0, 0.05) 5px,
                    transparent 5px,
                    transparent 10px);
        }

        .order-footer {
            height: 15px;
            background: #f8f9fa;
        }

        td {
            text-align: center;
        }
    </style>
