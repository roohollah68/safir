<table>
    <thead>
    <tr>
        <th>شماره صورتحساب (داخلی)</th>
        <th>تاریخ صورتحساب</th>
        <th>نوع صورتحساب</th>
        <th>الگوی صورتحساب</th>
        <th>موضوع صورتحساب</th>
        <th>نوع انجام معامله</th>
        <th>شماره منحصر به فرد مالیاتی صورتحساب مرجع</th>
        <th>نوع خریدار</th>
        <th>کد اقتصادی خريدار</th>
        <th>شناسه ملی/ کد ملی / کد اتباع/ شناسه مشارکت مدنی خریدار</th>
        <th>کد پستی خریدار</th>
        <th>شماره گذرنامه خریدار</th>
        <th>ساعت صورتحساب</th>
        <th>کد شعبه فروشنده</th>
        <th>کد شعبه خریدار</th>
        <th>نوع پرواز</th>
        <th>موضوع ماده ۱۷</th>
        <th>توضیحات</th>

    </tr>
    </thead>
    <tbody>
    @foreach($orders as $id => $order)
        <tr>
            <td>{{$id}}</td>
            <td><input type="text" value="{!!verta($order->created_at)->format('Y/m/d')!!}"></td>
            <td>2</td>
            <td>1</td>
            <td>1</td>
            <td>1</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><input type="text" value="000{{$order->warehouse_id}}"></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{$order->name. ': '.$order->desc}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
