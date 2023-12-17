<script>


    $(function () {
        setTimeout(function () {
            $("#errors").hide()
        }, 5000);
        $("#name").autocomplete({
            source: Object.keys(customers),
            select: function (event, ui) {
                let customer = customers[ui.item.value];
                setCustomerInfo(customer);

            }
        });
        $("#addToCustomers").checkboxradio();
    });

    @if(!$order)
    let customers = {!!json_encode($customers)!!};
    let customersId = {!!json_encode($customersId)!!};
    let paymentMethod = `{{old('paymentMethod')?old('paymentMethod'):"credit"}}`;

    let deliveryMethod = `{{old('deliveryMethod')?old('deliveryMethod'):"peyk"}}`;
    let product_table;
    let cart = {};
    let products = {!!json_encode($products)!!};
    $(function () {
        refreshProducts()
        $('#' + paymentMethod).click();
        $("#factor").checkboxradio();
        $("input[type=radio]").checkboxradio();
        $(".discount-value").on("change " ,function (){
            let id = $(this).attr('id').split("_")[1];
            products[id].coupon = $(this).val();
            products[id].priceWithDiscount = (products[id].price*(100-products[id].coupon)/100);
            $("#price_"+id+" .discount").html(priceFormat(products[id].priceWithDiscount));
            refreshProducts();
        })

        product_table = $('#product-table').DataTable({
            "autoWidth": false,
            "paging":   false,
        });

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
        $('#order-list').html('')
        $('#orders').html('');
        let total = 0, Total = 0;
        let hasProduct = false;
        $.each(cart, (id, number) => {
            if (number > 0) {
                let price = products[id].priceWithDiscount * number;
                let Price = products[id].price * number;

                $('#orders').append(products[id].name + ' ' + number + ' عدد ' + deleteBTN(id) + '| ');
                $('#order-list').append('<li>' + products[id].name + ' ' + number + ' عدد ' + deleteBTN(id) + ': ' + num(price) + '</li>')

                total += price;
                Total += Price;
                hasProduct = true;
            }
        })
        let deliveryCost = 0;
        if (Total < {{$settings->freeDelivery}} || {{$id}} == 10) //استثنا خانوم موسوی
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

    }

    function productMode() {   // نمایش محصولات قابل سفارش
        @if($admin)
        if ($('#factor').prop('checked'))
            return;
        @endif

        $('#products').show();
        $('#formElements').hide();
    }

    function formMode() {  //نمایش فرم ورود اطلاعات کاربر
        product_table
            .search( '' )
            .columns().search( '' )
            .draw();
        $('#products').hide();
        $('#formElements').show();
    }

    function deleteBTN(id) {
        return '<span class="btn btn-danger mx-1" ' +
            'onclick="$(`#product_' + id + '`).val(0);refreshProducts()">X</span>'
    }

    function priceFormat(price){
        return Number(price.toFixed()).toLocaleString('en-US');
    }

    function num_plus(id){
        let n = $('#product_'+id).val();
        $('#product_'+id).val(++n);
        cart[id] = n;
        refreshProducts();
    }

    function num_minus(id){
        let n = $('#product_'+id).val();
        $('#product_'+id).val(Math.max(0, --n));
        cart[id] = n;
        refreshProducts();
    }

    function num_product(id){
        cart[id] = $('#product_'+id).val();
        refreshProducts();
    }

    function customerFind(){
        let id = $('#customerId').val();
        setCustomerInfo(customersId[id])
    }

    function setCustomerInfo(customer){
        $('#customerId').val(customer.id);
        $("#name").val(customer.name)
        $('#phone').val(customer.phone);
        $('#address').val(customer.address);
        $('#zip_code').val(customer.zip_code);
    }

    @endif
</script>
<style>
    .D-none {
        display: none;
    }
</style>
