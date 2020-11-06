<?php

namespace Marshmallow\Sluggable\Tests\Models;

use Marshmallow\Sluggable\HasSlug;
use Marshmallow\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Marshmallow\Sluggable\Tests\Database\Factories\PostFactory;

class Post extends Model
{
    use HasSlug;
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title', 'content', 'is_published',
    ];

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

    protected static function newFactory()
    {
        return PostFactory::new();
    }
}
