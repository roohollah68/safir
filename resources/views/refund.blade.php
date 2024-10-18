@extends('layout.main')

@section('title')
    صدور فاکتور برگشت به انبار
@endsection

@section('content')
    <div class="w-100 m-2 p-2 bg-info rounded">
        <span>نام مشتری:</span> <b>{{$customer->name}}</b><br>
        <span>نام کاربر مرتبط:</span> <b>{{$customer->user->name}}</b><br>
        <span>شماره مشتری:</span> <b>{{$customer->id}}</b><br>
        <span>شماره تماس:</span> <b>{{$customer->phone}}</b><br>
        <span>شهر:</span> <b>{{$customer->city->name}}</b><br>
        <span>آدرس:</span> <b>{{$customer->address}}</b><br>
        <span>کد پستی:</span> <b>{{$customer->zip_code}}</b><br>
        <span class="h3">بدهکاری:</span>
        <b dir="ltr" class="h3 text-danger">{{number_format($customer->balance)}}</b>
        <span class="h3">ریال</span><br>
    </div><br>
    <form method="post" action="">
        @csrf
        <div id="hidden-input"></div>
        {{--مکان انبار--}}
        <div class="col-md-6 mb-1">
            <div class="form-group input-group">
                <div class="input-group-append">
                    <label for="warehouse-dialog" class="input-group-text">بازگشت به انبار:</label>
                </div>
                <select name="warehouseId" id="warehouse-dialog" class="form-control">
                    @foreach($warehouses as $warehouse)
                        <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <table class="table-striped table hide" id="selected-good-table">
            <thead>
            <tr>
                <th>نام محصول</th>
                <th>تعداد</th>
                <th>قیمت(ریال)</th>
                <th>قیمت روز</th>
            </tr>
            </thead>
            <tbody id="selected-good-list">

            </tbody>
            <tr>
                <th colspan="2">مجموع مبلغ بازگشتی</th>
                <th id="total" colspan="2"></th>
            </tr>
        </table>

        <input class="btn btn-success" type="submit" value="ثبت">
        <a class="btn btn-danger" href="/customer/transaction/{{$customer->id}}">بازگشت</a>
    </form>

    <br>
    <div id="tabs">
        <ul>
            <li><a href="#products">محصولات</a></li>
            <li><a href="#orders">سفارشات قبلی</a></li>
        </ul>
        <div id="products">
            <table id="goods" class="table table-striped">
                <thead>
                <tr>
                    <th>نام</th>
                    <th>قیمت (ریال)</th>
                    <th>افزودن</th>
                </tr>
                </thead>
                <tbody>
                @foreach($goods as $id => $good)
                    <tr>
                        <td>{{$good->name}}</td>
                        <td>{{number_format($good->price)}}</td>
                        <td><span class="btn btn-info fa fa-plus" onclick="addGood({{$id}})"></span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div id="orders">
            <table class="table table-striped" id="orders-table">
                <thead>
                <tr>
                    <th>نام</th>
                    <th>قیمت تمام شده(ریال)</th>
                    <th>تعداد</th>
                    <th>تخفیف %</th>
                    <th>افزودن</th>
                </tr>
                </thead>

                @foreach($orders as $id => $order)
                    <tr>
                        <th onclick="$('.order-{{$id}}').toggle()" class="btn btn-info">
                            <span class="fa fa-arrow-down order-{{$id}}"></span>
                            <span class="fa fa-arrow-left hide order-{{$id}}"></span>
                             سفارش  {{$order->id}}
                        </th>
                        <th>*****</th>
                        <th>*****</th>
                        <th>*****</th>
                        <th><a class="btn btn-info fa fa-eye"
                               onclick="view_order({{$order->id}})"
                               title="مشاهده سفارش"></a></th>
                    </tr>

                    @foreach($order->orderProducts as $orderProduct)
                        <tr class="order-{{$id}}">
                            <td>{{$orderProduct->name}}</td>
                            <td>{{number_format($orderProduct->price)}}</td>
                            <td>{{+$orderProduct->number}}</td>
                            <td>{{+$orderProduct->discount}}</td>
                            <td><span class="btn btn-info fa fa-plus" onclick="addGood({{$orderProduct->product->good_id}},{{$orderProduct->price}})"></span></td>
                        </tr>
                    @endforeach

                @endforeach

            </table>
        </div>


    </div>
@endsection

@section('files')

    <script>
        let goods = {!! $goods !!};
        let token = "{{ csrf_token() }}";
        let cart = [];
        $(function () {
            $('#tabs').tabs();
            $('#goods').dataTable()
            $('#orders-table').dataTable({
                ordering: false,
                paging: false,
            })
        })

        function addGood(id,price=null) {
            let good = goods[id];
            if (cart[id]) {
                if ($('#good-' + id)[0])
                    return;
            } else
                cart[id] = 1;
            $('#selected-good-table').show();
            let text = `<tr id="good-${id}">
        <td>${good.name}</td>
        <td>
            <span class="btn btn-primary fa fa-plus" onclick="num_plus(${id})"></span>
            <input name="good_${id}" id="good_${id}"
            onchange="num_product(${id},this.value)" type="number" value="1"
            style="width: 50px" min="0" step="1">
            <span class="btn btn-primary fa fa-minus" onclick="num_minus(${id})"></span>
        </td>
        <td>
            <input type="text" class="price-input" style="width: 80px;"
            name="price_${id}" id="price_${id}" value="${price||good.price}"
            onchange="priceChange(${id},this.value)" ></td>
        </td>
        <td>${priceFormat(good.price)}</td>
        </tr>`
            $('#selected-good-list').append(text)
            priceInput();
            refreshList();
        }

        function num_plus(id) {
            $('#good_' + id).val((index, value) => {
                return +value + 1
            }).change();
        }

        function num_minus(id) {
            $('#good_' + id).val((index, value) => {
                return +value - 1
            }).change();
        }

        function num_product(id, value) {
            value = Math.max(0, +value);
            value = Math.round(value);
            $('#product_' + id).val(value);
            cart[id] = value;
            if (+value <= 0) {
                delete cart[id];
                $('#good-' + id).remove();
                if (!Object.keys(cart).length) {
                    $('#selected-good-table').hide();
                }
            }
            refreshList();
        }

        function priceChange(id, value) {
            value = Math.max(0, +(value.replaceAll(',', '')));
            value = Math.round(value);
            $('#price_' + id).val(value);
            priceInput();
            refreshList();
        }

        function refreshList() {
            let total = 0;
            let hasProduct = false;
            $('#hidden-input').html('');
            $.each(cart, (id, number) => {
                if (!number)
                    return;
                let price = +$('#price_' + id).val().replaceAll(',', '');
                total += price * number;
                $('#hidden-input').append(`<input type="hidden" name="cart[${id}]" value="${number}">`);
                hasProduct = true;
            })
            $('#total').html(priceFormat(total));
        }

    </script>

@endsection
