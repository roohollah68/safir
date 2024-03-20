@extends('layout.main')

@section('title')
    محصولات
@endsection

@section('content')
    <a class="btn btn-info mb-3" href="{{route('addProduct')}}">
        <span class=" fa fa-plus"></span>
        افزودن محصول جدید
    </a>
    <br>
    <div class="container border input-box">
        <div class="row">
            <div class="col-md-3 border">
                <span class="btn btn-primary"
                      onclick="$('.input-box input').prop('checked', true).checkboxradio('refresh'); filter()">همه محصولات</span>
            </div>
            <div class="col-md-3 border">
                <input type="checkbox" name="low" id="low" checked>
                <label class="btn btn-warning mb-1" for="low">موجودی کم</label><br>
                <input type="checkbox" name="normal" id="normal" checked>
                <label class="btn btn-success mb-1" for="normal">موجودی مناسب</label><br>
                <input type="checkbox" name="high" id="high" checked>
                <label class="btn btn-danger " for="high">موجودی زیاد</label>
            </div>
            <div class="col-md-3 border">
                <input type="checkbox" name="available" id="available" checked>
                <label class="btn btn-success mb-1" for="available">محصولات موجود</label><br>
                <input type="checkbox" name="not-available" id="not-available">
                <label class="btn btn-danger " for="not-available">محصولات ناموجود</label>
            </div>
            <div class="col-md-3 border">
                <input type="checkbox" name="final" id="final" checked>
                <label class="btn btn-info mb-1" for="final">محصول نهایی</label><br>
                <input type="checkbox" name="raw" id="raw" checked>
                <label class="btn btn-secondary mb-1" for="raw">مواد اولیه</label><br>
                <input type="checkbox" name="pack" id="pack" checked>
                <label class="btn btn-secondary" for="pack">ملزومات بسته بندی</label>
            </div>
        </div>
    </div>


    <br>





    <br>
    <br>
    <table class="stripe" id="product-table">
        <thead>
        <tr>
            <th>شماره</th>
            <th>نام</th>
            <th>قیمت(ریال)</th>
            <th>موجودی</th>
            <th>حد پایین</th>
            <th>حد بالا</th>
            <th>وضعیت</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)

            <tr class="{{$product->alarm > $product->quantity ? 'bg-warning low' : ($product->high_alarm < $product->quantity ? 'bg-info high' : 'normal')}}
            {{$product->available?'available ':'not-available '}} {{$product->category}}" id="row_{{$product->id}}">
                <form>
                    <td>{{$product->id}}</td>
                    <td><input type="text" name="name" value="{{$product->name}}" style="width: 300px;" disabled></td>
                    <td><input type="text" name="price" class="price-input" value="{{$product->price}}"
                               style="width: 110px;" disabled></td>
                    <td><input type="number" name="quantity" value="{{+$product->quantity}}" style="width: 60px;"
                               disabled>
                    </td>
                    <td><input type="number" name="alarm" value="{{$product->alarm}}" style="width: 60px;" disabled>
                    </td>
                    <td><input type="number" name="high_alarm" value="{{$product->high_alarm}}" style="width: 60px;"
                               disabled></td>
                    <td style="width: 110px;">
                        <input type="checkbox" id="{{$product->id}}" name="available" disabled
                               @if($product->available) checked @endif>


                        @if($product->available)
                            <label for="{{$product->id}}" class="btn btn-success">موجود</label>
                        @else
                            <label for="{{$product->id}}" class="btn btn-danger">ناموجود</label>
                        @endif
                    </td>
                    <td style="width: 200px;">
                        <a class="fa fa-edit btn btn-primary" href="/product/edit/{{$product->id}}"
                           title="ویرایش محصول"></a>
                        <a class="fa fa-file-edit btn btn-info fast" onclick="fastEdit({{$product->id}})"
                           title="ویرایش سریع"></a>
                        <i class="fa fa-trash-alt btn btn-danger" onclick="delete_product({{$product->id}})"
                           title="حذف محصول"></i>
                        <i class="fa fa-save btn btn-success save" onclick="save({{$product->id}})"
                           title="ذخیره تغییرات" style="display: none;"></i>
                    </td>
                    <input type="hidden" name="category" value="{{$product->category}}">
                </form>
            </tr>

        @endforeach
        </tbody>
    </table>

@endsection


@section('files')
    <script>
        let products = {!!$products!!};
        let token = "{{ csrf_token() }}";

        $(function () {
            draw();
            filter();
            $('.input-box input, input[type=checkbox]').checkboxradio().click(filter);
        });

        function draw() {
            $('#product-table').DataTable({
                order: [[3, "desc"]],
                paging: false,
                destroy: true,
            });
        }

        function delete_product(id) {
            if (!products[id].available || confirm("برای همیشه حذف شود؟")) {
                $.post('/product/delete/' + id, {_token: "{{ csrf_token() }}"})
                    .done(res => {
                        if (res === 'ok')
                            $('#row_' + id).remove();
                    });
            }
        }

        function filter() {
            $('tbody tr').show();
            $('#low')[0].checked ? '' : $('.low').hide();
            $('#normal')[0].checked ? '' : $('.normal').hide();
            $('#high')[0].checked ? '' : $('.high').hide();
            $('#available')[0].checked ? '' : $('.available').hide();
            $('#not-available')[0].checked ? '' : $('.not-available').hide();
            $('#final')[0].checked ? '' : $('.final').hide();
            $('#raw')[0].checked ? '' : $('.raw').hide();
            $('#pack')[0].checked ? '' : $('.pack').hide();
        }

        function fastEdit(id) {
            let tag = '#row_' + id;
            $(tag + ' input').prop('disabled', (i, v) => {
                return !v;
            });
            $(tag + ' input[type=checkbox]').checkboxradio('refresh');
            $(tag + ' .save ,' + tag + ' .fast').toggle();
        }

        function save(id) {
            let tag = '#row_' + id;
            $.post('product/edit/' + id, {
                _token: token,
                name: $(tag + ' input[name=name]').val(),
                price: $(tag + ' input[name=price]').val(),
                value: $(tag + ' input[name=quantity]').val(),
                alarm: $(tag + ' input[name=alarm]').val(),
                high_alarm: $(tag + ' input[name=high_alarm]').val(),
                available: $(tag + ' input[name=available]').prop('checked'),
                category: $(tag + ' input[name=category]').val(),
                addType: 'value',
                fast: true,
            })
                .done(res => {
                    $.notify(res[0], 'success');
                    products[res[1].id] = res[1];
                    fastEdit(id)
                })
        }

    </script>
    <script src="/js/dom-to-image.min.js"></script>
@endsection
