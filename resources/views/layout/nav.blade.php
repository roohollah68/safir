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
                            <i class="fas fa-shopping-cart me-2" aria-hidden="true"></i>سفارشات
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            @if ($User->meta('addOrder'))
                                <a class="dropdown-item" href="{{ route('newOrder') }}">
                                    <i class="fas fa-plus me-2" aria-hidden="true"></i>ایجاد سفارش
                                </a>
                            @endif
                            <a class="dropdown-item" href="{{ route('listOrders') }}">
                                <i class="fas fa-list me-2" aria-hidden="true"></i>مشاهده سفارشات
                            </a>
                        </div>
                    </li>

                    @if ($User->meta('warehouse'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle active px-3" id="productDropdown" role="button"
                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-box me-2" aria-hidden="true"></i>محصولات
                            </a>
                            <div class="dropdown-menu" aria-labelledby="productDropdown">
                                <a class="dropdown-item" href="{{ route('productList') }}">
                                    <i class="fas fa-list me-2" aria-hidden="true"></i>لیست محصولات
                                </a>
                                @if ($User->meta('editWarehouse'))
                                    <a class="dropdown-item" href="{{ route('addProduct') }}">
                                        <i class="fas fa-plus me-2" aria-hidden="true"></i>افزودن محصول جدید
                                    </a>
                                @endif
                                @if ($User->meta('warehouseTransfer'))
                                    <a class="dropdown-item" href="/warehouse/transfer">
                                        <i class="fas fa-exchange-alt me-2" aria-hidden="true"></i>انتقال بین انبارها
                                    </a>
                                @endif
                                <a class="dropdown-item" href="/goods/management">
                                    <i class="fas fa-cogs me-2" aria-hidden="true"></i>مدیریت کالاها
                                </a>
                                <a class="dropdown-item" href="/warehouse/manager">
                                    <i class="fas fa-user-tie me-2" aria-hidden="true"></i>تعیین مسئول انبار
                                </a>
                                <a class="dropdown-item" href="/good/tag">
                                    <i class="fas fa-tag me-2" aria-hidden="true"></i>ثبت شناسه کالا
                                </a>
                                @if ($User->meta('formulation'))
                                    <a class="dropdown-item" href="/formulation/list">
                                        <i class="fas fa-flask me-2" aria-hidden="true"></i>فرمول تولید
                                    </a>
                                @endif
                                <a class="dropdown-item" href="{{ route('productionList') }}">
                                    <i class="fas fa-list me-2" aria-hidden="true"></i>لیست تولید
                                </a>
                            </div>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active px-3" href="#" id="customerDropdown" role="button"
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-users me-2" aria-hidden="true"></i>مشتریان
                        </a>
                        <div class="dropdown-menu" aria-labelledby="customerDropdown">
                            <a class="dropdown-item" href="{{ route('CustomerList') }}">
                                <i class="fas fa-list me-2" aria-hidden="true"></i>لیست مشتریان
                            </a>
                            <a class="dropdown-item" href="{{route('newCustomer')}}">
                                <i class="fas fa-plus me-2" aria-hidden="true"></i>افزودن مشتری جدید
                            </a>
                            <a class="dropdown-item" href="/customerPaymentTracking">
                                <i class="fas fa-money-check me-2" aria-hidden="true"></i>پیگیری پرداختی مشتریان
                            </a>
                            <a class="dropdown-item" href="/blockList">
                                <i class="fas fa-ban me-2" aria-hidden="true"></i>مسدود کردن دسترسی مشتریان
                            </a>
                            @if (!$safir)
                                <a class="dropdown-item" href="/customer/CRM">
                                    <i class="fas fa-ban me-2" aria-hidden="true"></i>CRM
                                </a>
                            @endif
                        </div>
                    </li>

                    @if ($User->meta('usersEdit'))
                        <li class="nav-item">
                            <a class="nav-link px-3" href="{{ route('usersList') }}">
                                <i class="fas fa-user-friends me-2" aria-hidden="true"></i>کاربران
                            </a>
                        </li>
                    @endif

                    @if ($User->meta('manageSafir'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle active px-3" href="#" id="navbarDropdown" role="button"
                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-star me-2" aria-hidden="true"></i>سفیران
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('DepositList') }}">
                                    <i class="fas fa-money-bill-wave me-2" aria-hidden="true"></i>واریزی های سفیران
                                </a>
                                <a class="dropdown-item" href="{{ route('couponList') }}">
                                    <i class="fas fa-ticket-alt me-2" aria-hidden="true"></i>مدیریت تخفیف‌ها
                                </a>
                                <a class="dropdown-item" href="{{ route('settings') }}">
                                    <i class="fas fa-cog me-2" aria-hidden="true"></i>تنظیمات
                                </a>
                            </div>
                        </li>
                    @endif

                    @if ($User->meta('counter'))
                        <li class="nav-item dropdown">
                            <a class="nav-link px-3 dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-calculator me-2" aria-hidden="true"></i>حسابداری
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item py-2" href="{{ route('customersOrderList') }}">
                                        <i class="fas fa-eye me-2" aria-hidden="true"></i>بررسی سفارشات
                                    </a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('customersDepositList') }}">
                                        <i class="fas fa-eye me-2" aria-hidden="true"></i>بررسی واریزی‌ها
                                    </a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('BankTransactionList') }}">
                                        <i class="fas fa-wallet me-2" aria-hidden="true"></i>مدیریت نقدینگی
                                    </a></li>
                                @if($User->meta('cheque'))
                                    <li><a class="dropdown-item py-2" href="{{ route('chequeList') }}">
                                            <i class="fas fa-money-check-alt me-2" aria-hidden="true"></i>لیست چک‌ها
                                        </a></li>
                                @endif
                                <li><a class="dropdown-item py-2" href="{{ route('TaxList') }}">
                                        <i class="fas fa-file-invoice-dollar me-2" aria-hidden="true"></i>سامانه مودیان
                                    </a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('fixed-costs.index') }}">
                                        <i class="fas fa-coins me-2" aria-hidden="true"></i>هزینه‌های ثابت
                                    </a></li>
                            </ul>
                        </li>
                    @endif

                    @if ($safir)
                        <li class="nav-item dropdown">
                            <a class="nav-link px-3 dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-wallet me-2" aria-hidden="true"></i>امور مالی
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item py-2" href="{{ route('DepositList') }}">
                                        <i class="fas fa-money-bill-wave me-2" aria-hidden="true"></i>واریزی‌ها
                                    </a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('transactions') }}">
                                        <i class="fas fa-exchange-alt me-2" aria-hidden="true"></i>تراکنش‌ها
                                    </a></li>
                            </ul>
                        </li>
                    @endif

                    @if ($User->meta(['withdrawal', 'allWithdrawal']))
                        <li class="nav-item">
                            <a class="nav-link px-3" href="{{ route('WithdrawalList') }}">
                                <i class="fas fa-hand-holding-usd me-2" aria-hidden="true"></i>درخواست پرداخت
                            </a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a class="nav-link px-3" href="{{ route('statistic') }}">
                            <i class="fas fa-chart-bar me-2" aria-hidden="true"></i>آمار
                        </a>
                    </li>

                    @if ($User->meta(['workReport', 'addWorkReport']))
                        <li class="nav-item">
                            <a class="nav-link px-3" href="{{ route('reportList') }}">
                                <i class="fas fa-clipboard me-2" aria-hidden="true"></i>گزارش کار
                            </a>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link px-3 dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cogs me-2" aria-hidden="true"></i>اتوماسیون
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item py-2" href="{{ route('processList') }}">
                                    <i class="fas fa-project-diagram me-2" aria-hidden="true"></i>فرآیند‌ها
                                </a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('projectList') }}">
                                    <i class="fas fa-tasks me-2" aria-hidden="true"></i>پروژه‌ها
                                </a></li>
                        </ul>
                    </li>


                    <li class="nav-item d-lg-none mt-3">
                        <a class="nav-link px-3 text-center" href="{{ route('logout') }}">
                            <button class="btn btn-logout w-100">
                                <i class="fas fa-sign-out-alt me-2" aria-hidden="true"></i>خروج
                            </button>
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

    .nav-link i, .dropdown-item i {
        display: none;
    }

    @media (max-width: 991px) {

        .navbar-collapse {
            margin-top: 70px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            background-color: var(--color-bg-nav);
            z-index: 10000;
            overflow-y: auto;
            transition: transform 0.3s ease;
            transform: translateX(-100%);
            padding-top: 200px;
            padding-bottom: 70px;
        }

        .navbar-collapse.show {
            transform: translateX(0);
        }

        .navbar-toggler {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 10001;
        }

        .nav-item.dropdown .dropdown-menu {
            position: absolute;
            width: 100%;
            text-align: center;
            top: 100%;
            margin-top: 0.5rem;
        }

        .nav-item {
            text-align: center;
            padding-bottom: 0.5em;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-item:last-child {
            border-bottom: none;
        }

        .nav-link {
            font-size: 1.4rem;
            padding: 1.5rem;
        }

        .dropdown-item {
            font-size: 1.4rem;
            padding: 1.5rem;
        }

        .navbar-brand {
            font-size: 1rem !important;
        }

        .logo-placeholder {
            width: 35px !important;
            height: 35px !important;
        }

        .nav-link i, .dropdown-item i {
            display: inline-block;
            font-size: 1.6rem;
            margin-right: 0.5rem;
        }

        .nav-link:active, .dropdown-item:active {
            background-color: rgba(255, 255, 255, 0.2);
        }

        body.nav-open {
            overflow: hidden;
        }

        .nav-link:focus, .dropdown-item:focus {
            outline: 2px solid #fff;
            outline-offset: -2px;
        }

        #navbarContent {
            position: absolute !important;
            top: 56px !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            overflow-y: auto !important;
            background-color: var(--bs-primary) !important;
            z-index: 1050;
        }
    }
</style>
