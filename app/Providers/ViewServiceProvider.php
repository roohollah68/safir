<?php

namespace App\Providers;

use App\Models\Deposit;
use App\Models\Order;
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
        View::composer(['orders','layout.nav','depositList','addEditOrder'], function ($view) {
            $view->with('admin', auth()->user()->role =='admin');
        });
        View::composer(['layout.nav'], function ($view) {
            $view->with('balance',auth()->user()->balance)->with('depositCount', Deposit::where('confirmed' , false)->get()->count())
                ->with('userCount',User::where('verified',false)->get()->count())->with('orderCount',Order::where('state','0')->get()->count());
        });
    }
}
