<nav class="navbar navbar-expand-lg navbar-dark bg-primary p-2" id="navbar">
    <a class="navbar-brand" href="{{route('editUser')}}/{{auth()->user()->id}}"> {{auth()->user()->name}} خوش آمدید
        <span
            class="fa fa-gear"></span></a>
    @if($safir)
        <a class="navbar-brand" href="{{route('DepositList')}}">| اعتبار <span
                dir="ltr">{{number_format(auth()->user()->balance)}}</span> ریال</a>
    @endif
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            @if(auth()->user()->meta('addOrder'))
                <li class="nav-item">
                    <a class="nav-link active" href="{{route('newOrder')}}">ایجاد سفارش</a>
                </li>
            @endif
            <li class="nav-item">
                <a class="nav-link active" href="{{route('listOrders')}}">مشاهده سفارشات</a>
            </li>
            @if($superAdmin || $admin || $safir)
                <li class="nav-item">
                    <a class="nav-link active" href="{{route('CustomerList')}}">مشتریان</a>
                </li>
            @endif
            @if(auth()->user()->meta('warehouse'))
                <li class="nav-item">
                    <a class="nav-link active" href="{{route('productList')}}">محصولات</a>
                </li>
            @endif
            @if(auth()->user()->meta('usersEdit'))
                <li class="nav-item">
                    <a class="nav-link active" href="{{route('manageUsers')}}">مدیریت کاربران</a>
                </li>
            @endif
            @if(auth()->user()->meta('manageSafir'))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button"
                       data-bs-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false">مدیریت سفیران</a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{route('DepositList')}}">واریزی های سفیران</a>
                        <a class="dropdown-item" href="{{route('couponList')}}">مدیریت تخفیف ها</a>
                        <a class="dropdown-item" href="{{route('settings')}}">تنظیمات </a>
                    </div>
                </li>
            @endif
            @if(auth()->user()->meta('counter'))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button"
                       data-bs-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false">حسابداری</a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{route('customersOrderList')}}">بررسی سفارشات</a>
                        <a class="dropdown-item" href="{{route('customersDepositList')}}">بررسی واریزی ها</a>
                    </div>
                </li>
            @endif
            <li class="nav-item">
                <a class="nav-link active" href="{{route('statistic')}}">آمار</a>
            </li>
            @if($safir)
                <li class="nav-item">
                    <a class="nav-link active" href="{{route('DepositList')}}">واریزی ها</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{route('transactions')}}">تراکنش ها</a>
                </li>
            @endif
            @if(auth()->user()->meta('workReport'))
                <li class="nav-item">
                    <a class="nav-link active" href="{{route('reportList')}}">گزارش کار</a>
                </li>
            @endif
            {{--            @if(auth()->user()->meta('addWithdrawal') || auth()->user()->meta('confirmWithdrawal') || auth()->user()->meta('payWithdrawal'))--}}
            {{--                <li class="nav-item">--}}
            {{--                    <a class="nav-link active" href="{{route('WithdrawalList')}}">درخواست پرداخت</a>--}}
            {{--                </li>--}}
            {{--            @endif--}}
            <li class="nav-item">
                <a class="nav-link active" href="{{route('logout')}}">خروج</a>
            </li>
        </ul>

    </div>
</nav>
<div class="my-5"></div>
<style>
    @media (min-width: 991px) {
        #navbar .nav-link {
            border-right: 2px solid white;
        }
    }

    #navbar .nav-link:hover, #navbar .navbar-brand:hover {
        color: black;
    }

    #navbar {
        position: sticky;
        top: 0;
        z-index: 1030;
    }
</style>
