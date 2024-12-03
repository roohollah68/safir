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
        <span style="font-weight: bold">{{$day==0?'امروز':($day==1?'دیروز':verta('-'.$day.' day')->formatWord('l dS F'))}}:</span><br>
        @foreach($warehouses as $id => $warehouse)
            <span class="btn btn-{{isset($reports[$day][$id])?'success':'danger'}}"
                  @isset($reports[$day][$id])
                      onclick="showDesc(`{{$reports[$day][$id]->description}}`,`{{$warehouse->name}}` , `{{$reports[$day][$id]->user->name}}`)"
                  @endisset
        >{{$warehouse->name}}</span>
        @endforeach
    @endforeach


@endsection

@section('files')
    <script>
        let dialog;
        function showDesc(desc,warehouse,user){
            let text = `<div title="گزارش کار نمایندگی ${warehouse}" class="dialogs">
<span>نمایندگی: </sapn><span style="font-weight: bold">${warehouse}</span><br>
<span>ثبت کننده: </span><span style="font-weight: bold">${user}</span><br>
<span>توضیحات: </span><br>
<span style="white-space: pre-wrap">${desc}</span>
            </div>`;
            dialog = Dialog(text);
        }
    </script>
@endsection
