@extends('layout.main')

@section('title')
    محصولات
@endsection

@section('content')
    <a class="btn btn-info" href="{{route('addProduct')}}">افزودن محصول جدید</a>
    <br>
    <br>
    <table class="stripe" id="product-table">
        <thead>
        <tr>
            <th>تصویر</th>
            <th>نام</th>
            <th>قیمت(تومان)</th>
            <th>وضعیت</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)
            <tr>
                <td>
                    <a target="_blank" href="/product_photo/{{$product->photo?$product->photo:'empty.jpg'}}">
                        <img src="/product_photo/{{$product->photo?$product->photo:'empty.jpg'}}" height="60">
                    </a>
                </td>
                <td>{{$product->name}}</td>
                <td>{{number_format($product->price)}}</td>
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
            $('#product-table').DataTable();
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
@endsection
