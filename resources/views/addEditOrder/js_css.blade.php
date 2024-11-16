<script>
    let customers = {!!json_encode($customers)!!};
    let paymentMethod = "credit";
    let deliveryMethod = "peyk";
    let products = {!!json_encode($products)!!};
    let cart = {!!json_encode($cart)!!};
    let cities = {!!json_encode($cities)!!};
    let creatorIsAdmin = !!'{{$creatorIsAdmin}}';
    let edit = !!'{{$edit}}';
    let changeDiscountPermit = !!'{{$user->meta('changeDiscount')}}';
    let changePricePermit = !!'{{$user->meta('changePrice')}}';
    let table;
    let submitStatus = false;


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
        deliveryAction();
    });

    @if($creatorIsAdmin || !$edit)
    $(function () {
        createTable()

        let customersName = {};
        $.each(customers, (id, customer) => {
            customersName[customer.name] = id;
        })
        $("#name").autocomplete({
            source: Object.keys(customersName),
            select: function (event, ui) {
                let id = customersName[ui.item.value];
                setCustomerInfo(customers[id]);
            }
        });


        let cityName = {};
        $.each(cities, (id, city) => {
            cityName[city.name + ` (${city.province.name})`] = id;
        })

        $("#city").autocomplete({
            source: Object.keys(cityName),
            select: function () {
                $('#city').change();
            }
        });

        $('#city').change(function () {
            let id = cityName[this.value];
            if (id)
                $('#city_id').val(id);
            else {
                let city = cities[$('#city_id').val()];
                $('#city').val(city.name + ` (${city.province.name})`)
            }
        });
    });

    @endif

    function createTable() {
        let data = [];

        $.each(products, (id, product) => {

            let price;
            if (product.coupon > 0)
                price = `${priceFormat(product.priceWithDiscount)} (${product.coupon}%)`;
            else
                price = priceFormat(product.good.price);
            data.push([
                product.good.name,
                price,
                `<span dir="ltr">${+product.quantity}</span>`,
                `<span class="btn btn-primary fa fa-add" onclick="addProduct(${id});refreshProducts();"></span>`,
            ]);

        });
        if (table) {
            table.clear();
            table.rows.add(data);
            table.draw();
        } else {
            table = $('#product-table').DataTable({
                data: data,
                pageLength: 100,
                destroy: true,
            });
        }
    }

    function addProduct(id) {
        let product = products[id];
        if (cart[id]) {
            if ($('#product-' + id)[0])
                return;
        } else
            cart[id] = 1;
        $('#selected-product-table').show();
        if(product.coupon != 100)
            product.price = product.priceWithDiscount * 100 / (100-product.coupon)
        else
            product.price = product.good.price;
        let text = `<tr id="product-${id}">
        <td>${product.good.name}</td>
        <td>
            <span class="btn btn-primary fa fa-plus" onclick="num_plus(${id})"></span>
            <input class="product-number"
            name="product_${id}" id="product_${id}"
            onchange="num_product(${id},this.value)"
            type="number" value="${cart[id]}"
            style="width: 50px" step="1" ${(edit && (!creatorIsAdmin)) ? 'readonly' : ''}>
            <span class="btn btn-primary fa fa-minus" onclick="num_minus(${id})"></span>
            <span class="btn btn-outline-info" dir="ltr">${+product.quantity}</span>
        </td>
        <td>
            <input type="text" class="price-input" style="width: 80px;"
            name="price_${id}" id="price_${id}" value="${product.price}"
            onchange="changePrice(${id},this.value)" ${changePricePermit ? '' : 'disabled'}>
        </td>
        <td>
            <input type="number" name="discount_${id}" class="discount-value" id="discount_${id}"
            value="${product.coupon}" style="width: 80px" onchange="changeDiscount(${id},this.value)"
            ${changeDiscountPermit ? '' : 'disabled'} min="0" max="100" step="0.25">
        @if($user->meta('changeDiscount'))
            <a class="btn btn-outline-info fa fa-plus" dir="ltr"
            onclick="$('#discount_${id}').val((index,value)=>{return +value+5}).change()">5
            <i class="fa fa-percent"></i></a>
        @endif
        </td>
        <td>
            <span class="text-success" id="price_discount_${id}">${priceFormat(product.priceWithDiscount)}</span>
        </td>
        <td>
            <span class="btn btn-danger fa fa-trash" onclick="deleteProduct(${id})"></span>
        </td>
        </tr>`;
        $('#product-form').append(text);
        priceInput();
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

    function deliveryAction() {
        deliveryMethod = $('input[name="deliveryMethod"]:checked').val();
        refreshProducts();
    }

    function refreshProducts() {
        let total = 0, Total = 0;
        let hasProduct = false;
        $('#hidden-input').html('');
        $.each(cart, (id, number) => {
            if (number) {
                $('#product_' + id).val(number);
                let price = +$('#price_' + id).val().replaceAll(',', '');
                let price_discount = +$('#price_discount_' + id).html().replaceAll(',', '');
                total += price_discount * number; //جمع قیمت با تخفیف;
                Total += price * number;  //قیمت بدون تخفیف;
                $('#hidden-input').append(`<input type="hidden" name="cart[${id}]" value="${number}">`);
                hasProduct = true;
            }
        })

        let deliveryCost = 0;
        if (Total < {{$settings->freeDelivery}} || '{{$user->id}}' === '10')
            if (deliveryMethod === 'peyk')
                deliveryCost = {{$settings->peykCost}};
            else if (deliveryMethod === 'post')
                deliveryCost = {{$settings->postCost}};
            else if (deliveryMethod === 'peykeShahri')
                deliveryCost = {{$settings->peykeShahri}};
        $('#deliveryCost').html(num(deliveryCost));
        $('#cartSum').html(num(total));
        $('#total').html(num(total + deliveryCost));
        $('#total-discount').html(num(Total - total));

        if (paymentMethod === 'onDelivery') {
            let customerDiscount = $('#customerDiscount').val()
            $('#onDeliveryMode').show();
            let customerTotal = Math.round(Total * (100 - customerDiscount) / 100 + deliveryCost)
            $('#customerTotal').html(num(customerTotal));
            let safirShare = customerTotal - total - deliveryCost;
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

    function setCustomerInfo(customer) {
        $('#customerId').val(customer.id);
        $("#name").val(customer.name)
        $('#phone').val(customer.phone);
        $('#address').val(customer.address);
        $('#zip_code').val(customer.zip_code);
        $('#set-customer-discount').html(customer.discount + ' %').click(()=>{
            $('.discount-value').val(customer.discount).change();
        })
        let city = cities[customer.city_id];
        $('#city').val(city.name + ` (${city.province.name})`).change();
    }

    function changeDiscount(id, discount) {
        discount = Math.min(100, +discount);
        discount = Math.max(0, +discount);
        discount = Math.round(discount * 4) / 4;
        $('#discount_' + id).val(discount);
        let price = $(`#price_${id}`).val().replaceAll(',', '');
        let price_discount = Math.round(price*(100-discount)/100);
        $(`#price_discount_${id}`).html(priceFormat(price_discount));
        refreshProducts();
    }

    function changePrice(id, value) {
        let price = value.replaceAll(',', '');
        let discount = $(`#discount_${id}`).val();
        let price_discount = Math.round(price*(100-discount)/100);
        $(`#price_discount_${id}`).html(priceFormat(price_discount));
        refreshProducts();
    }

    function beforeSubmit() {

        let number = Object.keys(cart).length;

        if (!number) {
            alert('محصولی انتخاب نشده است');
            return false;
        }
        if (cart.some(x => x >= 0) && cart.some(x => x <= 0)) {
            alert('تعداد همگی باید مثبت یا منفی باشند');
            return false;
        }
        if (submitStatus)
            return false;
        submitStatus = true
        return true;
    }

    function enableEditPrice(id , checked){
        let priceInput = $(`#price_${id}`).prop('disabled', checked);
        if(checked){
            priceInput.val(products[id].good.price).change();
        }else{

        }
        refreshProducts();
    }

</script>

