@extends('layout.main')

@section('title')
    برنامه تولید
@endsection

@section('content')

    <span>مکان انبار: {{$ware->name}}</span>
    <br>
    <table class="table table-striped" id="product-table">
        <thead>
        <tr>
            <th>شماره</th>
            <th>نام</th>
            <th>قیمت(ریال)</th>
            <th>موجودی</th>
            <th>حد پایین</th>
            <th>حد بالا</th>
            <th>میزان تولید</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $id => $product)
            @continue($product->good->category != 'final')
            <tr>
                <td>{{$id}}</td>
                <td>{{$product->good->name}}</td>
                <td>{{number_format($product->good->price)}}</td>
                {{--                <td>{{number_format($product->good->productPrice)}}</td>--}}
                <td dir="ltr">{{+$product->quantity}}</td>
                <td>{{$product->alarm}}</td>
                <td>{{$product->high_alarm}}</td>
                <td>{{$product->high_alarm - $product->quantity}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection


@section('files')
    <script>

        $(function () {
            table = $('#product-table').DataTable({
                order: [[6, "desc"]],
                pageLength: 100,
                layout: {
                    topStart: {
                        buttons: [
                            {
                                extend: 'excel',
                                text: 'دریافت فایل اکسل',
                                filename:  'برنامه تولید ' + '{{verta()->formatJalaliDate()}}',
                                title: null,
                                exportOptions: {
                                    modifier: {
                                        page: 'current'
                                    }
                                }
                            }
                        ]
                    }
                }
            });
        })

    </script>
@endsection
