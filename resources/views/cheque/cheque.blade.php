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
            const date1 = new mds.MdsPersianDateTimePicker($('#from_date')[0], {
                targetTextSelector: '#from_date',
                textFormat: 'yyyy/MM/dd',
                isGregorian: false,
                enableTimePicker: false,
                englishNumber: true,
                @if (isset($from))
                    selectedDate: '{{ $from }}',
                @endif
            });

            const date2 = new mds.MdsPersianDateTimePicker($('#to_date')[0], {
                targetTextSelector: '#to_date',
                textFormat: 'yyyy/MM/dd',
                isGregorian: false,
                enableTimePicker: false,
                englishNumber: true,
                @if (isset($to))
                    selectedDate: '{{ $to }}',
                @endif
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

        <form action="{{ route('cheque.filterChequeDate') }}" method="get">
            <div class="filter-section mb-4">
                <i>از تاریخ: </i><input type="text" value="{{ $from ?? '' }}" style="width: 120px" id="from_date"
                    name="from">
                <i class="mx-3"></i>
                <i>تا تاریخ: </i><input type="text" value="{{ $to ?? '' }}" style="width: 120px" id="to_date"
                    name="to">
            </div>

            <select name="state">
                <option value="">همه</option>
                <option value="1">پاس شده</option>
                <option value="0">پاس نشده</option>
            </select>
            <button type="submit">فیلتر</button>
        </form>

        {{-- <form action="{{ route('cheque.setNextMonth') }}" method="get">
            @csrf
            <button type="submit">چک‌های 30 روز آینده</button>
        </form>

        <form action="{{ route('cheque.pastCheques') }}" method="get">
            @csrf
            <button type="submit">چک‌های تاریخ گذشته</button>
        </form>  --}}

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
