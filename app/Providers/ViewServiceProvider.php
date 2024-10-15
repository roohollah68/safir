<?php

namespace App\Providers;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            if (auth()->user()) {
                $view->with('superAdmin', auth()->user()->superAdmin());
                $view->with('admin', auth()->user()->admin());
                $view->with('safir', auth()->user()->safir());
                $view->with('print', auth()->user()->print());
                $view->with('warehouse', auth()->user()->warehouse());
                $view->with('payMethods', config('payMethods'));
                $view->with('sendMethods', config('sendMethods'));
            }
            return $view;
        });

    }
}
