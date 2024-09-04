<script>
    let customers = {!!json_encode($customers)!!};
    let customersId = {!!json_encode($customersId)!!};
    let paymentMethod = "credit";
    let deliveryMethod = "peyk";
    let products = {!!json_encode($products)!!};
    let cart = {!!json_encode($cart)!!};
    let cities = {!!json_encode($cities)!!};
    let citiesId = {!!json_encode($citiesId)!!};
    let creatorIsAdmin = !!'{{$creatorIsAdmin}}';
    let safir = !!'{{$safir}}';
    let edit = !!'{{$edit}}';
    let table;

    $(function () {
        //Hide form errors after some time.
        setTimeout(function () {
            $("#errors").hide()
        }, 10000);

        $(".checkboxradio").checkboxradio();//jquery-ui
        $.each(cart,(id,number)=>{
            if(number)
                addProduct(id);
            else
                delete cart[id];
        });
    });

    @if($creatorIsAdmin || !$edit)
    $(function () {
        //create products table
        // $('#product-table').DataTable({
        //     autoWidth: false,
        //     paging: false,
        //     // pageLength: 100,
        //     order: [[3, "desc"]],
        // });

        createTable()

        $("#name").autocomplete({
            source: Object.keys(customers),
            select: function (event, ui) {
                let customer = customers[ui.item.value];
                setCustomerInfo(customer);
            }
        });
        refreshProducts()
        paymentAction()
        deliveryAction()
        $('input[name=paymentMethod]').on('click', paymentAction);
        $('input[name=deliveryMethod]').on('click', deliveryAction);

        $("#city").autocomplete({
            source: Object.keys(cities),
            select: function (event, ui) {
                $('#city').change();
            }
        });

        $('#city').change(function () {
            let city = cities[this.value];
            if (city)
                $('#city_id').val(city.id);
            else {
                let city = citiesId[$('#city_id').val()];
                $('#city').val(city.name)
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
                price = priceFormat(product.price);
            data.push([
                product.name,
                price,
                `<span dir="ltr">${+product.quantity}</span>`,
                `<span class="btn btn-primary fa fa-add" onclick="addProduct(${id})"></span>`,
            ])
        });
        if (table) {
            table.clear();
            table.rows.add(data);
            table.draw();
        } else {
            table = $('#product-table').DataTable({
                data: data,
                // order: [[3, "desc"]],
                pageLength: 100,
                destroy: true,
            });
        }
    }

    function addProduct(id) {
        if (cart[id]) {
            if ($('#product-' + id)[0])
                return;
        } else
            cart[id] = 1;
        $('#selected-product-table').show();
        let text = `<tr id="product-${id}">
        <td>${products[id].name}</td>
        <td>
            <span class="btn btn-primary fa fa-plus" onclick="num_plus(${id})"></span>
            <input class="product-number"
            name="product_${id}" id="product_${id}"
            onchange="num_product(${id},this.value)"
            type="number" value="${cart[id]}"
            style="width: 50px" min="0" ${(edit && safir)?'readonly':''}>
            <span class="btn btn-primary fa fa-minus" onclick="num_minus(${id})"></span>
            <span class="btn btn-outline-info" dir="ltr">${+products[id].quantity}</span>
        </td>
        <td>
            <input type="text" class="price-input text-success discount" style="width: 80px;"
            name="price_${id}" value="${products[id].priceWithDiscount}"
            onchange="calculate_discount(${id},this.value)" ${safir ? 'disabled' : ''}>` +
            ((products[id].priceWithDiscount == products[id].price) ? '' :
                `<span class="text-danger" style="text-decoration: line-through">
            ${priceFormat(products[id].price)}
        </span>`) +
            `</td>
        <td>
        <input type="number" name="discount_${id}" class="discount-value" id="discount_${id}"
        value="${products[id].coupon}" style="width: 80px" onchange="changeDiscount(${id},this.value)"
        ${creatorIsAdmin ? '' : 'disabled'} min="0" max="100" step="0.25">` +
            (creatorIsAdmin ?
                `<a class="btn btn-outline-info fa fa-plus" dir="ltr"
           onclick="$('#discount_${id}').val(+$('#discount_${id}').val()+5).change()">5
                            <i class="fa fa-percent"></i>
                        </a>
                    ` : ``) +
            `</td>
        </tr>`
        $('#product-form').append(text)
        priceInput();
        refreshProducts();
    }

    function paymentAction() {
        paymentMethod = $('input[name="paymentMethod"]:checked').val();
        $('.receiptPhoto,#customerDiscount,label[for=customerDiscount]').hide();
        if (paymentMethod == 'receipt') {
            $('.receiptPhoto').show();
        } else if (paymentMethod == 'onDelivery') {
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
        let ordersText = ''; //عبارت مربوط به قسمت محصولات
        let ordersListText = ''; // عبارت مربوط به فاکتور سفیران
        $.each(cart, (id, number) => {
            if (number) {
                // let price = products[id].priceWithDiscount * number; //قیمت با تخفیف
                // let Price = products[id].price * number;  //قیمت بدون تخفیف
                $('#product_' + id).val(number);
                // ordersText = ordersText.concat(products[id].name + ' ' + number + ' عدد ' + deleteBTN(id) + '<br>');
                // ordersListText = ordersListText.concat('<li>' + products[id].name + ' ' + number + ' عدد ' + deleteBTN(id) + ': ' + num(price) + '</li>');

                total += products[id].priceWithDiscount * number; //جمع قیمت با تخفیف
                Total += products[id].price * number;  //قیمت بدون تخفیف
                // $('#product_' + id).val(number);
                hasProduct = true;
            } else {
                $('#product_' + id).val('');
                delete cart[id];
            }
        })
        // $('#orders').html(ordersText);

        @if($safir)
        // $('#order-list').html(ordersListText)
        let deliveryCost = 0;
        if (Total < {{$settings->freeDelivery}} || '{{$user->id}}' == '10')
            if (deliveryMethod == 'peyk')
                deliveryCost = {{$settings->peykCost}};
            else if (deliveryMethod == 'post')
                deliveryCost = {{$settings->postCost}};
            else if (deliveryMethod == 'peykeShahri')
                deliveryCost = {{$settings->peykeShahri}};
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
        @if($creatorIsAdmin || !$edit)
            return '<span class="btn btn-danger mx-1 fa fa-xmark" ' +
            'onclick="$(`#product_' + id + '`).val(0);cart[' + id + '] =0 ;refreshProducts()"></span>'
        @else
            return '';
        @endif
    }

    function priceFormat(price) {
        return (+(+price).toFixed()).toLocaleString('en-US');
    }

    function num_plus(id) {
        if (edit && safir)
            return;
        let n = +$('#product_' + id).val() + 1;
        $('#product_' + id).val(n).change();
    }

    function num_minus(id) {
        if (edit && safir)
            return;
        let n = +$('#product_' + id).val() - 1;
        $('#product_' + id).val(n).change();
    }

    function num_product(id, value) {
        value = Math.max(0, +value);
        value = Math.round(value);
        $('#product_' + id).val(value);
        cart[id] = value;
        if (value == 0) {
            // $('#product_' + id).val('');
            delete cart[id];
            $('#product-'+id).remove();
            let number = Object.keys(cart).length;
            if (!number) {
                $('#selected-product-table').hide();
            }
        }
        refreshProducts();
    }

    function setCustomerInfo(customer) {
        $('#customerId').val(customer.id);
        $("#name").val(customer.name)
        $('#phone').val(customer.phone);
        $('#address').val(customer.address);
        $('#zip_code').val(customer.zip_code);
        // $('#category').val(customer.category).change();
        $('#city').val(citiesId[customer.city_id].name).change();
    }

    function changeDiscount(id, value) {
        value = Math.min(100, +value);
        value = Math.max(0, +value);
        value = Math.round(value * 4) / 4;
        $('#discount_' + id).val(value);
        products[id].coupon = value;
        products[id].priceWithDiscount = (products[id].price * (100 - products[id].coupon) / 100);
        $("#price_" + id + " .discount").val(priceFormat(products[id].priceWithDiscount));
        refreshProducts();
    }

    function calculate_discount(id, value) {
        // $('#discount_' + id).val(0);
        // return;
        value = +(value.replaceAll(',', ''));
        if (value <= products[id].price && '{{$user->id}}' !== '61') {
            // value = Math.min(products[id].price, +value);
            value = Math.max(0, +value);
            $('#discount_' + id).val((1 - value / products[id].price) * 100).change();
        } else {
            $('#discount_' + id).val(0);
        }
    }

    function beforeSubmit() {

        let number = Object.keys(cart).length;

        if (!number) {
            alert('محصولی انتخاب نشده است');
            return false;
        }
        return true;
    }

</script>

