@extends('layout.main')

@section('title')
    واریزی ها
@endsection

@section('content')
    <a class="btn btn-info" href="{{route('addDeposit')}}">ثبت سند واریزی جدید</a>
    <br>
    <br>
    <table class="table table-striped" id="product-table">
        <thead>
        <tr>
            <th>#</th>
            @if($superAdmin)
                <th>نام سفیر</th>
            @endif
            <th>تاریخ ثبت</th>
            <th>مبلغ (ریال)</th>

            <th>توضیحات</th>
            <th>تصویر</th>

            <th>وضعیت</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @php
            $counter = count($deposits);
        @endphp
        @foreach($deposits as $deposit)
            @continue(!isset($users[$deposit->user_id]))
            <tr>
                <td>{{$counter--}}</td>
                @if($superAdmin)
                    <th>{{$users[$deposit->user_id]->name}}</th>
                @endif
                <td>{{verta($deposit->created_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}</td>
                <td>{{number_format($deposit->amount)}}</td>

                <td>{{$deposit->desc}}</td>
                <td>
                    @if($deposit->photo)
                        <a target="_blank" href="/deposit/{{$deposit->photo}}">
                            <p>مشاهده سند</p>
                        </a>
                    @endif
                </td>

                <td>
                    @if($deposit->confirmed)
                        <p class="btn btn-success" @if($superAdmin) id="confirm{{$deposit->id}}"
                           onclick="confirm_deposit({{$deposit->id}})" @endif>
                            تایید شده</p>
                    @else
                        <p class="btn btn-danger" @if($superAdmin) id="confirm{{$deposit->id}}"
                           onclick="confirm_deposit({{$deposit->id}})" @endif>تایید
                            نشده</p>
                    @endif
                </td>
                <td>
                    <div id="operation{{$deposit->id}}" @if($deposit->confirmed) style="display: none" @endif>
                        <a class="fa fa-edit btn btn-primary" href="/deposit/edit/{{$deposit->id}}"
                           title="ویرایش"></a>
                        <i class="fa fa-trash-alt btn btn-danger" onclick="delete_deposit({{$deposit->id}})"
                           title="حذف"></i>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection


@section('files')
    @csrf
    <script>
        $(function () {
            $('#product-table').DataTable();
        });

        function delete_deposit(id) {
            confirm("برای همیشه حذف شود؟") ?
                $.post('/deposit/delete/' + id, {_token: "{{ csrf_token() }}"})
                    .done(res => {
                        location.reload();
                    })
                :
                ""
        }

        @if($superAdmin)
        function confirm_deposit(id) {
            $.post('/deposit/changeConfirm/' + id, {_token: "{{ csrf_token() }}"})
                .done(res => {
                    if (res) {
                        $('#confirm' + id).removeClass('btn-danger').addClass('btn-success').html('تایید شده');
                        $('#operation' + id).hide();
                    } else {
                        $('#confirm' + id).removeClass('btn-success').addClass('btn-danger').html('تایید نشده');
                        $('#operation' + id).show();
                    }
                })
        }
        @endif

    </script>
@endsection
