<div class="topnav" id="myTopnav">
    <a href="javascript:void(0);" class="icon" onclick="myFunction()">
        <i class="fa fa-bars"></i>
    </a>
    @if(!$admin)
        <a href="{{route('DepositList')}}" class="active">اعتبار <span dir="ltr">{{$balance}}</span>  تومن</a>
    @endif
    <a href="{{route('newOrder')}}">ایجاد سفارش</a>
    <a href="{{route('listOrders')}}">مشاهده سفارشات @if($admin) ({{$orderCount}}) @endif</a>
    <a href="{{route('CustomerList')}}">مشتریان </a>
    <a href="{{route('DepositList')}}">واریزی ها @if($admin) ({{$depositCount}}) @endif</a>
    @if($admin)
        <a href="{{route('manageUsers')}}">مدیریت کاربران ({{$userCount}}) </a>
        <a href="{{route('couponList')}}">تخفیف ها</a>
        <a href="{{route('productList')}}">محصولات</a>
        <a href="{{route('settings')}}">تنظیمات</a>
    @else
        <a href="{{route('editUser')}}">ویرایش حساب کاربری</a>
    @endif
    <a href="{{route('logout')}}">خروج</a>

</div>

<script>
    function myFunction() {
        var x = document.getElementById("myTopnav");
        if (x.className === "topnav") {
            x.className += " responsive";
        } else {
            x.className = "topnav";
        }
    }
</script>

<style>
    body {
        margin: 0;
    }

    .topnav {
        overflow: hidden;
        background-color: #333;
    }

    .topnav a {
        float: right;
        display: block;
        color: #f2f2f2;
        text-align: center;
        padding: 14px 16px;
        text-decoration: none;
        font-size: 17px;
    }

    .topnav a:hover {
        background-color: #ddd;
        color: black;
    }

    .topnav a.active {
        background-color: #04AA6D;
        color: white;
    }

    .topnav .icon {
        display: none;
    }

    @media screen and (max-width: 600px) {
        .topnav a:not(:first-child,.active) {
            display: none;
        }

        .topnav a.icon {
            float: right;
            display: block;
        }
    }

    @media screen and (max-width: 600px) {
        .topnav.responsive {
            position: relative;
        }

        .topnav.responsive .icon {
            position: absolute;
            right: 0;
            top: 0;
        }

        .topnav.responsive a {
            float: none;
            display: block;
            text-align: right;
        }

        .topnav.responsive a.active {
            text-align: left;
        }
    }
</style>
