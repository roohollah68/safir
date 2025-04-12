@extends('layout.main')

@section('title')
    دانلود اکسل برای سامانه کیسان
@endsection

@section('content')

    <a class="btn btn-danger m-1" href="{{route('productList')}}">بازگشت</a>
    <br>
    <div>
        <table class="table table-striped" id="table">
            <thead>
            <tr>
                <th>شناسه کالا</th>
                <th>شناسه داخلی</th>
                <th>نام کالا</th>
                <th>نوع کالا</th>
                <th>عمومی / اختصاصی</th>
                <th>مشمول /غیر مشمول</th>
                <th>نرخ مالیات</th>
                <th>کد واحد اندازگیری</th>
            </tr>
            </thead>
            <tbody>
            @foreach($goods as $id=>$good)
                <form>
                    <tr>
                        <td>{{$good->tag}}</td>
                        <td>{{$id}}</td>
                        <td>{{$good->name}}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{$good->vat?10:0}}</td>
                        <td></td>
                    </tr>
                </form>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('files')

    <script>
        let dataTable;

        $(function () {
            dataTable = $('#table').DataTable({
                paging: false,
                destroy: true,
                language:language,
                layout: {
                    topStart: {
                        buttons: [
                            {
                                extend: 'excel',
                                text: 'دریافت فایل اکسل',
                                filename: 'کالا-{{verta()->formatDatetime()}}',
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
