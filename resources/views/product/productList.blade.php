@extends('layout.main')

@section('title')
    محصولات
@endsection

@section('content')

    <span>مکان انبار: </span>
    <div id="warehouses">
        @foreach($warehouses as $warehouse)
            <a class="btn btn{{($warehouse->id == auth()->user()->meta('warehouseId'))?'':'-outline'}}-info"
               onclick="warehouseId={{$warehouse->id}};changeWarehouse(this)">{{$warehouse->name}}</a>
        @endforeach

    </div>
    <br>
    <a class="btn btn-info mb-3" href="{{route('addProduct')}}">
        <span class=" fa fa-plus"></span>
        افزودن محصول جدید
    </a>
    <a class="btn btn-primary mb-3" href="warehouse/transfer">
        <span class=" fa fa-warehouse"></span>
        انتقال بین انبارها
    </a>
    <a class="btn btn-secondary mb-3" href="goods/management">
        <span class=" fa fa-list"></span>
        مدیریت کالاها
    </a>
    <br>
    <div class="container border input-box">
        <div class="row">
            <div class="col-md-4 border">
                <input type="checkbox" id="low" checked>
                <label class="btn btn-warning mb-1" for="low">موجودی کم</label><br>

                <input type="checkbox" id="normal" checked>
                <label class="btn btn-success mb-1" for="normal">موجودی مناسب</label><br>

                <input type="checkbox" id="high" checked>
                <label class="btn btn-secondary " for="high">موجودی زیاد</label>
            </div>
            <div class="col-md-4 border">
                <input type="checkbox" id="available" checked>
                <label class="btn btn-success mb-1" for="available">محصولات موجود</label><br>

                <input type="checkbox" id="unavailable">
                <label class="btn btn-danger " for="unavailable">محصولات ناموجود</label>

                <input type="checkbox" id="undefined">
                <label class="btn btn-secondary " for="undefined">محصولات تعریف نشده</label>
            </div>
            <div class="col-md-4 border">
                <input type="checkbox" id="final" checked>
                <label class="btn btn-info mb-1" for="final">محصول نهایی</label><br>

                <input type="checkbox" id="raw">
                <label class="btn btn-secondary mb-1" for="raw">مواد اولیه</label><br>

                <input type="checkbox" id="pack">
                <label class="btn btn-secondary" for="pack">ملزومات بسته بندی</label>
            </div>
        </div>
    </div>


    <br>
    <div class="columns">
        <span>نمایش ستون ها:</span>
        <input type="checkbox" id="col-price" checked>
        <label class="btn btn-secondary" for="col-price">قیمت</label>

        <input type="checkbox" id="col-productPrice">
        <label class="btn btn-secondary" for="col-productPrice">قیمت تولید</label>

        <input type="checkbox" id="col-quantity" checked>
        <label class="btn btn-secondary" for="col-quantity">موجودی</label>

        <input type="checkbox" id="col-alarm">
        <label class="btn btn-secondary" for="col-alarm">حد پایین</label>

        <input type="checkbox" id="col-high_alarm">
        <label class="btn btn-secondary" for="col-high_alarm">حد بالا</label>

        <input type="checkbox" id="col-available">
        <label class="btn btn-secondary" for="col-available">وضعیت موجودی</label>
    </div>
    <br>
    <br>
    <table class="table table-striped" id="product-table">
        <thead>
        <tr>
            <th>شماره</th>
            <th>نام</th>
            <th>قیمت(ریال)</th>
            <th>قیمت تولید</th>
            <th>موجودی</th>
            <th>حد پایین</th>
            <th>حد بالا</th>
            <th>وضعیت</th>
            <th>عملیات</th>
        </tr>
        </thead>
    </table>


@endsection


