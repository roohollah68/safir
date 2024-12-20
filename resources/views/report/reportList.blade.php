@extends('layout.main')

@section('title')
    گزارش کار کارمندان
@endsection

@section('content')
    <h3>گزارش کار کارمندان</h3>
    <br>
    <hr>
    @if(auth()->user()->meta('addWorkReport'))
        <a class="btn btn-primary" href="/report/add"> ثبت گزارش کار امروز</a>
    @endif
    @foreach($reports as $day => $reportArray)
        <br>
        <hr>
        <span style="font-weight: bold">{{$day==0?'امروز':($day==1?'دیروز':verta('-'.$day.' day')->formatWord('l dS F'))}}:</span>
        <br>
        @foreach($reports[$day] as $id => $report)
            @continue(!auth()->user()->meta('workReport') && auth()->user()->id != $id)
            <span class="btn btn-success" onclick='showDesc({!! json_encode($reports[$day][$id]) !!})'>
                {{$users[$id]->name}}
            </span>
        @endforeach
    @endforeach

@endsection

@section('files')
    <script>
        let dialog;
        let token = '{{ csrf_token() }}';

        function showDesc(report) {
            let text = `<div title="گزارش کار ${report.user.name}" class="dialogs">` +
                (report.photo ? `<a href="/report/${report.photo}" target="_blank"><img width="100%" src="/report/${report.photo}"></a><br>` : ``) +
                `<span>کاربر: </sapn><span style="font-weight: bold">${report.user.name}</span><br>` +
                @if(auth()->user()->meta('workReport'))
                    `<span class="btn btn-primary" onclick="response(${report.id})">ثبت پاسخ</span><br>` +
                @endif
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
            @if(auth()->user()->meta('workReport'))
            let response = prompt("پاسخ به گزارش کار", "");
            if (response != null) {
                $.post('/commentResponse/' + id, {_token: token, response: response})
                    .done(res => {
                        location.reload();
                    })
            }
            @endif
        }

    </script>
@endsection
