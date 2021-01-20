<?php

namespace Marshmallow\Sluggable\Listeners;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Contracts\Queue\ShouldQueue;

class RunArtisanCommands implements ShouldQueue
{
    public function handle($event)
    {
        if ($event->model->runArtisanCommandsWhen()) {
            $this->runArtisanCommands($event);
        }
    }

    protected function runArtisanCommands($event)
    {
        $commands = $event->model->getArtisanCommands();
        foreach ($commands as $command) {
            Artisan::call($command);
        }
    }
}
