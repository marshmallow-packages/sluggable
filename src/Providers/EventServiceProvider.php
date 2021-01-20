<?php

namespace Marshmallow\Sluggable\Providers;

use Marshmallow\Sluggable\Events\SlugWasCreated;
use Marshmallow\Sluggable\Events\SlugWasDeleted;
use Marshmallow\Sluggable\Events\SlugHasBeenChanged;
use Marshmallow\Sluggable\Listeners\RunArtisanCommands;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SlugWasCreated::class => [
            RunArtisanCommands::class,
        ],
        SlugWasDeleted::class => [
            RunArtisanCommands::class,
        ],
        SlugHasBeenChanged::class => [
            RunArtisanCommands::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
