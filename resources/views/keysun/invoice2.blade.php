<table>
    <thead>
    <tr>
        <th>شماره صورتحساب (داخلی)</th>
        <th>تاریخ صورتحساب</th>
        <th>شناسه کالا / خدمت</th>
        <th>واحد اندازه گیري و سنجش</th>
        <th>مقدار / تعداد</th>
        <th>نوع ارز</th>
        <th>نرخ برابری ارز به ریال</th>
        <th>مبلغ واحد (فی)</th>
        <th>مبلغ تخفیف</th>
        <th>نرخ مالیات برارزش افزوده</th>
        <th>مبلغ مالیات بر ارزش افزوده</th>
        <th>نرخ سایر عوارض و مالیات</th>
        <th>مبلغ سایر عوارض و مالیات</th>
        <th>موضوع سایر عوارض و مالیات</th>
        <th>نرخ سایر وجوهات قانونی</th>
        <th>مبلغ سایر وجوهات قانونی</th>
        <th>موضوع سایر وجوهات قانونی</th>
        <th>شناسه یکتای ثبت قرارداد حق العملکاری *(اختیاری)</th>
        <th>شماره قرارداد بورس</th>
        <th>تاریخ قرارداد بورس</th>
        <th>شرح اضافی کالا / خدمت</th>


    </tr>
    </thead>
    <tbody>
    @foreach($orders as $id => $order)
        @foreach($order->orderProducts as $orderProduct)
        <tr>
            <td>{{$id}}</td>
            <td><input type="text" value="{!!verta($order->created_at)->format('Y/m/d')!!}"></td>
            <td>{{$orderProduct->product->good->id}}</td>
            <td>1627</td>
            <td>{{$orderProduct->number}}</td>
            <td>364</td>
            <td>1</td>
            <td>{{$orderProduct->originalPrice()}}</td>
            <td></td>
            <td>{{$orderProduct->product->good->vat?'10':'0'}}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        @endforeach
    @endforeach
    </tbody>
</table>

