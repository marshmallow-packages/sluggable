<?php

namespace Marshmallow\Sluggable\Tests\Models;

use Marshmallow\Sluggable\SlugOptions;
use Marshmallow\Sluggable\Tests\Models\User;

class UserWithoutLength extends User
{
	public function getSlugOptions() : SlugOptions
	{
		$options = SlugOptions::create()
				->generateSlugsFrom('name')
	            ->saveSlugsTo('slug')
				->slugsShouldBeNoLongerThan(0);
		return $options;
	}
}
