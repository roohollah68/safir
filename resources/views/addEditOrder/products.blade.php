@if( $creator || !$edit)
    <br>
    @if(!$safir)
        <a class="btn {{$location == 't'?'btn-info':'btn-outline-info'}}" href="/add_order?city=t">تهران</a>
{{--        <a class="btn {{$location == 'f'?'btn-info':'btn-outline-info'}}" href="/add_order?city=f">فریمان</a>--}}
        <a class="btn {{$location == 'm'?'btn-info':'btn-outline-info'}}" href="/add_order?city=m">مشهد</a>
        <br>
    @endif
    <div id="products" class="my-4">
        <table class="stripe" id="product-table">
            <thead>
            <tr>
                <th>محصول</th>
                <th>تعداد(موجودی انبار)</th>
                <th>قیمت(ریال)</th>
                <th>تخفیف(%)</th>
            </tr>
            </thead>
            <tbody>
            @foreach($products as $product)
                @if($product->available)
                    <tr>
                        {{--محصول--}}
                        <td>{{$product->name}}</td>


                        {{--تعداد--}}
                        <td>
                            <span class="btn btn-primary fa fa-plus" onclick="num_plus({{$product->id}})"></span>
                            <input class="product-number"
                                   name="product_{{$product->id}}" id="product_{{$product->id}}"
                                   onchange="num_product({{$product->id}},this.value)"
                                   type="number" value="{{old("product_".$product->id)}}" style="width: 50px" min="0">
                            <span class="btn btn-primary fa fa-minus" onclick="num_minus({{$product->id}})"></span>
                            <span class="btn btn-outline-info ">{{+$product->quantity}}</span>


                        </td>

                        {{--قیمت(ریال)--}}
                        <td id="price_{{$product->id}}">

                            <input type="text" class="price-input text-success discount" style="width: 80px;"
                                   name="price_{{$product->id}}"
                                   value="{{$product->priceWithDiscount}}"
                                   onchange="calculate_discount({{$product->id}},this.value)"
                                   @if($safir)
                                       disabled
                                @endif
                            >
                            <span class=" btn text-danger original"
                                  @if($product->priceWithDiscount!=$product->price)
                                      style="text-decoration: line-through"
                                  @endif
                                  @if($creator)
                                      onclick="$('#price_{{$product->id}} .discount').val('{{number_format($product->price)}}').change(); $('#discount_{{$product->id}}').val(0).change();"
                                            @endif
                                        >
                                            {{number_format($product->price)}}
                                        </span>
                        </td>


                        {{--تخفیف--}}
                        <td>
                            <input type="number" name="discount_{{$product->id}}"
                                   class="discount-value"
                                   id="discount_{{$product->id}}"
                                   value="{{old("discount_".$product->id)?:+$product->coupon}}"
                                   style="width: 80px"
                                   onchange="changeDiscount({{$product->id}},this.value)"
                                   @if(!$creator)
                                       disabled
                                   @endif
                                   min="0" max="100" step="0.25">
                            @if($creator)
                                <a class="btn btn-outline-info fa fa-plus" dir="ltr"
                                   onclick="$('#discount_{{$product->id}}').val(+$('#discount_{{$product->id}}').val()+5).change()">5
                                    <i class="fa fa-percent"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>
    </div>
@endif
