<h1>لیست مواد اولیه مورد نیاز برای تولید</h1>
<table>
    <thead>
        <tr>
            <th>تعداد محصول نهایی موردنیاز</th>
            <th>محصول نهایی</th>
            <th>مواد اولیه</th>
            <th>مقدار مواد اولیه مورد نیاز</th>
            <th>واحد اندازه‌گیری</th>
        </tr>
    </thead>
    <tbody>
        @foreach($reportData as $data)
            <tr style="border: 2px solid black;">
                <td rowspan="{{ count($data['raws']) + 1 }}">{{ number_format($data['remaining_requests']) }}</td>
                <td rowspan="{{ count($data['raws']) + 1 }}">{{ $data['good']->name }}</td>
            </tr>
            @foreach($data['raws'] as $raw)
            <tr style="border-left: 2px solid black; border-right: 2px solid black;">
                <td>{{ $raw['name'] }}</td>
                <td>{{ number_format($raw['amount'], 4) }}</td>
                <td>{{ $raw['unit'] }}</td>       
            </tr>
            @endforeach
        @endforeach
    </tbody>
</table>