@extends('layout.main')

@section('title')
    اصلاح موجودی انبار {{$product->name}}
@endsection

@section('content')
    <x-auth-validation-errors class="mb-4" :errors="$errors"/>
    <h4 class="">{{$product->name}}</h4>
    <h4 class="">تعداد موجود در انبار: {{$product->quantity}}</h4>
    {{--    <h5>وضعیت: {{$product->available?'موجود':'ناموجود'}}</h5>--}}
    <a class="btn btn-danger" href="{{route('productList')}}">بازگشت</a>
    <hr>
    <form action="" method="post" >
        @csrf
        <div class="row">

            <div class="col-md-6">
                <div class="form-group input-group">
                    <div class="input-group-text">
                        <input type="radio" name="addType" value="add"
                               aria-label="Radio button for following text input" checked
                               onclick="$('#value').prop('disabled', true);$('#add').prop('disabled', false);">
                    </div>
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="add" class="input-group-text w-100">افزودن به موجودی :</label>
                    </div>
                    <input type="number" step="1" id="add" class="form-control" name="add" value="">
                </div>
                <div class="form-group input-group">
                    <div class="input-group-text">
                        <input type="radio" name="addType" value="value"
                               aria-label="Radio button for following text input"
                               onclick="$('#add').prop('disabled', true);$('#value').prop('disabled', false);">
                    </div>
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="value" class="input-group-text w-100">اصلاح موجودی :</label>
                    </div>
                    <input type="number" step="1" id="value" class="form-control" name="value"
                           value="{{$product->quantity}}"
                           disabled>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="alarm" class="input-group-text w-100">حد آلارم:</label>
                    </div>
                    <input type="number" id="alarm" class="form-control" name="alarm" value="{{$product->alarm}}"
                           required>

                </div>
            </div>

            <div class="col-md-6">
                <div>
                    <span>
                        <input type="radio" id="available" name="available" value="true" checked>
                        <label for="available">موجود</label>
                    </span>
                    <span>
                        <input type="radio" id="notavailable" name="available" value="false">
                        <label id="navailable" for="notavailable">نا موجود</label>
                    </span>
                </div>
            </div>
        </div>
        <br>
        <input type="submit" class="btn btn-success" value="اعمال تغییرات">

    </form>
    <hr>
<span class="btn btn-warning m-2" onclick="$('.deleted').toggle()"><span class="deleted fa fa-check"></span>نمایش حذف شده ها</span>
    <table id="table1" class="stripe">
        <thead>
        <tr>
            <th>تاریخ</th>
            <th>توضیح</th>
            <th>میزان تغییر</th>
            <th>موجودی جدید</th>
            <th>عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($productChanges as $productChange)
            <tr class="{{$productChange->isDeleted?'deleted':''}}">
                <td>{{$productChange->created_at}}</td>
                <td>{{$productChange->desc}}</td>
                <td>{{$productChange->change}}</td>
                <td>{{$productChange->quantity}}</td>
                <td>
                    @if(!$productChange->order_id && !$productChange->isDeleted)
                        <span class="btn btn-danger fa fa-trash-alt" onclick="deleteRecord({{$productChange->id}})"></span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection

@section('files')
    <script>
        $(function () {
            $('input[name="available"]').checkboxradio();
            if (!({{$product->available}}))
                $('#notavailable').click();
            $('#table1').DataTable({
                order: [[0, "desc"]],
                pageLength: 100,
            });
        });
        function deleteRecord(id){
            if(confirm('آیا از حذف رکورد اطمینان دارید؟')){
                // $.get('/productQuantity/delete/'+id);
                window.location.replace('/productQuantity/delete/'+id)

            }
        }
    </script>
    <style>
        #navailable.ui-state-active {
            background: #ff0000;
        }
        .deleted{
            display: none;
        }
    </style>
@endsection
