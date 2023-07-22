<?php

namespace App\Providers;

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
            if (auth()->user())
                $view->with('admin', auth()->user()->role == 'admin');
            else
                return $view;
        });

        View::composer(['layout.nav'], function ($view) {

            $controller = new Controller();
            $id = Order::latest()->first()->id - $controller->settings()->loadOrders;
            $view->with('balance', auth()->user()->balance)->with('depositCount', Deposit::where('confirmed', false)->get()->count())
                ->with('userCount', User::where('verified', false)->get()->count())->with('orderCount',Order::where('state', '0')->where('id', '>',$id)->get()->count());
        });
    }
}
