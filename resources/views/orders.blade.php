@extends('layout.main')

@section('title')
    مشاهده سفارشات
@endsection

@section('files')
    <script src="{{mix('js/orders.js')}}"></script>
    <script src="{{mix('js/html2pdf.bundle.min.js')}}"></script>
@endsection

@section('content')
    @csrf
    <label for="deleted_orders">مشاهده سفارشات حذف شده</label><input type="checkbox" id="deleted_orders"
                                                                     onclick="prepare_data()">
    <br>
    @if($admin)
        <div>
            <div class="form-group input-group col-lg-3">
                <div class="input-group-append">
                    <label for="user" class="input-group-text">سفیر:</label>
                </div>
                <select class="form-control" id="user" onchange="prepare_data()">
                </select>
            </div>
        </div>
    @endif
    <table class="stripe">
    </table>
    @if($admin)
        <button class="btn btn-secondary m-2" onclick="generatePDFs()"> فایل PDF انتخابی ها</button>
    @endif
@endsection
