<script>

    function invoice(id) {
        let randSTR = makeid(3);
        let printInvoice = (page, index) => {
            $('#invoice-wrapper' + index).html(page);
            domtoimage.toJpeg($('#invoice' + index)[0], {width: 2100, height: 2970})
                .then(function (dataUrl) {
                    let link = document.createElement('a');
                    link.download = id + `_` + index + `_` + randSTR + '.jpg';
                    link.href = dataUrl;
                    link.click();
                    $('#invoice-wrapper' + index).html('');
                })
        }

        $.post('/invoice/' + id, {
            _token: token,
            totalPages: 1,
            pageContent: 'all',
        }).done(res => {
            $('#invoice-wrapper1').html(res[0]);
            if ($('#invoice-content')[0].offsetHeight < 2900) {
                printInvoice(res[0], 1);
            } else {
                $.post('/invoice/' + id, {
                    _token: token,
                }).done(res => {
                    $.each(res, (index, page) => {
                        printInvoice(page, index + 1, randSTR)
                    })
                })
            }
        })
    }

    function view_order(id) {
        $.post('/viewOrder/' + id, {_token: token})
            .done(res => {
                dialog = Dialog(res);
            })
    }

    function view_withdrawal(id) {
        $.post('/Withdrawal/view/' + id, {_token: token})
            .done(res => {
                dialog = Dialog(res);
            })
    }

    function view_comment(id) {
        $.post('/viewComment/' + id, {_token: token})
            .done(res => {
                dialog = Dialog(res);
            })
    }

    function view_deposit(id) {
        $.post('/customerDeposit/view/' + id, {_token: token})
            .done(res => {
                dialog = Dialog(res);
            })
    }

    function addComment(id) {
        $('#commentForm').submit(function (e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: '/addComment/' + id,
                data: new FormData(this),
                processData: false,
                contentType: false,
                headers: {
                    "Accept": "application/json"
                }
            }).done(function (res) {
                if (res === "ok") {
                    $.notify("با موفقیت ذخیره شد.", "success");
                    dialog.remove();
                }
            }).fail(function () {
                $.notify('خطایی رخ داده است.', 'warn');
            });
        });
        $('#commentForm').submit();
    }

    function num(x) {
        return numeral(x).format(0, 0);
    }

    function Dialog(text) {
        typeof dialog !== 'undefined' && dialog.remove();
        dialog = $(text).dialog({
            modal: true,
            open: () => {
                $('.ui-dialog-titlebar-close').hide();
                $('.ui-widget-overlay').bind('click', function () {
                    dialog.remove()
                });
            },
            show: {
                effect: "blind",
                duration: 500
            },
            width: '500',
        });
        return dialog;
    }

    $(function () {
        let $loading = $('#loadingDiv').hide();
        $(document)
            .ajaxStart(function () {
                $loading.show();
            })
            .ajaxStop(function () {
                $loading.hide();
            });
        priceInput();
    })

    function priceInput() {
        $(".price-input").on("keyup", function (event) {
            // When user select text in the document, also abort.
            var selection = window.getSelection().toString();
            if (selection !== '') {
                return;
            }
            // When the arrow keys are pressed, abort.
            if ($.inArray(event.keyCode, [38, 40, 37, 39]) !== -1) {
                return;
            }
            var $this = $(this);
            // Get the value.
            var input = $this.val();
            input = input.replace(/[\D\s\._\-]+/g, "");
            input = input ? parseInt(input, 10) : 0;
            $this.val(function () {
                return (input === 0) ? "0" : input.toLocaleString("en-US");
            });
        });
        $(".price-input").each(function (event) {
            // When user select text in the document, also abort.
            var selection = window.getSelection().toString();
            if (selection !== '') {
                return;
            }
            // When the arrow keys are pressed, abort.
            if ($.inArray(event.keyCode, [38, 40, 37, 39]) !== -1) {
                return;
            }
            var $this = $(this);
            // Get the value.
            var input = $this.val();
            input = input.replace(/[\D\s\._\-]+/g, "");
            input = input ? parseInt(input, 10) : 0;
            $this.val(function () {
                return (input === 0) ? "0" : input.toLocaleString("en-US");
            });
        });
    }

    function FarsiDate(uDate) {
        return new Intl.DateTimeFormat('fa-IR', {
            dateStyle: "short",
            timeStyle: "medium",
        }).format(uDate);
    }

    function priceFormat(price) {
        return (+(+price).toFixed()).toLocaleString('en-US');
    }

    function makeid(length) { //generate random string
        let result = '';
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        const charactersLength = characters.length;
        let counter = 0;
        while (counter < length) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
            counter += 1;
        }
        return result;
    }

    let language = {
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

    let token = "{{ csrf_token() }}";
</script>
