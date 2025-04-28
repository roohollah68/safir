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
    @if(auth()->user()->meta('editWarehouse'))
    <a class="btn btn-info mb-3" href="{{route('addProduct')}}">
        <span class=" fa fa-plus"></span>
        افزودن محصول جدید
    </a>
    @endif

    @if (auth()->user()->meta('warehouseTransfer'))
    <a class="btn btn-primary mb-3" href="/warehouse/transfer">
        <span class=" fa fa-warehouse"></span>
        انتقال بین انبارها
    </a>
    @endif

    <a class="btn btn-secondary mb-3" id="production_schedule"
       onclick="window.location.href = `production/schedule/${warehouseId}`">
        <span class=" fa fa-industry"></span>
        برنامه تولید
    </a>

    @if (auth()->user()->meta('warehouse'))
    <a class="btn btn-secondary mb-3" href="/goods/management">
        <span class=" fa fa-list"></span>
        مدیریت کالاها
    </a>

    <a class="btn btn-dark mb-3" href="/warehouse/manager">
        <span class=" fa fa-user"></span>
        تعیین مسئول انبار
    </a>

    <a class="btn btn-info mb-3" href="/good/tag">
        <span class=" fa fa-tag"></span>
        ثبت شناسه کالا
    </a>
    @endif

    @if (auth()->user()->meta('formulation'))
    <a class="btn btn-success mb-3" href="/formulation/list">
        <span class=" fa fa-flask"></span>
        فرمول تولید
    </a>
    @endif

    <a class="btn btn-warning mb-3" href="{{ route('productionList') }}">
        <span class="fa fa-list"></span>
        لیست تولید
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
                @foreach(config('goodCat') as $cat => $desc)
                    <input type="checkbox" id="{{$cat}}" checked>
                    <label class="btn btn-info mb-1" for="{{$cat}}">{{$desc}}</label><br>
                @endforeach


                {{--                <input type="checkbox" id="raw">--}}
                {{--                <label class="btn btn-secondary mb-1" for="raw">مواد اولیه</label><br>--}}

                {{--                <input type="checkbox" id="pack">--}}
                {{--                <label class="btn btn-secondary" for="pack">ملزومات بسته بندی</label>--}}
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

        <input type="checkbox" id="col-production">
        <label class="btn btn-secondary" for="col-production">برنامه تولید</label>

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
            <th>برنامه تولید</th>
            <th>وضعیت</th>
            <th>عملیات</th>
        </tr>
        </thead>
    </table>

@endsection


@section('files')
    <script>
        let products = {}, goods = {!! $goods !!};
        let table;
        let low, high, normal, unavailableFilter, availableFilter, undefinedFilter, final, raw, pack, other, hideCols,
            warehouseId = {{auth()->user()->meta('warehouseId')}};
        let Fast, dialog;
        let available = '<span class="btn btn-success">موجود</span>'
        let unavailable = '<span class="btn btn-danger">نا موجود</span>'

        let edit = (id) => {
            return `<a class="fa fa-edit btn btn-primary" href="/product/edit/${id}" title="ویرایش محصول"></a>`
        }
        let formulation = (id, hasFormulation) => {
            return `<a class="fa fa-flask btn btn-${hasFormulation ? 'success' : 'info'}" href="/formulation/edit/${id}" title="فرمول تولید"></a>`
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
        let Delete_good = (id) =>{
            return `<span class="btn btn-danger fa fa-trash" onclick="deleteGood(${id})"></span>`
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
                let good = product.good;
                if (good.category === 'final' && !final)
                    return;
                if (good.category === 'pack' && !pack)
                    return;
                if (good.category === 'raw' && !raw)
                    return;
                if (good.category === 'other' && !other)
                    return;
                data.push([
                    id,
                    good.name,
                    priceFormat(good.price),
                    priceFormat(good.productPrice),
                    quantity(product.alarm, product.high_alarm, product.quantity),
                    alarm(product.alarm, product.quantity),
                    high_alarm(product.high_alarm, product.quantity),
                    (product.quantity < product.alarm) ? product.high_alarm - product.quantity : 0,
                    product.available ? available : unavailable,
                    edit(id) + fastEditFilter(id) + saveFilter(id) + (good.category == 'final' ? formulation(good.id, good.formulations.length) : '') + Delete(id),
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
                        '',
                        undefined,
                        add(id) + Delete_good(id),
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
                    language: language,
                    columnDefs: [
                        {
                            targets: hideCols,
                            visible: false
                        }
                    ],
                    layout: {
                        topStart: {
                            buttons: [
                                {
                                    extend: 'excel',
                                    text: 'دریافت فایل اکسل',
                                    filename: 'محصولات ' + '{{verta()->formatJalaliDate()}}',
                                    title: null,
                                    exportOptions: {
                                        modifier: {
                                            page: 'current'
                                        }
                                    }
                                }
                            ]
                        }
                    }
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
            other = $('#other')[0].checked
        }

        function columns() {
            hideCols = [];
            $('#col-price')[0].checked ? null : hideCols.push(2);
            $('#col-productPrice')[0].checked ? null : hideCols.push(3);
            $('#col-quantity')[0].checked ? null : hideCols.push(4);
            $('#col-alarm')[0].checked ? null : hideCols.push(5);
            $('#col-high_alarm')[0].checked ? null : hideCols.push(6);
            $('#col-production')[0].checked ? null : hideCols.push(7);
            $('#col-available')[0].checked ? null : hideCols.push(8);
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
                            url: '/product/addOrEdit/' + id,
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
                    });
                });
            })
        }

        function deleteGood(id) {
            if (confirm(' آیا ' + goods[id].name + ' حذف شود؟ '))
                $.post('/good/delete/' + id, {
                    _token: token,
                }).done(res => {
                    delete goods[id];
                    $.notify('با موفقیت حذف شد.', 'success');
                    dataTable();
                });
        }

    </script>
@endsection
