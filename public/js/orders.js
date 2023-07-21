let token;
let table;
let orders, users, isAdmin;
let ids = [];
$(() => {
    token = $('input[name=_token]').val();
    $("#deleted_orders").checkboxradio();
    get_data();
})

function get_data() {
    let token = $('input[name=_token]').val();
    $.post('get_orders', {_token: token})
        .done(res => {
            orders = {};
            res[0].forEach(order => {
                orders[order.id] = order;
            })
            users = {};
            $('#user').html('<option value="all" selected>همه</option>');
            res[1].forEach(user => {
                users[user.id] = user;
                $('#user').append(`<option value="${user.id}">${user.name}</option>`)
            })
            isAdmin = res[2];
            ids = [];
            prepare_data();
            $('.main_check').prop('checked', false);
        })
}

function prepare_data() {
    let res = [];
    let counter = 0;
    let user = $('#user option:selected').val() || 'all';
    let deleted = $("#deleted_orders").prop('checked');

    $.each(orders, (id, row) => {
        if (user !== 'all' && user != row.user_id)
            return
        if (deleted ^ !!row.deleted_at)
            return
        let viewOrder = `<i class="fa fa-eye btn btn-info" onclick="view_order(${id})"></i> `;
        let deleteOrder = `<i class="fa fa-trash-alt btn btn-danger" onclick="delete_order(${id})" title="حذف سفارش" ></i> `;
        let editOrder = `<a class="fa fa-edit btn btn-primary" href="edit_order/${id}" title="ویرایش سفارش"></a> `;
        let sendToTelegram = `<i class="fab fa-telegram-plane btn btn-info" onclick="sendToTelegram(${id})" title="ارسال به تلگرام"></i> `;
        let generatePDF = `<i class="fa fa-file-pdf btn btn-secondary" onclick="generatePDF(${id})" title="مشاهده PDF"></i> `;
        counter++;
        res.push([
            `<input type="checkbox" class="orders_checkbox" onclick="list_ids()" order_id="${id}">`,

            counter,

            row.name,

            users[row.user_id].name,

            (row.orders.length > 30) ?
                row.orders.substr(0, 30) + ' ...'
                :
                row.orders,

            createdTime(row),


            viewOrder + (deleted ? '' : (row.state > 0 ? '' : deleteOrder + editOrder) + /*sendToTelegram +*/ (isAdmin?generatePDF:'') ),

            row.address,

            row.desc,

            row.orders,

            row.phone,

            row.zip_code,

            id

        ])
    })
    create_table(res);
}

