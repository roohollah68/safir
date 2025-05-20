@extends('layout.main')

@section('title')
    لیست چک‌ها
@endsection

@section('content')
    {{-- فیلترها --}}

    <div class="container mt-4">
        @php 
            $badgeClass = config('chequeStatus.statusClasses'); 
            $statuses = config('chequeStatus.statuses');
        @endphp

        <div class="mb-4">
            <form action="#" method="post">
                @csrf
                <div class="filter-section">
                    <input type="radio" class="btn-check" id="all" name="state" value=""
                        {{ request('state', 'type') === '' ? 'checked' : '' }}
                        onclick="document.querySelectorAll('input[name=type]').forEach(el => el.checked = false)">
                    <label class="btn btn-outline-primary" for="all">همه</label>

                    @foreach(config('chequeStatus.statuses') as $key => $status)
                        <input type="radio" class="btn-check" id="state_{{ $key }}" name="state" value="{{ $key }}"
                            {{ request('state') === (string)$key ? 'checked' : '' }}>
                        <label class="btn btn-outline-{{ $badgeClass[$key] }}" for="state_{{ $key }}">{{ $status }}</label>
                    @endforeach
                </div>
                <div class="filter-section mb-2 mt-2">
                    <input type="radio" class="btn-check" id="type_official" name="type" value="official"
                        {{ request('type') === 'official' ? 'checked' : '' }}>
                    <label class="btn btn-outline-success me-1" for="type_official">رسمی</label>
                    <input type="radio" class="btn-check" id="type_unofficial" name="type" value="unofficial"
                        {{ request('type') === 'unofficial' ? 'checked' : '' }}>
                    <label class="btn btn-outline-dark me-1" for="type_unofficial">غیررسمی</label>
                    <div class="vr ms-3 mb-2"></div>
                    <div class="filter-section ms-3 mb-2">
                        <span class="btn btn-outline-secondary me-2"
                            onclick="$('#from').val('{{ verta()->format('Y/m/d') }}');$('#to').val('{{ verta()->addMonth(1)->format('Y/m/d') }}')">
                            یک ماه آینده
                        </span>
                        <span class="btn btn-outline-danger"
                            onclick="$('#from').val('');$('#to').val('{{ verta()->format('Y/m/d') }}')">
                            تاریخ گذشته
                        </span>
                    </div>
                </div>
                <div class="filter-section mb-0 mt-2 d-flex align-items-start">
                    <label for="from">از تاریخ: </label>
                    <input type="text" class="form-control" style="width: 120px" id="from" name="from"
                        value="{{ $from ?? '' }}">
                    <label for="to">تا تاریخ: </label>
                    <input type="text" class="form-control" style="width: 120px" id="to" name="to"
                        value="{{ $to ?? '' }}">
                    <button type="submit" class="btn btn-primary mb-0 mt-0">فیلتر</button>
                </div>
            </form>
        </div>
    </div>

    {{-- جداول --}}

    <div id="tabs" dir="rtl">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="received-tab" data-bs-toggle="tab" href="#received" role="tab"
                    aria-controls="received" aria-selected="true">
                    چک‌های ورودی
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="given-tab" data-bs-toggle="tab" href="#given" role="tab" aria-controls="given"
                    aria-selected="false">
                    چک‌های خروجی
                </a>
            </li>
        </ul>

        <div id="received" class="tab-pane fade show active">
            <h3 class="mt-4 mb-4">چک‌های ورودی
                <br>
                <span class="badge bg-primary mt-3" style="font-size: 1rem">
                    جمع کل: {{ number_format($receivedTotal) }} ریال
                </span>
            </h3>

            <table id="receivedTable" class="table table-striped mt-4 mb-4" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>کاربر</th>
                        <th>صاحب حساب</th>
                        <th>تاریخ وصول</th>
                        <th>مبلغ (ریال)</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($receivedCheque as $index => $cheque)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $cheque->customer->name }}</td>
                            <td>
                                <a href="/customer/transaction/{{ $cheque->customer_id }}"
                                    class="text-primary text-decoration-none hover:text-decoration-underline">
                                    {{ $cheque->cheque_name }}
                                </a>
                            </td>
                            <td>{{ isset($cheque->cheque_date) ? verta($cheque->cheque_date)->formatJalaliDate() : 'نامشخص' }}
                            </td>
                            <td>{{ number_format($cheque->amount) }}</td>
                            <td>
                                <button type="button"
                                    class="btn btn-{{ $badgeClass[$cheque->cheque_status] }} btn-sm"
                                    onclick="confirmPassCheque({{ $cheque->id }}, 'received', '{{ $cheque->cheque_status }}')" 
                                    style="font-family: inherit">
                                    {{ $statuses[$cheque->cheque_status] ?? 'نامشخص' }}
                                </button>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <a href="/customerDeposit/edit/{{ $cheque->customer_id }}/{{ $cheque->id }}" 
                                       class="btn btn-warning btn-sm mx-1" 
                                       title="ویرایش">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <span class="fa fa-eye btn btn-info btn-sm mx-1 p-2" 
                                          onclick="view_recieved_cheque({{ $cheque->id }})" 
                                          title="مشاهده"></span>
                                    <button class="btn btn-sm btn-secondary mx-1" 
                                        onclick="showHistory({{ $cheque->id }}, 'received')">
                                        <i class="fa fa-history"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div id="given" class="tab-pane fade">
            <h3 class="mt-4 mb-4">چک‌های خروجی
                <br>
                <span class="badge bg-primary mt-3" style="font-size: 1rem">
                    جمع کل: {{ number_format($givenTotal) }} ریال
                </span>
            </h3>
            <table id="givenTable" class="table table-striped mt-4 mb-4" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>کاربر</th>
                        <th>صاحب حساب</th>
                        <th>تاریخ وصول</th>
                        <th>مبلغ (ریال)</th>
                        <th>شناسه ملی</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($givenCheque as $index => $cheque)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $cheque->user->name }}</td>
                            <td>{{ $cheque->account_name }}</td>
                            <td>{{ isset($cheque->cheque_date) ? verta($cheque->cheque_date)->formatJalaliDate() : 'نامشخص' }}
                            </td>
                            <td>{{ number_format($cheque->amount) }}</td>
                            <td>{{ $cheque->cheque_id }}</td>
                            <td>
                                <button type="button"
                                    class="btn btn-{{ $badgeClass[$cheque->cheque_status] }} btn-sm"
                                    onclick="confirmPassCheque({{ $cheque->id }}, 'given', '{{ $cheque->cheque_status }}')"
                                    style="font-family: inherit;">
                                    {{ $statuses[$cheque->cheque_status] ?? 'نامشخص' }}
                                </button>
                            </td>
                            <td>
                                <div class="ms-2">
                                    <span class="fa fa-eye btn btn-sm p-2 btn-info" onclick="view_given_cheque({{ $cheque->id }})"
                                          title="مشاهده"></span>
                                    <button class="btn btn-sm btn-secondary" 
                                        onclick="showHistory({{ $cheque->id }}, 'given')">
                                        <i class="fa fa-history"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="historyDialog" title="تاریخچه‌ی تغییرات" style="display: none;">
        <div id="historyContent"></div>
    </div>
