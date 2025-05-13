@extends('layout.main')

@section('title')
    لیست هزینه‌های ثابت
@endsection

@section('content')
    <a class="btn btn-outline-success mb-3" href="{{ route('fixed-costs.create') }}">
        <span class="fa fa-plus"></span>
            افزودن هزینه‌ ثابت
    </a>

    <div class="mb-4">
        <div aria-label="Category Filters">
            <input type="radio" class="btn-check filter-radio" name="categoryFilter" id="all" value="all" checked>
            <label class="btn btn-outline-primary" for="all">همه</label>

            <input type="radio" class="btn-check filter-radio" name="categoryFilter" id="category0" value="0">
            <label class="btn btn-outline-secondary" for="category0">حقوق تولید</label>

            <input type="radio" class="btn-check filter-radio" name="categoryFilter" id="category1" value="1">
            <label class="btn btn-outline-secondary" for="category1">حقوق فروش</label>

            <input type="radio" class="btn-check filter-radio" name="categoryFilter" id="category9" value="9">
            <label class="btn btn-outline-secondary" for="category9">هزینه‌ی اجاره</label>

            <input type="radio" class="btn-check filter-radio" name="categoryFilter" id="category6" value="6">
            <label class="btn btn-outline-secondary" for="category6">هزینه‌ی بیمه</label>

            <input type="radio" class="btn-check filter-radio" name="categoryFilter" id="category16" value="16">
            <label class="btn btn-outline-secondary" for="category16">هزینه‌ی تبلیغات</label>
        </div>
    </div>

    <div class="alert alert-info mb-3 py-1 d-inline-block">
        <i class="fa fa-coins"></i>
        <strong>مجموع مبلغ:</strong> {{ number_format($totalAmount) }} ریال
    </div>

    <table id="fixedCostsTable" class="table table-striped mt-3" style="width:100%; text-align: center;">
        <thead>
            <tr>
                <th>شناسه</th>
                <th>دسته‌بندی</th>
                <th>مبلغ (ریال)</th>
                <th>صاحب حساب</th>
                <th>شماره حساب</th>
                <th>بابت</th>
                <th>روز سررسید</th>
                <th>موقعیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($fixedCosts as $fixedCost)
                <tr>
                    <td>{{ $fixedCost->id }}</td>
                    <td>{{ $expenseTypes[$fixedCost->category] ?? 'نامشخص' }}</td>
                    <td>{{ number_format($fixedCost->amount) }}</td>
                    <td>{{ $fixedCost->account_owner }}</td>
                    <td>{{ $fixedCost->iban }}</td>
                    <td>{{ $fixedCost->desc }}</td>
                    <td>{{ $fixedCost->due_day }}</td>
                    <td>{{ $withdrawalLocations[$fixedCost->location] ?? 'نامشخص' }}</td>
                    <td>
                        <a href="{{ route('fixed-costs.edit', $fixedCost->id) }}">
                        <i class="btn btn-primary fas fa-edit" title="ویرایش"></i></a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('files')
    <script>
        $(document).ready(function() {
            const table = $('#fixedCostsTable').DataTable({
                paging: false,
                order: [[0, 'desc']],
                searching: true,
                language: language,
                "columnDefs": [
                { "targets": [2,4,5,6], "className": "dt-nowrap" }
                ]
            });

            $.fn.dataTable.ext.search.push(function(settings, data) {
                const selectedCategory = $('input[name="categoryFilter"]:checked').val();
                const rowCategory = data[1].trim();
                if (selectedCategory === 'all') {
                    return true;
                }
                return rowCategory === @json($expenseTypes)[selectedCategory];
            });

            $('input.filter-radio').on('change', function() {
                table.draw();
            });
        });
    </script>
@endsection