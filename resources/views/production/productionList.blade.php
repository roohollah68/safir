@extends('layout.main')

@section('title', 'لیست تولید')

@section('content')
<a class="btn btn-outline-success mb-5" href="{{ route('addEdit') }}">
        <span class="fa fa-plus"></span>
        افزودن درخواست تولید
    </a>
<a class="btn btn-outline-primary mb-5" href="{{ route('production.add.form') }}"><span class="fa fa-plus"></span> افزودن تولید انجام شده</a>
<table id="productionTable" class="table table-striped" style="width:100%; text-align: center;">
    <thead>
        <tr>
            <th>شماره</th>
            <th>شناسه محصول</th>
            <th>محصول</th>
            <th>تعداد مورد نیاز</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($productions->unique('good_id') as $production)
            @if ($production->remaining_requests > 0)
            <tr>
                <td>{{ $production->id }}</td>
                <td>{{ $production->good_id }}</td>
                <td>{{ $production->good->name }}</td>
                <td>{{ number_format($production->remaining_requests) }}</td>
            </tr>
            @endif
        @endforeach
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#productionTable').DataTable({
            paging: false,
            order: [
            [0, "desc"]
            ],
            language: language,
            dom: 'Bfrtip',
            buttons: [
            {
            extend: 'excelHtml5',
            text: '<i class="fas fa-file-excel me-1"></i> فایل اکسل',
            className: 'btn btn-success mb-3',
            }
            ],
            searching: true,
        });
    });
</script>
@endsection
