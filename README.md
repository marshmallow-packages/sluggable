# Generate slugs when saving Eloquent models

This package was created by Spatie. We have forked it so we can add new features we need and so we are not dependend on Spatie before we can upgrade to new Laravel versions.

```php
$model = new EloquentModel();
$model->name = 'activerecord is awesome';
$model->save();

echo $model->slug; // ouputs "activerecord-is-awesome"
```

The slugs are generated with Laravels `Str::slug` method, whereby spaces are converted to '-'.

## Installation

You can install the package via composer:
``` bash
composer require marshmallow/sluggable
```

## Usage

Your Eloquent models should use the `Marshmallow\Sluggable\HasSlug` trait and the `Marshmallow\Sluggable\SlugOptions` class.

The trait contains an abstract method `getSlugOptions()` that you must implement yourself.

Your models' migrations should have a field to save the generated slug to.

Here's an example of how to implement the trait:

```php
namespace App;

use Marshmallow\Sluggable\HasSlug;
use Marshmallow\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;

class YourEloquentModel extends Model
{
    use HasSlug;

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
}
```

With its migration:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYourEloquentModelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('your_eloquent_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug'); // Field name same as your `saveSlugsTo`
            $table->string('name');
            $table->timestamps();
        });
    }
}

```

To use the generated slug in routes, remember to use Laravel's [implicit route model binding](https://laravel.com/docs/5.8/routing#implicit-binding):

```php
namespace App;

use Marshmallow\Sluggable\HasSlug;
use Marshmallow\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;

class YourEloquentModel extends Model
{
    use HasSlug;

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
```

Want to use multiple field as the basis for a slug? No problem!

```php
public function getSlugOptions() : SlugOptions
{
    return SlugOptions::create()
        ->generateSlugsFrom(['first_name', 'last_name'])
        ->saveSlugsTo('slug');
}
```

You can also pass a `callable` to `generateSlugsFrom`.

By default the package will generate unique slugs by appending '-' and a number, to a slug that already exists.

You can disable this behaviour by calling `allowDuplicateSlugs`.

```php
public function getSlugOptions() : SlugOptions
{
    return SlugOptions::create()
        ->generateSlugsFrom('name')
        ->saveSlugsTo('slug')
        ->allowDuplicateSlugs();
}
```

You can also put a maximum size limit on the created slug:

```php
public function getSlugOptions() : SlugOptions
{
    return SlugOptions::create()
        ->generateSlugsFrom('name')
        ->saveSlugsTo('slug')
        ->slugsShouldBeNoLongerThan(50);
}
```

The slug may be slightly longer than the value specified, due to the suffix which is added to make it unique.

You can also use a custom separator by calling `usingSeparator`

```php
public function getSlugOptions() : SlugOptions
{
    return SlugOptions::create()
        ->generateSlugsFrom('name')
        ->saveSlugsTo('slug')
        ->usingSeparator('_');
}
```

To set the language used by `Str::slug` you may call `usingLanguage`

```php
public function getSlugOptions() : SlugOptions
{
    return SlugOptions::create()
        ->generateSlugsFrom('name')
        ->saveSlugsTo('slug')
        ->usingLanguage('nl');
}
```

You can also override the generated slug just by setting it to another value than the generated slug.

```php
$model = EloquentModel:create(['name' => 'my name']); //slug is now "my-name";
$model->slug = 'my-custom-url';
$model->save(); //slug is now "my-custom-url";
```

If you don't want to create the slug when the model is initially created you can set use the `doNotGenerateSlugsOnCreate()` function.

```php
public function getSlugOptions() : SlugOptions
{
    return SlugOptions::create()
        ->generateSlugsFrom('name')
        ->saveSlugsTo('slug')
        ->doNotGenerateSlugsOnCreate();
}
```

Similarly, if you want the slug to be generated again on model updates, call `generateSlugsOnUpdate()`.

```php
public function getSlugOptions() : SlugOptions
{
    return SlugOptions::create()
        ->generateSlugsFrom('name')
        ->saveSlugsTo('slug')
        ->generateSlugsOnUpdate();
}
```

This can be helpful for creating permalinks that don't change until you explicitly want it to.

```php
$model = EloquentModel:create(['name' => 'my name']); //slug is now "my-name";
$model->save();

$model->name = 'changed name';
$model->save(); //slug stays "my-name"
```

If you want to explicitly update the slug on the model you can call `generateSlug()` on your model at any time to make the slug according to your other options. Don't forget to `save()` the model to persist the update to your database.

### Integration with laravel-translatable

You can use this package along with [laravel-translatable](https://github.com/spatie/laravel-translatable) to generate a slug for each locale. Instead of using the `HasSlug` trait, you must use the `HasTranslatableSlug` trait, and add the name of the slug field to the `$translatable` array. For slugs that are generated from a single field _or_ multiple fields, you don't have to change anything else.

```php
namespace App;

use Marshmallow\Sluggable\HasTranslatableSlug;
use Marshmallow\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class YourEloquentModel extends Model
{
    use HasTranslations, HasTranslatableSlug;

    public $translatable = ['name', 'slug'];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
}
```

For slugs that are generated from a callable, you need to instantiate the `SlugOptions` with the `createWithLocales` method. The callable now takes two arguments instead of one. Both the `$model` and the `$locale` are available to generate a slug from.

```php
namespace App;

use Marshmallow\Sluggable\HasTranslatableSlug;
use Marshmallow\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class YourEloquentModel extends Model
{
    use HasTranslations, HasTranslatableSlug;

    public $translatable = ['name', 'slug'];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::createWithLocales(['en', 'nl'])
            ->generateSlugsFrom(function($model, $locale) {
                return "{$locale} {$model->id}";
            })
            ->saveSlugsTo('slug');
    }
}
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Security

If you discover any security related issues, please email stef@marshmallow.dev instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
