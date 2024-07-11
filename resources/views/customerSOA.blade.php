<html lang="fa" dir="rtl">

<h3 style="text-align: center;">گزارش گردش حساب</h3>
<span>نام:</span> <b>{{$customer->name}}</b>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
<span>شماره مشتری:</span> <b>{{$customer->id}}</b>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
<span>شماره تماس:</span> <b>{{$customer->phone}}</b><br>
<span>تاریخ:</span> <b dir="ltr">{{verta()->formatDate()}}</b>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
<span>آدرس:</span> <b>{{$customer->address}}</b><br>
<span>مانده کل:</span> <b dir="ltr">{{number_format($customer->balance)}} </b><span>ریال</span>
<table id="customers">
    <thead>
    <tr>
        <th>شماره سند</th>
        <th>تاریخ</th>
        <th>شرح</th>
        <th>بدهکاری(ریال)</th>
        <th>بستانکاری(ریال)</th>
        <th>مانده(ریال)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($customer->transactions as $trans)
        @if($trans->deleted || (!$trans->order_id && $trans->verified != 'approved'))
            @continue
        @endif
        @php
            $total += $trans->type?$trans->amount:-$trans->amount;
        @endphp
        <tr>
            <td>{{$trans->order_id?:$trans->id}}</td>
            <td>{{verta($trans->created_at)->formatDate()}}</td>
            <td>{{$trans->order_id?'سفارش '.$trans->order_id:$trans->description}}</td>
            <td dir="ltr">{{$trans->type?'':number_format($trans->amount)}}</td>
            <td dir="ltr">{{$trans->type?number_format($trans->amount):''}}</td>
            <td dir="ltr">{{number_format($total)}}</td>
        </tr>
    @endforeach

    </tbody>
</table>

<style>
    #customers {
        border-collapse: collapse;
        width: 100%;
    }

    #customers td, #customers th {
        border: 1px solid #ddd;
        padding: 8px;
    }

    #customers tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    #customers tr:hover {
        background-color: #ddd;
    }

    #customers th {
        padding-top: 12px;
        padding-bottom: 12px;
        background-color: gray;
        color: white;
    }
</style>
</html>

