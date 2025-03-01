<script>


    let admin = {{$admin ? 'true' : 'false'}};
    let safir = {{$safir ? 'true' : 'false'}};
    let table;
    let users = {!!json_encode($users)!!};
    let orders = {!!json_encode($orders)!!};
    let ids;
    let showDeleted, printWait, confirmWait, counterWait, proccessWait, sent, delivered, COD, refund, user = 'all',
        warehouseId = 'all';
    let notsent = false;
    let changeOrdersPermit = !!'{{$User->meta('showAllOrders')}}';
    let safirOrders = true, siteOrders = true, adminOrders = true;
    let dtp1Instance;
    let websites = {!!json_encode(config('websites'))!!};
    let dialog, reloadId;
    $(() => {
        $(".checkboxradio").checkboxradio();
        prepare_data();
        const var1 = new mds.MdsPersianDateTimePicker($('#fromDate')[0], {
            targetTextSelector: '#fromDate',
        });
        const var2 = new mds.MdsPersianDateTimePicker($('#toDate')[0], {
            targetTextSelector: '#toDate',
        });
        reloadId = setInterval(reload, 30000)
    });

    function reload() {
        $.ajax('/orders/reload', {
            method: "POST",
            global: false,
            data: {_token: token}
        })
            .done(res => {
                $.each(res, (id, order) => {
                    if (orders[id]) {
                        updateRow(order);
                    } else {
                        let row = singleRow(order);
                        if (row) {
                            table.row.add(row).draw();
                        }
                    }
                    orders[id] = order;
                });
            });
    }

    function prepare_data() {
        ids = [];
        let res = [];
        $.each(orders, (id, order) => {
            let row = singleRow(order);
            if (row)
                res.push(row)
        });
        create_table(res);
    }

    function singleRow(order) {
        let id = order.id;
        if (user !== 'all' && +user !== order.user_id)
            return
        if (showDeleted ^ !!order.deleted_at)
            return
        if (sent && order.state != 10)
            return
        if (delivered && order.state != 11)
            return
        if (confirmWait && order.confirm)
            return
        if (counterWait && (order.counter !== 'waiting' || !order.confirm || order.state))
            return
        if (printWait && (!order.confirm || order.state || order.counter !== 'approved'))
            return
        if (proccessWait && (order.state > 4 || order.state < 1))
            return
        if (notsent && !NotSent(order))
            return;
        if (+warehouseId !== +order.warehouse_id && warehouseId !== 'all')
            return;
        if (COD && order.paymentMethod !== 'cod' && order.paymentMethod !== 'پرداخت در محل' && order.paymentMethod !== 'onDelivery')
            return;
        if (refund && order.total >= 0)
            return;
        if (order.user.role === 'admin' && !adminOrders) {
            return
        }
        let isWebsite = websites[order.user_id];
        if (order.user.role === 'user' && !isWebsite && !safirOrders)
            return
        if (isWebsite && !siteOrders)
            return
        return [
            !!order.deleted_at ? '' : `<input type="checkbox" class="orders_checkbox" onclick="ids.includes(${id})?removeFromIds(${id}):ids.push(${id})">`,

            id,

            order.customer_id ? '<a href="/customer/transaction/' + order.customer_id + '">' + order.name + '</a>' : order.name,

            order.user.name + ((isWebsite && order.website) ? `(${order.website.website_id})` : ''),

            `<span title="${order.orders}">` + ((order.orders.length > 30) ? order.orders.substr(0, 30) + ' ...' : order.orders) +`</span>`,

            createdTime(order),

            operations(order),

            `<span id='orderCondition_${id}'>` + orderCondition(order) + '</span>',

            order.address,

            order.desc,

            order.orders,

            order.phone,

            order.zip_code,
        ];
    }
    function NotSent(order) {
        const lastUpdate = new Date(order.updated_at).getTime();
        const processingTime = Date.now() - lastUpdate;
        return (order.state === 1 || order.state === 2) && processingTime > 172800000;
    }
    function create_table(data) {
        if (table) {
            table.clear();
            table.rows.add(data);
            table.draw();
        } else {
            let hideCols = (changeOrdersPermit) ? [8, 9, 10, 11, 12] : [0, 3, 8, 9, 10, 11, 12]
            table = $('#main-table').DataTable({
                columns: [
                    {title: " "},
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
                ],
                columnDefs: [
                    {
                        targets: [0, 5, 6],
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
                order: [[1, "desc"]],
                // dom: 'lBfrtip',
                // buttons: [
                //     'excelHtml5',
                // ],
                language: language,
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
            text += `لحظاتی پیش`;
        } else if (diff < 3600) {
            let minute = Math.floor(diff / 60);
            text += `${minute} دقیقه قبل `
        } else if (diff < 3600 * 24) {
            let hour = Math.floor(diff / 3600);
            text += `${hour} ساعت قبل `
        } else if (diff < 3600 * 24 * 30) {
            let day = Math.floor(diff / (3600 * 24));
            text += `${day} روز قبل `
        } else {
            let month = Math.floor(diff / (3600 * 24 * 30));
            text += `${month} ماه قبل `
        }

        if (order.deleted_at) {
            res = `<span class="btn btn-secondary">${text}</span>`
        } else if (!order.state) {
            let btn = order.confirm ? (order.counter === 'waiting' ? 'info' : 'secondary') : 'primary';
            res = `<span class="btn btn-${btn}" onclick="change_state(${order.id}, 1)">${text}</span>`
        } 
        //else if (order.state < 3) {
        //     res = `<span class="btn btn-warning" onclick="selectSendMethod(${order.id})">${text}<i class="fas fa-check"></i></span>`
        // } 
        else if (+order.state === 4) {
            res = `<span class="btn btn-danger" onclick="change_state(${order.id}, 0)">${text}<i class="fas fa-question"></i></span>`
        } else if (+order.state === 10) {
            res = `<span class="btn btn-success" onclick="confirm('آیا وضعیت سفارش به در انتظار پرینت تغییر کند؟')&&change_state(${order.id}, 0)">${text}<i class="fas fa-check-double"></i></span>`
        } else if (+order.state === 11) {
            res = `<span class="btn btn-delivered" onclick="confirm('آیا وضعیت سفارش به در انتظار پرینت تغییر کند؟')&&confirm(change_state(${order.id}, 0))">${text}<i class="fas fa-check-double"></i></span>`
        }
        else if ([1, 2].includes(+order.state)) {
        const isExpired = NotSent(order);
        const color = isExpired ? 'danger' : 'warning';
        const icon = isExpired ? 'clock' : 'check';
        
        res = `<span class="btn btn-${color}" onclick="selectSendMethod(${order.id})">
            ${text}<i class="fas fa-${icon}"></i></span>`;
        }
        console.log('Order:', order.id, 'State:', order.state, 'Updated:', order.updated_at);
        return timestamp + res;
    }

    function operations(order) {
        let id = order.id;
        let viewOrder = `<i id="view_order_${id}" class="fa fa-eye btn btn-info" onclick="view_order(${id})"></i> `;
        let viewComment = `<i id="view_comment_${id}" class="fa fa-comment btn btn-info" onclick="view_comment(${id})"></i> `;
        let deleteOrder = `<i class="fa fa-trash-alt btn btn-danger" onclick="delete_order(${id},this)" title="حذف سفارش" ></i> `;
        let changeWarehouse = `<i class="fa fa-warehouse btn btn-warning" onclick="changeWarehouse(${id})" title="تغییر انبار" ></i> `;
        let editOrder = `<a class="fa fa-edit btn btn-primary" href="edit_order/${id}" title="ویرایش سفارش"></a> `;
        let res = viewOrder + viewComment;
        if (order.deleted_at)
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
        // let excel = `<a class="fa fa-file-excel btn btn-outline-info" href="/orderExcel/${id}" title="خروجی اکسل"></a> `;

        if (!order.state && (!order.confirm || creatorRole === 'user'))
            res += deleteOrder;
        if ((!order.confirm && order.customer_id) || (creatorRole === 'user' && !order.state))
            res += editOrder + changeWarehouse;

        if (changeOrdersPermit && order.state)
            res += generatePDF

        // if (order.customer_id)
        //     res += excel

        if (creatorRole !== 'user' && order.state < 10) {
            if (order.confirm)
                res += cancelInvoice;
            else
                res += confirmInvoice;
        }
        // if (creatorRole !== 'user') {
        if (order.confirm)
            res += invoice;
        else
            res += preInvoice;
        // }

        @endif
            return res;
    }

    function orderCondition(order) {
        if (order.deleted_at)
            return 'حذف شده';
        if (order.state === 11)
            return 'تحویل داده شده';
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
        // if (order.state === 1 || order.state === 2)
        //     return 'در حال پردازش برای ارسال';
        if (order.state === 1 || order.state === 2) {
            if (NotSent(order)) {
                return 'ارسال نشده';
            } else {
                return 'در حال پردازش برای ارسال';
            }
        }
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

    @if($User->meta('changeOrderState'))

    function selectSendMethod(id) {
        let order = orders[id];
        if (!order.confirm) {
            alert('ابتدا فاکتور باید تایید شود!');
            return;
        }
        if (order.total < 0) {
            $.post('/set_send_method/' + id, {_token: token}
            ).done(function (res) {
                $.notify("با موفقیت ذخیره شد.", "success");
                order = res;
                change_state(id, 10)
            })
            return;
        }

        dialog = Dialog(`@include('orders.sendMethods')`);

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
                order = res;
                change_state(id, 10)
            })
        });
    }

    function change_state(id, state) {
        order = orders[id];
        if ((!order.confirm || order.counter !== "approved") && +order.state !== 4) {
            alert('ابتدا فاکتور باید تایید شود!');
            return;
        }
        $.post('/change_state/' + id + '/' + state, {
            _token: token,
        })
            .done(res => {
                order.state = +res[0];
                $.notify(res[1], 'info');
                updateRow(order);
            });
    }

    @endif

    @if($User->meta(['showAllOrders','editAllOrders','changeOrderState']))

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

    function generateExcels() {
        if (ids.length === 0) {
            $.notify('ابتدا باید سفارشات مورد نظر را انتخاب کنید', 'error')
            return
        }
        groupedIds = ids.reduce((r, e, i) =>(i % 3 ? r[r.length - 1].push(e) : r.push([e])) && r, []);
        $.each(groupedIds, function (index, ids) {
            $.post('/orders/excel',{
                _token : token,
                ids : ids,
            })
                .done(res=>{
                    let table1 = document.createElement('table');
                    $(table1).html(res[0]).attr('data-excel-name' , 'صورتحساب');
                    let table2 = document.createElement('table');
                    $(table2).html(res[1]).attr('data-excel-name' , 'اقلام صورتحساب');
                    // console.log(res);
                    let table2excel = new Table2Excel();
                    table2excel.export([table1,table2] , 'excel' + index);
                    // let div = document.createElement('div');
                    // $(div).html(res[0] + res[1])
                    // $(div).find('table').table2excel();
                    $(table1).remove();
                    $(table2).remove();
                })
        });

    }
    @endif

    @if($admin)

    function selectPayment(id) {
        let applyChanges = function (order) {
            $.notify("با موفقیت تائید شد.", "success");
            updateRow(order);
        }

        $.post('/confirmAuthorize/' + id, {_token: token})
            .done((order) => {
                console.log(order.total);
                if (order.total <= 0) {
                    applyChanges(order);
                    return;
                }
                dialog = Dialog(`@include('orders.paymentMethods')`);
                $(".checkboxradio").checkboxradio();
                dtp1Instance = new mds.MdsPersianDateTimePicker($('#payInDate')[0], {
                    targetTextSelector: '#dateOfPayment',
                    targetDateSelector: '[name="payInDate"]',
                    persianNumber: true,
                });

                $("#paymentForm").submit(function (e) {
                    e.preventDefault();
                    let data = {};
                    $('#paymentForm').serializeArray().forEach((value) => {
                        data[value.name] = value.value;
                    })
                    $.post('/orders/paymentMethod/' + id, data)
                        .done((order) => {
                            applyChanges(order);
                            dialog.remove();
                        });
                })
            });

    }

    function cancelInvoice(id) {
        if (confirm('آیا از حذف کردن فاکتور مطمئن هستید؟')) {
            $.post('cancel_invoice/' + id, {_token: token})
                .done(order => {
                    orders[id] = order;
                    updateRow(order)
                });
        }

    }
    @endif

    function filter() {
        let fromDate = $('#fromDate').val();
        let toDate = $('#toDate').val();
        let fromId = $('#fromId').val();
        let toId = $('#toId').val();
        $.post('/orders/reload', {
            _token: token,
            fromDate: fromDate,
            toDate: toDate,
            fromId: fromId,
            toId: toId
        })
            .done(res => {
                orders = res;
                prepare_data();
            })
        clearInterval(reloadId);
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
        });
    }

    function updateRow(order) {
        $('#view_order_' + order.id).parent().html(operations(order));
        $('#state_' + order.id).parent().html(createdTime(order));
        $('#orderCondition_' + order.id).html(orderCondition(order));
    }

</script>

