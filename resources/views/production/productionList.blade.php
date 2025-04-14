@extends('layout.main')

@section('title', 'لیست درخواست‌ تولید')

@section('content')
<a class="btn btn-outline-success mb-5" href="{{ route('addEdit') }}">
        <span class="fa fa-plus"></span>
        افزودن درخواست تولید
    </a>
<a class="btn btn-outline-primary mb-5" href="{{ route('production.add.form') }}"><span class="fa fa-plus"></span> افزودن تولید</a>
<table id="productionTable" class="table table-striped" style="width:100%; text-align: center;">
    <thead>
        <tr>
            <th>شماره</th>
            <th>شناسه محصول</th>
            <th>تاریخ ثبت</th>
            <th>محصول</th>
            <th>کاربر</th>
            <th>تعداد درخواستی</th>
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
                <td>{{ number_format($production->amount) }}</td>
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
