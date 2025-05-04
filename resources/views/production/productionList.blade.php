@extends('layout.main')

@section('title', 'لیست تولید')

@section('content')
<a class="btn btn-outline-success mb-5" href="{{ route('addEdit') }}">
        <span class="fa fa-plus"></span>
        افزودن درخواست تولید
    </a>
<a class="btn btn-outline-primary mb-5" href="{{ route('production.add.form') }}"><span class="fa fa-plus"></span> افزودن تولید انجام شده</a>
<table id="productionTable" class="table table-striped" style="width:100%; text-align: center;">
    <thead>
        <tr>
            <th>شماره</th>
            <th>شناسه محصول</th>
            <th>محصول</th>
            <th>تعداد مورد نیاز</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($productions->unique('good_id') as $production)
            @if ($production->remaining_requests > 0)
            <tr>
                <td>{{ $production->id }}</td>
                <td>{{ $production->good_id }}</td>
                <td>{{ $production->good->name }}</td>
                <td>{{ number_format($production->remaining_requests) }}</td>
            </tr>
            @endif
        @endforeach
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#productionTable').DataTable({
            paging: false,
            order: [
            [0, "desc"]
            ],
            language: language,
            dom: 'Bfrtip',
            searching: true,
            buttons: [
            ],
        });

    function printTable() {
        const printWindow = window.open('', '_blank');
        const table = document.getElementById('productionTable');
        const tableHtml = table.outerHTML;

        printWindow.document.write(`
            <html>
                <head>
                    <title>لیست تولید</title>
                    <style>
                        @font-face {
                            font-family: BNazanin;
                            src: url('/css/BNAZANB.TTF');
                        }
                        body { font-family: 'Vazir', Arial, sans-serif; margin: 20px; direction: rtl; }
                        table { width: 100%; border-collapse: collapse; text-align: center; margin-top: 20px; direction: rtl; }
                        th, td { border: 1px solid black; padding: 10px; }
                        h1 { text-align: center; margin-bottom: 20px; }
                    </style>
                </head>
                <body>
                    <h1>لیست تولید</h1>
                    ${tableHtml}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.onafterprint = function() {
            printWindow.close();
        };
    }

    function printRaw() {
        const printWindow = window.open('', '_blank');
        fetch("{{ route('formulation.pdf') }}")
            .then(response => response.text())
            .then(html => {
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>لیست تولید مواد اولیه</title>
                            <style>
                                @font-face {
                                    font-family: 'Vazir';
                                    src: url('/css/Vazir.ttf');
                                }
                                body {
                                    font-family: Vazir, sans-serif;
                                    margin: 20px;
                                    direction: rtl;
                                }
                                table {
                                    width: 100%;
                                    border-collapse: collapse;
                                    margin-top: 20px;
                                }
                                th, td {
                                    border: 1px solid #000;
                                    padding: 8px;
                                    text-align: center;
                                }
                                h1 {
                                    text-align: center;
                                    margin-bottom: 30px;
                                }
                            </style>
                        </head>
                        <body>
                            ${html}
                        </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                printWindow.onafterprint = function() {
                    printWindow.close();
                };
            });
    }

    $('<button>')
        .html('<i class="fas fa-flask me-1"></i>فرمول تولید  ')
        .addClass('btn btn-info mb-3 ms-2')
        .on('click', printRaw)
        .insertAfter('.dt-buttons');

    $('<button>')
        .html('<i class="fas fa-print me-1"></i> برنامه تولید')
        .addClass('btn btn-warning mb-3')
        .on('click', printTable)
        .insertAfter('.dt-buttons');
        
    });
</script>
@endsection
