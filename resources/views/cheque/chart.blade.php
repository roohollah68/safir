@extends('layout.main')

@section('title')
    تحلیل چک‌ها
@endsection

@section('content')
<div class="container" dir="rtl">
    <form method="POST" class="mb-4">
        @csrf
        <div class="row g-3">
            <div class="col-md-2">
                <select name="state" class="form-select">
                    <option value="all" {{ request('state', 'all') == 'all' ? 'selected' : '' }}>همه وضعیت‌ها</option>
                    @foreach(config('chequeStatus.statuses') as $key => $status)
                        <option value="{{ $key }}" {{ request('state', 'all') == $key ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">همه</option>
                    <option value="official" {{ request('type') == 'official' ? 'selected' : '' }}>رسمی</option>
                    <option value="unofficial" {{ request('type') == 'unofficial' ? 'selected' : '' }}>غیررسمی</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="bank" class="form-select">
                    <option value="">همه بانک‌ها</option>
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}" {{ request('bank') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" id="from" name="from" class="form-control" value="{{ request('from') }}" placeholder="از تاریخ"
                style="cursor: pointer;">
            </div>
            <div class="col-md-2">
                <input type="text" id="to" name="to" class="form-control" value="{{ request('to') }}" placeholder="تا تاریخ"
                style="cursor: pointer;">
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary w-100">فیلتر</button>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            <h4 class="m-3">
                اختلاف خالص: 
                <span class="{{ ($receivedTotal - $givenTotal) >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($receivedTotal - $givenTotal) }}
                </span>
                ریال
            </h4>
            <canvas id="chequeChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script>
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

    const chartLabels = @json($chartLabels);
    const receivedData = @json($receivedData);
    const givenData = @json($givenData);

    const ctx = document.getElementById('chequeChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'چک‌های ورودی',
                    data: receivedData,
                    backgroundColor: '#4dc9f0aa',
                    borderColor: '#4dc9f0',
                    borderWidth: 2,
                    fill: false
                },
                {
                    label: 'چک‌های خروجی',
                    data: givenData,
                    backgroundColor: '#f67019aa',
                    borderColor: '#f67019',
                    borderWidth: 2,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const label = `${ctx.dataset.label}: ${ctx.raw.toLocaleString('fa-IR')}`;
                            return label.replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
                        },
                        title: (ctx) => {
                            const persianDigits = d => '۰۱۲۳۴۵۶۷۸۹'[d];
                            const dateLabel = ctx[0].label.replace(/\d/g, persianDigits);
                            return dateLabel;
                        }
                    },
                    titleFont: {
                        family: 'Vazir',
                        size: '12px'
                    },
                    bodyFont: {
                        family: 'Vazir',
                        weight: 'bold'
                    }
                },
                legend: {
                    labels: {
                        font: {
                            family: 'Vazir',
                            size: '15px'
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'تاریخ',
                        font: {
                            family: 'Vazir',
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        font: {
                            family: 'Vazir',
                            weight: 'bold'
                        },
                        callback: function(value, index, ticks) {
                            const label = this.getLabelForValue(value);
                            return label.replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            family: 'Vazir',
                            weight: 'bold'
                        },
                        callback: (value) => value.toLocaleString('fa-IR')
                    },
                    title: {
                        display: false,
                        font: {
                            family: 'Vazir',
                            weight: 'bold'
                        }
                    }
                }
            }
        }
    });
</script>
@endsection