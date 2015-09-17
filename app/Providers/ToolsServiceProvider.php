<?php namespace App\Providers;

use App;
use App\Tools\Pagination;
use Illuminate\Support\ServiceProvider;

class ToolsServiceProvider extends ServiceProvider
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
        App::bind('pagination', function () {
            return new Pagination();
        });

    }

}
