<?php

namespace App\Providers;

use App\Models\BingoUser;
use App\Observers\BingoUserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observer
        BingoUser::observe(BingoUserObserver::class);
        if (app()->environment('local')) {
            \URL::forceScheme('https');
        }
    }
}
