<?php

namespace Marshmallow\Sluggable;

use Illuminate\Support\ServiceProvider;
use Marshmallow\Sluggable\Providers\EventServiceProvider;

class SluggableServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
