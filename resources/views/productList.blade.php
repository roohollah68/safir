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
                <span class="btn btn-primary" onclick="$('.input-box input').prop('checked', true);$('.input-box input').checkboxradio('refresh'); filter()">همه محصولات</span>
            </div>
            <div class="col-md-3 border">
                <input type="checkbox" name="low" id="low" checked>
                <label class="btn btn-warning mb-1" for="low" )">موجودی کم</label><br>
                <input type="checkbox" name="normal" id="normal" checked>
                <label class="btn btn-success mb-1" for="normal" ">موجودی مناسب</label><br>
                <input type="checkbox" name="high" id="high" checked>
                <label class="btn btn-danger " for="high">موجودی زیاد</label>
            </div>
            <div class="col-md-3 border">
                <input type="checkbox" name="available" id="available" checked>
                <label class="btn btn-success mb-1" for="available">محصولات موجود</label><br>
                <input type="checkbox" name="not-available" id="not-available" >
                <label class="btn btn-danger " for="not-available">محصولات ناموجود</label>
            </div>
            <div class="col-md-3 border">
                <input type="checkbox" name="final" id="final" checked >
                <label class="btn btn-info mb-1" for="final">محصول نهایی</label><br>
                <input type="checkbox" name="raw" id="raw" checked >
                <label class="btn btn-secondary mb-1" for="raw">مواد اولیه</label><br>
                <input type="checkbox" name="pack" id="pack" checked >
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
                <td>{{$product->id}}</td>
                <td><a class="btn" href="/product/edit/{{$product->id}}">{{$product->name}}</a></td>
                <td>{{number_format($product->price)}}</td>
                <td>{{+$product->quantity}}</td>
                <td>{{$product->alarm}}</td>
                <td>{{$product->high_alarm}}</td>
                <td>
                    @if($product->available)
                        <p class="btn btn-success">موجود</p>
                    @else
                        <p class="btn btn-danger">ناموجود</p>
                    @endif
                </td>
                <td>
                    <a class="fa fa-edit btn btn-primary" href="/product/edit/{{$product->id}}"
                       title="ویرایش محصول"></a>
                    <i class="fa fa-trash-alt btn btn-danger" onclick="delete_product({{$product->id}})"
                       title="حذف محصول"></i>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection


@section('files')
    <script>
        let products = {!!$products!!};

        $(function () {
            draw();
            filter();
            $('.input-box input').checkboxradio().click(filter);
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
                        if (res == 'ok')
                            $('#row_' + id).remove();
                    });
            }
        }

        function filter(){
            $('tbody tr').show();
            $('#low')[0].checked?'':$('.low').hide();
            $('#normal')[0].checked?'':$('.normal').hide();
            $('#high')[0].checked?'':$('.high').hide();
            $('#available')[0].checked?'':$('.available').hide();
            $('#not-available')[0].checked?'':$('.not-available').hide();
            $('#final')[0].checked?'':$('.final').hide();
            $('#raw')[0].checked?'':$('.raw').hide();
            $('#pack')[0].checked?'':$('.pack').hide();
        }

    </script>
    <script src="/js/dom-to-image.min.js"></script>
@endsection
