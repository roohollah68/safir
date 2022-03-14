@extends('layout.main')

@section('title')
    آمار فروش محصولات
@endsection

@section('content')
    <form action="">


    </form>
    <br>
    <br>
    <table class="stripe" id="statistic-table">
        <thead>
        <tr>
            <th>نام محصول</th>
            <th>تعداد فروش</th>
            <th>مبلغ کل(تومان)</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)
            <tr>
                <td>{{$product->name}}</td>
                <td>{{$product->number}}</td>
                <td>{{number_format($product->total)}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection


@section('files')
    @csrf
    <script>
        $(function () {
            $('#statistic-table').DataTable({
                order: [[2, "desc"]],
            });
        });



    </script>
@endsection
