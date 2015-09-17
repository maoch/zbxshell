<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ZbxTopServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\Contracts\ApmSystemInterface',
            'App\Services\ZabbixSystem'
        );
        $this->app->bind(
            'App\Contracts\CmdbSystemInterface',
            'App\Services\ItopSystem'
        );
    }

}
