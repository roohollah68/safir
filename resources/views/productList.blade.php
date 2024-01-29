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
    <span class="btn btn-primary" onclick="hide([])">همه محصولات</span>
    <span class="btn btn-warning" onclick="hide(['na','hq'])">موجودی کم</span>
    <span class="btn btn-success" onclick="hide(['na'])">محصولات موجود</span>
    <span class="btn btn-danger" onclick="hide(['a'])">محصولات ناموجود</span>
    <span class="btn btn-info" onclick="hide(['na','r','p'])">محصول نهایی</span>
    <span class="btn btn-secondary" onclick="hide(['na','f','p'])">مواد اولیه</span>
    <span class="btn btn-secondary" onclick="hide(['na','f','r'])">ملزومات بسته بندی</span>
    <br>
    <br>
    <table class="stripe" id="product-table">
        <thead>
        <tr>
            <th>شماره</th>
            <th>نام</th>
            <th>قیمت(ریال)</th>
            <th>موجودی</th>
            <th>حدآلارم</th>
            <th>وضعیت</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)
            <tr class="{{$product->alarm > $product->quantity ? 'bg-warning ' : 'high-quantity '}}
            {{$product->available?'available ':'not-available '}} {{$product->category}}" id="row_{{$product->id}}">
                <td>{{$product->id}}</td>
                <td><a class="btn" href="/productQuantity/add/{{$product->id}}">{{$product->name}}</a></td>
                <td>{{number_format($product->price)}}</td>
                <td>{{$product->quantity}}</td>
                <td>{{$product->alarm}}</td>
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
            hide(['na']);
        });

        function draw(){
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

        function hide(list) {
            let array = {
                hq: '.high-quantity',
                a: '.available',
                na: '.not-available',
                f: '.final',
                r: '.raw',
                p: '.pack'
            };
            $.each(array, (index, value) => {
                $(value).show();
            });
            $.each(array, (index, value) => {
                if (list.includes(index)) {
                    $(value).hide();
                }
            });
        }

    </script>
    <script src="/js/dom-to-image.min.js"></script>
@endsection
