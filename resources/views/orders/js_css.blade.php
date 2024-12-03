<script>

    let token = "{{ csrf_token() }}";
    let superAdmin = !!"{{$superAdmin ? 'true' : ''}}";
    let print = {{$print ? 'true' : 'false'}};
    let admin = {{$admin ? 'true' : 'false'}};
    let safir = {{$safir ? 'true' : 'false'}};
    let table;
    let users = {!!json_encode($users)!!};
    let orders = {!!json_encode($orders)!!};
    let ids;
    let showDeleted, printWait, confirmWait, counterWait, proccessWait, sent, COD, user = 'all',
        warehouseId = 'all';
    let changeOrdersPermit = !!'{{$user->meta('showAllOrders')}}';
    let safirOrders = true, siteOrders = true, adminOrders = true;
    let dtp1Instance;
    let sendMethods = {!!json_encode(config('sendMethods'))!!};
    let payMethods = {!!json_encode(config('payMethods'))!!};
    let payMethodText, sendMethodText;
    let dialog;
    $(() => {
        payMethodText = $('#paymentMethodText').html();
        $('#paymentMethodText').html('');
        sendMethodText = $('#sendMethodText').html();
        $('#sendMethodText').html('');
        $(".checkboxradio").checkboxradio();
        $('#warehouse-' + warehouseId).click();
        prepare_data();

        const dtp1Instance2 = new mds.MdsPersianDateTimePicker(document.getElementById('date1'), {
            targetTextSelector: '[data-name="date1-text"]',
            targetDateSelector: '[data-name="date1-date"]',
        });
        const dtp1Instance3 = new mds.MdsPersianDateTimePicker(document.getElementById('date2'), {
            targetTextSelector: '[data-name="date2-text"]',
            targetDateSelector: '[data-name="date2-date"]',
        });
    });

    function prepare_data() {
        ids = [];
        let res = [];
        let counter = 0;
        let select = (id) => {
            return `<input type="checkbox" class="orders_checkbox" onclick="ids.includes(${id})?removeFromIds(${id}):ids.push(${id})">`;
        }

        $.each(orders, (id, order) => {
            if (user !== 'all' && +user !== order.user_id)
                return
            if (showDeleted ^ !!order.deleted_at)
                return
            if (sent && order.state != 10)
                return
            if (confirmWait && order.confirm)
                return
            if (counterWait && (order.counter !== 'waiting' || !order.confirm || order.state))
                return
            if (printWait && (!order.confirm || order.state || order.counter !== 'approved'))
                return
            if (proccessWait && (order.state > 4 || order.state < 1))
                return
            if (print && !order.confirm)
                return
            if (+warehouseId !== +order.warehouse_id && warehouseId !== 'all')
                return;
            if (COD && order.paymentMethod !== 'cod' && order.paymentMethod !== 'پرداخت در محل' && order.paymentMethod !== 'onDelivery')
                return;
            let website = false;
            if (order.user_id === 30 || order.user_id === 32 || order.user_id === 33 || order.user_id === 75)
                website = true;
            if (users[order.user_id].role === 'admin' && !adminOrders)
                return
            if (users[order.user_id].role === 'user' && !website && !safirOrders)
                return
            if (website && !siteOrders)
                return
            counter++;
            res.push([
                !!order.deleted_at ? '' : select(id),

                counter,

                order.customer_id ? '<a href="/customer/transaction/' + order.customer_id + '">' + order.name + '</a>' : order.name,

                users[order.user_id].name + ((website && order.website) ? `(${order.website.website_id})` : ''),

                (order.orders.length > 30) ? order.orders.substr(0, 30) + ' ...' : order.orders,

                createdTime(order),

                operations(order),

                `<span id='orderCondition_${id}'>` + orderCondition(order) + '</span>',

                order.address,

                order.desc,

                order.orders,

                order.phone,

                order.zip_code,

                id,

                order.total,

            ])
        });
        create_table(res);
    }

    function create_table(data) {
        if (table) {
            table.clear();
            table.rows.add(data);
            table.draw();
        } else {
            let hideCols = (changeOrdersPermit) ? [1, 8, 9, 10, 11, 12, 13, 14] : [0, 1, 3, 8, 9, 10, 11, 12, 13, 14]
            table = $('#main-table').DataTable({
                columns: [
                    {title: "انتخاب"},
                    {title: "#"},
                    {title: "نام"},
                    {title: "سفیر"},
                    {title: "سفارش"},
                    {title: "زمان ثبت"},
                    {title: "عملیات"},
                    {title: "وضعیت"},
                    {title: "آدرس"},
                    {title: "توضیحات"},
                    {title: "سفارشات"},
                    {title: "همراه"},
                    {title: "کدپستی"},
                    {title: "آیدی"},
                    {title: "مبلغ سفارش"},
                ],
                columnDefs: [
                    {
                        targets: [0, 1, 5, 6],
                        searchable: false
                    },
                    {
                        targets: [0, 4, 6],
                        orderable: false
                    },

                    {
                        targets: hideCols,
                        visible: false
                    }
                ],
                pageLength: 100,
                // paging: false,
                data: data,
                order: [[13, "desc"]],
                dom: 'lBfrtip',
                buttons: [
                    'excelHtml5',
                ],
                language: {
                    "decimal": "",
                    "emptyTable": "هیچ سفارشی موجود نیست",
                    "info": "نمایش _START_ تا _END_ از _TOTAL_ مورد",
                    "infoEmpty": "نمایش  0 تا 0 از 0 مورد",
                    "infoFiltered": "(فیلتر شده از مجموع _MAX_ داده)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "نمایش _MENU_ مورد",
                    "loadingRecords": "در حال بارگذاری...",
                    "processing": "در حال پردازش...",
                    "search": "جستجو:",
                    "zeroRecords": "هیچ مورد منطبقی یافت نشد",
                    "paginate": {
                        "first": "اولین",
                        "last": "آخرین",
                        "next": "بعدی",
                        "previous": "قبلی"
                    },
                    aria: {
                        "sortAscending": ": activate to sort column ascending",
                        "sortDescending": ": activate to sort column descending"
                    }
                }
            });
        }

    }

    function createdTime(order) {

        let timestamp = new Date(order.created_at);
        timestamp = timestamp.getTime();
        let diff = (Date.now() - timestamp) / 1000;
        timestamp = `<span class="d-none" id="state_${order.id}">${timestamp}</span>`;
        let text = '';
        if (diff < 60) {
            text += `<span>لحظاتی پیش</span>`;
        } else if (diff < 3600) {
            let minute = Math.floor(diff / 60);
            text += `<span>${minute} دقیقه قبل </span>`
        } else if (diff < 3600 * 24) {
            let hour = Math.floor(diff / 3600);
            text += `<span>${hour} ساعت قبل </span>`
        } else if (diff < 3600 * 24 * 30) {
            let day = Math.floor(diff / (3600 * 24));
            text += `<span>${day} روز قبل </span>`
        } else {
            let month = Math.floor(diff / (3600 * 24 * 30));
            text += `<span>${month} ماه قبل </span>`
        }

        if (order.deleted_at) {
            res = timestamp + `<span class="btn btn-secondary">${text}</span>`
        } else if (!order.state) {
            let btn = order.confirm ? (order.counter === 'waiting' ? 'info' : 'secondary') : 'primary';
            res = timestamp + `<span class="btn btn-${btn}" onclick="change_state(${order.id}, 1)">${text}</span>`
        } else if (order.state < 3) {
            res = timestamp + `<span class="btn btn-warning" onclick="selectSendMethod(${order.id})">${text}<i class="fas fa-check"></i></span>`
        } else if (+order.state === 4) {
            res = timestamp + `<span class="btn btn-danger" onclick="change_state(${order.id}, 0)">${text}<i class="fas fa-question"></i></span>`
        } else {
            res = timestamp + `<span class="btn btn-success" onclick="change_state(${order.id}, 0)">${text}<i class="fas fa-check-double"></i></span>`
        }
        return res;
    }

    function operations(order) {
        let id = order.id;
        let viewOrder = `<i id="view_order_${id}" class="fa fa-eye btn btn-info" onclick="view_order(${id})"></i> `;
        let viewComment = `<i id="view_comment_${id}" class="fa fa-comment btn btn-info" onclick="view_comment(${id})"></i> `;
        let deleteOrder = `<i class="fa fa-trash-alt btn btn-danger" onclick="delete_order(${id},this)" title="حذف سفارش" ></i> `;
        let changeWarehouse = `<i class="fa fa-warehouse btn btn-warning" onclick="changeWarehouse(${id})" title="تغییر انبار" ></i> `;
        let editOrder = `<a class="fa fa-edit btn btn-primary" href="edit_order/${id}" title="ویرایش سفارش"></a> `;
        let res = viewOrder + viewComment;
        if (showDeleted)
            return res;
        @if($safir)
        if (!order.state) {
            res += deleteOrder;
            if (order.total >= 0)
                res += editOrder + changeWarehouse;
        }
        @else
        let creatorRole = users[order.user_id].role

        let cancelInvoice = `<a class="fa-regular fa-xmark btn btn-danger" onclick="cancelInvoice(${id})" title=" رد فاکتور"> </a> `;
        let generatePDF = `<i class="fa fa-file-pdf btn btn-${+order.state > 1 ? 'success' : 'secondary'}" onclick="generatePDF([${id}])" title="دانلود لیبل"></i> `;
        let confirmInvoice = `<a class="fa fa-check btn btn-success" onclick="selectPayment(${id})" title=" تایید فاکتور"></a> `;
        let invoice = `<a class="fa fa-file-invoice-dollar btn btn-info text-success" onclick="invoice(${id})" title=" فاکتور"></a> `;
        let preInvoice = `<a class="fa fa-file-invoice-dollar btn btn-secondary" onclick="invoice(${id})" title="پیش فاکتور"></a> `;
        let excel = `<a class="fa fa-file-excel btn btn-outline-info" href="/orderExcel/${id}" title="خروجی اکسل"></a> `;

        if (!order.state && (!order.confirm || creatorRole === 'user'))
            res += deleteOrder;
        if (!order.confirm || (creatorRole === 'user' && !order.state))
            res += editOrder + changeWarehouse;

        if (changeOrdersPermit && order.state)
            res += generatePDF

        if (order.customer_id)
            res += excel

        if (creatorRole !== 'user' && order.state < 10) {
            if (order.confirm)
                res += cancelInvoice;
            else
                res += confirmInvoice;
        }
        if (creatorRole !== 'user') {
            if (order.confirm)
                res += invoice;
            else
                res += preInvoice;
        }

        @endif
            return res;
    }

    function orderCondition(order) {
        if (order.state === 10)
            return 'ارسال شده';
        if (order.counter === 'rejected')
            return 'رد شده در حسابداری';
        if (!order.confirm)
            return 'منتظر تایید کاربر';
        if (order.confirm && order.counter === 'waiting') {
            return 'منتظر تایید حسابدار';
        }
        if (order.confirm && order.counter === 'approved' && order.state === 0)
            return 'در انتظار پرینت';
        if (order.state === 1 || order.state === 2)
            return 'در حال پردازش برای ارسال';
        if (order.state === 4)
            return 'در انتظار پرینت';
    }

    function delete_order(id, element) {
        $.post('delete_order/' + id, {_token: token})
            .done(res => {
                $.notify(res[0], 'info');
                orders[id] = res[1];
                if (res[1].deleted_at) {
                    ids.includes(id) ? removeFromIds(id) : '';
                    $(element).parent().parent().remove()
                }
            })
    }

    @if($user->meta('changeOrderState'))

    function selectSendMethod(id) {
        if (!orders[id].confirm) {
            alert('ابتدا فاکتور باید تایید شود!');
            return;
        }
        dialog = Dialog(sendMethodText);

        $(".checkboxradio").checkboxradio();

        $("#sendForm").submit(function (e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: '/set_send_method/' + id,
                data: new FormData(this),
                processData: false,
                contentType: false,
                headers: {
                    "Accept": "application/json"
                }
            }).done(function (res) {
                $.notify("با موفقیت ذخیره شد.", "success");
                dialog.remove();
                orders[id] = res;
                change_state(id, 10)
            }).fail(function () {
                $.notify('خطایی رخ داده است.', 'warn');
            });
        });
    }

    function change_state(id, state) {
        if ((!orders[id].confirm || orders[id].counter !== "approved") && +orders[id].state !== 4) {
            alert('ابتدا فاکتور باید تایید شود!');
            return;
        }
        $.post('/change_state/' + id + '/' + state, {
            _token: token,
        })
            .done(state => {
                orders[id].state = +state;
                $('#view_order_' + id).parent().html(operations(orders[id]));
                $('#state_' + id).parent().html(createdTime(orders[id]));
                $('#orderCondition_' + id).html(orderCondition(orders[id]));

            });
    }

    @endif

    @if($user->meta('showAllOrders') || $user->meta('changeOrderState'))

    function generatePDF(Ids) {

        $.get('pdfs/' + Ids.toString())
            .done(res => {
                $('#pdf-link').html("لینک دانلود").attr('href', "{{env('APP_URL')}}" + res)[0].click();
                $.each(Ids, function (index, id) {
                    orders[id].state = orders[id].state % 10 + 10
                    $('#view_order_' + id).parent().html(operations(orders[id]));
                });
            });
    }

    function generatePDFs() {
        let verifiedIds = [];
        $.each(ids, function (index, id) {
            if (orders[id].state)
                verifiedIds.push(id);
        });
        if (verifiedIds.length === 0) {
            $.notify('ابتدا باید سفارشات مورد نظر را انتخاب کنید', 'error')
            return
        }
        generatePDF(verifiedIds)
    }

    function removeFromIds(id) {
        const index = ids.indexOf(id);
        if (index > -1) {
            ids.splice(index, 1);
        }
    }
    @endif

    @if($admin || $superAdmin)

    function selectPayment(id) {
        if (orders[id].confirm)
            return
        dialog = Dialog(payMethodText);

        $(".checkboxradio").checkboxradio();

        dtp1Instance = new mds.MdsPersianDateTimePicker($('#payInDate')[0], {
            targetTextSelector: '#dateOfPayment',
            targetDateSelector: '[name="payInDate"]',
            persianNumber: true,
        });

        $("#paymentForm").submit(function (e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: '/orders/paymentMethod/' + id,
                data: new FormData(this),
                processData: false,
                contentType: false,
                headers: {
                    "Accept": "application/json"
                }
            }).done(function (res) {
                if (res[0] === "error")
                    $.notify(res[1], 'warn');
                else if (res[0] === "ok") {
                    $.notify("با موفقیت ذخیره شد.", "success");
                    dialog.remove();
                    orders[id] = res[1];
                    $('#view_order_' + id).parent().html(operations(orders[id]));
                    $('#state_' + id).parent().html(createdTime(orders[id]));
                    $('#orderCondition_' + id).html(orderCondition(orders[id]));
                }
            }).fail(function () {
                $.notify('خطایی رخ داده است.', 'warn');
            });
        });
    }

    function cancelInvoice(id) {
        if (confirm('آیا از حذف کردن فاکتور مطمئن هستید؟')) {
            $.post('cancel_invoice/' + id, {_token: token})
                .done(res => {
                    orders[id] = res;
                    $('#view_order_' + id).parent().html(operations(orders[id]));
                    $('#state_' + id).parent().html(createdTime(orders[id]));
                    $('#orderCondition_' + id).html(orderCondition(orders[id]));
                });
        }

    }
    @endif

    function dateFilter() {
        let date1 = $('input[name=from]').val();
        let date2 = $('input[name=to]').val();
        let limit = $('input[name=limit]').val();
        $.post('/orders/dateFilter', {_token: token, date1: date1, date2: date2, limit: limit})
            .done(res => {
                orders = res;
                prepare_data();
            })


        return false;

    }

    function changeWarehouse(id) {
        let order = orders[id];
        let text = `
        <div title="تغییر انبار" class="dialogs">
<span>انتقال از انبار</span><br>
<span class="btn btn-info">${order.warehouse.name}</span><br><br>
<span>به انبار</span><br>
@foreach($warehouses as $warehouse)
        <span class="btn btn-outline-secondary" onclick="warehouseChange(${id},{{$warehouse->id}});dialog.remove()">{{$warehouse->name}}</span>
@endforeach
        </div>
`;

        dialog = Dialog(text);
        $(".checkboxradio").checkboxradio();
    }

    function warehouseChange(order_id, warehouse_id) {
        $.get(`changeWarehouse/${order_id}/${warehouse_id}`, {_token: token}).done((res) => {
            orders[order_id] = res;
            prepare_data();
            $.notify('با موفقیت ذخیره شد.', 'success');
        }).fail(() => {
            $.notify('مشکلی پیش آمده', 'warn');
        })
    }

</script>