@section('files')
    <script>
        let products = {}, goods = {!! $goods !!};
        let token = "{{ csrf_token() }}";
        let table;
        let low, high, normal, unavailableFilter, availableFilter, undefinedFilter, final, raw, pack, hideCols,
            warehouseId = {{auth()->user()->meta('warehouseId')}};
        let Fast, dialog;
        let available = '<span class="btn btn-success">موجود</span>'
        let unavailable = '<span class="btn btn-danger">نا موجود</span>'

        let edit = (id) => {
            return `<a class="fa fa-edit btn btn-primary" href="/product/edit/${id}" title="ویرایش محصول"></a>`
        }
        let fastEditFilter = (id) => {
            return `<a class="fa fa-file-edit btn btn-info fast" onclick="fastEdit(${id})" title="ویرایش سریع"></a>`
        }
        let saveFilter = (id) => {
            return `<i class="fa fa-save btn btn-success save" onclick="save(${id})" title="ذخیره تغییرات" style="display: none;"></i>`
        }
        let Delete = (id) => {
            return `<i class="fa fa-trash-alt btn btn-danger" onclick="delete_product(${id},this)" title="حذف محصول"></i>`
        }
        let add = (id) => {
            return `<a class="fa fa-plus btn btn-success" onclick="addToProducts(${id})" title="اضافه کردن"></a>`
        }
        let transferKey = (id) => {
            return `<a class="fa fa-warehouse btn btn-secondary" onclick="transfer(${id})" title="جابجایی بین انبارها"></a>`
        }
        let quantity = (alarm, high_alarm, quantity) => {
            let btn = (alarm > quantity) ? 'btn-warning' : ((high_alarm < quantity) ? 'btn-secondary' : '');
            return `<span dir="ltr" class="btn ${btn}">` + (+quantity) + '</span>'
        }
        let alarm = (alarm, quantity) => {
            let btn = (alarm > quantity) ? 'btn-warning' : '';
            return `<i class="btn ${btn}">${alarm}</i>`
        }
        let high_alarm = (high_alarm, quantity) => {
            let btn = (high_alarm < quantity) ? 'btn-secondary' : '';
            return `<i class="btn ${btn}">${high_alarm}</i>`
        }

        $(function () {
            refreshTable()
            $('.input-box input[type=checkbox]').click(dataTable).checkboxradio();
            $('.columns input[type=checkbox]').click(dataTable).checkboxradio();
            Fast = $('#fastEdit').html();
            $('#fastEdit').html('');
        });

        function refreshTable() {

            $.post('/product/getData', {
                _token: token,
                warehouseId: warehouseId,
                available: availableFilter,
                unavailable: unavailableFilter,
                final: final,
                raw: raw,
                pack: pack,
                low: low,
                high: high,
                normal: normal,
            }).done(res => {
                products = res;
                dataTable()
            })
        }

        function dataTable() {
            columns();
            filter();
            let data = [];
            let undefined = '<span class="btn btn-secondary">تعریغ نشده</span>'
            let goodList = [];
            $.each(products, (id, product) => {
                if (+product.warehouse_id !== +warehouseId)
                    return;
                goodList[+product.good_id] = true;
                if (product.alarm > product.quantity && !low)
                    return;
                if (product.high_alarm < product.quantity && !high)
                    return;
                if (product.alarm <= product.quantity && product.high_alarm >= product.quantity && !normal)
                    return;
                if (product.available && !availableFilter)
                    return;
                if (!product.available && !unavailableFilter)
                    return;
                if (product.good.category === 'final' && !final)
                    return;
                if (product.good.category === 'pack' && !pack)
                    return;
                if (product.good.category === 'raw' && !raw)
                    return;
                data.push([
                    id,
                    product.good.name,
                    priceFormat(product.good.price),
                    priceFormat(product.good.productPrice),
                    quantity(product.alarm, product.high_alarm, product.quantity),
                    alarm(product.alarm, product.quantity),
                    high_alarm(product.high_alarm, product.quantity),
                    product.available ? available : unavailable,
                    edit(id) + fastEditFilter(id) + saveFilter(id) + Delete(id),
                ])
            });
            if (undefinedFilter)
                $.each(goods, (id, good) => {
                    if (goodList[id])
                        return;
                    data.push([
                        '',
                        good.name,
                        priceFormat(good.price),
                        priceFormat(good.productPrice),
                        '',
                        '',
                        '',
                        undefined,
                        add(id,),
                    ])
                });

            if (table) {
                table.clear();
                table.columns().visible(true)
                table.columns(hideCols).visible(false)
                table.rows.add(data);
                table.draw();
            } else {
                table = $('#product-table').DataTable({
                    data: data,
                    order: [[3, "desc"]],
                    pageLength: 100,
                    destroy: true,
                    columnDefs: [
                        {
                            targets: hideCols,
                            visible: false
                        }
                    ],
                });
            }

        }

        function delete_product(id) {
            if (!products[id].available || confirm("برای همیشه حذف شود؟")) {
                $.post('/product/delete/' + id, {_token: "{{ csrf_token() }}"})
                    .done(res => {
                        if (res === 'ok') {
                            delete products[id];
                            dataTable();
                        }
                    });
            }
        }

        function filter() {
            low = $('#low')[0].checked
            normal = $('#normal')[0].checked
            high = $('#high')[0].checked
            availableFilter = $('#available')[0].checked
            unavailableFilter = $('#unavailable')[0].checked
            undefinedFilter = $('#undefined')[0].checked
            final = $('#final')[0].checked
            raw = $('#raw')[0].checked
            pack = $('#pack')[0].checked


        }

        function columns() {
            hideCols = [];
            $('#col-price')[0].checked ? null : hideCols.push(2);
            $('#col-productPrice')[0].checked ? null : hideCols.push(3);
            $('#col-quantity')[0].checked ? null : hideCols.push(4);
            $('#col-alarm')[0].checked ? null : hideCols.push(5);
            $('#col-high_alarm')[0].checked ? null : hideCols.push(6);
            $('#col-available')[0].checked ? null : hideCols.push(7);
        }

        function changeWarehouse(element) {
            $('#warehouses a').removeClass('btn-info').addClass('btn-outline-info')
            $(element).removeClass('btn-outline-info').addClass('btn-info')
            refreshTable();
        }

        function fastEdit(id) {
            $.get('product/fastEdit/' + id)
                .done((res => {
                    dialog = Dialog(res);
                    $('.dialogs .checkboxradio').checkboxradio();
                    priceInput();
                    $("#fastEditForm").submit(function (e) {
                        e.preventDefault();
                        $.ajax({
                            type: "POST",
                            url: '/product/edit/' + id,
                            data: new FormData(this),
                            processData: false,
                            contentType: false,
                            headers: {
                                "Accept": "application/json"
                            }
                        }).done(function (res) {
                            $.notify(res[0], "success")
                            products[id] = res[1];
                            dialog.remove();
                            dataTable()
                        }).fail(function () {
                            $.notify('خطایی رخ داده است.', 'warn');
                        });
                    });
                }));
        }

        function addToProducts(id) {
            $.post('/addToProducts/' + id, {
                '_token': token,
                'warehouseId': warehouseId,
            }).done(res => {
                products['' + res.id] = res;
                dataTable()
            }).fail(function () {
                $.notify('خطایی رخ داده است.', 'warn');
            });
        }

        function transfer(id) {
            $.post('/warehouse/transfer/' + id, {
                '_token': token,
            }).done(res => {
                dialog = Dialog(res);
                $("#transferForm").submit(function (e) {
                    e.preventDefault();
                    $.ajax({
                        type: "POST",
                        url: '/transfer/save/' + id,
                        data: new FormData(this),
                        processData: false,
                        contentType: false,
                        headers: {
                            "Accept": "application/json"
                        }
                    }).done(function (res) {
                        products[id] = res;
                        dialog.remove();
                        dataTable()
                    }).fail(function () {
                        $.notify('خطایی رخ داده است.', 'warn');
                    });
                });
            })
        }

    </script>
@endsection
