@extends('layout.main')

@section('title')
    مشاهده مشتریان مسدود شده
@endsection

@section('content')

    <br>
    <div id="table-container">
        <table class="table table-striped" id="customer-table">
            <thead>
            <tr>
                <th>شماره</th>
                <th>نام</th>
                <th>بدهکاری(ریال)</th>
                <th>کاربر مرتبط</th>
                <th>وضعیت</th>
            </tr>
            </thead>
            <tbody>
            @foreach($customers as $id => $customer)
                <tr>
                    <td>{{$id}}</td>
                    <td>{{$customer->name}}</td>
                    <td dir="ltr">
                        <a href="/customer/transaction/{{$id}}" class="btn btn-outline-danger">
                            {{number_format($customer->balance())}}
                        </a>
                    </td>
                    <td>{{$customer->user->name}}</td>
                    <td>
                        @if($customer->block)
                            <span class="btn btn-danger" onclick="block({{$id}}, this)">مسدود</span>
                        @else
                            <span class="btn btn-success" onclick="block({{$id}}, this)">آزاد</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endsection


@section('files')
    @csrf
    <script>
        $(function () {
            $('#customer-table').DataTable({
                order: [[2, "asc"]],
                pageLength: 100,
            });
        });

        function block(id,object) {
            $.get('/changeBlock/' + id).done((res) => {
                if (res) {
                    $(object).addClass('btn-danger').removeClass('btn-success').html('مسدود');
                } else {
                    $(object).removeClass('btn-danger').addClass('btn-success').html('آزاد');
                }
            });
        }
    </script>
@endsection
