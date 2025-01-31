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

        $(document).ready(function() {
            const date1 = new mds.MdsPersianDateTimePicker($('#start_date')[0], {
                targetTextSelector: '#start_date',
                textFormat: 'yyyy/MM/dd',
                isGregorian: false,
                enableTimePicker: false,
                englishNumber: true,
                placement: 'bottom',
                trigger: 'focus',
                onSelect: function() {
                    filterChequeDate();
                }
            });

            const date2 = new mds.MdsPersianDateTimePicker($('#end_date')[0], {
                targetTextSelector: '#end_date',
                textFormat: 'yyyy/MM/dd',
                isGregorian: false,
                enableTimePicker: false,
                englishNumber: true,
                placement: 'bottom',
                trigger: 'focus',
                onSelect: function() {
                    filterChequeDate();
                }
            });

            $('#start_date, #end_date').on('change', function() {
                filterChequeDate();
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

        .state-filter-btn.active {
            background-color: #007bff;
            color: white;
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
    </style>
@endsection

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">لیست چک‌ها</h1>

        {{-- فیلترها --}}

        <div class="filter-section mb-4">
            <label for="start_date">از تاریخ:</label>
            <input type="text" id="start_date" class="form-control" autocomplete="off">
            <label for="end_date">تا تاریخ:</label>
            <input type="text" id="end_date" class="form-control" autocomplete="off">
        </div>

        <button class="btn btn-secondary mb-4" onclick="filterPassedCheques('all')">همه</button>
        <button class="btn btn-success mb-4" onclick="filterPassedCheques('1')">پاس شده</button>
        <button class="btn btn-warning mb-4" onclick="filterPassedCheques('0')">پاس نشده</button>
        <button class="btn btn-info mb-4" onclick="setNextMonth()">چک‌های ماه آینده</button>
        <button class="btn btn-danger mb-4" onclick="pastCheques()">چک‌های گذشته</button>

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
                    <a class="nav-link" id="given-tab" data-bs-toggle="tab" href="#given" role="tab"
                        aria-controls="given" aria-selected="false">
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
                                <td>{{ verta($cheque->cheque_date)->formatJalaliDate() }}</td>
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
                                <td>{{ verta($cheque->cheque_date)->formatJalaliDate() }}</td>
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
                                        <span class="fa fa-eye btn btn-info"
                                            onclick="view_given_cheque({{ $cheque->id }})" title="مشاهده"></span>
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
