@extends('layout.main')

@section('title')
    @if (!$edit)
        ثبت درخواست تولید
    @else
        ویرایش درخواست تولید
    @endif
@endsection

@section('content')
<div class="container mt-5">

    {{-- FORM --}}
    <div class="container mt-5">
        <form action="{{ $edit ? route('production.update', $production->id) : route('production.store') }}" method="POST">
            @csrf
            @if($edit)
                @method('POST')
            @endif
            <div class="row my-4">
                {{-- محصول --}}
                <div class="col-md-6 my-2">
                    <div class="form-group input-group required">
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="good_id" class="input-group-text w-100">محصول:</label>
                        </div>
                        <input type="text" id="good_name" name="good_name" class="form-control" value="{{ old('good_name', $production->good->name ?? null) }}" placeholder="جستجوی محصول..." required>
                        <input type="hidden" id="good_id" name="good_id" value="{{ old('good_id', $production->good->id ?? null) }}">
                    </div>
                </div>

                {{-- تعداد --}}
                <div class="col-md-6 my-2">
                    <div class="form-group input-group required">
                        <div class="input-group-append" style="min-width: 160px">
                            <label for="amount" class="input-group-text w-100">تعداد:</label>
                        </div>
                        <input type="number" id="amount" name="amount" 
                            class="form-control" value="{{ old('amount', $production->amount ?? null) }}" required>
                    </div>
                </div>
            </div>

            <div class="row my-4">
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-success">{{ $edit ? 'ویرایش' : 'ثبت' }}</button>
                    <a href="{{ route('productionList') }}" class="btn btn-danger">بازگشت</a>
                </div>
            </div>
        </form>
    </div>

    {{-- TABLE TABS --}}

    <div id="tabs" class="mt-5">
        <ul class="nav nav-tabs">
            <li class="text-dark custom-tab"><a class="custom-link active" href="#productionTable">لیست تولید</a></li>
            <li class="text-dark custom-tab"><a class="custom-link" href="#history">تاریخچه</a></li>
        </ul>

        <div class="tab-content mt-3">
            {{-- Production Tab --}}
            <div id="productionTable" class="tab-pane active">
                <table id="alertTable" class="table table-striped" style="width:100%; text-align: center;">
                    <thead>
                        <tr>
                            <th>نام کالا</th>
                            <th>موجودی</th>
                            <th>حد پایین</th>
                            <th>حد بالا</th>
                            <th>تعداد مورد نیاز</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>{{ $product->good->name ?? 'نامشخص' }}</td>
                                <td>{{ number_format($product->quantity) }}</td>
                                <td>{{ $product->alarm }}</td>
                                <td>{{ $product->high_alarm }}</td>
                                <td>{{ number_format($product->required_quantity) }}</td>
                                <td>
                                    <button onclick="fillForm({{ $product->good_id }}, {{ $product->required_quantity }})" 
                                            class="btn btn-sm btn-success">
                                        <i class="fas fa-plus"></i> 
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- History Tab --}}
            <div id="history" class="tab-pane">
                <table id="historyTable" class="table table-striped" style="width:100%; text-align: center;">
                    <thead>
                        <tr>
                            <th>تاریخ درخواست</th>
                            <th>محصول</th>
                            <th>تعداد درخواستی</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productionHistory as $request)
                            <tr>
                                <td>{{ verta($request->created_at)->formatJalaliDate() }}</td>
                                <td>{{ $request->good->name }}</td>
                                <td>{{ number_format($request->amount)}}</td>
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
            active: 0,
        });
        
        $('#alertTable').DataTable({
            paging: false,
            order: [
                [0, "asc"]
            ],
            language: language,
            search: true
        });

        $('#historyTable').DataTable({
            paging: false,
            order: [
                [0, "desc"]
            ],
            language: language
        });

    });

    function fillForm(goodId, quantity) {
        const goods = @json($goods->map(fn($good) => ['id' => $good->id, 'name' => $good->name]));
        const selectedGood = goods.find(good => good.id === goodId);
        if (selectedGood) {
            document.getElementById('good_name').value = selectedGood.name;
        }
        document.getElementById('good_id').value = goodId;
        document.getElementById('amount').value = quantity;
        document.getElementById('amount').dispatchEvent(new Event('input'));
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
            dropdown.innerHTML = goods.filter(good => good.name.toLowerCase().includes(value))
                .map(good => `<li class="dropdown-item" style="cursor:pointer">${good.name}</li>`).join('');
            dropdown.style.display = value && dropdown.innerHTML ? 'block' : 'none';
            dropdown.querySelectorAll('li').forEach((item, i) => item.onclick = () => {
                goodInput.value = goods[i].name;
                goodIdInput.value = goods[i].id;
                dropdown.style.display = 'none';
            });
        });

        goodInput.addEventListener('blur', () => setTimeout(() => dropdown.style.display = 'none', 200));
    });
</script>
@endsection