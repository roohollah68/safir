@extends('layout.main')

@section('title')
    مشاهده سفارشات
@endsection

@section('files')
    @include('orders.js_css')
    <script src="/js/dom-to-image.min.js"></script>
@endsection

@section('content')

    <label for="deleted_orders">مشاهده سفارشات حذف شده</label><input type="checkbox" id="deleted_orders"
                                                                     onclick="deleted = $('#deleted_orders').prop('checked');prepare_data()">
    <br>
    @if($admin)
        <div>
            <div class="form-group input-group col-lg-3">
                <div class="input-group-append">
                    <label for="user" class="input-group-text">سفیر:</label>
                </div>
                <select class="form-control" id="user" onchange="user = $('#user option:selected').val() || 'all';prepare_data()">
                    <option value="all" selected>همه</option>
                    @foreach($users as $id=>$user)
                        <option value="{{$id}}">{{$user->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif
    <table id="main-table" class="stripe">
    </table>
    @if($admin)
        <button class="btn btn-secondary m-2" onclick="generatePDFs()"> فایل PDF انتخابی ها</button>
        <div id="invoice-wrapper" class="d-none"></div>
    @endif
@endsection
