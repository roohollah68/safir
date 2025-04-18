@extends('layout.main')

@section('title', 'ثبت تولید')

@section('content')
<div class="container mt-5">
    <form action="{{ route('production.add') }}" method="POST">
        @csrf
        <div class="row my-4">
            {{-- انتخاب درخواست  --}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="good_id" class="input-group-text w-100">محصول:</label>
                    </div>
                    <input type="text" id="good_name" name="good_name" class="form-control" placeholder="جستجوی محصول..." required>
                    <input type="hidden" id="good_id" name="good_id">
                </div>
            </div>

            {{-- تعداد تولید شده --}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="amount" class="input-group-text w-100">تعداد تولید شده:</label>
                    </div>
                    <input type="number" id="amount" name="amount" 
                        class="form-control"
                        required>
                </div>
            </div>
        </div>

        <div class="row my-4">
            <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-success">ثبت تولید</button>
                <a href="{{ route('productionList') }}" class="btn btn-danger">بازگشت</a>
            </div>
        </div>
    </form>

    {{-- TABLE TABS --}}
    <div id="tabs" class="mt-5">
        <ul class="nav nav-tabs">
            <li class="text-dark custom-tab"><a class="custom-link active" href="#currentRequests">درخواست‌ها </a></li>
            <li class="text-dark custom-tab"><a class="custom-link" href="#productionHistory">تاریخچه</a></li>
        </ul>

        <div class="tab-content mt-3">
            {{-- Requests Tab --}}
            <div id="currentRequests" class="tab-pane active">
                <table id="currentRequestsTable" class="table table-striped" style="width:100%; text-align: center;">
                    <thead>
                        <tr>
                            <th>شناسه محصول</th>
                            <th>نام کالا</th>
                            <th>تعداد باقی‌مانده</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests->unique('good_id') as $request)
                            @if ($request->good->remainingRequests() > 0)
                                <tr>
                                    <td>{{ $request->good->id }}</td>
                                    <td>{{ $request->good->name }}</td>
                                    <td>{{ number_format($request->good->remainingRequests()) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" 
                                            onclick="addRequest('{{ $request->good->id }}', '{{ $request->good->name }}')">
                                            <i class="fas fa-plus"></i> 
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- History Tab --}}
            <div id="productionHistory" class="tab-pane">
                <table id="productionHistoryTable" class="table table-striped" style="width:100%; text-align: center;">
                    <thead>
                        <tr>
                            <th>تاریخ تولید</th>
                            <th>شناسه محصول</th>
                            <th>محصول</th>
                            <th>کاربر</th>
                            <th>تعداد تولید شده</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productionHistory as $production)
                            <tr>
                                <td>{{ verta($production->created_at)->formatJalaliDate() }}</td>
                                <td>{{ $production->good->id }}</td>
                                <td>{{ $production->good->name }}</td>
                                <td>{{ $production->user->name }}</td>
                                <td>{{ number_format($production->amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#tabs").tabs({
            active: 0
        });

        $('#currentRequestsTable').DataTable({
            paging: false,
            order: [
                [0, "asc"]
            ],
            language: language,
            search: true
        });

        $('#productionHistoryTable').DataTable({
            paging: false,
            order: [
                [0, "desc"]
            ],
            language: language
        });
    });

    function addRequest(goodId, goodName) {
        document.getElementById('good_name').value = goodName;
        document.getElementById('good_id').value = goodId;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const goods = @json($goods->map(fn($good) => ['id' => $good->id, 'name' => $good->name]));
        const goodInput = document.getElementById('good_name');
        const goodIdInput = document.getElementById('good_id');
        const dropdown = document.createElement('ul');
        Object.assign(dropdown.style, {
            position: 'absolute', zIndex: '1000', display: 'none', width: `${goodInput.offsetWidth}px`,
            marginTop: `${goodInput.offsetHeight}px`, left: `${goodInput.offsetLeft}px`
        });
        dropdown.className = 'dropdown-menu';
        goodInput.parentNode.style.position = 'relative';
        goodInput.parentNode.appendChild(dropdown);

        goodInput.addEventListener('input', () => {
            const value = goodInput.value.trim().toLowerCase();
            const filteredGoods = goods.filter(good => good.name.toLowerCase().includes(value));
            
            dropdown.innerHTML = filteredGoods.map(good => `
                <li class="dropdown-item" 
                    style="cursor:pointer" 
                    data-id="${good.id}" 
                    data-name="${good.name}">
                    ${good.name}
                </li>
            `).join('');

            dropdown.style.display = value && dropdown.innerHTML ? 'block' : 'none';
            
            dropdown.querySelectorAll('li').forEach(item => {
                item.onclick = () => {
                    goodInput.value = item.dataset.name;
                    goodIdInput.value = item.dataset.id;
                    dropdown.style.display = 'none';
                };
            });
        });

        goodInput.addEventListener('blur', () => setTimeout(() => dropdown.style.display = 'none', 200));
    });
</script>
@endsection