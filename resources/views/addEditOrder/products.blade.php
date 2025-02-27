<style>
    .ui-tabs .ui-tabs-nav .ui-state-active a {
        background: #007bff !important;
        color: white !important;
        border-radius: 0 !important;
    }

    .ui-tabs .ui-tabs-nav .ui-state-default a {
        color: black !important;
        background: none !important;
    }

    #history th {
        text-align: center;
    }
</style>

<div id="tabs" class="mt-5">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="products-tab" data-bs-toggle="tab" href="#tabs-1" role="tab"
                aria-controls="products" aria-selected="true">
                محصولات
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="history-tab" data-bs-toggle="tab" href="#tabs-2" role="tab"
                aria-controls="history" aria-selected="false">
                تاریخچه‌ی سفارشات
            </a>
        </li>
    </ul>

    <div id="tabs-1" class="ms-2 me-2">
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
                    @foreach ($products as $id => $product)
                        <tr>
                            <td>{{ $product->good->name }}</td>
                            <td>{{ number_format($product->priceWithDiscount) . ($product->discount > 0 ? "($product->discount%)" : '') }}
                            </td>
                            <td>{{ +$product->quantity }}</td>
                            <td><span class="btn btn-primary fa fa-add"
                                    onclick="addProduct({{ $id }});refreshProducts();"></span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="tabs-2" class="ms-2 me-2 tab-pane fade">
        @include('addEditOrder.history')
    </div>
</div>

<script>
    $(function() {
        $("#tabs").tabs();
    });
</script>
