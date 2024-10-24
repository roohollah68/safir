<script>

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

    function view_order(id) {
        $.post('/viewOrder/' + id, {_token: token})
            .done(res => {
                dialog && dialog.remove();
                dialog = Dialog(res);
            })
    }

    function view_comment(id) {
        $.post('/viewComment/' + id, {_token: token})
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

</script>
