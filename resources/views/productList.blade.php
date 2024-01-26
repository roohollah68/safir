@extends('layout.main')

@section('title')
    محصولات
@endsection

@section('content')
    <a class="btn btn-info" href="{{route('addProduct')}}">
        <span class=" fa fa-plus"></span>
        افزودن محصول جدید
    </a>
    <span class="btn btn-primary" onclick="$('.high-quantity, .available, .not-available').show()">همه محصولات</span>
    <span class="btn btn-warning" onclick="$('.available').show();$('.high-quantity, .not-available').hide()">موجودی کم</span>
    <span class="btn btn-success" onclick="$('.high-quantity, .available').show();$('.not-available').hide()">محصولات موجود</span>
    <span class="btn btn-danger" onclick="$('.high-quantity, .not-available').show();$('.available').hide()">محصولات ناموجود</span>
{{--    <span class="btn btn-secondary" onclick="print()">پرینت</span>--}}
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
            <tr class="{{$product->alarm > $product->quantity ? 'bg-warning' : 'high-quantity '}}
            {{$product->available?'available':'deleted not-available'}}">
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
        // let notAvailable = true;
        // let highQuantity = false;
        // function hide_show(){
        //     $('.not-available').show();
        //     $('.high-quantity').show();
        //     if(notAvailable)
        //         $('.not-available').hide();
        //     if(highQuantity)
        //         $('.high-quantity').hide();
        // }

        $(function () {
            $('#product-table').DataTable({
                order: [[3, "desc"]],
                paging: false,
            });
            $('#all, #low, #not-available').checkboxradio();
            hide_show();
        });

        function delete_product(id) {
            confirm("برای همیشه حذف شود؟") ?
                $.post('/product/delete/' + id, {_token: "{{ csrf_token() }}"})
                    .done(res => {
                        location.reload();
                    })
                :
                ""
        }

        // function print(){
        //     domtoimage.toJpeg(document.getElementById('product-table'), {})
        //         .then(function (dataUrl) {
        //             var link = document.createElement('a');
        //             link.download = 'محصولات.jpeg';
        //             link.href = dataUrl;
        //             link.click();
        //             submit = true;
        //             $('#form').submit();
        //         });
        //     return false;
        // }
    </script>
    <script src="/js/dom-to-image.min.js"></script>
    <style>
        .deleted {
            display: none;
        }
    </style>
@endsection
