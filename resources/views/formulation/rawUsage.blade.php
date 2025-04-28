@extends('layout.main')

@section('title')
    لیست مواد اولیه و بسته‌بندی
@endsection

@section('content')
    <table class="table table-striped" id="raw-usage-table">
        <thead>
            <tr>
                <th>شناسه کالا</th>
                <th>ماده اولیه/ بسته‌بندی</th>
                <th>مقدار استفاده در محصولات نهایی</th>
                <th>مقدار کل مصرفی</th>
            </tr>
        </thead>
        <tbody>
        @foreach($rawMaterial as $material)
            <tr>
                <td>{{ $material->material->id }}</td>
                <td>
                    {{ $material->material->name }}
                    <small class="text-muted">
                        ({{ $material->material->unit->name }})
                    </small>
                </td>
                <td>
                    @foreach($material->usage as $usage)
                        <div class="mb-1">
                            {{ $usage->final_product }} <i class="fas fa-arrow-left text-primary"></i> 
                            {{ number_format($usage->amount, 4) }}
                        </div>
                    @endforeach
                </td>
                <td>
                    {{ number_format($material->total, 3) }}
                    {{ $material->material->unit->name }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection

@section('files')
    <script>
        $(function () {
            $('#raw-usage-table').DataTable({
                pageLength: 50,
                order: [[3, "desc"]],
                columnDefs: [
                    { 
                        type: 'num-fmt',
                        targets: [3]
                    },
                    { 
                        orderable: false, 
                        targets: [2]
                    }
                ],
                language: {
                    search: "جست‌وجو:&nbsp;",
                }
            });
        });
    </script>
@endsection