<script>
    let customers = {!!json_encode($customers)!!};
    $(function () {
        setTimeout(function () {
            $("#errors").hide()
        }, 5000);
        $("#name").autocomplete({
            source: Object.keys(customers),
            select: function (event, ui) {
                let customer = customers[ui.item.value];
                $('#phone').val(customer.phone);
                $('#address').val(customer.address);
                $('#zip_code').val(customer.zip_code);
            }
        });
        $("#addToCustomers").checkboxradio();
    });

    @if(!$order)
    let paymentMethod = `{{old('paymentMethod')?old('paymentMethod'):"credit"}}`;

    let deliveryMethod = `{{old('deliveryMethod')?old('deliveryMethod'):"peyk"}}`;

    let cart = {};
    let products = {!!json_encode($products)!!};
    $(function () {
        refreshProducts()
        $('#' + paymentMethod).click();
        $("#factor").checkboxradio();
        $("input[type=radio]").checkboxradio();
        $('#product-table').DataTable({
            "autoWidth": false,
            "paging":   false,
        });

        $('.plusOne').click(function () {
            $(this).next().val((i, n) => {
                return ++n
            })
            refreshProducts();
        })
        $('.minusOne').click(function () {
            $(this).prev().val((i, n) => {
                return Math.max(0, --n)
            })
            refreshProducts();
        })
        $('.product-number').change(function (e) {
            refreshProducts();
        })
        paymentAction();
        $('input[name=paymentMethod]').click(paymentAction);
        deliveryAction()
        $('input[name=deliveryMethod]').click(deliveryAction);
    });

    function paymentAction() {
        paymentMethod = $('input[name="paymentMethod"]:checked').val();
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
        $('#' + deliveryMethod).next().next().show();
        refreshProducts();
    }

    function refreshProducts() {
        $('.product-number').each(function () {
            cart[$(this).attr('product_id')] = $(this).val();
        });
        $('#order-list').html('')
        $('#orders').html('');
        let total = 0, Total = 0;
        $.each(cart, (id, number) => {
            if (number > 0) {
                let price = products[id].priceWithDiscount * number;
                let Price = products[id].price * number;

                $('#orders').append(products[id].name + ' ' + number + ' عدد ' + deleteBTN(id) + '| ');
                $('#order-list').append('<li>' + products[id].name + ' ' + number + ' عدد ' + deleteBTN(id) + ': ' + price + '</li>')

                total += price;
                Total += Price;
            }
        })
        let deliveryCost = 0;
        if (Total < {{$settings->freeDelivery}})
            if (deliveryMethod == 'peyk')
                deliveryCost = {{$settings->peykCost}};
            else if (deliveryMethod == 'post')
                deliveryCost = {{$settings->postCost}};
        $('#deliveryCost').html(deliveryCost);
        $('#cartSum').html(total);
        $('#total').html(total + deliveryCost);
        $('#onDeliveryMode').hide();

        if (paymentMethod == 'onDelivery') {
            let customerDiscount = $('#customerDiscount').val()
            $('#onDeliveryMode').show();
            let customerTotal = Math.round(Total * (100 - customerDiscount) / 100 + deliveryCost)
            $('#customerTotal').html(customerTotal);
            let safirShare = customerTotal - total - deliveryCost;
            $('#safirShare').html(safirShare);
        }
        if (total == 0)
            $('#paymentDetails').hide();
        else
            $('#paymentDetails').show();

    }

    function productMode() {
        @if($admin)
        if ($('#factor').prop('checked'))
            return;
        @endif

        $('#products').show();
        $('#formElements').hide();
    }

    function formMode() {
        $('#products').hide();
        $('#formElements').show();
    }

    function deleteBTN(id) {
        return '<span class="btn btn-danger mx-1" ' +
            'onclick="$(`#product_' + id + '`).val(0);refreshProducts()">X</span>'
    }
    @endif
</script>
<style>
    .D-none {
        display: none;
    }
</style>
