<script>

    $(function () {
        setTimeout(function () {
            $("#errors").hide()
        }, 10000);
        $("#name").autocomplete({
            source: Object.keys(customers),
            select: function (event, ui) {
                let customer = customers[ui.item.value];
                setCustomerInfo(customer);
            }
        });
        $("#addToCustomers").checkboxradio();
    });


    let customers = {!!json_encode($customers)!!};
    let customersId = {!!json_encode($customersId)!!};
    let paymentMethod = "credit";
    let deliveryMethod = "peyk";
    let product_table;
    let products = {!!json_encode($products)!!};
    let cart = {!!json_encode($cart)!!};
    let submit = false;
    $(function () {
        startEditProccess();
        refreshProducts()
        $("input[type=radio]").checkboxradio();
        product_table = $('#product-table').DataTable({
            "autoWidth": false,
            "paging": false,
            order: [[3, "desc"]],
        });

        $('input[name=paymentMethod]').click(paymentAction);
        $('input[name=deliveryMethod]').click(deliveryAction);
    });

    function paymentAction() {
        paymentMethod = $('input[name="paymentMethod"]:checked').val();
        $('input[name=paymentMethod]').val(paymentMethod);
        $('#receiptPhoto,#customerDiscount,label[for=customerDiscount]').hide();
        if (paymentMethod == 'receipt') {
            $('#receiptPhoto').show();
        } else if (paymentMethod == 'onDelivery') {
            $('#customerDiscount,label[for=customerDiscount]').show();
        }
        refreshProducts()
    }

    function deliveryAction() {
        $('.deliveryDesc').hide();
        deliveryMethod = $('input[name="deliveryMethod"]:checked').val();
        $('input[name="deliveryMethod"]').val(deliveryMethod);
        $('#' + deliveryMethod).next().next().show();
        refreshProducts();
    }

    function refreshProducts() {
        let total = 0, Total = 0;
        let hasProduct = false;
        let ordersText = ''; //عبارت مربوط به قسمت محصولات
        let ordersListText = ''; // عبارت مربو به فاکتور سفیران
        let invoiceOrders = ''; //مربوط به پیش فاکتور
        let ii = 1;
        $.each(cart, (id, number) => {
            if (number > 0) {
                let price = products[id].priceWithDiscount * number; //قیمت با تخفیف
                let Price = products[id].price * number;  //قیمت بدون تخفیف
                $('#product_' + id).val(number);
                ordersText = ordersText.concat(products[id].name + ' ' + number + ' عدد ' + deleteBTN(id) + '<br>');
                invoiceOrders = invoiceOrders.concat(
                    `<tr class="invoice-list">
                    <td>${ii}</td>
                    <td>${products[id].name}</td>
                    <td>${number}</td>
                    <td>${priceFormat(products[id].price)}</td>
                    <td>${products[id].coupon}</td>
                    <td>${priceFormat(products[id].priceWithDiscount)}</td>
                    <td>${priceFormat(price)}</td>
                </tr>`);
                ordersListText = ordersListText.concat('<li>' + products[id].name + ' ' + number + ' عدد ' + deleteBTN(id) + ': ' + num(price) + '</li>');

                total += price; //جمع قیمت با تخفیف
                Total += Price; // جمع قیمت بدون تخفیف
                hasProduct = true;
                ii++;
            } else {
                $('#product_' + id).val('');
                delete cart[id];
            }
        })
        $('#orders').html(ordersText);
        $('.invoice-list').remove();
        $('#invoice-head').after(invoiceOrders);
        $('#invoice-total-no-discount').html(priceFormat(Total));
        $('#invoice-total-discount').html(priceFormat(Total - total));
        $('#invoice-total-with-discount').html(priceFormat(total));

        @if(!$admin)
        $('#order-list').html(ordersListText)
        let deliveryCost = 0;
        if (Total < {{$settings->freeDelivery}} || {{$id}} == 10)
            if (deliveryMethod == 'peyk')
                deliveryCost = {{$settings->peykCost}};
            else if (deliveryMethod == 'post')
                deliveryCost = {{$settings->postCost}};
        $('#deliveryCost').html(num(deliveryCost));
        $('#cartSum').html(num(total));
        $('#total').html(num(total + deliveryCost));
        $('#onDeliveryMode').hide();

        if (paymentMethod == 'onDelivery') {
            let customerDiscount = $('#customerDiscount').val()
            $('#onDeliveryMode').show();
            let customerTotal = Math.round(Total * (100 - customerDiscount) / 100 + deliveryCost)
            $('#customerTotal').html(num(customerTotal));
            let safirShare = customerTotal - total - deliveryCost;
            $('#safirShare').html(num(safirShare));
        }
        if (!hasProduct)
            $('#paymentDetails').hide();
        else
            $('#paymentDetails').show();
        @endif

    }

    function deleteBTN(id) {
        @if($admin || !$edit)
            return '<span class="btn btn-danger mx-1" ' +
            'onclick="$(`#product_' + id + '`).val(0);cart[' + id + '] =0 ;refreshProducts()">X</span>'
        @else
            return '';
        @endif
    }

    function priceFormat(price) {
        return (+(+price).toFixed()).toLocaleString('en-US');
    }

    function num_plus(id) {
        let n = +$('#product_' + id).val() + 1;
        n = Math.min(+n, products[id].quantity);
        $('#product_' + id).val(n);
        cart[id] = n;
        refreshProducts();
    }

    function num_minus(id) {
        let n = +$('#product_' + id).val() - 1;
        n = Math.max(0, +n)
        $('#product_' + id).val(n);
        cart[id] = n;
        refreshProducts();
    }

    function num_product(id, value) {
        value = Math.max(0, +value);
        value = Math.min(value, products[id].quantity);
        value = Math.round(value);
        $('#product_' + id).val(value);
        cart[id] = value;
        if (value == 0) {
            $('#product_' + id).val('');
            delete cart[id];
        }
        refreshProducts();
    }

    function customerFind() {
        let id = $('#customerId').val();
        setCustomerInfo(customersId[id])
    }

    function setCustomerInfo(customer) {
        $('#customerId').val(customer.id);
        $("#name").val(customer.name)
        $('#phone').val(customer.phone);
        $('#address').val(customer.address);
        $('#zip_code').val(customer.zip_code);
    }

    function beforeSubmit() {
        if (!Object.keys(cart).length) {
            alert('محصولی انتخاب نشده است');
            return false;
        }
        @if(!$admin)
            return true;
        @else
        if (submit)
            return submit
        $('#invoice-name').text($('#name').val());
        $('#invoice-phone').text($('#phone').val());
        $('#invoice-address').text($('#address').val());
        $('#invoice-zip_code').text($('#zip_code').val());
        if (!$('#zip_code').val())
            $('#invoice-zip').text('');
        $('#invoice-description').text($('#desc').val());
        domtoimage.toJpeg(document.getElementById('invoice'), {width: 2100, height: 2970})
            .then(function (dataUrl) {
                var link = document.createElement('a');
                link.download = 'invoice.jpeg';
                link.href = dataUrl;
                link.click();
                submit = true;
                $('#form').submit();
            });
        return false;
        @endif
    }

    function changeDiscount(id, value) {
        value = Math.min(100, +value);
        value = Math.max(0, +value);
        value = Math.round(value);
        $('#discount_' + id).val(value);
        products[id].coupon = value;
        products[id].priceWithDiscount = (products[id].price * (100 - products[id].coupon) / 100);
        $("#price_" + id + " .discount").html(priceFormat(products[id].priceWithDiscount));
        refreshProducts();
    }

    function startEditProccess() {
        @if($errors->count())
        @if(!$admin)
        $('#{{old("paymentMethod")}}').click();
        $('#{{old("deliveryMethod")}}').click();
        @endif
        @elseif($edit)
        $('#name').val("{{$order->name}}");
        $('#phone').val("{{$order->phone}}");
        $('#address').val("{{$order->address}}");
        $('#zip_code').val("{{$order->zip_code}}");
        $('#desc').val("{{$order->desc}}");
        $('#customerId').val("{{$order->customer_id}}");
        deliveryMethod = "{{$order->deliveryMethod}}";
        paymentMethod = "{{$order->paymentMethod}}";

        @if($order->paymentMethod == 'receipt')
        $('#edit-payment-method').html(`<p>نحوه پرداخت: کارت به کارت با رسید بانکی .</p>
            <a href="/receipt/{{$order->receipt}}" target="_blank"><img
                style="max-width: 200px; max-height: 200px"
                src="/receipt/{{$order->receipt}}"></a>`);
        @elseif($order->paymentMethod == 'credit')
        $('#edit-payment-method').html(`<p>نحوه پرداخت اعتباری است.</p>`);
        @elseif($order->paymentMethod == 'onDelivery')
        $('#edit-payment-method').html(`<p>نحوه پرداخت ، پرداخت در محل است.</p>`);
        @endif
        @endif
    }

</script>

