@extends('layout.main')

@section('title')
    تخفیف ها
@endsection

@section('content')
    <a class="btn btn-info" href="{{route('addCoupon')}}">افزودن تخفیف جدید</a>
    <br>
    <br>
    <table class="table table-striped" id="coupon-table">
        <thead>
        <tr>
            <th>تخفیف(درصد)</th>
            <th>کاربران</th>
            <th>محصولات</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($coupons as $coupon)
            @php
                $user_names = [];
                $product_names = [];
            @endphp
            @foreach($coupon->couponLinks as $couponLink)
                @php
                    if($couponLink->user && $couponLink->good){
                        $user_names[$couponLink->user->id] = $couponLink->user->name;
                        $product_names[$couponLink->good->id] = $couponLink->good->name;
                    }
                @endphp
            @endforeach
            <tr>
                <td>
                    {{$coupon->percent}}
                </td>
                <td>

                    @foreach($user_names as $user_name)
                        {{$user_name}},
                    @endforeach
                </td>
                <td>

                    @foreach($product_names as $product_name)
                        {{$product_name}},
                    @endforeach
                </td>
                <td>
                    <a class="fa fa-edit btn btn-primary" href="/coupon/edit/{{$coupon->id}}"
                       title="ویرایش"></a>
                    <i class="fa fa-trash-alt btn btn-danger" onclick="delete_coupon({{$coupon->id}})"
                       title="حذف"></i>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection


@section('files')
    <script>
        $(function () {
            $('#coupon-table').DataTable();
        });

        function delete_coupon(id) {
            confirm("برای همیشه حذف شود؟") ?
                $.post('/coupon/delete/' + id, {_token: "{{ csrf_token() }}"})
                    .done(res => {
                        location.reload();
                    })
                :
                ""
        }


    </script>
@endsection
