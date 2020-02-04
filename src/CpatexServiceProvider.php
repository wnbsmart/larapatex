<?php

namespace wnbsmart\larapatex;

use Illuminate\Support\ServiceProvider;

class CpatexServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            __DIR__.'/../config/cpatex.php' => config_path('cpatex.php')
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cpatex.php', 'cpatex');
        $this->app->bind('cpatex', function() {
            return new CpatexAPI(config('cpatex'));
        });
    }
}
