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
    let deleted, printWait, confirmWait, proccessWait, user = 'all', Location = 't';
    let safirOrders = true, siteOrders = true, adminOrders = true;
    let role = users[userId].role;
    let dtp1Instance;
    let sendMethods = {!!json_encode(config('sendMethods'))!!};
    let payMethods = {!!json_encode(config('payMethods'))!!};
    $(() => {
        $(".checkboxradio").checkboxradio();
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
            if (confirmWait && order.confirm)
                return
            if (printWait && (!order.confirm || order.state))
                return
            if (proccessWait && (order.state > 4 || order.state < 1))
                return
            if (print && !order.confirm)
                return
            if (Location !== order.location)
                return;
            let website = false;
            if (order.user_id === 30 || order.user_id === 32 || order.user_id === 33)
                website = true;
            if (users[order.user_id].role === 'admin' && !adminOrders)
                return
            if (users[order.user_id].role === 'user' && !website && !safirOrders)
                return
            if (website && !siteOrders)
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
        });
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
        $.post('/viewOrder/' + id, {_token: token})
            .done(res => {
                $(res).dialog({
                    modal: true,
                    open: () => {
                        $('.ui-dialog-titlebar-close').hide();
                        $('.ui-widget-overlay').bind('click', function () {
                            $(".dialogs").dialog('close');
                        });
                    }
                });
            })
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
            res = timestamp + `<span class="btn btn-secondary" onclick="change_state(${order.id}, 1)">${text}</span>`
        } else if (order.state < 3) {
            res = timestamp + `<span class="btn btn-warning" onclick="selectSendMethod(${order.id})">${text}<i class="fas fa-check"></i></span>`
        } else if (+order.state === 4) {
            res = timestamp + `<span class="btn btn-danger" onclick="change_state(${order.id}, ${order.confirm ? 1 : 0})">${text}<i class="fas fa-question"></i></span>`
        } else {
            res = timestamp + `<span class="btn btn-success" onclick="change_state(${order.id}, 0)">${text}<i class="fas fa-check-double"></i></span>`
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
        let generatePDF = `<i class="fa fa-file-pdf btn btn-${+order.state > 1 ? 'success' : 'secondary'}" onclick="generatePDF([${id}])" title="دانلود لیبل"></i> `;
        let confirmInvoice = `<a class="fa fa-check btn btn-success" onclick="confirmInvoice(${id},this)" title=" تایید فاکتور"></a> `;
        let invoice = `<a class="fa fa-file-invoice-dollar btn btn-info text-success" onclick="invoice(${id})" title=" فاکتور"></a> `;
        let preInvoice = `<a class="fa fa-file-invoice-dollar btn btn-secondary" onclick="invoice(${id})" title="پیش فاکتور"></a> `;

        if (!order.state)
            res += deleteOrder;
        if (!order.confirm || creatorRole === 'user')
            res += editOrder;

        if ((print || superAdmin) && order.state)
            res += generatePDF

        if (creatorRole !== 'user') {
            if (order.confirm) {
                res += invoice;
                if (order.state < 10)
                    res += cancelInvoice;

            } else {
                res += preInvoice;
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
        let dialog = `<div title="نحوه ارسال" class="dialogs" >`;
        dialog += `@csrf`;
        dialog += `<p class="btn btn-success" onclick="setSendMethod(${id},1)">${sendMethods[1]}</p> `;
        dialog += `<p class="btn btn-info" onclick="setSendMethod(${id},2)">${sendMethods[2]}</p> `;
        dialog += `<p class="btn btn-secondary" onclick="setSendMethod(${id},4)">${sendMethods[4]}</p> `;
        dialog += `<p class="btn btn-warning" onclick="setSendMethod(${id},5)">${sendMethods[5]}</p> `;
        dialog += `<p class="btn btn-warning" onclick="setSendMethod(${id},6)">${sendMethods[6]}</p><br><br><br>`;
        dialog += `<label for="postCode">کد مرسوله:</label>`;
        dialog += `<input id="postCode" name="note" type="text" class="w-100"><br>`;
        dialog += `<p class="btn btn-primary" onclick="setSendMethod(${id},3)">${sendMethods[3]}</p><br> `;
        // dialog += `<label for="send-file">فایل الحاقی:</label>`;
        // dialog += `<input id="send-file" name="file" type="file" ><br>`;
        // dialog += `<input  value="submit" type="submit" ><br>`;
        dialog += `</div>`;

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

    function setSendMethod(id, method) {
        let note = $('#postCode').val();
        if (note)
            note = ' - کد مرسوله: ' + note;
        $(".dialogs").dialog('destroy').remove();

        $.post('/set_send_method/' + id, {
            _token: token,
            sendMethod: sendMethods[method] + note,
        })
            .done(order => {
                orders[id] = order;
                change_state(id, 10)
            });
    }

    function change_state(id, state) {
        if (!orders[id].confirm && +orders[id].state !== 4) {
            alert('ابتدا فاکتور باید تایید شود!');
            return;
        }
        $.post('/change_state/' + id, {
            _token: token,
            state: state,
        })
            .done(state => {
                orders[id].state = +state;
                $('#view_order_' + id).parent().html(operations(orders[id]));
                $('#state_' + id).parent().html(createdTime(orders[id]));
            });
    }

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

    function confirmInvoice(id, element) {
        if (orders[id].confirm)
            return


        dialog = `
<div title="نحوه پرداخت" class="dialogs">

<input type="radio" id="cash" value="1" name="paymentMethod" class="checkboxradio" onchange="$('.hide').hide();$('.cashPhoto').show()">
<label for='cash' class="btn btn-success my-1">پرداخت نقدی</label>
<label for='cashPhoto' class="btn btn-info m-2 hide cashPhoto" >بارگذاری رسید بانکی  <i class="fa fa-image"></i></label>
<input type="file" class="hide" name="cashPhoto" id="cashPhoto">
<br>
<input type="radio" id="cheque" value="2" name="paymentMethod" class="checkboxradio" onchange="$('.hide').hide();$('.chequePhoto').show()">
<label for='cheque' class="btn btn-info my-1">پرداخت چکی</label>
<label for='chequePhoto' class="btn btn-info m-2 hide chequePhoto">بارگذاری تصویر چک  <i class="fa fa-image"></i></label>
<input type="file" class="hide" name="chequePhoto" id="chequePhoto">
<br>
<input type="radio" id="cod" value="3" name="paymentMethod" class="checkboxradio" onchange="$('.hide').hide();">
<label for='cod' class="btn btn-primary my-1">پرداخت در محل</label>
<br>
<input type="radio" id="factor" value="4" name="paymentMethod" class="checkboxradio" onchange="$('.hide').hide();">
<label for='factor' class="btn btn-secondary my-1">فاکتور به فاکتور</label>
<br>
<input type="radio" id="barrow" value="5" name="paymentMethod" class="checkboxradio" onchange="$('.hide').hide();">
<label for='barrow' class="btn btn-warning my-1">امانی</label>
<br>
<input type="radio" id="payInDate" value="6" name="paymentMethod" class="checkboxradio" onchange="$('.hide').hide();$('#dateOfPayment').show()">
<label for='payInDate' class="btn btn-danger my-1">پرداخت در تاریخ</label>
<input type="text" id="dateOfPayment" class="form-control hide" placeholder="تاریخ پرداخت" data-name="dtp1-text">

<br><label for="send-note">یادداشت:</label>
<input id="send-note" name="note" type="text" class="w-100">
</div>
        `;

        $(dialog).dialog({
            modal: true,
            open: () => {
                $('.ui-dialog-titlebar-close').hide();
                $('.ui-widget-overlay').bind('click', function () {
                    $(".dialogs").dialog('destroy').remove()
                });
            }
        });
        $(".checkboxradio").checkboxradio();
        dtp1Instance = new mds.MdsPersianDateTimePicker(document.getElementById('payInDate'), {
            targetTextSelector: '[data-name="dtp1-text"]',
            persianNumber: true,
            // onDayClick: function () {
            //     sendConfirm(id, 5);
            // }
        });
    }

    function sendConfirm(id, index) {
        let date = $('#dateOfPayment').val();
        let pay = payMethods[index];
        if (date)
            pay = pay + ' ' + date;
        let note = $('#send-note').val();
        if (note)
            pay = pay + ' - یادداشت: ' + note;
        $(".dialogs").dialog('destroy').remove();
        $.post('confirm_invoice/' + id, {_token: token, confirm: index, pay: pay})
            .done(res => {
                orders[id] = res;
                $('#view_order_' + id).parent().html(operations(orders[id]));
                $('#state_' + id).parent().html(createdTime(orders[id]));
            })
    }

    function cancelInvoice(id, element) {
        if (confirm('آیا از حذف کردن فاکتور مطمئن هستید؟')) {
            $.post('cancel_invoice/' + id, {_token: token})
                .done(res => {
                    orders[id] = res;
                    $('#view_order_' + id).parent().html(operations(orders[id]));
                    $('#state_' + id).parent().html(createdTime(orders[id]));
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
                                    totalPages = 1;
                                    firstPageItems = 40;
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

