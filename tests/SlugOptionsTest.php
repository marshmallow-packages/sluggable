<?php

namespace Marshmallow\Sluggable\Tests;

use Marshmallow\Sluggable\SlugOptions;

/**
 * @property EloquentBuilder eloquentBuilder
 */
class SlugOptionsTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_has_a_high_dash_as_default_separator()
    {
        $options = new SlugOptions;
        $this->assertEquals($options->slugSeparator, '-');
    }

    /** @test */
    public function it_can_change_the_seperator()
    {
        $options = new SlugOptions;
        $options->usingSeparator('_');
        $this->assertEquals($options->slugSeparator, '_');
    }

    /** @test */
    public function it_has_en_as_default_language()
    {
        $options = new SlugOptions;
        $this->assertEquals($options->slugLanguage, 'en');
    }

    /** @test */
    public function it_makes_unique_slugs_by_default()
    {
        $options = new SlugOptions;
        $this->assertTrue($options->generateUniqueSlugs);
    }

    /** @test */
    public function it_can_be_created_with_locales()
    {
        $options = SlugOptions::createWithLocales(['nl']);
        $this->assertInstanceOf(SlugOptions::class, $options);
    }

    /** @test */
    public function it_can_allow_duplicate_slugs()
    {
        $options = new SlugOptions;
        $this->assertFalse($options->allowDuplicateSlugs()->generateUniqueSlugs);
    }

    /** @test */
    public function it_set_creating_slug_to_false_on_do_not_generate_slugs_on_create_call()
    {
        $options = new SlugOptions;
        $this->assertTrue($options->generateSlugsOnCreate);
        $this->assertFalse($options->doNotGenerateSlugsOnCreate()->generateSlugsOnCreate);
    }

    /** @test */
    public function it_sets_language_and_returns_it_self()
    {
        $options = new SlugOptions;
        $language = $options->slugLanguage;

        $setter = $options->usingLanguage('nl');

        $this->assertInstanceOf(SlugOptions::class, $setter);
        $this->assertEquals($setter->slugLanguage, 'nl');
    }

    /** @test */
    public function it_do_not_generate_slugs_on_update_set_the_settings_to_false()
    {
        $options = new SlugOptions;
        $options->generateSlugsOnUpdate();
        $this->assertTrue($options->generateSlugsOnUpdate);
        $this->assertFalse($options->doNotGenerateSlugsOnUpdate()->generateSlugsOnUpdate);
    }

    /** @test */
    public function it_doesnt_generate_a_new_slug_on_update_by_default()
    {
        $options = new SlugOptions;
        $this->assertFalse($options->generateSlugsOnUpdate);
    }

    /** @test */
    public function it_can_update_the_generate_slugs_on_update_setting()
    {
        $options = new SlugOptions;
        $options = $options->generateSlugsOnUpdate();
        $this->assertTrue($options->generateSlugsOnUpdate);
    }
}
