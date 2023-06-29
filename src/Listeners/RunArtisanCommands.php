<?php

namespace Marshmallow\Sluggable\Listeners;

use Illuminate\Support\Facades\Artisan;

class RunArtisanCommands
{
    public function handle($event)
    {
        if ($event->model->runArtisanCommandsWhen()) {
            $this->runArtisanCommands($event);
        }
    }

    protected function runArtisanCommands($event)
    {
        if (method_exists($event->model, 'runSluggableArtisanCommands')) {
            $event->model->runSluggableArtisanCommands();
        } else {
            $commands = $event->model->getArtisanCommands();
            foreach ($commands as $command) {
                Artisan::call($command);
            }
        }
    }
}
