@extends('layout.main')

@section('title')
    آمار فروش محصولات
@endsection

@section('content')
    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
                <canvas id="salesChart" width="350" height="150" style="direction: rtl"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

    <script>
        Chart.defaults.plugins.tooltip.titleFont = () => ({
            family: 'Vazir',
            size: 20,
            lineHeight: 1.2,
            weight: 800,
        });
        Chart.defaults.plugins.tooltip.bodyFont = () => ({
            family: 'Vazir',
            size: 15,
            lineHeight: 1.2,
            weight: 500
        });
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($labels),
                datasets: [{
                    label: 'میانگین تعداد فروش',
                    data: @json($data),
                    borderColor: '#3b82f6',
                    tension: 0.1
                }]
            },
            options: {
                plugins: { // ← plugins section
                    tooltip: {
                        titleAlign: 'right',
                        bodyAlign: 'right',
                        displayColors: false,
                        backgroundColor: '#2d3748',
                        titleColor: '#cbd5e0',
                        bodyColor: '#cbd5e0'
                    }
                },
                interaction: {
                    mode: 'nearest',
                    intersect: false
                },
                elements: {
                    point: {
                        hitRadius: 20
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            font: {
                                family: 'Vazir'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                family: 'Vazir'
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection
