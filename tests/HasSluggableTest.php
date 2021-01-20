<?php

namespace Marshmallow\Sluggable\Tests;

use Illuminate\Support\Str;
use Marshmallow\Sluggable\InvalidOption;
use Marshmallow\Sluggable\Tests\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Marshmallow\Sluggable\Tests\Models\UserSoftDeletes;
use Marshmallow\Sluggable\Tests\Models\UserWithoutFrom;
use Marshmallow\Sluggable\Tests\Models\UserWithoutLength;

class HasSluggableTest extends TestCase
{
    /** @test */
    public function it_will_save_a_slug_when_saving_a_model()
    {
        $model = User::create(['name' => 'this is a test']);

        $this->assertEquals('this-is-a-test', $model->slug);
    }

    /** @test */
    public function it_can_handle_null_values_when_creating_slugs()
    {
        $model = User::create(['name' => null]);

        $this->assertEquals('-1', $model->slug);
    }

    /** @test */
    public function it_will_not_change_the_slug_when_the_source_field_is_not_changed()
    {
        $model = User::create(['name' => 'this is a test']);

        $model->other_field = 'otherValue';
        $model->save();

        $this->assertEquals('this-is-a-test', $model->slug);
    }

    /** @test */
    public function it_will_use_the_source_field_if_the_slug_field_is_empty()
    {
        $model = User::create(['name' => 'this is a test']);

        $model->slug = null;
        $model->save();

        $this->assertEquals('this-is-a-test', $model->slug);
    }

    /** @test */
    public function it_will_not_update_the_slug_when_the_source_field_is_changed()
    {
        $model = User::create(['name' => 'this is a test']);

        $model->name = 'this is another test';
        $model->save();

        $this->assertEquals('this-is-a-test', $model->slug);
    }

    /** @test */
    public function it_will_save_a_unique_slug_by_default()
    {
        User::create(['name' => 'this is a test']);

        foreach (range(1, 10) as $i) {
            $model = User::create(['name' => 'this is a test']);
            $this->assertEquals("this-is-a-test-{$i}", $model->slug);
        }
    }

    /** @test */
    public function it_can_handle_empty_source_fields()
    {
        foreach (range(1, 10) as $i) {
            $model = User::create(['name' => '']);
            $this->assertEquals("-{$i}", $model->slug);
        }
    }

    /** @test */
    public function it_can_generate_slugs_from_multiple_source_fields()
    {
        $model = new User;
        $model->setSlugOptions(
            $model->getSlugOptions()->generateSlugsFrom(['name', 'other_field'])
        );

        $model->name = 'this is a test';
        $model->other_field = 'this is another field';
        $model->save();

        $this->assertEquals('this-is-a-test-this-is-another-field', $model->slug);
    }

    /** @test */
    public function it_can_generate_slugs_from_a_callable()
    {
        $model = new User;
        $model->setSlugOptions(
            $model->getSlugOptions()->generateSlugsFrom(function (User $model): string {
                return 'foo-'.Str::slug($model->name);
            })
        );

        $model->name = 'this is a test';
        $model->save();

        $this->assertEquals('foo-this-is-a-test', $model->slug);
    }

    /** @test */
    public function it_can_generate_duplicate_slugs()
    {
        foreach (range(1, 10) as $i) {
            $model = new User;
            $model->setSlugOptions(
                $model->getSlugOptions()->allowDuplicateSlugs()
            );

            $model->name = 'this is a test';
            $model->save();

            $this->assertEquals('this-is-a-test', $model->slug);
        }
    }

    /** @test */
    public function it_can_generate_slugs_with_a_maximum_length()
    {
        $model = new User;
        $model->setSlugOptions(
            $model->getSlugOptions()->slugsShouldBeNoLongerThan(5)
        );

        $model->name = '123456789';
        $model->save();

        $this->assertEquals('12345', $model->slug);
    }

