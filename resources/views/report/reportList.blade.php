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
{{--    <br>--}}
{{--    <hr>--}}
{{--    <span style="font-weight: bold">امروز:</span><br>--}}
{{--    @foreach($warehouses as $id => $warehouse)--}}
{{--        <span class="btn btn-{{isset($reports[0][$id])?'success':'danger'}}"--}}
{{--              @isset($reports[0][$id])--}}
{{--                onclick="showDesc(`{{$reports[0][$id]->description}}`,`{{$warehouse->name}}` , `{{$reports[0][$id]->user->name}}`)"--}}
{{--                  @endisset--}}
{{--        >{{$warehouse->name}}</span>--}}
{{--    @endforeach--}}
{{--    <br>--}}
{{--    <hr>--}}
{{--    <span style="font-weight: bold">دیروز:</span><br>--}}
{{--    @foreach($warehouses as $id => $warehouse)--}}
{{--        <span class="btn btn-{{isset($reports[1][$id])?'success':'danger'}}"--}}
{{--              @isset($reports[1][$id])--}}
{{--                  onclick="showDesc(`{{$reports[1][$id]->description}}`,`{{$warehouse->name}}` , `{{$reports[1][$id]->user->name}}`)"--}}
{{--                  @endisset--}}
{{--        >{{$warehouse->name}}</span>--}}
{{--    @endforeach--}}
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
