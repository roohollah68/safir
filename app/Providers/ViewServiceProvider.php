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
//                $user = User::with('userMetas')->find(auth()->user()->id);
                $user = auth()->user();
                $view->with('User', $user);
                $view->with('superAdmin', $user->superAdmin());
                $view->with('admin', $user->admin());
                $view->with('safir', $user->safir());
            }
            return $view;
        });

    }
}
