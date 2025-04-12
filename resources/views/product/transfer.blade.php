@extends('layout.main')

@section('title')
    انتقال بین انبارها
@endsection

@section('content')
    <form method="post" action="">
        @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="form-group input-group">
                <div class="input-group-append">
                    <label for="warehouse1" class="input-group-text"> انبار مبدا:</label>
                </div>
                <select name="warehouseId1" id="warehouse1" class="form-control">
                    @foreach($warehouses as $warehouse)
                        <option value="{{$warehouse->id}}" @selected(auth()->
                            user()->meta('warehouseId')==$warehouse->id)>{{$warehouse->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6 ">
            <div class="form-group input-group">
                <div class="input-group-append">
                    <label for="warehouse2" class="input-group-text"> انبار مقصد:</label>
                </div>
                <select name="warehouseId2" id="warehouse2" class="form-control">
                    @foreach($warehouses as $warehouse)
                        <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <br>
    <input type="submit" class="btn btn-success" value="ثبت">
    <a class="btn btn-danger" href="{{route('productList')}}">بازگشت</a>
    <br>
    <br>
    <div id="tableWrapper">
        <table id="transferTable" class="table table-striped">
            <thead>
            <tr>
                <th>شماره</th>
                <th>نام</th>
                <th>موجودی</th>
                <th>تعداد</th>
            </tr>
            </thead>
            <tbody id="transferTableBody">
            </tbody>
        </table>
    </div>
    </form>
@endsection

@section('files')

    <script>
        let products = {!! $products !!};
        let warehouseId1;
        let warehouseId2;
        let initialTable;
        let DataTable;
        let warehouses = {!! $warehouses !!};

        $(function () {
            initialTable = $('#tableWrapper').html();
            $('#tableWrapper').html('');
            $.each(products, (id, product) => {
                if(!warehouses[product.warehouse_id]){
                    console.log(product);
                }
                warehouses[product.warehouse_id][product.good_id] = product;
            })
            $('select').change(function (e) {

                warehouseId1 = $('#warehouse1').val()
                warehouseId2 = $('#warehouse2').val()
                createTable();
            })

            $('form').submit(submit);
        })

        function createTable() {
            $bodyText = ''
            $.each(products, (id, product) => {
                if (+product.warehouse_id !== +warehouseId1)
                    return;
                $bodyText += `<tr><td>${id}</td><td>${product.good.name}</td>
<td>${+product.quantity}</td><td><input type="number" name="${id}" ${warehouses[warehouseId2][product.good_id] ? '' : 'disabled'}> </td></tr>`
            })
            $('#tableWrapper').html('');
            if (warehouseId1 === warehouseId2) {
                return;
            }
            $('#tableWrapper').html(initialTable);
            $('#transferTableBody').append($bodyText);
            DataTable = $('#transferTable').DataTable({
                destroy: true,
                paging: false,
            })
        }

        function submit() {
            $('input[type=search]').val('')
            DataTable.search('').draw();
            return true;
        }

    </script>


@endsection
