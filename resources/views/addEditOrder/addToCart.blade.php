<tr id="product-${id}">
    <td>${product.good.name}</td>
    <td>
        <span class="btn btn-primary fa fa-plus" onclick="num_plus(${id})"></span>
        <input class="product-number"
{{--               name="product_${id}" id="product_${id}"--}}
               name="cart[${id}][number]" id="product_${id}"
               onchange="num_product(${id},this.value)"
               type="number" value="${cart[id]}"
               style="width: 50px" step="1" ${(edit && (!creatorIsAdmin)) ? 'readonly' : ''}>
        <span class="btn btn-primary fa fa-minus" onclick="num_minus(${id})"></span>
        <span class="btn btn-outline-info" dir="ltr">${+product.quantity}</span>
    </td>
    <td>
        <input type="text" class="price-input" style="width: 80px;"
{{--               name="price_${id}" id="price_${id}" value="${product.price}"--}}
               name="cart[${id}][price]" id="price_${id}" value="${product.price}"
               onchange="changePrice(${id},this.value)" ${changePricePermit ? '' : 'disabled'}>
    </td>
    <td>
{{--        <input type="number" name="discount_${id}" class="discount-value" id="discount_${id}"--}}
        <input type="number" name="cart[${id}][discount]" class="discount-value" id="discount_${id}"
               value="${product.discount}" style="width: 80px" onchange="changeDiscount(${id},this.value)"
               ${changeDiscountPermit ? '' : 'disabled'} min="0" max="100" step="0.25">
        @if($user->meta('changeDiscount'))
        <span class="btn btn-outline-info fa fa-plus" dir="ltr"
              onclick="$('#discount_${id}').val((index,value)=>{return +value+5}).change()">5
            <i class="fa fa-percent"></i></span>
        @endif
    </td>
    <td>
        <span class="text-success" id="price_discount_${id}">${priceFormat(product.priceWithDiscount)}</span>
    </td>
    <td>
        <span class="btn btn-danger fa fa-trash" onclick="deleteProduct(${id})"></span>
    </td>
</tr>