@endsection

@section('files')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const config = {
            chequeStatus: @json(config('chequeStatus')),
            badgeClass: @json($badgeClass)
        };

        $(function() {
            $('#tabs').tabs({
                active: 0
            });

            $('#receivedTable').DataTable({
                paging: false,
                order: [
                    [0, "desc"]
                ],
                language: language,
                columnDefs: [
                    {
                        "targets": [1, 2],
                        "className": "dt-wrap"
                    },
                    {
                        "targets": [3,4,5,6],
                        "className": "dt-nowrap"
                    }
                ]
            });

            $('#givenTable').DataTable({
                paging: false,
                order: [
                    [0, "desc"]
                ],
                language: language,
                columnDefs: [
                    {
                        "targets": [6,7],
                        "className": "dt-nowrap"
                    }
                ]
            });
        });

        $(function() {
            const fromDate =
                @if ($from)
                    new Date('{{ Verta::parse($from)->datetime()->format('Y-m-d') }}')
                @else
                    null
                @endif ;
            const toDate =
                @if ($to)
                    new Date('{{ Verta::parse($to)->datetime()->format('Y-m-d') }}')
                @else
                    null
                @endif ;

            new mds.MdsPersianDateTimePicker($('#from')[0], {
                targetTextSelector: '#from',
                selectedDate: fromDate
            });

            new mds.MdsPersianDateTimePicker($('#to')[0], {
                targetTextSelector: '#to',
                selectedDate: toDate
            });
        });

        function showHistory(chequeId, type) {
            $('#historyContent').load(`/cheque/log/${chequeId}/${type}`, function() {
                $("#historyDialog").dialog({
                    modal: true,
                    width: 600,
                    buttons: {
                        "بستن": {
                            text: "بستن",
                            class: "btn btn-danger",
                            style: "font-family: inherit;",
                            click: function() {
                                $(this).dialog("close");
                            }
                        }
                    }
                });
            });
        }

        function confirmPassCheque(id, type, currentStatus) {
        Swal.fire({
            title: 'تغییر وضعیت چک',
            input: 'select',
            inputOptions: @json(config('chequeStatus.statuses')),
            inputValue: currentStatus,
            inputValidator: (value) => {
                if (value === null || value === "") return 'لطفا وضعیت را انتخاب کنید';
            },
            showCancelButton: true,
            confirmButtonText: 'تایید',
            cancelButtonText: 'انصراف'
            ,
                customClass: {
                    confirmButton: 'btn btn-success me-1',
                    cancelButton: 'btn btn-danger ms-1',
                },
                buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                const $button = $(`button[onclick^="confirmPassCheque(${id}, '${type}',"]`);
                const originalText = $button.text();
                const originalClass = $button.attr('class');
                
                $.ajax({
                    url: '/cheque/pass',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cheque_id: id,
                        type: type,
                        cheque_status: result.value
                    },
                    success: (response) => {
                        if (response.success) {
                            const statusConfig = @json(config('chequeStatus.statuses'));
                            const newStatus = statusConfig[result.value];
                            const badgeClass = config.badgeClass[result.value] ?? 'secondary';
                            
                            $button
                                .removeClass(originalClass)
                                .addClass(`btn btn-${badgeClass} btn-sm`)
                                .text(newStatus);
                        }
                    },
                    error: (xhr) => {
                        $button.html(originalText).attr('class', originalClass);
                        Swal.fire('خطا!', xhr.responseJSON?.message || 'خطایی رخ داد', 'error');
                    }
                });
            }
        });
    }
    </script>

    <style>
        .ui-tabs .ui-tabs-nav .ui-state-active a {
            background: #007bff !important;
            color: white !important;
            border-radius: 0 !important;
        }

        .ui-tabs .ui-tabs-nav .ui-state-default a {
            color: black !important;
            background: none !important;
        }

        .filter-section {
            display: flex;
            align-items: center;
        }

        .filter-section label,
        .filter-section input {
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .filter-section input {
            width: 100px;
        }

        .btn-radio input[type="radio"] {
            display: none;
        }

        .btn-radio input[type="radio"]:checked+label {
            background-color: #007bff;
            color: white;
        }

        .btn-primary {
            margin-right: 30px;
            width: 80px;
        }

        .swal2-select,
        .swal2-input-custom {
            font-family: inherit !important;
            border-radius: 1.5rem !important;
        }
    </style>
@endsection