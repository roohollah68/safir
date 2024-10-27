@extends('layout.main')

@section('title')
    مدیریت کالا ها
@endsection

@section('content')

    @foreach($warehouses as $id=>$warehouse)
        <span class="btn btn-primary m-1"
              onclick="$('.warehouse-{{$warehouse->id}}').toggle().is(':visible')?
                  $(this).addClass('btn-primary').removeClass('btn-outline-primary'):
                  $(this).addClass('btn-outline-primary').removeClass('btn-primary')">
            {{$warehouse->name}}
        </span>
    @endforeach
    <br>
    @foreach(['final'=>'محصول نهایی', 'raw'=>'ماده اولیه' , 'pack'=>'ملزومات بسته بندی'] as $category => $desc)
        <span class="btn btn-info m-1"
              onclick="$('.{{$category}}').toggle().is(':visible')?
                  $(this).addClass('btn-info').removeClass('btn-outline-info'):
                  $(this).addClass('btn-outline-info').removeClass('btn-info')">
            {{$desc}}
        </span>
    @endforeach
    <a class="btn btn-danger m-1" href="{{route('productList')}}">بازگشت</a>

    <span class="btn btn-danger m-1 fa fa-trash"
          onclick="$('.trash').toggle().is(':visible')?
              $(this).addClass('btn-danger').removeClass('btn-outline-danger'):
              $(this).addClass('btn-outline-danger').removeClass('btn-danger')"></span>
    <br>
    <br>
    <div>
        <table class="table table-striped table-bordered" id="goodsTable">
            <thead>
            <tr>
                <th>شماره</th>
                <th>نام کالا</th>
                @foreach($warehouses as $id=>$warehouse)
                    <th class="warehouse-{{$id}}">{{$warehouse->name}}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($goods as $id=>$good)
                <tr class="{{$good->category}}" id="good-{{$id}}">
                    <td>{{$id}}</td>
                    <td>
                        {{$good->name}}
                    </td>

                    @php
                        $products = $good->products->keyBy('warehouse_id');
                    @endphp
                    @foreach($warehouses->keys() as $warehouse_id)
                        <th class="warehouse-{{$warehouse_id}}">
                            @if(isset($products[$warehouse_id]))
                                <span class="btn btn-{{$products[$warehouse_id]->available?'success':'danger'}}" dir="ltr"
                                      onclick="changeAvailable({{$products[$warehouse_id]->id}},this)">
                            {{+$products[$warehouse_id]->quantity}}
                        </span>
                                <span role="button" class="fa fa-trash text-danger trash"
                                      onclick="deleteProduct({{$products[$warehouse_id]->id}},{{$id}},{{$warehouse_id}})"></span>
                            @else
                                <span class="btn btn-outline-success fa fa-plus"
                                      onclick="addProduct({{$id}},{{$warehouse_id}})"></span>
                            @endif
                        </th>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('files')

    <script>
        let dataTable;

        $(function () {
            dataTable = $('#goodsTable').DataTable({
                // pageLength: 100,
                paging: false,
                destroy: true,
            });
        })

        function deleteProduct(id, good_id, warehouse_id) {

            $.post('/product/delete/' + id, {_token: "{{ csrf_token() }}"})
                .done(res => {
                    if (res === 'ok') {
                        let text = `
                        <span class="btn btn-outline-success fa fa-plus"
                                      onclick="addProduct(${good_id},${warehouse_id})"></span>
                        `;
                        $(`#good-${good_id} .warehouse-${warehouse_id}`).html(text)
                    } else {
                        $.notify('خطایی رخ داده است.', 'warn');
                    }
                });

        }

        function addProduct(good_id, warehouse_id) {
            $.post('/addToProducts/' + good_id, {
                '_token': "{{ csrf_token() }}",
                'warehouseId': warehouse_id,
            }).done(product => {
                let text = `
                <span class="btn btn-${product.available ? 'success' : 'danger'}" dir="ltr"
                    onclick="changeAvailable(${product.id},this)">
                            ${+product.quantity}
                </span>
                        <span role="button" class="fa fa-trash text-danger trash"
                              onclick="deleteProduct(${product.id},${good_id},${warehouse_id})"></span>
                `;
                $(`#good-${good_id} .warehouse-${warehouse_id}`).html(text);
            }).fail(function () {
                $.notify('خطایی رخ داده است.', 'warn');
            });
        }

        function changeAvailable(id, element) {
            $.post('/product/change/available/' + id, {_token: "{{ csrf_token() }}"})
                .done(product => {
                    if (product.available) {
                        $(element).addClass('btn-success').removeClass('btn-danger');
                        $.notify('موجود شد.', 'success');
                    }else{
                        $(element).addClass('btn-danger').removeClass('btn-success');
                        $.notify('ناموجود شد.', 'success');
                    }
                }).fail(function () {
                    $.notify('خطایی رخ داده است.', 'warn');
                });
        }

        function deleteGood($id) {

        }
    </script>
@endsection
