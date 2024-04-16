<script>

    let customers = {!!json_encode($customers)!!};
    let customersId = {!!json_encode($customersId)!!};
    let paymentMethod = "credit";
    let deliveryMethod = "peyk";
    let product_table;
    let products = {!!json_encode($products)!!};
    let cart = {!!json_encode($cart)!!};
    let cities = {!!json_encode($cities)!!};
    let citiesId = {!!json_encode($citiesId)!!};
    let province = {!!json_encode($province)!!};
    let submit = false;
    let creator = !!'{{$creator}}';

    $(function () {
        setTimeout(function () {
            $("#errors").hide()
        }, 10000);
        startEditProccess();

        $("input[type=radio]").checkboxradio();
        $("#addToCustomers").checkboxradio();
    });

    @if($creator || !$edit)
    $(function () {
        product_table = $('#product-table').DataTable({
            autoWidth: false,
            paging: false,
            order: [[3, "desc"]],
        });
        $("#name").autocomplete({
            source: Object.keys(customers),
            select: function (event, ui) {
                let customer = customers[ui.item.value];
                setCustomerInfo(customer);
            }
        });
        refreshProducts()
        // $('input[name=paymentMethod]').on('click',paymentAction);
        $('input[name=deliveryMethod]').on('click',deliveryAction);

        $("#city").autocomplete({
            source: Object.keys(cities),
            select: function (event, ui) {
                $('#city').change();
            }
        });

        $('#city').change(function (){
            let city = cities[this.value];
            if(city) {
                $('#city_id').val(city.id);
                $('#province').html(province[city.province_id].name);
            }
            else {
                let city = citiesId[$('#city_id').val()];
                $('#city').val(city.name)
                $('#province').html(province[city.province_id].name);
            }
        }).click(function (){
            this.value = '';
            $('#province').html('<sapn class="fa fa-arrow-rotate-back"></span>');
        });
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
        let total = 0, Total = 0;
        let hasProduct = false;
        let ordersText = ''; //عبارت مربوط به قسمت محصولات
        let ordersListText = ''; // عبارت مربوط به فاکتور سفیران
        $.each(cart, (id, number) => {
            if (number > 0) {
                let price = products[id].priceWithDiscount * number; //قیمت با تخفیف
                let Price = products[id].price * number;  //قیمت بدون تخفیف
                $('#product_' + id).val(number);
                ordersText = ordersText.concat(products[id].name + ' ' + number + ' عدد ' + deleteBTN(id) + '<br>');
                ordersListText = ordersListText.concat('<li>' + products[id].name + ' ' + number + ' عدد ' + deleteBTN(id) + ': ' + num(price) + '</li>');

                total += price; //جمع قیمت با تخفیف
                Total += Price; // جمع قیمت بدون تخفیف
                hasProduct = true;
            } else {
                $('#product_' + id).val('');
                delete cart[id];
            }
        })
        $('#orders').html(ordersText);

        @if($safir)
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
        @if($creator || !$edit)
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
        let n = +$('#product_' + id).val() + 1;
        $('#product_' + id).val(n).change();
    }

    function num_minus(id) {
        let n = +$('#product_' + id).val() - 1;
        $('#product_' + id).val(n).change();
    }

    function num_product(id, value) {
        value = Math.max(0, +value);
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
        $('#category').val(customer.category).change();
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

    function beforeSubmit() {
        $('input[type="search"]').val('').keyup();
        let number = Object.keys(cart).length;

        if (!number) {
            alert('محصولی انتخاب نشده است');
            return false;
        }
        console.log($('input[name="paymentMethod"]:checked').val());
        // return false;
        return true;
    }

    function calculate_discount(id, value) {
        value = +(value.replaceAll(',', ''));
        value = Math.min(products[id].price, +value);
        value = Math.max(0, +value);
        $('#discount_' + id).val((1 - value / products[id].price) * 100).change();
    }

    @else

    function beforeSubmit() {
        return true;
    }

    @endif

    function startEditProccess() {

        @if($errors->count())
        $.each(products,function (id){
            $('#product_'+id).change();
        });
        refreshProducts();
        @if($safir)
        $('#{{old("paymentMethod")}}').click();
        $('#{{old("deliveryMethod")}}').click();
        @endif
        @elseif($edit)
        deliveryMethod = `{{$order->deliveryMethod}}`;
        paymentMethod = `{{$order->paymentMethod}}`;
        @endif
    }

</script>