function create_table(data) {
    if (table){
        table.clear();
        table.rows.add(data);
        table.draw();
        // table.destroy();
    }else{
        let hideRows = isAdmin ? [1, 7, 8, 9, 10, 11, 12] : [0 ,1, 3, 7, 8, 9, 10, 11, 12]
        table = $('table').DataTable({
            columns: [
                {title: '<input type="checkbox" onclick="all_ids(this)" class="main_check">'},
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
            "columnDefs": [
                {
                    "targets": [0, 1, 5, 6],
                    "searchable": false
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
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }
            }
        });
    }

}

function delete_order(id) {
    $.post('delete_order/' + id, {_token: token})
        .done(res => {
            $.notify(res, 'info');
            get_data();
        })
}

function list_ids() {
    setTimeout(() => {
        ids = [];
        $.each($('.orders_checkbox:checked'), (key, value) => {
            ids.push($(value).attr('order_id'))
        })
    }, 100)
}

function all_ids(box) {
    setTimeout(() => {
        $('.orders_checkbox').prop('checked', false);
        if (box.checked)
            $('.orders_checkbox').prop('checked', true);
        list_ids()
    }, 100)
}

function view_order(id) {
    let order = orders[id]
    let paymentMethods ={
        credit: 'اعتباری',
        receipt: 'رسید واریز',
        onDelivery: 'پرداخت در محل',
        admin: 'ادمین',
    }
    let deliveryMethods ={
        peyk: 'پیک',
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
    <span>مبلغ کل:</span> <b>${num(order.total)}</b> <b> تومان</b> <br>
    <span>پرداختی مشتری:</span> <b>${num(order.customerCost)}</b> <b> تومان</b> <br>
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

function generatePDF(id) {
    let order = orders[id];
    let dialog = label_text(order);
    let opt = {
        margin: 1.2,
        image: {type: 'jpeg', quality: 1},
        filename: order.name + '_' + order.id + '.pdf',
        jsPDF: {format: [60, 30], unit: 'cm', orientation: 'l'}
    };
    html2pdf()
        .set(opt)
        .from(dialog)
        .save();
}

function generatePDFs() {
    if (ids.length === 0) {
        $.notify('ابتدا باید سفارشات مورد نظر را انتخاب کنید', 'error')
        return
    }
    let dialog = [];
    ids.forEach(id => {
        let order = orders[id]
        dialog.push(label_text(order));
    })
    dialog = dialog.join('<i class="breakhere"></i>')//
    let opt = {
        margin: 1.2,
        image: {type: 'jpeg', quality: 1},
        filename: 'سفازشات.pdf',
        pagebreak: {after: '.breakhere'},
        jsPDF: {format: [60, 30], unit: 'cm', orientation: 'l'}
    };
    html2pdf()
        .set(opt)
        .from(dialog)
        .save();
}

function fix_persian(text) {

    text = text.replace(/[آ-ی]+/g, function (x) {
        return `<b>${x}</b>`
    })
    return text;


    let number = new RegExp('[0-9۰-۹\/\.]+', 'g');
    text = text.replace(number, function (x) {
        return x + ' '
    });
    text = text.replace(new RegExp('\\\/[^0-9۰-۹]', 'g'), function (x) {
        return "</b>" + x + "<b>"
    })
    let symbols = ["\\\\", "\\\,", "\\\.[^0-9۰-۹]", "\\\+", "\\\-", "\\\:", "\\\_", "\\\#", "\\\@", "\\\(", "\\\)", "\\\{", "\\\}", "\\\[", "\\\]", "\\\،", "\\\$", "\\\|"];
    symbols.forEach(symbol => {
        let pattern = new RegExp(symbol, 'g');
        text = text.replace(pattern, function (x) {
            return " <b> " + x + " </b> "
        })
    })

    text = text.replace(/\(/g, '^^')
    text = text.replace(/\)/g, ' ( ')
    text = text.replace(/\^\^/g, ' ) ')

    return text
}

function label_text(order) {
    let text = `
<div class="printed">
    <span>نام و نام خانوادگی </span>: <b>${fix_persian(order.name)}</b> <br>
    <span>شماره تماس </span>: <b>${order.phone}</b>&nbsp;&nbsp;&nbsp; `
        +
        (order.zip_code ? `<span>کد پستی </span>: <b>${order.zip_code}</b><br>` : '<br>')
        +
    `<span>آدرس </span>: <b>${fix_persian(order.address)}</b> <br>`
        +
        (order.orders ? `<span>سفارشات </span>: <b>${fix_persian(order.orders)}</b>` : '')
        +
        (order.desc ? `<br><span>توضیحات </span>: <b>${fix_persian(order.desc)}</b></div>` : '</div>')
    ;
    if ((order.address.length + order.orders.length + (order.desc ? order.desc.length : 0)) > 260) {
        return `<div class="long-text">${text}</div>`;
    }

    if ((order.address.length + order.orders.length + (order.desc ? order.desc.length : 0)) < 170) {
        return `<div class="short-text">${text}</div>`;
    }
    return text;
}

function sendToTelegram(id) {
    $.post('send_to_telegram/' + id, {_token: token})
        .done(res => {
            $.notify(res, 'info');
        })
}

function createdTime(order) {
    let timestamp = new Date(order.created_at);
    timestamp = timestamp.getTime() + 1000 * 3600 * 3.5;
    let diff = (Date.now() - timestamp) / 1000;
    let res = `<span class="d-none">${timestamp}</span>`
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
    if (order.state == 0) {
        res = `<span class="btn btn-secondary" onclick="change_state(${order.id})">${res} <i id="row-state-${order.id}"></i></span>`
    } else if (order.state == 1) {
        res = `<span class="btn btn-info" onclick="change_state(${order.id})">${res} <i id="row-state-${order.id}" class="fas fa-check"></i></span>`
    } else if (order.state > 1) {
        res = `<span class="btn btn-success" onclick="change_state(${order.id})">${res} <i id="row-state-${order.id}" class="fas fa-check-double"></i></span>`
    }

    return res;
}

function change_state(id) {
    if(!isAdmin)
        return
    $.post('increase_state/' + id, {_token: token})
        .done(res => {
            orders[id].state = res;
            if (res == 0) {
                $('#row-state-' + id).removeClass().parent().removeClass().addClass('btn btn-secondary')
            } else if (res == 1) {
                $('#row-state-' + id).removeClass().addClass('fas fa-check').parent().removeClass().addClass('btn btn-info')
            } else if (res > 1) {
                $('#row-state-' + id).removeClass().addClass('fas fa-check-double').parent().removeClass().addClass('btn btn-success')
            }
            prepare_data();
        })
}
