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
                dialog = Dialog(res);
            })
    }

    function num(x) {
        return numeral(x).format(0, 0);
    }

    function Dialog(text){
        return dialog = $(text).dialog({
            modal: true,
            open: () => {
                $('.ui-dialog-titlebar-close').hide();
                $('.ui-widget-overlay').bind('click', function () {
                    dialog.remove()
                });
            }
        });
    }

    $(function () {

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


    })

    function FarsiDate(uDate) {
        return new Intl.DateTimeFormat('fa-IR', {
            dateStyle: "short",
            timeStyle: "medium",
        }).format(uDate);
    }

</script>
