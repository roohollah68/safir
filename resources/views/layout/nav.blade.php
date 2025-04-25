<nav class="navbar navbar-expand-lg navbar-dark bg-primary p-2 shadow-sm" id="navbar">
    <div class="container-fluid me-3 ms-3">
        <div class="d-flex align-items-center">
            <a href="{{ route('editUser') }}/{{ $User->id }}">
                <img src="{{ asset('logo-300x300.webp') }}" alt="Logo" class="logo-placeholder me-3"
                     style="width: 40px; height: 40px; object-fit: cover;">
            </a>

            <a class="navbar-brand fw-bold" style="font-size: 1.25em" href="{{ route('editUser') }}/{{ $User->id }}">
                {{ $User->name }}
            </a>

            @if ($safir)
                <a class="fw-bold d-none d-lg-block btn btn-balance me-4 outline-none"
                   href="{{ route('DepositList') }}">
                    اعتبار: <span dir="ltr"> {{ number_format($User->balance) }} </span>&nbsp;ریال
                </a>
            @endif
        </div>

        <div class="d-flex align-items-center gap-2">
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse justify-content-center d-flex" id="navbarSupportedContent">
            <ul class="navbar-nav mb-2 mb-lg-0 w-100">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-center w-100">

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active px-3" href="#" id="navbarDropdown" role="button"
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            سفارشات
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            @if ($User->meta('addOrder'))
                                <a class="dropdown-item" href="{{ route('newOrder') }}">ایجاد سفارش</a>
                            @endif
                            <a class="dropdown-item" href="{{ route('listOrders') }}">مشاهده سفارشات</a>
                        </div>
                    </li>

                    @if ($User->meta('warehouse'))
                        <li class="nav-item"><a class="nav-link px-3" href="{{ route('productList') }}">محصولات</a></li>
                    @endif

                    <li class="nav-item"><a class="nav-link px-3" href="{{ route('CustomerList') }}">مشتریان</a></li>

                    @if ($User->meta('usersEdit'))
                        <li class="nav-item"><a class="nav-link px-3" href="{{ route('usersList') }}">کاربران</a></li>
                    @endif

                    @if ($User->meta('manageSafir'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle active px-3" href="#" id="navbarDropdown" role="button"
                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">سفیران</a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('DepositList') }}">واریزی های سفیران</a>
                                <a class="dropdown-item" href="{{ route('couponList') }}">مدیریت تخفیف‌ها</a>
                                <a class="dropdown-item" href="{{ route('settings') }}">تنظیمات </a>
                            </div>
                        </li>
                    @endif

                    @if ($User->meta('counter'))
                        <li class="nav-item dropdown">
                            <a class="nav-link px-3 dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                حسابداری
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item py-2" href="{{ route('customersOrderList') }}">بررسی
                                        سفارشات</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('customersDepositList') }}">بررسی
                                        واریزی‌ها</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('BankTransactionList') }}">مدیریت
                                        نقدینگی</a></li>
                                @if($User->meta('cheque'))
                                    <li><a class="dropdown-item py-2" href="{{ route('chequeList') }}">لیست چک‌ها</a>
                                    </li>
                                @endif
                                <li><a class="dropdown-item py-2" href="{{ route('TaxList') }}">سامانه
                                        مودیان</a></li>
                            </ul>
                        </li>
                    @endif

                    @if ($safir)
                        <li class="nav-item dropdown">
                            <a class="nav-link px-3 dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                امور مالی
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item py-2" href="{{ route('DepositList') }}">واریزی‌ها</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('transactions') }}">تراکنش‌ها</a></li>
                            </ul>
                        </li>
                    @endif

                    @if ($User->meta(['withdrawal', 'allWithdrawal']))
                        <li class="nav-item">
                            <a class="nav-link px-3" href="{{ route('WithdrawalList') }}">
                                درخواست پرداخت
                            </a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a class="nav-link px-3" href="{{ route('statistic') }}">
                            آمار
                        </a>
                    </li>

                    @if ($User->meta(['workReport', 'addWorkReport']))
                        <li class="nav-item">
                            <a class="nav-link px-3" href="{{ route('reportList') }}">
                                گزارش کار
                            </a>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link px-3 dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            اتوماسیون
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item py-2" href="{{ route('processList') }}">فرآیند‌ها</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('projectList') }}">پروژه‌ها</a></li>
                        </ul>
                    </li>

                    <!-- Mobile Logout -->
                    <li class="nav-item d-lg-none mt-3">
                        <a class="nav-link px-3 text-center" href="{{ route('logout') }}">
                            <button class="btn btn-logout w-100">خروج</button>
                        </a>
                    </li>
                </div>
            </ul>
        </div>

        <!-- Desktop Logout -->
        <a class="btn btn-logout px-3 d-none d-lg-block" href="{{ route('logout') }}">
            خروج
        </a>
    </div>
