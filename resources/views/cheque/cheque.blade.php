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
    </style>
@endsection

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">لیست چک‌ها</h1>
        <div id="tabs" dir="rtl">

            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link" id="received-tab" data-bs-toggle="tab" href="#received" role="tab"
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

            <div id="received">
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
                                <td><span>پاس نشده</span></td>
                                <td>
                                    <div class="ms-2">
                                        <a href="" class="fa fa-eye btn-info btn"></a>
                                    </div>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div id="given">
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
                                <td><span>پاس نشده</span></td>
                                <td>
                                    <div class="ms-2">
                                        <a href="{{ route('chequeView', ['id' => $cheque->id]) }}"
                                            class="fa fa-eye btn-info btn"></a>
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
