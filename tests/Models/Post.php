<?php

namespace Marshmallow\Sluggable\Tests\Models;

use Marshmallow\Sluggable\HasSlug;
use Marshmallow\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasSlug;

    protected $guarded = [];

    public $timestamps = false;

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(config('sluggable.slug_from'))
            ->saveSlugsTo(config('sluggable.slug_to'))
            ->slugsShouldBeNoLongerThan(
                (config('sluggable.length')) ? config('sluggable.length') : 255
            );
    }

    /**
     * Get the user that owns the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
