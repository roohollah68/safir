<script>

    let token = "{{ csrf_token() }}";
    let superAdmin = {{$superAdmin ? 'true' : 'false'}};
    let print = {{$print ? 'true' : 'false'}};
    let admin = {{$admin ? 'true' : 'false'}};
    let safir = {{$safir ? 'true' : 'false'}};
    let userId = {{$userId}};
    let table;
    let users = {!!json_encode($users)!!};
    let orders = {!!json_encode($orders)!!};
    let ids;
    let deleted, user = 'all';
    let role = users[userId].role;
    let globalElement;
    let dtp1Instance;
    let sendMethods = {!!json_encode($sendMethods)!!}
    $(() => {
        $("#deleted_orders").checkboxradio();
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

        $.each(orders, (id, order) => {
            if (user != 'all' && user != order.user_id)
                return
            if (deleted ^ !!order.deleted_at)
                return
            // if (safir && !order.confirm)
            //     return
            if (print && !order.confirm)
                return
            counter++;
            res.push([
                `<input type="checkbox" class="orders_checkbox" onclick="ids.includes(${id})?removeFromIds(${id}):ids.push(${id})">`,

                counter,

                order.customer_id ? '<a href="/customer/transaction/' + order.customer_id + '">' + order.name + '</a>' : order.name,

                users[order.user_id].name,

                (order.orders.length > 30) ? order.orders.substr(0, 30) + ' ...' : order.orders,

                createdTime(order),

                operations(order),

                order.address,

                order.desc,

                order.orders,

                order.phone,

                order.zip_code,

                id

            ])
        })
        create_table(res);
    }

    function create_table(data) {
        if (table) {
            table.clear();
            table.rows.add(data);
            table.draw();
        } else {
            let hideRows = (print || superAdmin) ? [1, 7, 8, 9, 10, 11, 12] : [0, 1, 3, 7, 8, 9, 10, 11, 12]
            table = $('#main-table').DataTable({
                columns: [
                    {title: "انتخاب"},
                    {title: "#"},
                    {title: "نام"},
                    {title: "سفیر"},
                    {title: "سفارش"},
                    {title: "زمان ثبت"},
                    {title: "عملیات"},
                    {title: "آدرس"},
                    {title: "توضیحات"},
                    {title: "سفارشات"},
                    {title: "همراه"},
                    {title: "کدپستی"},
                    {title: "آیدی"},
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
                        targets: hideRows,
                        visible: false
                    }
                ],
                pageLength: 100,
                data: data,
                order: [[12, "desc"]],
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

    function view_order(id) {
        let order = orders[id]
        let createdDate = new Date(order.created_at);
        let updatedDate = new Date(order.updated_at);
        let deletedDate = new Date(order.deleted_at);
        let paymentMethods = {
            credit: 'اعتباری',
            receipt: 'رسید واریز',
            onDelivery: 'پرداخت در محل',
            admin: 'ادمین',
        }
        let deliveryMethods = {
            peyk: 'تیپاکس',
            post: 'پست',
            paskerayeh: 'پسکرایه',
            admin: 'ادمین',
        }
        let sendMethod = sendMethods[order.state % 10];
        if (order.state)
            sendMethod += ' - ارسال شده در : ' + FarsiDate(updatedDate);
        let dialog = `
    <div title="مشاهده سفارش" class="dialogs">` +
            (order.receipt ?
                `<a href="receipt/${order.receipt}" target="_blank"><img style="width: 300px" src="receipt/${order.receipt}"></a>`
                :
                "")
            + `<span>نام و نام خانوادگی:</span> <b>${order.name}</b> <br>
    <span>شماره تماس:</span> <b>${order.phone}</b> <br>
    <span>آدرس:</span> <b>${order.address}</b> <br>
    <span>کد پستی:</span> <b>${order.zip_code ? order.zip_code : ''}</b> <br>
    <span>سفارشات:</span> <b>${order.orders}</b> <br>
    <span>مبلغ کل:</span> <b>${num(order.total)}</b> <b> ریال</b> <br>
    <span>پرداختی مشتری:</span> <b>${num(order.customerCost)}</b> <b> ریال</b> <br>
    <span>نحوه پرداخت:</span> <b>${paymentMethods[order.paymentMethod]}</b> <br>
    <span>نحوه ارسال:</span> <b>${deliveryMethods[order.deliveryMethod]} - ${sendMethod}</b> <br>
    <span>توضیحات:</span> <b>${order.desc ? order.desc : ''}</b> <br>
    <span>زمان ثبت:</span> <b>${FarsiDate(createdDate)}</b> <br>
    <span>زمان آخرین ویرایش:</span> <b>${FarsiDate(updatedDate)}</b> <br>` +
            (order.deleted_at ?
                    `<span>زمان حذف:</span> <b>${FarsiDate(deletedDate)}</b> <br>`
                    :
                    ""
            ) + `

</div>
    `;
        $(dialog).dialog({
            modal: true,
            open: () => {
                $('.ui-dialog-titlebar-close').hide();
                $('.ui-widget-overlay').bind('click', function () {
                    $(".dialogs").dialog('close');
                });
            }
        });

    }

    function createdTime(order) {

        let timestamp = new Date(order.created_at);
        timestamp = timestamp.getTime();
        let diff = (Date.now() - timestamp) / 1000;
        timestamp = `<span class="d-none" id="state_${order.id}">${timestamp}</span>`;
        let text = '';
        if (diff < 60) {
            res += `<span>لحظاتی پیش</span>`;
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
        if (!order.state || order.deleted_at) {
            res = timestamp + `<span class="btn btn-secondary" onclick="selectSendMethod(${order.id})">${text}</span>`
        } else {
            res = timestamp + `<span class="btn btn-success" onclick="selectSendMethod(${order.id})">${text}<i class="fas fa-check"></i></span>`
        }
        return res;
    }

    function operations(order) {
        let id = order.id;
        let viewOrder = `<i id="view_order_${id}" class="fa fa-eye btn btn-info" onclick="view_order(${id})"></i> `;
        let deleteOrder = `<i class="fa fa-trash-alt btn btn-danger" onclick="delete_order(${id},this)" title="حذف سفارش" ></i> `;
        let editOrder = `<a class="fa fa-edit btn btn-primary" href="edit_order/${id}" title="ویرایش سفارش"></a> `;
        let res = viewOrder;
        if (deleted)
            return res;
        @if($safir)
        if (!order.state)
            res += deleteOrder + editOrder;
        @else
        let creatorRole = users[order.user_id].role

        let cancelInvoice = `<a class="fa-regular fa-xmark btn btn-danger" onclick="cancelInvoice(${id},this)" title=" رد فاکتور"> </a> `;
        let generatePDF = `<i class="fa fa-file-pdf btn btn-${order.state > 10 ? 'success' : 'secondary'}" onclick="generatePDF([${id}])" title="دانلود لیبل"></i> `;
        let confirmInvoice = `<a class="fa fa-check btn btn-success" onclick="confirmInvoice(${id},this)" title=" تایید فاکتور"></a> `;
        let invoice = `<a class="fa fa-file-invoice-dollar btn btn-secondary" onclick="invoice(${id})" title=" فاکتور"></a> `;
        let preInvoice = `<a class="fa fa-file-invoice-dollar btn btn-secondary" onclick="invoice(${id})" title="پیش فاکتور"></a> `;

        if (!order.state && (!order.confirm || creatorRole === 'user') && !print)
            res += deleteOrder + editOrder;

        if ((print || superAdmin) && order.state)
            res += generatePDF

        if (creatorRole !== 'user') {
            if (order.confirm) {
                res += invoice;
                if (!print && !order.state)
                    res += cancelInvoice;

            } else {
                res += preInvoice;
                if (!print)
                    res += confirmInvoice;
            }
        }
        @endif
            return res;
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

    @if($print || $superAdmin )

    function selectSendMethod(id) {
        if (!orders[id].confirm) {
            alert('ابتدا فاکتور باید تایید شود!');
            return;
        }
        if (orders[id].state) {
            change_state(id, 0);
            return;
        }
        let dialog = `<div title="نحوه ارسال" class="dialogs">`;
        dialog += `<p class="btn btn-success" onclick="change_state(${id},1)">${sendMethods[1]}</p><br>`;
        dialog += `<p class="btn btn-info" onclick="change_state(${id},2)">${sendMethods[2]}</p><br>`;
        dialog += `<p class="btn btn-primary" onclick="change_state(${id},3)">${sendMethods[3]}</p><br>`;
        dialog += `<p class="btn btn-secondary" onclick="change_state(${id},4)">${sendMethods[4]}</p><br>`;
        dialog += `<p class="btn btn-warning" onclick="change_state(${id},5)">${sendMethods[5]}</p><br>`;
        dialog += `<p class="btn btn-warning" onclick="change_state(${id},6)">${sendMethods[6]}</p><br>`;
        $(dialog).dialog({
            modal: true,
            open: () => {
                $('.ui-dialog-titlebar-close').hide();
                $('.ui-widget-overlay').bind('click', function () {
                    $(".dialogs").dialog('destroy').remove()
                });
            }
        });
    }

    function change_state(id, sendMethod) {
        $(".dialogs").dialog('destroy').remove();
        $.post('change_state/' + id, {_token: token, sendMethod: sendMethod})
            .done(res => {
                orders[id].state = res[0];
                $('#view_order_' + id).parent().html(operations(orders[id]));
                $('#state_' + id).parent().html(createdTime(orders[id]))
            })
    }

    function generatePDF(Ids) {

        $.get('pdfs/' + Ids.toString())
            .done(res => {
                $('#pdf-link').html("لینک دانلود").attr('href', "{{env('APP_URL')}}" + res)[0].click();
                $.each(ids, function (index, id) {
                    orders[id].state = orders[id].state % 10 + 10
                    $('#view_order_' + id).parent().html(operations(orders[id]));
                });
            })
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

    function confirmInvoice(id, element) {
        if (orders[id].state)
            return
        globalElement = element;
        let dialog = `<div title="نحوه پرداخت" class="dialogs">`;
        dialog += `<p class="btn btn-success" onclick="sendConfirm(${id},1)">نقدی</p><br>`;
        dialog += `<p class="btn btn-info" onclick="sendConfirm(${id},2)">چک</p><br>`;
        dialog += `<p class="btn btn-primary" onclick="sendConfirm(${id},3)">پرداخت در محل</p><br>`;
        dialog += `<p class="btn btn-secondary" onclick="sendConfirm(${id},6)">فاکتور به فاکتور</p><br>`;
        dialog += `<p class="btn btn-warning" onclick="sendConfirm(${id},4)">امانی</p><br>`;
        dialog += `<p class="btn btn-danger" id="dtp1">پرداخت در تاریخ</p>`;
        dialog += `<input type="text" id="dateOfPayment" class="form-control d-none" placeholder="تاریخ پرداخت" data-name="dtp1-text">`;

        $(dialog).dialog({
            modal: true,
            open: () => {
                $('.ui-dialog-titlebar-close').hide();
                $('.ui-widget-overlay').bind('click', function () {
                    $(".dialogs").dialog('destroy').remove()
                });
            }
        });

        dtp1Instance = new mds.MdsPersianDateTimePicker(document.getElementById('dtp1'), {
            targetTextSelector: '[data-name="dtp1-text"]',
            persianNumber: true,
            onDayClick: function () {
                sendConfirm(id, 5);
            }
        });
    }

    function sendConfirm(id, pay) {
        let date = $('#dateOfPayment').val();
        $(".dialogs").dialog('destroy').remove();
        $.post('confirm_invoice/' + id, {_token: token, pay: pay, date: date})
            .done(res => {
                orders[id] = res;
                $(globalElement).parent().html(operations(orders[id]));
            })
    }

    function cancelInvoice(id, element) {
        if (confirm('آیا از حذف کردن فاکتور مطمئن هستید؟')) {
            $.post('cancel_invoice/' + id, {_token: token})
                .done(res => {
                    orders[id] = res;
                    $(element).parent().html(operations(orders[id]));
                });
        }

    }
    @endif

    @if($admin || $superAdmin || $print)

    let totalPages = 1;
    let firstPageItems = 40;

    function invoice(id) {
        $.post('/invoice/' + id, {_token: token, firstPageItems: firstPageItems, totalPages: totalPages})
            .done(res => {
                $('#invoice-wrapper').html(res[0][0]);
                if ($('#invoice-content')[0].offsetHeight > 2900) {
                    totalPages = 2;
                    firstPageItems--;
                    invoice(id);
                    return
                }
                domtoimage.toJpeg($('#invoice')[0], {width: 2100, height: 2970})
                    .then(function (dataUrl) {
                        let link = document.createElement('a');
                        link.download = res[0][1] + '.jpg';
                        link.href = dataUrl;
                        link.click();
                        $('#invoice-wrapper').html('');
                        if (res.length > 1) {
                            $('#invoice-wrapper').html(res[1][0]);
                            domtoimage.toJpeg($('#invoice')[0], {width: 2100, height: 2970})
                                .then(function (dataUrl) {
                                    let link = document.createElement('a');
                                    link.download = res[1][1] + '.jpg';
                                    link.href = dataUrl;
                                    link.click();
                                    $('#invoice-wrapper').html('');
                                });
                        }
                    });
            })


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

</script>

