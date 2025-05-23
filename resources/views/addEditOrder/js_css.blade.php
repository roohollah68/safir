<script>
    let customers = {!!json_encode($customers)!!};
    let paymentMethod = "credit";
    let deliveryMethod = "peyk";
    let products = {!!json_encode($products)!!};
    let cart = {!!json_encode($cart)!!};
    let creatorIsAdmin = !!'{{$creatorIsAdmin}}';
    let edit = !!'{{$edit}}';
    let changeDiscountPermit = !!'{{$user->meta('changeDiscount')}}';
    let changePricePermit = !!'{{$user->meta('changePrice')}}';
    let table;
    let submitStatus = false;
    let official = !!'{{$order->official}}';

    $(function () {
        setTimeout(function () {
            $("#errors").hide()
        }, 10000);
        $(".checkboxradio").checkboxradio();
        $.each(cart, (id, number) => {
            if (number)
                addProduct(id);
            else
                delete cart[id];
        });
        refreshProducts();
        paymentAction();
    });

    @if($creatorIsAdmin || !$edit)
    $(function () {
        table = $('#product-table').DataTable({
            pageLength: 100,
            language: language,
        });

        let customersName = {};
        $.each(customers, (id, customer) => {
            customersName[customer.name] = id;
        })
        $("#name").autocomplete({
            source: Object.keys(customersName),
            select: function (event, ui) {
                let id = customersName[ui.item.value];
                setCustomerInfo(id);
                fetchOrderData(id);
            }
        });
        $("#name").change((data) => {
            let id = customersName[data.target.value];
            setCustomerInfo(id);
        });

        function fetchOrderData(customerId) {
            let warehouseId = $('input[name="warehouse_id"]').val();
            $.ajax({
                url: '{{ route('history') }}',
                method: 'GET',
                data: {
                    customer_id: customerId,
                    warehouse_id: warehouseId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('#history').html(response);
                },
                error: function (xhr) {
                    console.error('خطا در دریافت اطلاعات:', xhr.responseText);
                }
            });
        }
    });

    @endif

    function addProduct(id) {
        let product = products[id];
        if (cart[id]) {
            if ($('#product-' + id)[0])
                return;
        } else
            cart[id] = 1;
        $('#selected-product-table').show();
        if (product.discount != 100)
            product.price = product.priceWithDiscount * 100 / (100 - product.discount)
        else
            product.price = product.good.price;
        $('#product-form').append(`@include('addEditOrder.addToCart')`);
        priceInput();
        chaneOfficial();
    }

    function deleteProduct(id) {
        delete cart[id];
        $('#product-' + id).remove();
        if (!Object.keys(cart).length) {
            $('#selected-product-table').hide();
        }
        refreshProducts();
    }

    function paymentAction() {
        paymentMethod = $('input[name="paymentMethod"]:checked').val();
        $('.receiptPhoto,#customerDiscount,label[for=customerDiscount]').hide();
        if (paymentMethod === 'receipt') {
            $('.receiptPhoto').show();
        } else if (paymentMethod === 'onDelivery') {
            $('#customerDiscount,label[for=customerDiscount]').show();
        }
        refreshProducts()
    }

    function refreshProducts() {
        let total = 0, Total = 0, total_tax = 0, totalNum = 0;
        let hasProduct = false;
        $.each(cart, (id, number) => {
            if (number) {
                $(`input[name='cart[${id}][number]']`).val(number);
                let price = +$(`input[name='cart[${id}][price]']`).val().replaceAll(',', '');
                let discount = +$(`input[name='cart[${id}][discount]']`).val()
                let price_discount = price * (1 - discount / 100);
                let price_discount_tax = price_discount * ((official && products[id].good.vat) ? 1.1 : 1)
                $('#price_discount_tax_' + id).html(priceFormat(price_discount_tax ));
                $('#tax_' + id).html(priceFormat(price_discount_tax - price_discount));
                total += price_discount * number; //جمع قیمت با تخفیف;
                Total += price * number;  //قیمت بدون تخفیف;
                total_tax += price_discount_tax * number;
                totalNum += number;
                hasProduct = true;
            }
        })

        let deliveryCost = 0;
        if (!creatorIsAdmin && (Total < {{$settings->freeDelivery}} || '{{$user->id}}' === '10'))
            if (deliveryMethod === 'peyk')
                deliveryCost = {{$settings->peykCost}};
            else if (deliveryMethod === 'post')
                deliveryCost = {{$settings->postCost}};
            else if (deliveryMethod === 'peykeShahri')
                deliveryCost = {{$settings->peykeShahri}};
        $('#deliveryCost').html(num(deliveryCost));
        $('#cartSum').html(num(total_tax));
        $('#total').html(num(total_tax + deliveryCost));
        $('#total-num').html(totalNum);
        $('#total-discount').html(num(Total - total));

        if (paymentMethod === 'onDelivery') {
            let customerDiscount = $('#customerDiscount').val()
            $('#onDeliveryMode').show();
            let customerTotal = Math.round(Total * (100 - customerDiscount) / 100 + deliveryCost)
            $('#customerTotal').html(num(customerTotal));
            let safirShare = customerTotal - total_tax - deliveryCost;
            $('#safirShare').html(num(safirShare));
        } else
            $('#onDeliveryMode').hide();
        if (!hasProduct)
            $('#paymentDetails').hide();
        else
            $('#paymentDetails').show();

    }

    function deleteBTN(id) {
        if (edit && !creatorIsAdmin)
            return '';
        return '<span class="btn btn-danger mx-1 fa fa-xmark" ' +
            'onclick="$(`#product_' + id + '`).val(0);cart[' + id + '] =0 ;refreshProducts()"></span>'
    }

    function num_plus(id) {
        if (edit && !creatorIsAdmin)
            return;
        $('#product_' + id).val((index, value) => {
            return +value + 1
        }).change();
    }

    function num_minus(id) {
        if (edit && (!creatorIsAdmin))
            return;
        $('#product_' + id).val((index, value) => {
            return +value - 1
        }).change();
    }

    function num_product(id, value) {
        value = Math.round(value);
        $('#product_' + id).val(value);
        cart[id] = value;
        @if(!$user->meta('refund'))
        if (value <= 0) {
            deleteProduct(id)
        }
        @endif
        refreshProducts();
    }

    function setCustomerInfo(id) {
        let customer = customers[id] || {};
        @if(!$creatorIsAdmin)
        if (!customer.name)
            return;
        @endif
        $('#customerId').html(id);
        $('#customer_id').val(id);
        $('#name').val(customer.name)
        $('#phone').val(customer.phone).change();
        $('#address').val(customer.address);
        $('#zip_code').val(customer.zip_code).change();
        $('#set-customer-discount').html((customer.discount || '0') + ' %').click(() => {
            $('.discount-value').val(customer.discount || 0).change();
        });
        $('#customer-agreement').html('<span>تفاهم: </span>' + (customer.agreement || ' '));

    }

    function changeDiscount(id, discount) {
        discount = Math.min(100, +discount);
        discount = Math.max(0, +discount);
        discount = Math.round(discount * 4) / 4;
        $('#discount_' + id).val(discount);
        // let price = $(`#price_${id}`).val().replaceAll(',', '');
        // let price_discount = Math.round(price * (100 - discount) / 100);
        // $(`#price_discount_${id}`).html(priceFormat(price_discount));
        refreshProducts();
    }

    function changePrice(id, value) {
        let price = value.replaceAll(',', '');
        let discount = $(`#discount_${id}`).val();
        let price_discount = Math.round(price * (100 - discount) / 100);
        $(`#price_discount_${id}`).html(priceFormat(price_discount));
        refreshProducts();
    }

    function beforeSubmit() {
        let number = Object.keys(cart).length;
        if (!number) {
            alert('محصولی انتخاب نشده است');
            return false;
        }
        let values = Object.values(cart);
        if ((values.some(x => x >= 0) && values.some(x => x <= 0)) || values.some(x => x == 0)) {
            alert('تعداد همگی باید مثبت یا منفی باشند');
            return false;
        }
        $('input[type=submit]').attr('disabled', 'disabled');
        return true;
    }

    function enableEditPrice(id, checked) {
        let priceInput = $(`#price_${id}`).prop('disabled', checked);
        if (checked) {
            priceInput.val(products[id].good.price).change();
        } else {

        }
        refreshProducts();
    }

    function chaneOfficial(value = official) {
        official = value;
        $('.official').toggle(official);
    }

</script>

<style>
    .official {
        display: none;
    }
</style>

