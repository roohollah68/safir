@extends('layout.main')

@section('title')
@endsection

@section('files')
@endsection

@section('content')

   @include('orders/invoice')
<style>
    .fs-40 ,.fs-35{
        font-size: 20px;
    }


    #invoice1{
        display: contents;
    }

    #title1{
        font-size: 30px!important;
        margin: 0 150px!important;
    }
    #title2 , #title3{
        font-size: 30px!important;
        margin: 0 350px!important;
    }
    #section2{
        height: 220px!important;
    }
    #section3{
        height: 290px!important;
    }
    #address1{
        height: 70px!important;
    }
    #invoice-content th, #invoice-content .normal,#invoice-content td, #invoice-content .smaller, #acount  {
        font-size: 25px!important;
    }

</style>
@endsection
