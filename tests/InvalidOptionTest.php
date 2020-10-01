<?php

namespace Marshmallow\Sluggable\Tests;

use Illuminate\Support\Facades\Config;
use Marshmallow\Sluggable\SlugOptions;
use Marshmallow\Sluggable\InvalidOption;
use Illuminate\Database\Eloquent\Builder;
use Marshmallow\Sluggable\Tests\Models\Post;
use Marshmallow\Sluggable\Tests\Models\User;

/**
 * @property EloquentBuilder eloquentBuilder
 */
class InvalidOptionTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_work_when_setting_from_variables()
    {
    	Config::set('sluggable.slug_from', 'name');
    	Config::set('sluggable.slug_to', 'slug');

    	Post::factory()->create([
    		'name' => 'Stef'
    	]);
        $this->assertEquals('stef', Post::first()->slug);
    }

    /** @test */
    public function it_throws_an_missing_slug_field_when_slug_is_not_set()
    {
    	Config::set('sluggable.slug_from', 'name');
    	Config::set('sluggable.slug_to', '');

    	$this->expectException(InvalidOption::class);
    	Post::factory()->create();
    }

    /** @test */
    public function missing_from_field_returns_an_exception()
    {
    	$exception = InvalidOption::missingFromField();
    	$this->assertTrue(($exception instanceof \Exception));
    }

    /** @test */
    public function missing_slug_field_returns_an_exception()
    {
    	$exception = InvalidOption::missingSlugField();
    	$this->assertTrue(($exception instanceof \Exception));
    }

    /** @test */
    public function invalid_maximum_length_returns_an_exception()
    {
    	$exception = InvalidOption::invalidMaximumLength();
    	$this->assertTrue(($exception instanceof \Exception));
    }
}