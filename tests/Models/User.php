<?php

namespace Marshmallow\Sluggable\Tests\Models;

use Marshmallow\Sluggable\HasSlug;
use Marshmallow\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Marshmallow\Sluggable\Tests\Database\Factories\UserFactory;

class User extends Model
{
	use HasSlug;
	use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    /**
     * Get the posts for the user.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Set the options for generating the slug.
     */
    public function setSlugOptions(SlugOptions $slugOptions): self
    {
        $this->slugOptions = $slugOptions;
        return $this;
    }

    protected static function newFactory()
	{
	    return UserFactory::new();
	}

	// public function getSlugOptions() : SlugOptions
 //    {
 //    	$options = SlugOptions::create()
	//             ->generateSlugsFrom('name')
	//             ->saveSlugsTo('slug');

	//     $options->generateSlugFrom = [];
	//     return $options;
 //    }
}
