<script>

    let token = "{{ csrf_token() }}";
    let isAdmin ={{$admin ? 'true' : 'false'}};
    let userId = {{$userId}};
    let table;
    let users = {!!json_encode($users)!!};
    let orders = {!!json_encode($orders)!!};
    let ids;
    let deleted, user = 'all';
    let role = users[userId].role;
    $(() => {
        $("#deleted_orders").checkboxradio();
        prepare_data();
    });

    function prepare_data() {
        ids = [];
        let res = [];
        let counter = 0;

        $.each(orders, (id, order) => {
            if (user !== 'all' && user != order.user_id)
                return
            if (deleted ^ !!order.deleted_at)
                return
            if (role != 'admin' && order.paymentMethod == 'admin')
                return
            counter++;
            res.push([
                `<input type="checkbox" class="orders_checkbox" onclick="ids.includes(${id})?removeFromIds(${id}):ids.push(${id})">`,

                counter,

                order.name,

                users[order.user_id].name,

                (order.orders.length > 30) ?
                    order.orders.substr(0, 30) + ' ...'
                    :
                    order.orders,

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
            let hideRows = isAdmin ? [1, 7, 8, 9, 10, 11, 12] : [0, 1, 3, 7, 8, 9, 10, 11, 12]
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
    <span>نحوه ارسال:</span> <b>${deliveryMethods[order.deliveryMethod]}</b> <br>
    <span>توضیحات:</span> <b>${order.desc ? order.desc : ''}</b> <br>
    <span>زمان ثبت:</span> <b>${order.created_at_p}</b> <br>
    <span>زمان آخرین ویرایش:</span> <b>${order.updated_at_p}</b> <br>` +
            (order.deleted_at_p ?
                    `<span>زمان حذف:</span> <b>${order.deleted_at_p}</b> <br>`
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
        timestamp = timestamp.getTime(); //+ 1000 * 3600 * 3.5
        let diff = (Date.now() - timestamp) / 1000;
        let res = `<span class="d-none">${timestamp}</span>`;
        if (diff < 60) {
            res += `<span>لحظاتی پیش</span>`

        } else if (diff < 3600) {
            let minute = Math.floor(diff / 60);
            res += `<span>${minute} دقیقه قبل </span>`

        } else if (diff < 3600 * 24) {
            let hour = Math.floor(diff / 3600);
            res += `<span>${hour} ساعت قبل </span>`
        } else if (diff < 3600 * 24 * 30) {
            let day = Math.floor(diff / (3600 * 24));
            res += `<span>${day} روز قبل </span>`
        } else {
            let month = Math.floor(diff / (3600 * 24 * 30));
            res += `<span>${month} ماه قبل </span>`
        }
        if (!order.state || order.deleted_at) {
            res = `<span class="btn btn-secondary" onclick="change_state(${order.id},this)">${res}</span>`
        } else if (order.state) {
            if (order.admin !== userId) {
                res = `<span class="btn btn-info" onclick="change_state(${order.id}),this">${res} <i class="fas fa-check"></i></span>`
            } else {
                res = `<span class="btn btn-success" onclick="change_state(${order.id},this)">${res} <i class="fas fa-check"></i></span>`
            }
        }

        return res;
    }

    function operations(order) {
        let creatorRole = users[order.user_id].role
        let id = order.id;
        let cancelInvoice = `<a class="fa-regular fa-xmark btn btn-danger" onclick="cancelInvoice(${id},this)" title=" رد فاکتور"> </a> `;
        let viewOrder = `<i class="fa fa-eye btn btn-info" onclick="view_order(${id})"></i> `;
        let deleteOrder = `<i class="fa fa-trash-alt btn btn-danger" onclick="delete_order(${id},this)" title="حذف سفارش" ></i> `;
        let editOrder = `<a class="fa fa-edit btn btn-primary" href="edit_order/${id}" title="ویرایش سفارش"></a> `;
        let generatePDF = `<i class="fa fa-file-pdf btn btn-secondary" onclick="generatePDF(${id})" title="دانلود لیبل"></i> `;
        let confirmInvoice = `<a class="fa fa-check btn btn-success" onclick="confirmInvoice(${id},this)" title=" تایید فاکتور"></a> `;
        let invoice = `<a class="fa fa-file-invoice-dollar btn btn-secondary" onclick="invoice(${id})" title=" فاکتور"></a> `;
        let preInvoice = `<a class="fa fa-file-invoice-dollar btn btn-secondary" onclick="invoice(${id})" title="پیش فاکتور"></a> `;

        let res = viewOrder;

        if (deleted)
            return res;

        if (!order.state && (creatorRole === 'user' || order.paymentMethod === 'admin'))
            res += deleteOrder + editOrder;

        if ((role === 'print' || role === 'admin') && order.state)
            res += generatePDF

        if (creatorRole === 'admin' && role === 'admin') {
            if (order.paymentMethod === 'admin') {
                res += preInvoice;
                res += confirmInvoice;
            } else if (!order.state) {
                res += invoice;
                res += cancelInvoice;
            } else
                res += invoice;
        }
        return res;
    }

    function delete_order(id, element) {
        $.post('delete_order/' + id, {_token: token})
            .done(res => {
                console.log(res);
                $.notify(res[0], 'info');
                orders[id] = res[1];
                if (res[1].deleted_at) {
                    ids.includes(id) ? removeFromIds(id) : '';
                    $(element).parent().parent().remove()
                }
            })
    }

    @if($admin)

    function change_state(id, element) {
        if (!isAdmin || (orders[id].admin !== userId && orders[id].state))
            return
        if (orders[id].paymentMethod === 'admin' && users[orders[id].user_id].role == 'admin') {
            alert('ابتدا فاکتور باید تایید شود!');
            return
        }
        $.post('change_state/' + id, {_token: token})
            .done(res => {
                orders[id].state = res[0];
                orders[id].admin = res[1];
                $(element).parent().next().html(operations(orders[id]));
                $(element).parent().html(createdTime(orders[id]))
            })
    }

    function generatePDF(id) {
        $.post('pdf/' + id, {_token: token})
            .done(res => {

            })
    }

    function generatePDFs() {
        if (ids.length === 0) {
            $.notify('ابتدا باید سفارشات مورد نظر را انتخاب کنید', 'error')
            return
        }
        $.get('pdfs/' + ids.toString())
            .done(res => {

            })
    }

    function removeFromIds(id) {
        const index = ids.indexOf(id);
        if (index > -1) {
            ids.splice(index, 1);
        }
    }

    function confirmInvoice(id, element) {
        if (!isAdmin || orders[id].state)
            return
        $.post('confirm_invoice/' + id, {_token: token})
            .done(res => {
                orders[id] = res;
                $(element).parent().html(operations(orders[id]));
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

    function invoice(id) {
        $.post('/invoice/' + id, {_token: token})
            .done(res => {
                // $.each(res,(index , content)=>{
                //     console.log(content);
                //     $('#invoice-wrapper').html(content[0]);
                //     domtoimage.toJpeg($('#invoice')[0], {width: 2100, height: 2970})
                //         .then(function (dataUrl) {
                //             let link = document.createElement('a');
                //             link.download = content[1] + '.jpg';
                //             link.href = dataUrl;
                //             link.click();
                //             $('#invoice-wrapper').html('');
                //         });
                // })
                $('#invoice-wrapper').html(res[0][0]);
                domtoimage.toJpeg($('#invoice')[0], {width: 2100, height: 2970})
                    .then(function (dataUrl) {
                        let link = document.createElement('a');
                        link.download = res[0][1] + '.jpg';
                        link.href = dataUrl;
                        link.click();
                        $('#invoice-wrapper').html('');
                        if(res.length>1) {
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

</script>