    /**
     * @test
     * @dataProvider weirdCharacterProvider
     */
    public function it_can_handle_weird_characters_when_generating_the_slug(string $weirdCharacter, string $normalCharacter)
    {
        $model = User::create(['name' => $weirdCharacter]);

        $this->assertEquals($normalCharacter, $model->slug);
    }

    public function weirdCharacterProvider()
    {
        return [
            ['é', 'e'],
            ['è', 'e'],
            ['à', 'a'],
            ['a€', 'aeur'],
            ['ß', 'ss'],
            ['a/ ', 'a'],
        ];
    }

    /**
     * @test
     */
    public function it_can_handle_multibytes_characters_cutting_when_generating_the_slug()
    {
        $model = User::create(['name' => 'là']);
        $model->setSlugOptions($model->getSlugOptions()->slugsShouldBeNoLongerThan(2));
        $model->generateSlug();

        $this->assertEquals('la', $model->slug);
    }

    /** @test */
    public function it_can_handle_overwrites_when_updating_a_model()
    {
        $model = User::create(['name' => 'this is a test']);

        $model->slug = 'this-is-an-url';
        $model->save();

        $this->assertEquals('this-is-an-url', $model->slug);
    }

    /** @test */
    public function it_can_handle_duplicates_when_overwriting_a_slug()
    {
        $model = User::create(['name' => 'this is a test']);
        User::create(['name' => 'this is an other']);
        $model = $model->setSlugOptions(
            $model->getSlugOptions()->generateSlugsOnUpdate()
        );
        $model->slug = 'this-is-an-other';
        $model->save();

        $this->assertEquals('this-is-an-other-1', $model->slug);
    }

    /** @test */
    public function it_has_an_method_that_prevents_a_slug_being_generated_on_creation()
    {
        $model = new User;
        $model->setSlugOptions(
            $model->getSlugOptions()->doNotGenerateSlugsOnCreate()
        );

        $model->name = 'this is a test';
        $model->save();

        $this->assertEquals(null, $model->slug);
    }

    /** @test */
    public function it_has_an_method_that_prevents_a_slug_being_generated_on_update()
    {
        $model = new User;
        $model->setSlugOptions(
            $model->getSlugOptions()->doNotGenerateSlugsOnUpdate()
        );

        $model->name = 'this is a test';
        $model->save();

        $model->name = 'this is another test';
        $model->save();

        $this->assertEquals('this-is-a-test', $model->slug);
    }

    /** @test */
    public function it_will_use_separator_option_for_slug_generation()
    {
        $model = new User;
        $model->setSlugOptions($model->getSlugOptions()->usingSeparator('_'));

        $model->name = 'this is a separator test';
        $model->save();

        $this->assertEquals('this_is_a_separator_test', $model->slug);
    }

    /** @test */
    public function it_will_use_language_option_for_slug_generation()
    {
        $model = new User;
        $model->setSlugOptions($model->getSlugOptions()->usingLanguage('nl'));

        $this->assertEquals('nl', $model->getSlugOptions()->slugLanguage);
    }

    /** @test */
    public function it_will_save_a_unique_slug_by_default_even_when_soft_deletes_are_on()
    {
        UserSoftDeletes::create(['name' => 'this is a test', 'deleted_at' => date('Y-m-d h:i:s')]);

        foreach (range(1, 10) as $i) {
            $model = UserSoftDeletes::create(['name' => 'this is a test']);
            $this->assertEquals("this-is-a-test-{$i}", $model->slug);
        }
    }

    /** @test */
    public function it_throwns_an_error_when_generate_slug_from_is_not_an_array()
    {
        $this->expectException(InvalidOption::class);

        $model = new UserWithoutFrom;
        $model->create([
            'name' => 'Stef',
        ]);
    }

    /** @test */
    public function it_doesnt_allow_a_length_of_null()
    {
        $this->expectException(InvalidOption::class);

        $model = new UserWithoutLength;
        $model->create([
            'name' => 'Stef',
        ]);
    }
}
