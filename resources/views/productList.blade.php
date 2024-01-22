@extends('layout.main')

@section('title')
    محصولات
@endsection

@section('content')
    <a class="btn btn-info" href="{{route('addProduct')}}">افزودن محصول جدید</a>
    <span class="btn btn-warning" onclick="$('.high-quantity').toggle()">
        <span class="fa fa-check deleted high-quantity"></span>محصولات با موجودی کم
    </span>
    <span class="btn btn-primary" onclick="$('.not-available').toggle()">
        <span class="fa fa-check not-available"></span>محصولات موجود
    </span>
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
            <tr class="{{$product->alarm > $product->quantity ? '' : 'high-quantity'}}
            {{$product->available?'':'deleted not-available'}}">
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
        $(function () {
            $('#product-table').DataTable({
                order: [[3, "desc"]],
                paging: false,
            });
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
    </script>
    <style>
        .deleted {
            display: none;
        }
    </style>
@endsection
