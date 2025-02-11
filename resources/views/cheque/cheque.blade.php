@extends('layout.main')

@section('title')
    لیست چک‌ها
@endsection

@section('files')
    <script>
        $(function() {
            $('#tabs').tabs();

            $('#tabs').tabs({
                active: 0
            });

            $('#receivedTable').DataTable({
                paging: false,
                order: [
                    [0, "asc"]
                ],
                language: language
            });

            $('#givenTable').DataTable({
                paging: false,
                order: [
                    [0, "asc"]
                ],
                language: language
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
            flex-wrap: wrap;
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
    </style>
@endsection

@section('content')
    {{-- فیلترها --}}

    <div class="container mt-4">
        <div class="mb-4">
            <form action="#" method="post">
                @csrf
                <div class="filter-section mb-3">
                    <label for="from">از تاریخ: </label>
                    <input type="text" class="form-control" style="width: 120px" id="from" name="from"
                        value="{{ $from ?? '' }}">
                    <label for="to">تا تاریخ: </label>
                    <input type="text" class="form-control" style="width: 120px" id="to" name="to"
                        value="{{ $to ?? '' }}">
                </div>

                <div class="filter-section">
                    <label for="state">وضعیت:</label>

                    <input type="radio" class="btn-check" id="all" name="state" value=""
                        {{ request('state') === '' ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary" for="all">همه</label>

                    <input type="radio" class="btn-check" id="passed" name="state" value="1"
                        {{ request('state') === '1' ? 'checked' : '' }}>
                    <label class="btn btn-outline-success" for="passed">پاس شده</label>

                    <input type="radio" class="btn-check" id="not_passed" name="state" value="0"
                        {{ request('state') === '0' ? 'checked' : '' }}>
                    <label class="btn btn-outline-warning" for="not_passed">پاس نشده</label>

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

                    <button type="submit" class="btn btn-primary mb-2">فیلتر</button>
                </div>
        </div>
        </form>
    </div>

    {{-- جداول --}}

    <div id="tabs" dir="rtl">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="received-tab" data-bs-toggle="tab" href="#received" role="tab"
                    aria-controls="received" aria-selected="true">
                    چک‌های دریافتی
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="given-tab" data-bs-toggle="tab" href="#given" role="tab" aria-controls="given"
                    aria-selected="false">
                    چک‌های پرداختی
                </a>
            </li>
        </ul>

        <div id="received" class="tab-pane fade show active">
            <h3 class="mt-4 mb-4">چک‌های دریافتی</h3>

            <table id="receivedTable" class="table table-striped mt-4 mb-4" style="width:100%">
                <thead>
                    <tr>
                        <th>نام صاحب چک</th>
                        <th>تاریخ چک</th>
                        <th>مبلغ (ریال)</th>
                        <th>کد چک</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($receivedCheque as $cheque)
                        <tr>
                            <td>
                                <a href="/customer/transaction/{{ $cheque->customer_id }}"
                                    class="text-primary text-decoration-none hover:text-decoration-underline">
                                    {{ $cheque->cheque_name }}
                                </a>
                            </td>
                            <td>{{ isset($cheque->cheque_date) ? verta($cheque->cheque_date)->formatJalaliDate() : 'نامشخص' }}
                            </td>
                            <td>{{ number_format($cheque->amount) }}</td>
                            <td>{{ $cheque->cheque_code }}</td>
                            <td>
                                @if ($cheque->cheque_pass)
                                    <input type="button" class="btn btn-success" value="پاس شده" disabled>
                                @else
                                    <input type="button" class="btn btn-warning" value="پاس نشده"
                                        onclick="confirmPassCheque({{ $cheque->id }}, 'received')">
                                @endif
                            </td>
                            <td>
                                <div class="ms-2">
                                    <span class="fa fa-eye btn btn-info"
                                        onclick="view_recieved_cheque({{ $cheque->id }})" title="مشاهده"></span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div id="given" class="tab-pane fade">
            <h3 class="mt-4 mb-4">چک‌های پرداختی</h3>
            <table id="givenTable" class="table table-striped mt-4 mb-4" style="width:100%">
                <thead>
                    <tr>
                        <th>نام حساب</th>
                        <th>تاریخ چک</th>
                        <th>مبلغ (ریال)</th>
                        <th>شناسه ملی</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($givenCheque as $cheque)
                        <tr>
                            <td>{{ $cheque->account_name }}</td>
                            <td>{{ isset($cheque->cheque_date) ? verta($cheque->cheque_date)->formatJalaliDate() : 'نامشخص' }}
                            </td>
                            <td>{{ number_format($cheque->amount) }}</td>
                            <td>{{ $cheque->cheque_id }}</td>
                            <td>
                                @if ($cheque->cheque_pass)
                                    <input type="button" class="btn btn-success" value="پاس شده" disabled>
                                @else
                                    <input type="button" class="btn btn-warning" value="پاس نشده"
                                        onclick="confirmPassCheque({{ $cheque->id }}, 'given')">
                                @endif
                            </td>
                            <td>
                                <div class="ms-2">
                                    <span class="fa fa-eye btn btn-info" onclick="view_given_cheque({{ $cheque->id }})"
                                        title="مشاهده"></span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    </div>
@endsection
