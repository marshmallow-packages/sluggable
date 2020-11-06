<?php

namespace Marshmallow\Sluggable\Tests\Models;

use Marshmallow\Sluggable\SlugOptions;

class UserWithoutFrom extends User
{
    public function getSlugOptions() : SlugOptions
    {
        $options = SlugOptions::create()
                ->generateSlugsFrom('name')
                ->saveSlugsTo('slug');

        $options->generateSlugFrom = [];

        return $options;
    }
}
