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
    @foreach($keysuns as $keysun)
        @foreach($keysun->keysunMetas as $keysunMeta)
            <tr>
                <td>{{$keysun->id}}</td>
                <td><input type="text" value="{!!verta()->format('Y/m/d')!!}"></td>
                <td>{{$keysunMeta->keysungood_id}}</td>
                <td>1627</td>
                <td>{{$keysunMeta->number}}</td>
                <td>364</td>
                <td>1</td>
                <td>{{round($keysunMeta->price * (100/($keysunMeta->keysungood->vat + 100)))}}</td>
                <td>0</td>
                <td>{{$keysunMeta->keysungood->vat}}</td>
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

