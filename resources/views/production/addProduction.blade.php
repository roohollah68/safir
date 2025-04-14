@extends('layout.main')

@section('title', 'ثبت تولید')

@section('content')
<div class="container mt-5">
    <form action="{{ route('production.add') }}" method="POST">
        @csrf
        <div class="row my-4">
            {{-- انتخاب درخواست --}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required" style="position: relative;">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="request_search" class="input-group-text w-100">درخواست:</label>
                    </div>
                    <input type="text" id="request_search" 
                        class="form-control" placeholder="جستجوی درخواست..." 
                        autocomplete="off">
                    <input type="hidden" id="request_id" name="request_id">
                    <ul id="requestDropdown" class="dropdown-menu" 
                        style="position: absolute; z-index: 1000; display: none; width: 70%; top: 100%; left: 0;">
                    </ul>
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
                            <th>نام کالا</th>
                            <th>تعداد درخواست</th>
                            <th>تعداد باقی‌مانده</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $request)
                            <tr>
                                <td>{{ $request->good->name }}</td>
                                <td>{{ number_format($request->amount) }}</td>
                                <td>{{ number_format($request->amount - ($request->productions->sum('amount') ?? 0)) }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" 
                                        onclick="addRequest('{{ $request->id }}', '{{ $request->good->name }}')">
                                        <i class="fas fa-plus"></i> 
                                    </button>
                                </td>
                            </tr>
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
                            <th>محصول</th>
                            <th>تعداد تولید شده</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productionHistory as $production)
                            <tr>
                                <td>{{ verta($production->created_at)->formatJalaliDate() }}</td>
                                <td>{{ $production->good->name }}</td>
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

    function addRequest(requestId, requestName) {
        const requestSearch = document.getElementById('request_search');
        const requestIdInput = document.getElementById('request_id');
        requestSearch.value = requestName;
        requestIdInput.value = requestId;
        requestSearch.dispatchEvent(new Event('input'));
    }

    document.addEventListener('DOMContentLoaded', function () {
        const requests = @json($requests->map(fn($request) => [
            'id' => $request->id,
            'name' => $request->good->name,
            'remaining' => $request->amount - $request->productions->sum('amount')
        ]));
        
        const requestSearch = document.getElementById('request_search');
        const requestIdInput = document.getElementById('request_id');
        const dropdown = document.getElementById('requestDropdown');

        requestSearch.addEventListener('input', function () {
            const value = requestSearch.value.trim().toLowerCase();
            dropdown.innerHTML = '';
            if (value) {
                const matches = requests.filter(request => 
                    request.name.toLowerCase().includes(value) && 
                    request.remaining > 0
                );
                matches.forEach(match => {
                    const item = document.createElement('li');
                    item.className = 'dropdown-item';
                    item.textContent = `${match.name} - باقیمانده: ${match.remaining}`;
                    item.style.cursor = 'pointer';
                    item.addEventListener('click', function () {
                        requestSearch.value = match.name;
                        requestIdInput.value = match.id;  
                        dropdown.style.display = 'none';
                    });
                    dropdown.appendChild(item);
                });
                dropdown.style.display = matches.length ? 'block' : 'none';
            } else {
                dropdown.style.display = 'none';
            }
        });

        requestSearch.addEventListener('blur', function () {
            setTimeout(() => dropdown.style.display = 'none', 200);
        });
    });
</script>
@endsection