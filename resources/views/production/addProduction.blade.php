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
                <div id="formError" style="display: none"></div>
                <button type="submit" class="btn btn-success" id="submitBtn">ثبت تولید</button>
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
                                <tr data-good-id="{{ $request->good_id }}" 
                                    data-has-formulation="{{ $request->good->formulations->isNotEmpty() ? 1 : 0 }}">
                                    <td>{{ $request->good->id }}</td>
                                    <td>{{ $request->good->name }}</td>
                                    <td>{{ number_format($request->good->remainingRequests()) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" 
                                                onclick="addRequest('{{ $request->good->id }}', '{{ $request->good->name }}')">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        
                                        @if($request->good->formulations->isNotEmpty())
                                            <a href="{{ route('formulation.edit', ['id' => $request->good->id]) }}" 
                                            class="btn btn-sm btn-info" 
                                            target="_blank"
                                            data-toggle="tooltip" 
                                            title="مشاهده/ویرایش فرمولاسیون">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('formulation.add') }}" 
                                            class="btn btn-sm btn-danger"
                                            target="_blank"
                                            data-toggle="tooltip" 
                                            title="افزودن فرمولاسیون">
                                                <i class="fas fa-flask text-light"></i>
                                            </a>
                                        @endif
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
        
        $('#good_name').on('input', function() {
            const goodId = $('#good_id').val();
            if(goodId) updateSubmitButton(goodId);
        });
    });

    function addRequest(goodId, goodName) {
        document.getElementById('good_name').value = goodName;
        document.getElementById('good_id').value = goodId;
        updateSubmitButton(goodId);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const initialGoodId = $('#good_id').val();
        if (initialGoodId) {
            updateSubmitButton(initialGoodId);
        }
        const goods = @json($requests->unique('good_id')->map(fn($request) => [
            'id' => $request->good->id,
            'name' => $request->good->name
        ]));
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
                    updateSubmitButton(item.dataset.id);
                };
            });
        });

        goodInput.addEventListener('blur', () => setTimeout(() => dropdown.style.display = 'none', 200));
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        const goodId = document.getElementById('good_id').val();
        if(!goodId) {
            e.preventDefault();
            return;
        }
        const row = $(`tr[data-good-id="${goodId}"]`);
        const hasFormulation = row.length ? row.data('has-formulation') : false;

        if(!hasFormulation) {
            e.preventDefault();
            $('#formError').show().html(`
                <div class="alert alert-danger mt-3 d-inline-block">
                    <i class="fas fa-exclamation-triangle"></i>
                    فرمولاسیون برای این محصول ثبت نشده است!
                    <a href="/formulation/add" target="_blank" class="alert-link">
                        (افزودن فرمولاسیون)
                    </a>
                </div>
            `);
            window.scrollTo(0, 0);
        }
    });

    function updateSubmitButton(goodId) {
        const row = $(`tr[data-good-id="${goodId}"]`);
        const hasFormulation = row.length ? row.data('has-formulation') : false;
        
        $('#submitBtn').prop('disabled', !hasFormulation);
        
        if(!hasFormulation) {
            $('#formError').show().html(`
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    فرمولاسیون برای این محصول ثبت نشده است!
                    <a href="/formulation/add" target="_blank" class="alert-link">
                        (افزودن فرمولاسیون)
                    </a>
                </div>
            `);
        } else {
            $('#formError').hide();
        }
    }
</script>
@endsection