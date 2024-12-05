@extends('layout.main')

@section('title')
    'گزارش کار نمایندگی ها'
@endsection

@section('content')
    <h3>گزارش کار نمایندگی ها</h3>
    <br>
    <hr>
    <span style="font-weight: bold">ثبت گزارش کار امروز:</span><br>
    @foreach($warehouses as $id => $warehouse)
        <a class="btn btn-primary" href="/report/add/{{$id}}"> {{$warehouse->name}}</a>
    @endforeach
    @foreach($reports as $day => $reportArray)
        <br>
        <hr>
        <span style="font-weight: bold">{{$day==0?'امروز':($day==1?'دیروز':verta('-'.$day.' day')->formatWord('l dS F'))}}:</span>
        <br>
        @foreach($warehouses as $id => $warehouse)
            <span class="btn btn-{{isset($reports[$day][$id])?'success':'danger'}}"
                  @isset($reports[$day][$id])
                      onclick='showDesc({!! json_encode($reports[$day][$id]) !!})'
                  @endisset
        >{{$warehouse->name}}</span>
        @endforeach
    @endforeach

@endsection

@section('files')
    <script>
        let dialog;
        let token = '{{ csrf_token() }}';

        function showDesc(report) {
            let text = `<div title="گزارش کار نمایندگی ${report.warehouse.name}" class="dialogs">` +
                (report.photo ? `<a href="/report/${report.photo}" target="_blank"><img width="100%" src="/report/${report.photo}"></a><br>` : ``) +
                `<span>نمایندگی: </sapn><span style="font-weight: bold">${report.warehouse.name}</span><br>
<span>ثبت کننده: </span><span style="font-weight: bold">${report.user.name}</span><br>
<span class="btn btn-primary" onclick="response(${report.id})">ثبت پاسخ</span><br>` +
                (report.response ?
                    `<span class="fw-bold">پاسخ: </span><br>
<span style="white-space: pre-wrap">${report.response}</span>
<br>` : ``) +
                `<span class="fw-bold">توضیحات: </span><br>
<span style="white-space: pre-wrap">${report.description}</span>
            </div>`;
            dialog = Dialog(text);
        }

        function response(id) {
            let response = prompt("پاسخ به گزارش کار", "");
            if (response != null) {
                $.post('/commentResponse/' + id, {_token: token, response: response})
                    .done(res => {
                        location.reload();
                    })
            }
        }

    </script>
@endsection
