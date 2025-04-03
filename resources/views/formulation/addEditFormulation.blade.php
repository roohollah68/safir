@extends('layout.main')

@section('title')
    @if($edit)
        ویرایش فرمول
    @else
        افزودن فرمول
    @endif
@endsection

@section('content')
    <div>
        <label for="good_name">نام کالا</label>
        <input type="text" style="width: 350px" id="good_name">
        <input type="text" style="width: 60px" id="good_id" readonly>
    </div>

    <a class="btn btn-danger m-2" href="/formulation/list"><span class="fa fa-arrow-rotate-back"></span> بازگشت</a>
    <hr>
    <div id="rawGoods"></div>
    <hr>
    <div class="hide" id="add-formulation">
        <label for="component_name">نام ترکیب</label>
        <input type="text" style="width: 350px" id="component_name">
        <input type="text" style="width: 60px" id="rawGood_id" readonly>
        |
        <label for="amount">مقدار</label>
        <input type="number" style="width: 200px" id="amount" min="0" step="0.0001">
        |
        <span class="btn btn-success fa fa-save" onclick="addFormulation()"></span>
    </div>
@endsection

@section('files')
    <script>
        let goods = {!! json_encode($goods) !!};
        let finals = {!! json_encode($finals) !!};
        let raws = {!! json_encode($raws) !!};
        let good_id;
        $(function () {
            $("#good_name").autocomplete({
                source: Object.keys(finals),
                select: function (event, ui) {
                    good_id = finals[ui.item.value];
                    $('#good_id').val(good_id);
                    getRawGoods(good_id);
                    $('#add-formulation').show();
                }
            });
            $("#component_name").autocomplete({
                source: Object.keys(raws),
                select: function (event, ui) {
                    let id = raws[ui.item.value];
                    $('#rawGood_id').val(id);
                }
            });
            @if($edit)
            $('#good_name').val('{{$good->name}}');
            $('#good_id').val({{$good->id}});
            good_id = {{$good->id}};
            getRawGoods(good_id);
            $('#add-formulation').show();
            @endif
        })

        function getRawGoods(id) {
            $.post('/formulation/getRawGoods/' + id, {
                _token: token,
            }).done(res => {
                $('#rawGoods').html(res);
            });
        }

        function editFormulation(id) {
            $.post('/formulation/addEditRow/' + id, {
                _token: token,
                amount: $('#formulation-' + id).val(),
            }).done(res => {
                $('#rawGoods').html(res);
            });
        }

        function addFormulation() {
            $.post('/formulation/addEditRow', {
                _token: token,
                amount: $('#amount').val(),
                good_id: good_id,
                rawGood_id: $('#rawGood_id').val(),
            }).done(res => {
                $('#rawGoods').html(res);
            });
        }

        function deleteFormulation(id) {
            $.get('/formulation/deleteRow/' + id).done(res => {
                $('#rawGoods').html(res);
            });
        }
    </script>
@endsection
