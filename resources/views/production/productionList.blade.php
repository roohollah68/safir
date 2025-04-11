@extends('layout.main')

@section('title', 'لیست درخواست‌ تولید')

@section('content')

<table id="productionTable" class="table table-striped" style="width:100%; text-align: center;">
    <thead>
        <tr>
            <th>شماره</th>
            <th>شناسه محصول</th>
            <th>تاریخ ثبت</th>
            <th>محصول</th>
            <th>کاربر</th>
            <th>تعداد درخواست شده</th>
            <th>تعداد تولید شده</th> 
            <th>وضعیت</th>
            <th>عملیات</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($productions as $production)
            <tr>
                <td>{{ $production->id }}</td>
                <td>{{ $production->good_id }}</td>
                <td>{{ verta($production->created_at)->formatJalaliDate() }}</td>
                <td>{{ $production->good->name }}</td>
                <td>{{ $production->user->name }}</td>
                <td>{{ number_format($production->requested_quantity) }}</td>
                <td>
                    <span class="produced-quantity-text" data-id="{{ $production->id }}">
                        {{ number_format($production->produced_quantity) }}
                    </span>
                    <input type="number" class="form-control produced-quantity-input" 
                           value="{{ $production->produced_quantity }}" 
                           data-id="{{ $production->id }}" 
                           style="width: 80px; text-align: center; display: none;" />
                    <button class="btn btn-sm btn-secondary edit-quantity-btn" 
                            data-id="{{ $production->id }}" 
                            style="margin-top: 5px;">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>
                <td>{{ $production->status_in_persian }}</td>
                <td>
                    <a href="{{ route('production.edit', $production->id) }}">
                        <i class="btn btn-primary fas fa-edit" title="ویرایش"></i>
                    </a>
                    <button class="btn btn-danger delete-production-btn" data-id="{{ $production->id }}" title="حذف">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#productionTable').DataTable({
            paging: false,
            order: [
                [0, "asc"]
            ],
            language: language
        });

        $('.edit-quantity-btn').on('click', function() {
            const productionId = $(this).data('id');
            const textSpan = $(`.produced-quantity-text[data-id="${productionId}"]`);
            const inputField = $(`.produced-quantity-input[data-id="${productionId}"]`);

            textSpan.hide();
            inputField.show().prop('disabled', false).focus();

            inputField.on('blur', function() {
                const newQuantity = inputField.val();
                textSpan.text(newQuantity).show();
                inputField.hide().prop('disabled', true);

                $.ajax({
                    url: `/production/updateQuantity/${productionId}`,
                    method: 'POST',
                    data: {
                        produced_quantity: newQuantity,
                        _token: token
                    },
                    success: function(response) {
                        console.log('تعداد تولید شده با موفقیت به‌روزرسانی شد.');
                    },
                    error: function() {
                        console.log('خطا در به‌روزرسانی تعداد تولید شده.');
                    }
                });
            });
        });

        $('.delete-production-btn').on('click', function() {
            const productionId = $(this).data('id');
            if (confirm('آیا از حذف این درخواست تولید اطمینان دارید؟')) {
                $.ajax({
                    url: `/production/${productionId}`,
                    method: 'DELETE',
                    data: {
                        _token: token
                    },
                    success: function(response) {
                        alert('درخواست تولید با موفقیت حذف شد.');
                        location.reload();
                    },
                    error: function() {
                        alert('خطا در حذف درخواست تولید.');
                    }
                });
            }
        });
    });
</script>
@endsection
