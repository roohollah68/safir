@if($admin || !$edit)
    <div id="products" class="my-4">
        <table class="stripe" id="product-table">
            <thead>
            <tr>
                <th>محصول</th>
                <th>تخفیف(%)</th>
                <th>قیمت(ریال)</th>
                <th>تعداد(موجودی انبار)</th>
            </tr>
            </thead>
            <tbody>
            @foreach($products as $product)
                @if($product->available)
                    <tr>
                            {{--محصول--}}
                            <td>{{$product->name}}</td>

                            {{--تخفیف--}}
                            <td>
                                <input type="number" name="discount_{{$product->id}}"
                                       class="discount-value"
                                       id="discount_{{$product->id}}"
                                       value="{{old("discount_".$product->id)?:$product->coupon}}"
                                       style="width: 50px"
                                       onchange="changeDiscount({{$product->id}},this.value)"
                                       @if(!$admin)
                                           disabled
                                       @endif
                                       min="0" max="100" step="1">
                            </td>

                            {{--قیمت(ریال)--}}
                            <td id="price_{{$product->id}}">
                                        <span class="text-danger original"
                                              @if($product->priceWithDiscount!=$product->price)
                                              style="text-decoration: line-through"
                                            @endif
                                        >
                                            {{number_format($product->price)}}
                                        </span>
                                <span class="text-success discount">
                                            {{$product->priceWithDiscount!=$product->price?number_format($product->priceWithDiscount):''}}
                                        </span>
                            </td>

                        {{--تعداد--}}
                        <td>
                            <span class="btn btn-primary" onclick="num_plus({{$product->id}})">+</span>
                            <input class="product-number" product_id="{{$product->id}}"
                                   name="product_{{$product->id}}" id="product_{{$product->id}}"
                                   onchange="num_product({{$product->id}},this.value)"
                                   type="number" value="{{old("product_".$product->id)}}" style="width: 50px" min="0">
                            <span class="btn btn-primary" onclick="num_minus({{$product->id}})">-</span>
                            <span class="btn btn-outline-info">{{$product->quantity}}</span>
                        </td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>
        {{--                <span class="btn btn-info" onclick="formMode()">بازگشت</span>--}}
    </div>
    @endif
    </form>
