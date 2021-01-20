<?php

namespace Marshmallow\Sluggable\Tests\Models;

use Marshmallow\Sluggable\HasSlug;
use Marshmallow\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasSlug;

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
}
