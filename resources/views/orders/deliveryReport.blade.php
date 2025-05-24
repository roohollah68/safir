@extends('layout.main')

@section('title')
گزارش نحوه‌ی ارسال
@endsection

@section('content')
<div class="container">    
    <form method="GET" class="mb-4 border p-3 rounded">
        <div class="row">
            <div class="col-md-3 d-flex align-items-center">
                <label for="from" class="form-label mb-0 me-2" style="min-width: 55px;">از تاریخ:</label>
                <input type="text" name="from" id="from" class="form-control" value="{{ $from }}" style="cursor: pointer">
            </div>
            <div class="col-md-3 d-flex align-items-center">
                <label for="to" class="form-label mb-0 me-2" style="min-width: 55px;">تا تاریخ:</label>
                <input type="text" name="to" id="to" class="form-control" value="{{ $to }}" style="cursor: pointer">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                     فیلتر
                </button>
            </div>
        </div>
    </form>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="fw-bold text-primary">
                جمع سفارشات:
                <span class="badge bg-primary fs-6">{{ $chartData['total'] }}</span>
            </h6>
        </div>
        <div class="card-body">
            <h5 class="fw-bold text-center mt-1 mb-4">
                توزیع نحوه‌ی ارسال
            </h5>
            @if($chartData['total'] > 0)
            <div class="chart-container" style="position: relative; height:400px; width:100%">
                <canvas id="deliveryChart"></canvas>
            </div>
            @else
            <div class="text-center text-danger fw-bold py-5">
                سفارش معتبری یافت نشد.
            </div>
            @endif
        </div>
    </div>

    @if($chartData['total'] > 0)
    <div class="row mt-4">
        @foreach($chartData['labels'] as $index => $label)
        <div class="col-md-4 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col ms-2">
                            <div class="fw-bold text-primary mb-1">
                                {{ $label }}
                            </div>
                            <div class="h5 mb-0 text-gray-800">
                                {{ number_format($chartData['data'][$index]) }}
                                سفارش
                                <small class="text-muted">(%{{ number_format(($chartData['data'][$index]/$chartData['total'])*100, 1) }})</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
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

    @if($chartData['total'] > 0)
    const ctx = document.getElementById('deliveryChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: @json($chartData['labels']),
            datasets: [{
                data: @json($chartData['data']),
                backgroundColor: @json($chartData['colors']),
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    rtl: true,
                    labels: {
                        font: {
                            family: 'Vazir, Tahoma',
                            size: 14,
                            weight: 'bold'
                        },
                        padding: 20
                    },
                    align: 'start',
                    title: {
                        display: false
                    }
                },
                tooltip: {
                    rtl: true,
                    bodyFont: {
                        family: 'Vazir, Tahoma',
                        size: 14
                    },
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const percentage = (value / {{ $chartData['total'] }} * 100).toFixed(1);
                            return `${label}: ${value} سفارش (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    @endif
});
</script>
@endsection