</nav>

<style>
    :root {
        --color-teal-blue: #336666;
        --color-turquoise-light: #64D1D2;
        --color-deep-cyan: #14697B;
        --color-muted-aqua: #5BB6B7;
        --color-dark-teal: #205B5C;
        --color-bg-nav: #40b6f5;
        --color-rose-red: #821d30;
    }

    .btn-logout {
        color: white;
        background: var(--color-rose-red);
        transition: transform 0.3s ease;
    }

    .btn-logout:hover {
        color: white;
        transform: scale(1.09);
    }

    .btn-balance {
        color: var(--color-dark-teal) !important;
        outline: var(--color-dark-teal) solid 1px;
        padding: 0.35rem 0.6rem;
        font-size: 0.9rem;
    }

    #navbar {
        height: 70px;
        position: sticky;
        top: 0;
        z-index: 1030;
        transition: all 0.3s ease;
        background: var(--color-bg-nav) !important;
    }

    .nav-link, .navbar-brand {
        transition: all 0.2s ease;
        position: relative;
        color: white !important;
        font-size: 1.05rem;
    }

    .nav-link:hover,
    .nav-link:focus {
        color: white !important;
        transform: translateY(-2px);
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 2px;
        background: var(--color-turquoise-light);
        transition: all 0.3s ease;
    }

    .nav-link:hover::after {
        width: 60%;
        left: 20%;
    }

    .dropdown-item {
        transition: all 0.2s ease;
        text-align: center;
    }

    .dropdown-item:hover {
        background-color: var(--color-muted-aqua);
        color: white !important;
    }

    .nav-item.dropdown:hover .dropdown-menu {
        display: block;
    }

    .nav-item.dropdown .dropdown-toggle::after {
        display: inline-block;
        margin-right: 0.255em;
        vertical-align: 0.255em;
        content: "";
        border-top: 0.2em solid;
        border-right: 0.2em solid transparent;
        border-bottom: 0;
        border-left: 0.2em solid transparent;
        background: var(--color-bg-nav);
    }

    .nav-item.dropdown .dropdown-menu {
        left: 50%;
        right: auto;
        transform: translateX(-50%);
        text-align: center;
        box-shadow: gray 0px 1px 2px 0px;
    }

    @media (max-width: 991px) {

        .navbar-collapse {
            margin-top: 70px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background-color: var(--color-bg-nav);
            z-index: 10000;
            overflow-y: auto;
            transition: transform 0.3s ease;
            transform: translateX(-100%);
        }

        .navbar-collapse.show {
            transform: translateX(0);
        }

        .navbar-toggler {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1050;
        }

        .nav-item.dropdown .dropdown-menu {
            transform: none;
            width: 100%;
            text-align: center;
        }

        .nav-item {
            text-align: center;
            padding-bottom: 0.5em;
        }

        .navbar-brand {
            font-size: 1rem !important;
        }

        .logo-placeholder {
            width: 35px !important;
            height: 35px !important;
        }

    }
</style>
