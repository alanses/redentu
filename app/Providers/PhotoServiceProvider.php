<?php

namespace App\Providers;

use App\Services\Photo\PhotoService;
use Illuminate\Support\ServiceProvider;

class PhotoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PhotoService::class, function ($app) {
            return new PhotoService();
        });
    }
}
